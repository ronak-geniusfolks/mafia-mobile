@extends('layout.app')

@section('title')
    Bill Detail #{{$bill->bill_no}}
@endsection

@section('content')
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
        }
        .action-buttons {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
        }
        .action-buttons .btn {
            margin: 0 10px;
        }
        .bill-detail-card {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px auto;
            max-width: 1000px;
            background: #fff;
        }
        .bill-header {
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .bill-info {
            margin: 15px 0;
        }
        .bill-info strong {
            width: 150px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
    </style>

    <div class="action-buttons">
        <a href="{{ route('print-bill', $bill->id) }}" class="btn btn-success">
            <i class="mdi mdi-printer"></i> Print Bill
        </a>
        <a href="{{ route('bill-edit', $bill->id) }}" class="btn btn-primary">
            <i class="mdi mdi-pencil"></i> Edit Bill
        </a>
        <a href="{{ route('allbills') }}" class="btn btn-danger">
            <i class="mdi mdi-keyboard-backspace"></i> Back to List
        </a>
    </div>

    <div class="bill-detail-card">
        <div class="bill-header">
            <h2 class="text-center">BILL DETAIL</h2>
            <h4 class="text-center">Bill No: {{ $bill->bill_no }}</h4>
        </div>

        <div class="bill-info">
            <strong>Bill Date:</strong> {{ date("d-m-Y", strtotime($bill->bill_date)) }}<br>
            <strong>Dealer Name:</strong> {{ $bill->dealer->name ?? 'N/A' }}<br>
            <strong>Contact:</strong> {{ $bill->dealer->contact_number ?? 'N/A' }}<br>
            <strong>Address:</strong> {{ $bill->dealer->address ?? 'N/A' }}<br>
            <strong>Payment Type:</strong> <span class="badge badge-{{ $bill->payment_type === 'cash' ? 'success' : 'warning' }}">{{ ucfirst($bill->payment_type) }}</span>
            @if($bill->payment_type === 'credit')
                <br><strong>Cash Amount:</strong> ₹{{ number_format($bill->cash_amount, 2) }}
                <br><strong>Credit Amount:</strong> ₹{{ number_format($bill->credit_amount, 2) }}
            @endif
            <br><strong>Created By:</strong> {{ $bill->user->name ?? 'N/A' }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>Sr No</th>
                    <th>Description</th>
                    <th>IMEI</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bill->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->item_description }}</td>
                    <td>{{ $item->purchase->imei ?? 'N/A' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                    <td>₹{{ number_format($item->total_amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No items found</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="bill-info" style="text-align: right;">
            <strong>Total Amount:</strong> ₹{{ number_format($bill->total_amount, 2) }}<br>
            @if($bill->discount > 0)
            <strong>Discount:</strong> ₹{{ number_format($bill->discount, 2) }}<br>
            @endif
            @if($bill->tax_amount > 0)
            <strong>Tax Amount:</strong> ₹{{ number_format($bill->tax_amount, 2) }}<br>
            @endif
            <strong style="font-size: 18px;">Net Amount:</strong> ₹{{ number_format($bill->net_amount, 2) }}<br>
            <strong>Amount in Words:</strong> {{ $amountInWords }}
        </div>

        @if($bill->declaration)
        <div class="bill-info" style="margin-top: 20px;">
            <strong>Declaration:</strong> {{ $bill->declaration }}
        </div>
        @endif
    </div>
@endsection

