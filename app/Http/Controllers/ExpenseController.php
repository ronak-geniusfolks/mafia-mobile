<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        // Validate (year optional, month can be 1–12 or full month name)
        $request->validate([
            'year'  => ['nullable','integer','min:1970','max:'.now()->year],
            'month' => ['nullable', function ($attr, $value, $fail) {
                if (is_null($value)) return;
                if (is_numeric($value) && (int)$value >= 1 && (int)$value <= 12) return;
                try { Carbon::parse("1 $value"); } catch (\Exception $e) { $fail('Invalid month.'); }
            }],
        ]);

        // Defaults to current month/year if not provided
        $yearInput  = (int)($request->input('year') ?? now()->year);
        $monthInput = $request->input('month') ?? now()->format('F');

        // Normalize month to number (1–12)
        $monthNum = is_numeric($monthInput)
            ? (int)$monthInput
            : Carbon::parse("1 $monthInput")->month;

        // Calculate range once
        $start = Carbon::createFromDate($yearInput, $monthNum, 1)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        // Base query (DRY)
        $base = Expense::query()
            ->where('deleted', 0)                 // consider SoftDeletes long-term
            ->whereBetween('entrydate', [$start, $end]);

        // Reuse the base query
        $totalExpenseAmount = (clone $base)->sum('amount');

        // Prefer pagination for large datasets
        $expenses = (clone $base)
            ->orderByDesc('entrydate')
            ->orderByDesc('id')                   // stable tie-breaker
            ->paginate(50)                        // ->get() if you need all
            ->withQueryString();

        return view('expenses.index', [
            'expenses'           => $expenses,
            'year'               => $yearInput,
            'month'              => $start->format('F'),
            'totalExpenseAmount' => number_format($totalExpenseAmount, 2, '.', ''),
        ]);
    }

    public function addExpense()
    {
        return view('expenses.add');
    }

    public function saveExpense(Request $request)
    {
        // dd($request);
        $request->validate([
            'amount' => 'required|numeric',
            'entrydate' => 'required|date',
        ]);
        $expense = new Expense;
        $expense->amount = $request->amount;
        $expense->entrydate = $request->entrydate;
        $expense->expense_category = $request->expense_category;
        $expense->description = $request->description;
        $expense->added_by = auth()->user()->id;
        $expense->save();

        return redirect()->route('expenses')->withStatus('Expense Added Successfully..');
    }

    public function editExpense($id)
    {
        $expense = Expense::findOrFail($id);

        return view('expenses.expenseedit', ['expense' => $expense]);
    }

    public function updateExpense(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'entrydate' => 'required|date',
        ]);
        $expense = Expense::findOrFail($id);
        $updateData = $request->all();
        $expense->update($updateData);

        return redirect()->route('expenses')->withStatus('Expense Updated Successfully..');
    }

    public function deleteExpense($id)
    {
        Expense::findOrFail($id)->update([
            'deleted' => 1,
        ]);

        return redirect()->route('expenses')->withStatus('Expense Deleted Successfully..');
    }
}
