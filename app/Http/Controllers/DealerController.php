<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreDealerRequest;
use App\Http\Requests\UpdateDealerRequest;
use App\Models\Bill;
use App\Models\Dealer;
use App\Models\DealerPayment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DealerController extends Controller
{
    /**
     * Display a listing of dealers.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Dealer::query();

            return DataTables::of($data)
                ->addIndexColumn()
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('dealers.index');
    }

    /**
     * Store a newly created dealer.
     */
    public function store(StoreDealerRequest $request)
    {
        try {
            $dealer = Dealer::create($request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Dealer created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the dealer.',
            ], 500);
        }
    }

    /**
     * Update the specified dealer.
     */
    public function update(UpdateDealerRequest $request, $id)
    {
        try {
            $dealer = Dealer::findOrFail($id);
            $dealer->update($request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Dealer updated successfully!',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Dealer not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the dealer.',
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified dealer.
     */
    public function edit($id)
    {
        try {
            $dealer = Dealer::findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $dealer->toArray(),
                'message' => 'Dealer fetched successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Dealer not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'An error occurred while fetching the dealer.',
            ], 500);
        }
    }

    /**
     * Remove the specified dealer from storage.
     */
    public function destroy($id)
    {
        try {
            $dealer = Dealer::findOrFail($id);

            // Check if dealer has any connected data
            $hasBills = $dealer->bills()->exists();
            $hasPayments = $dealer->payments()->exists();

            if ($hasBills || $hasPayments) {
                $messages = [];
                if ($hasBills) {
                    $messages[] = 'bills';
                }
                if ($hasPayments) {
                    $messages[] = 'payments';
                }

                $message = 'Cannot delete dealer. This dealer has associated '.implode(' and ', $messages).'. Please remove all associated data before deleting.';

                return response()->json([
                    'status' => false,
                    'message' => $message,
                ], 200);
            }

            $dealer->delete();

            return response()->json([
                'status' => true,
                'message' => 'Dealer deleted successfully!',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Dealer not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete dealer',
            ], 500);
        }
    }

    /**
     * Generate dealer transaction report.
     */
    public function report(Request $request, $id)
    {
        try {
            $dealer = Dealer::findOrFail($id);

            $dateFrom = $request->input('from', Carbon::now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->input('to', Carbon::now()->format('Y-m-d'));

            // Get bills within date range (DEBIT entries) - exclude soft deleted
            $bills = Bill::where('dealer_id', $dealer->id)
                ->whereBetween('bill_date', [$dateFrom, $dateTo])
                ->where('payment_type', 'credit')
                ->with(['items' => function($query) {
                    $query->withoutTrashed();
                }])
                ->orderBy('bill_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            // Get payments within date range (CREDIT entries) - exclude soft deleted
            $payments = DealerPayment::where('dealer_id', $dealer->id)
                ->whereBetween('payment_date', [$dateFrom, $dateTo])
                ->orderBy('payment_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            // Combine transactions
            $transactions = [];

            // Add bills as DEBIT entries
            foreach ($bills as $bill) {
                $notes = [];
                foreach ($bill->items as $item) {
                    $notes[] = $item->item_description;
                }
                $note = !empty($notes) ? implode(', ', $notes) : 'Bill #' . $bill->bill_no;
                $note .= ' (' . $dealer->name . ')';

                $transactions[] = [
                    'date' => $bill->bill_date,
                    'type' => 'debit',
                    'amount' => $bill->credit_amount,
                    'note' => $note,
                ];
            }

            // Add payments as CREDIT entries
            foreach ($payments as $payment) {
                $note = $payment->note ?: 'Transfer';
                $note .= ' (' . $dealer->name . ')';

                $transactions[] = [
                    'date' => $payment->payment_date,
                    'type' => 'credit',
                    'amount' => $payment->payment_amount,
                    'note' => $note,
                ];
            }

            // Sort by date
            usort($transactions, function ($a, $b) {
                $dateA = Carbon::parse($a['date']);
                $dateB = Carbon::parse($b['date']);
                if ($dateA->eq($dateB)) {
                    // If same date, debits come before credits
                    if ($a['type'] === 'debit' && $b['type'] === 'credit') {
                        return -1;
                    }
                    if ($a['type'] === 'credit' && $b['type'] === 'debit') {
                        return 1;
                    }
                    return 0;
                }
                return $dateA->lt($dateB) ? -1 : 1;
            });

            // Calculate totals
            $totalDebit = collect($transactions)->where('type', 'debit')->sum('amount');
            $totalCredit = collect($transactions)->where('type', 'credit')->sum('amount');
            $balance = $totalDebit - $totalCredit;

            return view('dealers.report', [
                'dealer' => $dealer,
                'transactions' => $transactions,
                'dateFrom' => Carbon::parse($dateFrom),
                'dateTo' => Carbon::parse($dateTo),
                'totalDebit' => $totalDebit,
                'totalCredit' => $totalCredit,
                'balance' => $balance,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Dealer not found');
        } catch (Exception $e) {
            abort(500, 'An error occurred while generating the report.');
        }
    }
}
