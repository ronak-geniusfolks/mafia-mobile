<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function sale(Request $request)
    {
        $allSales = Invoice::where('deleted', 0)->orderBy('invoice_date', 'desc')->get();
        $period = $request->input('period');
        $totalSalesAmount = $totalProfitAmount = 0;
        $fromDate = $toDate = '';
        if ($period === 'thismonth') {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now();  // today's date)
            $allSales = Invoice::whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
                ->where('deleted', 0)
                ->orderBy('created_at', 'desc')->paginate(10);
            $totalSalesAmount = Invoice::whereBetween('invoice_date', [$startOfMonth, $endOfMonth])->where('deleted', 0)->sum('net_amount');
            $totalProfitAmount = InvoiceItem::whereHas('invoice', function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('invoice_date', [$startOfMonth, $endOfMonth]);
            })->where('deleted', 0)->sum('profit');
            $totalExpenseAmount = Expense::whereBetween('entrydate', [$startOfMonth, $endOfMonth])->where('deleted', 0)->sum('amount');
            $timePeriod = Carbon::now()->format('F');
        } elseif ($period === 'lastmonth') {
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();  // today's date)
            $allSales = Invoice::whereBetween('invoice_date', [$lastMonthStart, $lastMonthEnd])
                ->where('deleted', 0)
                ->orderBy('created_at', 'desc')->paginate(10);
            $totalSalesAmount = Invoice::whereBetween('invoice_date', [$lastMonthStart, $lastMonthEnd])->where('deleted', 0)->sum('net_amount');
            $totalProfitAmount = InvoiceItem::whereHas('invoice', function ($query) use ($lastMonthStart, $lastMonthEnd) {
                $query->whereBetween('invoice_date', [$lastMonthStart, $lastMonthEnd]);
            })->where('deleted', 0)->sum('profit');
            $totalExpenseAmount = Expense::whereBetween('entrydate', [$lastMonthStart, $lastMonthEnd])->where('deleted', 0)->sum('amount');
            $timePeriod = Carbon::now()->subMonth()->format('F');
        } elseif ($period === 'thisyear') {
            $startOfYear = Carbon::now()->startOfYear();
            $endOfYear = Carbon::now();  // today's date)
            $allSales = Invoice::whereBetween('invoice_date', [$startOfYear, $endOfYear])
                ->where('deleted', 0)
                ->orderBy('created_at', 'desc')->paginate(10);
            $totalSalesAmount = Invoice::whereBetween('invoice_date', [$startOfYear, $endOfYear])->where('deleted', 0)->sum('net_amount');
            $totalProfitAmount = InvoiceItem::whereHas('invoice', function ($query) use ($startOfYear, $endOfYear) {
                $query->whereBetween('invoice_date', [$startOfYear, $endOfYear]);
            })->where('deleted', 0)->sum('profit');
            $totalExpenseAmount = Expense::whereBetween('entrydate', [$startOfYear, $endOfYear])->where('deleted', 0)->sum('amount');
            $timePeriod = Carbon::now()->format('Y');
        } elseif ($period === 'lastyear') {
            $startOfLastYear = Carbon::now()->subYear()->startOfYear();
            $endOfLastYear = Carbon::now()->subYear()->endOfYear();
            $allSales = Invoice::whereBetween('invoice_date', [$startOfLastYear, $endOfLastYear])
                ->where('deleted', 0)
                ->orderBy('created_at', 'asc')->paginate(15);
            $totalSalesAmount = Invoice::whereBetween('invoice_date', [$startOfLastYear, $endOfLastYear])->where('deleted', 0)->sum('net_amount');
            $totalProfitAmount = InvoiceItem::whereHas('invoice', function ($query) use ($startOfLastYear, $endOfLastYear) {
                $query->whereBetween('invoice_date', [$startOfLastYear, $endOfLastYear]);
            })->where('deleted', 0)->sum('profit');
            $totalExpenseAmount = Expense::whereBetween('entrydate', [$startOfLastYear, $endOfLastYear])->where('deleted', 0)->sum('amount');
            $timePeriod = Carbon::now()->subYear()->format('Y');
        } elseif ($period === 'custom') {
            $fromDate = $request->input('fromdate');
            $toDate = $request->input('todate');
            $allSales = Invoice::whereBetween('invoice_date', [$fromDate, $toDate])->where('deleted', 0)->orderBy('created_at', 'desc')->paginate(15);
            $totalSalesAmount = Invoice::whereBetween('invoice_date', [$fromDate, $toDate])->where('deleted', 0)->sum('net_amount');
            $totalProfitAmount = InvoiceItem::whereHas('invoice', function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('invoice_date', [$fromDate, $toDate]);
            })->where('deleted', 0)->sum('profit');
            $totalExpenseAmount = Expense::whereBetween('entrydate', [$fromDate, $toDate])->where('deleted', 0)->sum('amount');
            $timePeriod = Carbon::now()->format('F');
        } elseif ($period === 'alls') {
            $allSales = Invoice::where('deleted', 0)
                ->orderBy('created_at', 'desc')->paginate(10);
            $totalSalesAmount = Invoice::whereBetween('invoice_date', [Carbon::now()->startOfMonth(), Carbon::now()])
                ->where('deleted', 0)->sum('net_amount');
            $totalProfitAmount = InvoiceItem::whereHas('invoice', function ($query) {
                $query->whereBetween('invoice_date', [Carbon::now()->startOfMonth(), Carbon::now()]);
            })->where('deleted', 0)->sum('profit');
            $totalExpenseAmount = Expense::whereBetween('entrydate', [Carbon::now()->startOfMonth(), Carbon::now()])->where('deleted', 0)->sum('amount');
            $timePeriod = Carbon::now()->format('F');
        } else {
            $allSales = Invoice::where('deleted', 0)
                ->orderBy('created_at', 'desc')->paginate(10);
            $totalSalesAmount = Invoice::whereBetween('invoice_date', [Carbon::now()->startOfMonth(), Carbon::now()])
                ->where('deleted', 0)->sum('net_amount');
            $totalProfitAmount = InvoiceItem::whereHas('invoice', function ($query) {
                $query->whereBetween('invoice_date', [Carbon::now()->startOfMonth(), Carbon::now()]);
            })->where('deleted', 0)->sum('profit');
            $totalExpenseAmount = Expense::whereBetween('entrydate', [Carbon::now()->startOfMonth(), Carbon::now()])->where('deleted', 0)->sum('amount');
            $timePeriod = Carbon::now()->format('F');
        }

        return view('reports.sales', [
            'allSales' => $allSales,
            'period' => $period,
            'totalSalesAmount' => number_format((float) $totalSalesAmount, 2, '.', ''),
            'totalProfitAmount' => number_format((float) $totalProfitAmount, 2, '.', ''),
            'fromdate' => $fromDate,
            'todate' => $toDate,
            'totalItems' => $allSales->total(),
            'currentMonth' => Carbon::now()->format('F'),
            'timePeriod' => $timePeriod,
            'totalExpenseAmount' => number_format((float) $totalExpenseAmount, 2, '.', ''),
        ]);
    }

    /**
     * Download sales records using route(sale-export)
     */
    public function downloadExcel(Request $request)
    {
        $period = $request->input('period');
        $monthName = Carbon::now()->format('F');

        // Get sales data based on the selected period
        if ($period === 'thismonth') {
            $monthName = Carbon::now()->format('F');
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now();
        } elseif ($period === 'lastmonth') {
            $monthName = Carbon::now()->subMonth()->format('F');
            $start = Carbon::now()->subMonth()->startOfMonth();
            $end = Carbon::now()->subMonth()->endOfMonth();
        } elseif ($period === 'thisyear') {
            $monthName = Carbon::now()->format('Y');
            $start = Carbon::now()->startOfYear();
            $end = Carbon::now();
        } elseif ($period === 'custom') {
            $from = $request->input('fromdate');
            $to = $request->input('todate');
            $monthName = Carbon::parse($from)->format('d-M') . '_to_' . Carbon::parse($to)->format('d-M');
            $start = $from;
            $end = $to;
        } else {
            $start = null;
            $end = null;
        }

        // Query invoices with relationships eager loaded
        $query = Invoice::with(['items.purchase'])->where('deleted', 0);

        if ($start && $end) {
            $query->whereBetween('invoice_date', [$start, $end]);
        }

        $salesReport = $query->orderBy('created_at', 'asc')->get();

        // Prepare CSV headers
        $fileName = $monthName . '-sales.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // CSV callback generator
        $callback = function () use ($salesReport) {
            $file = fopen('php://output', 'w+');

            // CSV header
            fputcsv($file, [
                'Sr No',
                'Invoice No.',
                'Invoice Date',
                'IMEI',
                'Model',
                'Storage (GB)',
                'Color',
                'Customer Name',
                'Mobile No.',
                'Sell Price',
                'Profit',
                'Payment Mode',
            ], ';');

            $count = 1;

            foreach ($salesReport as $invoice) {
                foreach ($invoice->items as $item) {
                    $purchase = $item->purchase; // Purchase related to this item

                    fputcsv($file, [
                        $count++,
                        $invoice->invoice_no,
                        $invoice->invoice_date ? Carbon::parse($invoice->invoice_date)->format('d-m-Y') : '',
                        $purchase->imei ?? '',
                        $purchase->model ?? '',
                        $purchase->storage ?? '',
                        $purchase->color ?? '',
                        $invoice->customer_name ?? '',
                        $invoice->customer_no ?? '',
                        $item->sell_price ?? $invoice->net_amount, // prefer per-item price if available
                        $item->profit ?? 0,
                        $invoice->payment_type ?? '',
                    ], ';');
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function downloadPurchaseExcel(Request $request)
    {
        $period = $request->input('period');
        $monthName = Carbon::now()->format('F');
        if ($period === 'thismonth') {
            $monthName = Carbon::now()->format('F');
            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::now()->format('Y-m-d');  // today's date)
            $allSales = Purchase::whereBetween('purchase_date', [$startOfMonth, $endOfMonth])
                ->where('deleted', 0)
                ->orderBy('created_at', 'asc')->get();
        } elseif ($period === 'lastmonth') {
            $monthName = Carbon::now()->subMonth()->format('F');
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');  // today's date)
            $allSales = Purchase::whereBetween('purchase_date', [$lastMonthStart, $lastMonthEnd])
                ->where('deleted', 0)
                ->orderBy('created_at', 'asc')->get();
        } elseif ($period === 'thisyear') {
            $monthName = Carbon::now()->format('Y');
            $startOfYear = Carbon::now()->startOfYear()->format('Y-m-d');
            $endOfYear = Carbon::now()->format('Y-m-d');
            $allSales = Purchase::whereBetween('purchase_date', [$startOfYear, $endOfYear])
                ->where('deleted', 0)
                ->orderBy('created_at', 'asc')->get();
        } elseif ($period === 'custom') {
            $fromDate = $request->input('fromdate');
            $toDate = $request->input('todate');
            $monthName = date('d-m-Y', time());
            $endOfYear = Carbon::now()->format('Y-m-d');
            $allSales = Purchase::whereBetween('purchase_date', [$fromDate, $toDate])
                ->where('deleted', 0)
                ->orderBy('created_at', 'asc')->get();
        } else {
            $allSales = Purchase::where('deleted', 0)->orderBy('created_at', 'asc')->get();
        }

        $fileName = $monthName.'-Purchase.csv';
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        $callback = function () use ($allSales): void {
            $file = fopen('php://output', 'w+');

            // Add the CSV header (modify this based on your model)
            fputcsv($file, ['Sr No', 'Purchase Date', 'IMEI', 'Model', 'Storage', 'Color', 'Sell Date', 'Buy From', 'Mobile No', 'Buy Cost', 'Repairing', 'Buy Price', 'Sold', 'Remark'], ';'); // Specify the custom separator (;)

            // Add the data rows
            foreach ($allSales as $row) {
                fputcsv($file, [
                    $row->id,
                    Carbon::parse($row->purchase_date)->format('d/m/Y'),
                    (string) $row->imei,
                    $row->model,
                    $row->storage,
                    $row->color,
                    ($row->sell_date) ? Carbon::parse($row->sell_date)->format('d/m/Y') : '',
                    $row->purchase_from,
                    $row->contactno,
                    $row->purchase_cost,
                    $row->repairing_charge ?: 0,
                    $row->purchase_price,
                    ($row->is_sold === 1) ? 'Yes' : 'No',
                    $row->remark,
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function downloadPurchaseReport()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now();
        $totalPurchaseAmount = 0;
        $timePeriod = Carbon::now()->format('F');
        $allPurchased = Purchase::where('deleted', 0)->orderBy('created_at', 'desc')->paginate(20);
        $totalPurchaseAmount = Purchase::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()])
            ->where('deleted', 0)->sum('purchase_cost');

        /* $totalProfitAmount = Purchase::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()])
             ->where('deleted', 0)->sum('profit');*/
        return view('reports.purchase', [
            'totalPurchaseAmount' => number_format($totalPurchaseAmount, 2, '.', ''),
            'timePeriod' => $timePeriod,
            'allPurchased' => $allPurchased,
        ]);
    }

    /**
     * display Sales & profit Charts
     */
    public function displayCharts()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now();  // today's date)
        $allSales = Invoice::whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->with('items')
            ->where('deleted', 0)
            ->orderBy('created_at', 'desc')->get();

        // json_encode($allSales);
        return view('reports.saleschart', ['allSales' => $allSales]);
    }

    public function customers()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now();  // today's date)
        $uniqueCustomers = Invoice::where('deleted', 0)
            ->select('customer_name', 'customer_no', 'created_at')
            ->distinct()
            ->orderBy('created_at', 'desc')->get();

        // $totalSalesAmount = Invoice::whereBetween('invoice_date', [$startOfMonth, $endOfMonth])->where('deleted', 0)->sum('net_amount');
        // $totalProfitAmount = Invoice::whereBetween('invoice_date', [$startOfMonth, $endOfMonth])->where('deleted', 0)->sum('profit');
        // $timePeriod = Carbon::now()->format('F');
        return view('reports.customers', ['customers' => $uniqueCustomers, 'totalcustomers' => $uniqueCustomers->count()]);
    }

    public function exportcustomers()
    {
        $uniqueCustomers = Invoice::where('deleted', 0)
            ->select('customer_name', 'customer_no')
            ->distinct()
            ->orderBy('created_at', 'desc')->get();
        $fileName = 'mafia-mobile-customers.csv';
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        $callback = function () use ($uniqueCustomers): void {
            $file = fopen('php://output', 'w+');

            // Add the CSV header (modify this based on your model)
            fputcsv($file,
                [
                    'ID',
                    'Customer Name',
                    'Contact Number',
                ],
                ';'
            );

            // Add the data rows
            foreach ($uniqueCustomers as $key => $row) {
                fputcsv($file, [
                    $key + 1,
                    $row->customer_name,
                    $row->customer_no,
                ], ';');
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
