<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer Report - {{ $dealer->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: white;
            padding: 20px;
            color: #000;
        }
        
        .report-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
        }
        
        .report-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .report-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .header-left {
            flex: 1;
        }
        
        .header-right {
            text-align: right;
        }
        
        .balance-display {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 15px 0;
            padding: 10px;
            background: #f5f5f5;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .report-table thead {
            background: #f8f9fa;
        }
        
        .report-table th {
            padding: 12px 8px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        
        .report-table td {
            padding: 10px 8px;
            border: 1px solid #ddd;
        }
        
        .report-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .debit-amount {
            color: #28a745;
            font-weight: 600;
        }
        
        .credit-amount {
            color: #dc3545;
            font-weight: 600;
        }
        
        .total-row {
            font-weight: bold;
            background: #f8f9fa !important;
        }
        
        .total-debit {
            color: #28a745;
        }
        
        .total-credit {
            color: #dc3545;
        }
        
        .notes-column {
            max-width: 400px;
            word-wrap: break-word;
        }
        
        @media print {
            body {
                padding: 10px;
            }
            
            .no-print {
                display: none;
            }
            
            .report-table {
                page-break-inside: auto;
            }
            
            .report-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
        
        .print-button {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .btn-print {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-print:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="print-button no-print">
            <button class="btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
        
        <div class="report-title">Mafia Mobile</div>
        
        <div class="report-header">
            <div class="header-left">
                <div><strong>Name:</strong> {{ $dealer->name }}</div>
                <div><strong>Date:</strong> {{ $dateFrom->format('jS M Y') }} - {{ $dateTo->format('jS M Y') }}</div>
            </div>
            <div class="header-right">
                <div><strong>Created On:</strong> {{ now()->format('d-m-Y') }}</div>
            </div>
        </div>
        
        <div class="balance-display">
            Rs. {{ number_format(abs($balance), 2) }}
        </div>
        
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 15%;">DATE</th>
                    <th style="width: 45%;">NOTES</th>
                    <th style="width: 20%;">
                        DEBIT
                        @if($totalDebit > 0)
                            <span class="total-debit" style="display: block; margin-top: 5px; font-size: 12px;">
                                Rs. {{ number_format($totalDebit, 2) }}
                            </span>
                        @endif
                    </th>
                    <th style="width: 20%;">
                        CREDIT
                        @if($totalCredit > 0)
                            <span class="total-credit" style="display: block; margin-top: 5px; font-size: 12px;">
                                Rs. {{ number_format($totalCredit, 2) }}
                            </span>
                        @endif
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    @php
                        $date = Carbon\Carbon::parse($transaction['date']);
                        $formattedDate = $date->format('jS M');
                    @endphp
                    <tr>
                        <td>{{ $formattedDate }}</td>
                        <td class="notes-column">{{ $transaction['note'] }}</td>
                        <td>
                            @if($transaction['type'] === 'debit')
                                <span class="debit-amount">Rs. {{ number_format($transaction['amount'], 2) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($transaction['type'] === 'credit')
                                <span class="credit-amount">Rs. {{ number_format($transaction['amount'], 2) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px;">
                            No transactions found for the selected date range.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>

