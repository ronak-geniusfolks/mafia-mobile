@extends('layout.app')

@section('title')
    Invoice #{{$invoice->invoice_no}}
@endsection
@section('content')
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
        }

        .page-container {
            display: flex;
            justify-content: center;   /* Horizontal center */
            align-items: center;       /* Vertical center */
            min-height: 100vh;         /* Full viewport height */
            background: #f5f5f5;       /* Optional: light background around invoice */
            padding: 20px;             /* Some breathing space */
        }

        .invoice-box {
            max-width: 900px;
            margin: auto;
            padding: 25px 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
            background: #fff;
            border-radius: 6px;
        }

        .invoice-box h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #0986c6;
            letter-spacing: 1px;
        }

        /* ---------- HEADER SECTION ---------- */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 15px;
            border-bottom: 2px solid #0986c6;
            margin-bottom: 20px;
        }

        /* Left block (Logo + Address stacked) */
        .company-block {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .company-logo {
            max-height: 50px;   /* Balanced logo size */
            margin-bottom: 8px; /* Space between logo and text */
        }

        .company-address h2 {
            margin: 0 0 5px;
            font-size: 18px;
            font-weight: bold;
            color: #0986c6;
        }

        .company-address p {
            margin: 0;
            font-size: 13px;
            line-height: 20px;
            color: #444;
        }

        /* Right block (Invoice details) */
        .invoice-details {
            background: #f5f9fc;
            padding: 12px 18px;
            border-radius: 6px;
            font-size: 14px;
            line-height: 22px;
            min-width: 220px;
            box-shadow: 0 0 4px rgba(0,0,0,0.05);
        }

        .invoice-details p {
            margin: 4px 0;
        }

        .invoice-details b {
            color: #0986c6;
        }

        /* ---------- TABLES ---------- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th {
            background: #0986c6;
            color: #fff;
            padding: 10px;
            text-align: center;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table td {
            border: 1px solid #eee;
            padding: 10px;
            font-size: 14px;
        }

        table tr:nth-child(even) {
            background: #fafafa;
        }

        .totals td {
            text-align: right;
            font-weight: bold;
            background: #f1f8fc;
        }

        /* ---------- AMOUNT IN WORDS ---------- */
        .amount-words {
            padding: 12px;
            background: #f5f5f5;
            font-weight: bold;
            margin-bottom: 20px;
            border-left: 4px solid #0986c6;
            font-size: 14px;
        }

        /* ---------- TERMS & FOOTER ---------- */
        .terms {
            font-size: 12px;
            line-height: 18px;
            border-top: 2px solid #0986c6;
            padding-top: 12px;
        }

        .terms h4 {
            color: #ed1547;
            margin-bottom: 8px;
        }

        .terms ul {
            margin: 0;
            padding-left: 18px;
        }

        .terms li {
            margin-bottom: 6px;
        }

        .gst-notes {
            margin-top: 20px;
            padding: 15px 20px;
            background: #f9f9f9;
            border-left: 4px solid #0986c6;
            border-radius: 4px;
            font-size: 12px;
            line-height: 18px;
            color: #444;
        }

        .gst-notes h4 {
            margin: 0 0 10px;
            font-size: 14px;
            color: #0986c6;
            font-weight: bold;
        }

        .gst-notes ul {
            margin: 0;
            padding-left: 18px;
        }

        .gst-notes li {
            margin-bottom: 6px;
        }

        .footer {
            text-align: center;
            font-weight: bold;
            margin-top: 35px;
            font-size: 14px;
            color: #555;
        }

        .thanks-text {
            text-align: center;
            font-weight: 700;
            font-size: 15px;
            margin-top: 25px;
            margin-bottom: 10px;
            color: #0986c6;
        }


        /* ---------- PRINT FIXES ---------- */
        @media print {
            @page {
                size: A4;
                margin: 10mm; /* give clean page margins */
            }

            body {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                margin: 0;
                background: #fff !important;
            }

            .page-container {
                /* display: block !important; */
                /* padding: 0 !important; */
                background: #fff !important;
                /* min-height: auto !important; */
            }

            .invoice-box {
                /* margin: 0 auto !important; */
                /* box-shadow: none !important; */
                border: none !important;
                /* border-radius: 0 !important; */
                /* width: 100% !important; */
                /* padding: 10mm; controlled padding inside */
            }

            .invoice-details,
            table th,
            .amount-words,
            .terms h4,
            .gst-notes {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            /* shrink text a bit to avoid page break */
            .terms,
            .gst-notes {
                font-size: 11px !important;
                line-height: 16px !important;
            }

            .thanks-text {
                margin-top: 15px !important;
            }
        }
    </style>

    <div class="page-container">
        <div class="invoice-box">
            <h2>RECEIPT</h2>

            <div class="invoice-header">
                <!-- Left Side (Logo + Address) -->
                <div class="company-block">
                    <img src="{{asset('assets/images/new_logo/main_logo.png')}}" alt="Logo" class="company-logo">
                    <div class="company-address">
                        <p>
                            Shop No.112, 1st Floor, Shivalik Platinum,<br>
                            Opp. Chief Justice Bunglow,<br>
                            Bodakdev, Ahmedabad - 380054<br>
                            <b>GSTIN:</b> 24BNEPT8042H1ZU<br>
                            <b>Mo:</b> +91 89898 09797, +91 89899 05757
                        </p>
                    </div>
                </div>

                <!-- Right Side (Invoice Details) -->
                <div class="invoice-details">
                    <p><b>Receipt No:</b> #{{ $invoice->invoice_no }}</p>
                    <p><b>Date:</b> {{ date("d/m/Y", strtotime($invoice->invoice_date)) }}</p>
                    @if($invoice->warranty_expiry_date)
                        <p><b>Warranty Expiry:</b> {{ date("d/m/Y", strtotime($invoice->warranty_expiry_date)) }}</p>
                    @endif
                </div>
            </div>

            <div>
                <b>Bill To:</b>
                {{ $invoice->customer_name }}<br>
                @if($invoice->customer_no) <b>Mobile:</b> {{$invoice->customer_no}} @endif
            </div>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Particulars</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td align="center">1</td>
                        <td>@php echo nl2br($invoice->item_description) @endphp</td>
                        <td align="center">1</td>
                        <td align="right">₹{{ $invoice->total_amount }}</td>
                        <td align="right">₹{{ $invoice->total_amount }}</td>
                    </tr>
                </tbody>
            </table>

            <table>
                <tbody>
                    <tr class="totals">
                        <td colspan="4">Total</td>
                        <td>₹{{ $invoice->total_amount }}</td>
                    </tr>
                    @if($invoice->cgst_amount)
                        <tr>
                            <td colspan="4" align="right">CGST ({{$invoice->cgst_rate}}%)</td>
                            <td align="right">₹{{ $invoice->cgst_amount }}</td>
                        </tr>
                    @endif
                    @if($invoice->sgst_amount)
                        <tr>
                            <td colspan="4" align="right">SGST ({{$invoice->sgst_rate}}%)</td>
                            <td align="right">₹{{ $invoice->sgst_amount }}</td>
                        </tr>
                    @endif
                    @if($invoice->discount)
                        <tr>
                            <td colspan="4" align="right">Discount</td>
                            <td align="right"> ₹{{ $invoice->discount }}</td>
                        </tr>
                    @endif
                    <tr class="totals">
                        <td colspan="4">Net Total</td>
                        <td>₹{{ $invoice->net_amount }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="amount-words">
                Amount (in words): {{ $amountInWords }}
            </div>

            <div class="terms">
                <h4>Terms & Conditions</h4>
                <ul>
                    <li>પાણીથી /ભાંગ તૂટથી/ ઇલેક્ટ્રીક શોર્ટ સર્કિટથી થયેલી નુકસાની ની વોરંટી મળશે નહીં.</li>
                    <li>કંપની વોરંટી દરમિયાન ગ્રાહકે જાતે જ કંપનીના સર્વિસ સેન્ટરમાં જવાનું રહેશે અને કંપનીનો નિર્ણય જ માન્ય
                        ગણાશે.</li>
                    <li>અમારી શોપ વોરંટી Expiry Date સુધી ગણાશે.</li>
                    <li>અમારી વોરંટી દરમિયાન ફોનમાં કોઈ ખામી આવે તો તેની સર્વિસ થશે પાછું લેવા કે રિફંડ આપવામાં આવશે નહીં.</li>
                    <li>ફોન લીધા બાદ જો રીટર્ન આપવું હશે કે એક્સચેન્જ કરવું હશે તો અમે આપેલા ભાવ જ માન્ય ગણાશે - ભાવ બાબતે કોઈ
                        રક-જક
                        કરવી નહીં.</li>
                    <li>કોઈ પણ ફોન ની બેટરી (mAh) & ફોન ના વપરાશ ઉપર આધાર રાખે છે જેથી વોરંટી દરમિયાન ચેક કર્યા બાદ જો કોઈ ખામી
                        જણાય તો
                        જ (સર્વિસ/ચેન્જ) કરી આપવામાં આવશે. </li>
                    <li>ફોન લેવા કે વેચવા માટે અમે આપેલા ભાવ એક દિવસ જ માન્ય રહેશે. </li>
                    <li>ઇન્ટરનેશનલ(ગ્લોબલ) નવા કે જુના ફોનમાં કંપનીના નિયમો બદલાતા રહેતા હોય છે જેથી જે તે સમયે કંપની વોરંટી
                        દરમિયાન
                        કંપનીનો નિયમ જ માન્ય રહેશે. </li>
                    <li>ફોન લેતા સમયે ફોન પર રહેલા ડેન્ટ કે સ્ક્રેચ ચકાસીને લેવા પછીથી આ બાબતે અમારી કોઇ જવાબદારી રહેશે નહીં.
                    </li>
                    <li>ફોન લીધા બાદ 24 કલાકની અંદર કંપનીના સર્વિસ સેન્ટરમાં ચેક કરાવી લેવું (જેમકે રીપેર થયેલ છે કે નહીં
                        પાર્ટ્સ ચેન્જ
                        છે કે નહીં બહાર ઓપન કરાવેલ છે કે નહીં વગેરે) પછીથી આ બાબતે અમારી કોઇ જવાબદારી રહેશે નહીં.</li>
                    <li>રિપ્લેસમેન્ટ વખતે જે મોડલ ખરીદેલ હશે સામે બીજું સેમ મોડેલ જ મળશે.</li>
                    <li>Display માં લાઈન-ડોટ-કલર ચેન્જ કે બીજી અન્ય તકલીફ માં વોરંટી મળશે નહીં.</li>
                    <li>Android ઇન્ટરનેશનલ ફોનમાં 5G નેટવર્ક બેન્ડ અલગ આવતા હોવાથી 5G ચાલવા કે ના ચાલવાની જવાબદારી મળતી નથી.
                    </li>
                </ul>
            </div>

            <div class="gst-notes">
                <h4>GST & HSN Notes</h4>
                <ul>
                    <li>GST on Refurbished mobile phone (HSN Code 8517) and Refurbished Laptop / Desktop (HSN Code: 8471) has been
                        charged under the margin scheme of GST.</li>
                    <li>For HSN code: 8517 (Refurbished Mobile phones) and HSN Code: 8471 (Refurbished Laptop / Desktop), value of
                        supply is determined in accordance with Section 15(5) of the Central Goods and Services Tax Act read with
                        Rule 32(5) of “Determination of the value of supply”. The credit for GST input shall not be available to the
                        buyer if buyer follows the same valuation rule.</li>
                    <li>For HSN code 8517 (other than refurbished Mobile), GST is charged as per the prescribed rate on the full
                        value of the product, for which input credit shall be available to the registered buyer.</li>
                </ul>
            </div>

            <div class="thanks-text">
                ✨ Thanks for Visit! ✨
            </div>
            <div class="footer">
                Authorised Signatory<br><b>Vision Mobile</b>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
@endsection