@extends('layout.app')

@section('title')
    Invoice Detail #{{$invoice->invoice_no}}
@endsection
@section('content')
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .page-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            position: relative;
        }

        .invoice-box {
            width: 100%;
            padding: 15mm;
            box-sizing: border-box;
            position: relative;
            z-index: 2;
        }

        /* ---------- DECORATIVE HEADER ---------- */
        .decorative-header {
            border: 2px solid #000;
            padding: 8px;
            margin-bottom: 15px;
            position: relative;
        }

        .decorative-border {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .border-text {
            background: #000;
            color: #fff;
            padding: 4px 12px;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .central-logo {
            width: 100px;
            height: 100px;
            /* border: 2px solid #000; */
            /* border-radius: 50%; */
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            margin: 0 15px;
            overflow: hidden;
        }

        .central-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .invoice-body {
            border: 1px solid #000;
            padding: 10px;
            box-sizing: border-box;
            position: relative;
            z-index: 2;
        }

        /* ---------- BILL OF SUPPLY SECTION ---------- */
        .bill-of-supply {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .bill-left {
            flex: 1;
            text-align: center;
        }

        .bill-title {
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .gst-info {
            font-size: 12px;
            margin-bottom: 0;
        }

        .bill-right {
            flex: 1;
            text-align: left;
            align-items: center;
        }

        .company-info-line {
            display: flex;
            align-items: flex-start;
            margin-bottom: 3px;
            line-height: 1.4;
        }

        .company-info-line strong {
            width: 80px; /* fixed width to align titles */
            display: inline-block;
        }

        .company-info-line span {
            flex: 1;
        }

        /* ---------- INVOICE DETAILS SECTION ---------- */
        .invoice-details-section {
            margin-bottom: 20px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            border: 1px solid #000; /* outer border */
        }

        .details-table td {
            padding: 6px 8px;
            border: 1px solid #000; /* gives neat box grid like your screenshot */
        }

        .details-table .label {
            font-weight: bold;
            width: 25%;
        }

        /* ---------- ITEMIZED BILLING TABLE ---------- */
        .billing-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .billing-table th {
            border: 1px solid #000;
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            background: #f0f0f0;
        }

        .billing-table td {
            border: 1px solid #000;
            padding: 8px 4px;
            text-align: center;
        }

        .billing-table .description {
            text-align: left;
        }

        .billing-table .imei {
            font-size: 10px;
            color: #666;
        }

        /* ---------- SUMMARY SECTION ---------- */
        .summary-section {
            text-align: right;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .summary-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 3px;
        }

        .summary-label {
            width: 120px;
            text-align: right;
            padding-right: 10px;
        }

        .summary-value {
            width: 100px;
            text-align: right;
            font-weight: bold;
        }

        /* ---------- PAYMENT DETAILS ---------- */
        .payment-details {
            margin-bottom: 15px;
            font-size: 12px;
        }

        .payment-row {
            display: flex;
            margin-bottom: 5px;
        }

        .payment-label {
            width: 150px;
            font-weight: bold;
        }

        /* ---------- TERMS & CONDITIONS ---------- */
        .terms-signature-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .terms-box {
            width: 70%;
            border: 1px solid #000;
            padding: 10px;
            font-size: 10px;
            line-height: 1.3;
        }

        .terms-header {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .terms-box ul {
            margin: 0;
            padding-left: 15px;
        }

        .terms-box li {
            margin-bottom: 3px;
        }

        .signature-section {
            width: 28%;
            text-align: center;
            padding-top: 10px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .signature-text {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: auto;
        }

        .authorized-signatory {
            font-size: 12px;
            font-weight: bold;
            margin-top: auto;
        }

        /* ---------- FOOTER ---------- */
        .footer-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #000;
            padding-top: 10px;
            font-size: 10px;
        }

        .footer-left {
            flex: 1;
        }

        .footer-right {
            flex: 1;
            text-align: right;
        }

        .footer-logo {
            width: 60px;
            height: 60px;
            display: inline-block;
        }

        .footer-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* ---------- ACTION BUTTONS ---------- */
        .action-buttons {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
        }

        .action-buttons .btn {
            margin: 0 10px;
        }
    </style>

    @php
        $companyName = 'Mafia Mobile'; // Default company name
        $companyNameUpper = strtoupper($companyName);
    @endphp

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="{{ route('print-invoice', $invoice->id) }}" class="btn btn-success">
            <i class="mdi mdi-printer"></i> Print Invoice
        </a>
        <a href="{{ route('invoice-edit', $invoice->id) }}" class="btn btn-primary">
            <i class="mdi mdi-pencil"></i> Edit Invoice
        </a>
        <a href="{{ route('allinvoices') }}" class="btn btn-danger">
            <i class="mdi mdi-keyboard-backspace"></i> Back to List
        </a>
    </div>

    <div class="page-container">
        <div class="invoice-box">
            <div class="invoice-body">
                <!-- Bill of Supply Section -->
                <div class="bill-of-supply">
                    <div class="bill-left">
                        <div class="bill-title">BILL OF SUPPLY</div>
                        <div class="company-name">{{ $companyName }}</div>
                        <div class="gst-info">
                            <strong>GST NO:</strong> 24DTVPD2928H1ZG<br>
                            Not eligible to collect tax on supplies
                        </div>
                    </div>

                    <div class="bill-right">
                        <div class="company-info-line">
                            <strong>Address:</strong>
                            <span>GF-04, Parth Avenue, Nr. ONGC Avani Bhavan, Chandkheda, Ahmedabad - 380005.</span>
                        </div>
                        <div class="company-info-line">
                            <strong>Contact Us:</strong>
                            <span>+91 8901606060</span>
                        </div>
                    </div>
                </div>

                <!-- Invoice and Client Details -->
                <div class="invoice-details-section">
                    <table class="details-table">
                        <tr>
                            <td class="label">Date of Invoice:</td>
                            <td>{{ date("j F, Y", strtotime($invoice->invoice_date)) }}</td>
                            <td class="label">Invoice No.:</td>
                            <td>{{ $invoice->invoice_no }}</td>
                        </tr>
                        <tr>
                            <td class="label">Client Name:</td>
                            <td>{{ strtoupper($invoice->customer_name) }}</td>
                            <td class="label">Address:</td>
                            <td>{{ strtoupper($invoice->customer_address ?? 'N/A') }}</td>
                        </tr>
                        <tr>
                            <td class="label">City & State:</td>
                            <td>AHMEDABAD</td>
                            <td class="label">Contact Number:</td>
                            <td>{{ $invoice->customer_no ?? 'N/A' }}</td>
                        </tr>
                        <tr >
                            <td class="label">BILED BY:</td>
                            <td colspan="3">Mr {{ $invoice->user->name ?? 'Admin' }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Itemized Billing Table -->
                <table class="billing-table">
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Description of Item</th>
                            <th>Quantity</th>
                            <th>Rate</th>
                            <th>Amount in Rs.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td class="description">
                                {{ $invoice->item_description }}
                                @if($invoice->purchase && $invoice->purchase->imei)
                                    <div class="imei">IMEI {{ $invoice->purchase->imei }}</div>
                                @endif
                            </td>
                            <td>{{ $invoice->quantity ?? 1 }} Unit</td>
                            <td>₹{{ number_format($invoice->total_amount, 2) }}</td>
                            <td>₹{{ number_format($invoice->net_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Summary Section -->
                <div class="summary-section">
                    <div class="summary-row">
                        <div class="summary-label">Sub Total:</div>
                        <div class="summary-value">₹{{ number_format($invoice->total_amount, 2) }}</div>
                    </div>
                    @if($invoice->discount > 0)
                    <div class="summary-row">
                        <div class="summary-label">Discount (-) :</div>
                        <div class="summary-value">₹{{ number_format($invoice->discount, 2) }}</div>
                    </div>
                    @endif
                    @if($invoice->cgst_amount > 0)
                    <div class="summary-row">
                        <div class="summary-label">CGST:</div>
                        <div class="summary-value">₹{{ number_format($invoice->cgst_amount, 2) }}</div>
                    </div>
                    @endif
                    @if($invoice->sgst_amount > 0)
                    <div class="summary-row">
                        <div class="summary-label">SGST:</div>
                        <div class="summary-value">₹{{ number_format($invoice->sgst_amount, 2) }}</div>
                    </div>
                    @endif
                    @if($invoice->igst_amount > 0)
                    <div class="summary-row">
                        <div class="summary-label">IGST:</div>
                        <div class="summary-value">₹{{ number_format($invoice->igst_amount, 2) }}</div>
                    </div>
                    @endif
                    <div class="summary-row">
                        <div class="summary-label">Gross Total:</div>
                        <div class="summary-value">₹{{ number_format($invoice->net_amount, 2) }}</div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="payment-details">
                    <div class="payment-row">
                        <div class="payment-label">Mode of Payment:</div>
                        <div>{{ ucfirst($invoice->payment_type) }}</div>
                    </div>
                    <div class="payment-row">
                        <div class="payment-label">Total Amount In Words:</div>
                        <div>{{ $amountInWords }}</div>
                    </div>
                </div>

                <!-- Terms & Conditions with Signature Section -->
                <div class="terms-signature-section">
                    <div class="terms-box">
                        <div class="terms-header">
                            <h5>Terms & Conditions</h5>
                        </div>
                        <ul>
                            <li>All devices comes with limited 3-Month Mafia Mobile Warranty, which covers hardware malfunction and issue, Battery health is covered under warranty only in case of "Mandatory Service Request" Message. No warranty on Physical & Water Damage.</li>
                            <li>Once the Invoice is generated, the product shall remain non-returnable and any payment so made shall be Non Refundable.</li>
                            <li>Mafia Mobile as a firm is engaged in Sale/Buy/Exchange of Active Second hand devices. We Declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.</li>
                            <li>Value of supply is Determined in accordance to section 15(5) of the central goods and services tax act read with rule 32(5) of "determination of the value of supply "The credit for GST input shall not be available to the buyer if buyer follow the same valuation rule.</li>
                        </ul>
                    </div>
                    <div class="signature-section">
                        <div class="signature-text">For, {{ $companyName }}</div>
                        <div class="authorized-signatory">Authorized Signatory</div>
                    </div>
                </div>

                <!-- Footer Section -->
                <div class="footer-section">
                    <div class="footer-left">
                        <strong>GST: 24DTVPD2928H1ZG </strong><br>
                        GF-04, Parth Avenue, Nr. ONGC Avani Bhavan, Chandkheda, Ahmedabad. 8901606060
                    </div>
                    <div class="footer-right">
                        <div class="footer-logo">
                            <img src="{{asset('assets/images/new_logo/main_logo.png')}}" alt="Company Logo">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection