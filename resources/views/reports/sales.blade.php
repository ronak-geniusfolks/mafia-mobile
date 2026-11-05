@extends('layout.app')

@section('title')
    Sales Report
@endsection

@section('content')
    <style>
        .showdefault {
            display: block !important;
        }

        .card-summary {
            border-radius: 10px;
        }

        .card-summary h5 {
            font-size: 1.15rem;
        }

        .card-summary p {
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .filter-section,
        .download-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }
    </style>

    <!-- Page Title -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Transactions Report [{{ $totalItems }}]</h4>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm card-summary">
                <div class="card-body text-center py-3">
                    <p class="text-muted text-uppercase mb-1">Total Sales - {{ $timePeriod }}</p>
                    <h5 class="fw-semibold text-primary mb-0">{{ number_format($totalSalesAmount, 2) }} ₹</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm card-summary">
                <div class="card-body text-center py-3">
                    <p class="text-muted text-uppercase mb-1">Total Profit - {{ $timePeriod }}</p>
                    <h5 class="fw-semibold text-success mb-0">{{ number_format($totalProfitAmount, 2) }} ₹</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm card-summary">
                <div class="card-body text-center py-3">
                    <p class="text-muted text-uppercase mb-1">Total Expense - {{ $timePeriod }}</p>
                    <h5 class="fw-semibold text-danger mb-0">{{ number_format($totalExpenseAmount, 2) }} ₹</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm card-summary">
                <div class="card-body text-center py-3">
                    <p class="text-muted text-uppercase mb-1">Net Profit - {{ $timePeriod }}</p>
                    <h5 class="fw-semibold text-warning mb-0">{{ number_format($totalProfitAmount - $totalExpenseAmount, 2) }} ₹</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-section">
        <form method="GET" id="salesreport" class="row g-2 align-items-center">
            <!-- Dropdown -->
            <div class="col-12 col-md-4">
                <select name="period" class="form-control" id="selectsalesperiod">
                    <option value="alls" @selected($period == 'alls')>All Sales</option>
                    <option value="thismonth" @selected($period == 'thismonth')>This Month</option>
                    <option value="lastmonth" @selected($period == 'lastmonth')>Last Month</option>
                    <option value="thisyear" @selected($period == 'thisyear')>This Year</option>
                    <option value="lastyear" @selected($period == 'lastyear')>Last Year</option>
                    <option value="custom" @selected($period == 'custom')>Custom</option>
                </select>
            </div>

            <!-- Custom Date Range -->
            <div class="col-md-2">
                <input type="date" id="salefromdate" name="fromdate"
                    class="form-control @if($period == 'custom') showdefault @endif" value="{{ $fromdate }}"
                    style="display: none;">
            </div>
            <div class="col-md-2">
                <input type="date" id="saletodate" name="todate"
                    class="form-control @if($period == 'custom') showdefault @endif" value="{{ $todate }}"
                    style="display: none;">
            </div>

            <!-- Buttons -->
            <div class="col-12 col-md-4 d-flex gap-2">
                <!-- Filter Button -->
                <button type="submit" formaction="{{ route('sale-report') }}" class="btn btn-outline-primary w-100 mr-2">
                    Filter
                </button>

                <!-- Download Button -->
                <button type="submit" formaction="{{ route('sale-export') }}" class="btn btn-outline-success w-100 mr-2">
                    Download
                </button>

                <!-- Clear Button -->
                <button type="reset" class="btn btn-outline-danger w-100">
                    Clear
                </button>
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="row">
        <div class="col-12">
            <div class="card-box">
                <div class="table-responsive">
                    @if (count($allSales))
                        <table class="table table-hover mb-0" id="invoice-tables">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice No.</th>
                                    <th>Party Name</th>
                                    <th>Billing Info</th>
                                    <th>Payment Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allSales as $key => $invoice)
                                    <tr>
                                        <td><b>{{ date("d/m/Y", strtotime($invoice->invoice_date)) }}</b></td>
                                        <td>
                                            <a href="{{ route('invoice-detail', $invoice->id) }}">
                                                <b>#{{ $invoice->invoice_no }}</b>
                                            </a>
                                        </td>
                                        <td>
                                            <ul class="list-unstyled mb-0">
                                                @if($invoice->customer_name)
                                                    <li><b>Name:</b> {{ ucwords($invoice->customer_name) }}</li>
                                                @endif
                                                @if($invoice->customer_no)
                                                    <li><b>Contact:</b> {{ ucfirst($invoice->customer_no) }}</li>
                                                @endif
                                            </ul>
                                        </td>
                                        <td>
                                            <ul class="list-unstyled mb-0">
                                                @if($invoice->total_amount)
                                                    <li><b>Total Amount:</b> ₹{{ $invoice->total_amount }}</li>
                                                @endif
                                                @if($invoice->tax_amount)
                                                    <li><b>TAX:</b> ₹{{ $invoice->tax_amount }}</li>
                                                @endif
                                                @if($invoice->discount)
                                                    <li><b>Discount:</b> ₹{{ $invoice->discount }}</li>
                                                @endif
                                                @if($invoice->net_amount)
                                                    <li><b>Net Amount:</b> ₹{{ $invoice->net_amount }}</li>
                                                @endif
                                            </ul>
                                        </td>
                                        <td>{{ ucfirst($invoice->payment_type) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $allSales->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <h5 class="text-muted">No Transactions to show</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection