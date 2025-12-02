@extends('layout.app')
@section('title', 'Sale Details')

@php
use Carbon\Carbon;
$expirydate = Carbon::parse($sale->warranty_expiry_date);
$currentDate = Carbon::now();
@endphp

@section('content')
    <div class="container-fluid">
        <div class="col-xl-10 col-lg-10 col-12 mx-auto">
            <div class="card-body">
                {{-- Header Row inside Card --}}
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="mb-0 fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                        Sales Detail
                    </h3>

                    <a href="javascript:history.back();"
                        class="btn btn-success btn-sm px-3 py-2 d-flex align-items-center shadow-sm"
                        style="border-radius: 6px;">
                        <i class="mdi mdi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-11 col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">

                        {{-- Customer & Invoice Info --}}
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 text-uppercase text-primary">Invoice Information</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="20%">Invoice No:</th>
                                    <td><b>#{{ $sale->invoice_no }}</b></td>
                                    <th width="20%">Sale Date:</th>
                                    <td>{{ $sale->invoice_date ? Carbon::parse($sale->invoice_date)->format('d-m-Y') : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Customer Name:</th>
                                    <td>{{ $sale->customer_name ?? '-' }}</td>
                                    <th>Customer No:</th>
                                    <td>{{ $sale->customer_no ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Type:</th>
                                    <td><span class="badge badge-info">{{ strtoupper($sale->payment_type ?? '-') }}</span>
                                    </td>
                                    <th>Sold By:</th>
                                    <td>{{ $soldBy->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        {{-- Product Details --}}
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 text-uppercase text-primary">Product Details</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered align-middle mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Model</th>
                                            <th>IMEI</th>
                                            <th>Color</th>
                                            <th>Storage (GB)</th>
                                            <th>Purchase Price</th>
                                            <th>Sell Price</th>
                                            <th>Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($sale->items as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item->purchase->model ?? '-' }}</td>
                                                <td>{{ $item->purchase->imei ?? '-' }}</td>
                                                <td>{{ $item->purchase->color ?? '-' }}</td>
                                                <td>{{ $item->purchase->storage ?? '-' }}</td>
                                                <td>₹{{ number_format($item->purchase->purchase_price ?? 0, 2) }}</td>
                                                <td>₹{{ number_format($item->unit_price ?? 0, 2) }}</td>
                                                <td>₹{{ number_format($item->profit ?? 0, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">No items found for this sale.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Summary Section --}}
                        <div class="mb-2">
                            <h5 class="border-bottom pb-2 text-uppercase text-primary">Summary</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="25%">Warranty Expiry Date:</th>
                                    <td>
                                        @if($sale->warranty_expiry_date)
                                            @if($expirydate->lt($currentDate))
                                                <span class="badge badge-danger text-uppercase">
                                                    {{ Carbon::parse($sale->warranty_expiry_date)->format('d-m-Y') }} (Expired)
                                                </span>
                                            @else
                                                <span class="badge badge-success text-uppercase">
                                                    {{ Carbon::parse($sale->warranty_expiry_date)->format('d-m-Y') }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">Not Available</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Net Amount:</th>
                                    <td><strong class="text-dark">₹{{ number_format($sale->net_amount ?? 0, 2) }}</strong>
                                    </td>
                                </tr>

                                @if(in_array('super-admin', Auth::user()->roles->pluck('name')->toArray()))
                                    <tr>
                                        <th>Total Profit:</th>
                                        <td><strong class="text-success">₹{{ number_format($totalProfit ?? 0, 2) }}</strong>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection