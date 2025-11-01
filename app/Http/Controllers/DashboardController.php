<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // “Now” boundaries
        $today        = Carbon::today();
        $yesterday    = Carbon::yesterday();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now();
        $sevenDaysAgo = Carbon::now()->subDays(7);

        // KPIs
        $stocksInHand      = Purchase::notSold()->count();
        $totalSales        = Invoice::whereDate('invoice_date', $today)->notDeleted()->sum('net_amount');
        $currentMonthSales = Invoice::whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->notDeleted()
            ->sum('net_amount');

        // Filter inputs (safe defaults)
        $filter   = $request->string('filtertime')->toString();
        $fromdate = $request->date('fromdate') ?: $today;
        $todate   = $request->date('todate') ?: $today;

        // Build the base query with eager loading to avoid N+1
        $base = Invoice::with([
            'invoiceItems.purchase', // we’ll show purchase details per item
        ])
            ->notDeleted();

        // Apply time filter
        $invoices = match ($filter) {
            'yesterday' => (clone $base)->whereDate('invoice_date', $yesterday),
            'lastweek'  => (clone $base)->whereBetween('invoice_date', [$sevenDaysAgo, $today]),
            'month'     => (clone $base)->whereBetween('invoice_date', [$startOfMonth, $endOfMonth]),
            'custom'    => (clone $base)->whereBetween('invoice_date', [
                Carbon::parse($fromdate)->startOfDay(),
                Carbon::parse($todate)->endOfDay(),
            ]),
            default     =>(clone $base)->whereDate('invoice_date', $today),
        };

        $invoices = $invoices->latest()->paginate(10);

        // Items sold this month (sum of quantities in invoice_items linked to non-deleted invoices in current month)
        $numberOfProductsSoldInMonth = InvoiceItem::with('purchase')
            ->whereHas('invoice', function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
                    ->notDeleted();
            })
            ->sum('quantity'); // if you store qty per line; fallback to count() if needed

        return view('dashboard', [
            'stocksInHand'                => $stocksInHand,
            'totalSales'                  => number_format((float) $totalSales, 0, '.', ','),
            'currentMonthSales'           => number_format((float) $currentMonthSales, 0, '.', ','),
            'currentMonth'                => Carbon::now()->format('F'),
            'numberOfProductsSoldInMonth' => $numberOfProductsSoldInMonth,
            'todaysSales'                 => $invoices,
            'filtertime'                  => $filter,
            'fromdate'                    => Carbon::parse($fromdate)->format('Y-m-d'),
            'todate'                      => Carbon::parse($todate)->format('Y-m-d'),
            'totalRecords'                => $invoices->total(),
        ]);
    }
}