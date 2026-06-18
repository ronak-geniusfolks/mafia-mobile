<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents Export — Mafia Mobile</title>
    <style>
    /* ── Reset & base ─────────────────────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 12px;
        color: #111827;
        background: #f3f4f6;
        padding: 20px;
    }

    /* ── Print page ───────────────────────────────────────────────────────── */
    @media print {
        body { background: #fff; padding: 0; }
        .no-print { display: none !important; }
        .page-break { page-break-before: always; }
        .record-block { page-break-inside: avoid; }
        @page { size: A4; margin: 15mm 12mm; }
    }

    /* ── Wrapper ──────────────────────────────────────────────────────────── */
    .export-wrapper {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    }

    /* ── Report header ────────────────────────────────────────────────────── */
    .report-header {
        background: linear-gradient(135deg, #1a56db 0%, #1e40af 100%);
        color: #fff;
        padding: 24px 28px 20px;
    }
    .report-header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }
    .company-name {
        font-size: 20px;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .company-sub {
        font-size: 11px;
        opacity: 0.75;
        margin-top: 2px;
    }
    .export-meta {
        text-align: right;
        font-size: 10px;
        opacity: 0.8;
        line-height: 1.6;
    }
    .report-title {
        font-size: 15px;
        font-weight: 700;
        opacity: 0.9;
    }
    .filter-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
    }
    .filter-pill {
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        padding: 2px 10px;
        font-size: 10px;
        font-weight: 600;
    }

    /* ── Summary bar ──────────────────────────────────────────────────────── */
    .summary-bar {
        background: #f8f9fc;
        border-bottom: 1px solid #e2e8f0;
        padding: 10px 28px;
        display: flex;
        gap: 24px;
        font-size: 11px;
        color: #6b7280;
    }
    .summary-bar strong { color: #111827; }

    /* ── Record block ─────────────────────────────────────────────────────── */
    .record-block {
        border-bottom: 2px solid #e8f0fe;
        padding: 18px 28px;
    }
    .record-block:last-child { border-bottom: none; }

    .record-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
        flex-wrap: wrap;
        gap: 8px;
    }
    .record-id {
        font-size: 14px;
        font-weight: 800;
        color: #1a56db;
    }
    .record-type-badge {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 3px 8px;
        border-radius: 20px;
    }
    .badge-invoice  { background: #dbeafe; color: #1e40af; }
    .badge-purchase { background: #dcfce7; color: #166534; }

    /* ── Info grid ────────────────────────────────────────────────────────── */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 6px 16px;
        margin-bottom: 14px;
        background: #f8f9fc;
        border-radius: 6px;
        padding: 10px 12px;
    }
    .info-item-label {
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #9ca3af;
        margin-bottom: 1px;
    }
    .info-item-value {
        font-size: 11px;
        font-weight: 600;
        color: #111827;
        word-break: break-word;
    }

    /* ── Attachments ──────────────────────────────────────────────────────── */
    .attachments-section-title {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #6b7280;
        margin-bottom: 8px;
        padding-bottom: 4px;
        border-bottom: 1px solid #e2e8f0;
    }
    .attachments-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
    }
    .att-item {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 8px;
        background: #fff;
    }
    .att-thumb-wrap {
        width: 100%;
        height: 110px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 4px;
        background: #f8f9fc;
        margin-bottom: 6px;
    }
    .att-thumb-wrap img.img-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px;
    }
    .att-thumb-wrap img.img-pdf {
        width: 38px;
        height: 48px;
        object-fit: contain;
    }
    .att-label {
        font-size: 9px;
        font-weight: 700;
        color: #1e40af;
        background: #dbeafe;
        border-radius: 10px;
        padding: 1px 6px;
        margin-bottom: 3px;
        display: inline-block;
    }
    .att-name {
        font-size: 9px;
        color: #6b7280;
        word-break: break-word;
        line-height: 1.3;
    }
    .att-meta {
        font-size: 8px;
        color: #9ca3af;
        margin-top: 2px;
    }

    /* ── PDF placeholder (no actual embed — link instead) ─────────────────── */
    .pdf-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 9px;
        text-align: center;
        gap: 4px;
    }

    /* ── No-print bar ─────────────────────────────────────────────────────── */
    .print-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #1a56db;
        color: #fff;
        padding: 10px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 9999;
        gap: 12px;
        flex-wrap: wrap;
    }
    .print-bar-info { font-size: 12px; }
    .print-bar-info strong { font-size: 14px; }
    .print-bar-actions { display: flex; gap: 8px; }
    .btn-print {
        background: #fff;
        color: #1a56db;
        border: none;
        border-radius: 6px;
        padding: 7px 18px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }
    .btn-back {
        background: rgba(255,255,255,0.2);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.4);
        border-radius: 6px;
        padding: 7px 14px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-back:hover { color: #fff; text-decoration: none; }

    /* Padding so content isn't hidden behind the fixed bar */
    @media screen { .export-wrapper { margin-bottom: 70px; } }
    </style>
</head>
<body>

{{-- ── Fixed Print Bar (hidden when printing) ────────────────────────────── --}}
<div class="print-bar no-print">
    <div class="print-bar-info">
        <strong>{{ $total }} document(s)</strong> ready to export
        &nbsp;·&nbsp; To save as PDF: click Print &rarr; choose <em>Save as PDF</em> in your browser
    </div>
    <div class="print-bar-actions">
        <a href="javascript:history.back();" class="btn-back">&larr; Back</a>
        <button class="btn-print" onclick="window.print()">
            🖨 Print / Save as PDF
        </button>
    </div>
</div>

<div class="export-wrapper">

    {{-- ── Report header ───────────────────────────────────────────────────── --}}
    <div class="report-header">
        <div class="report-header-top">
            <div>
                <div class="company-name">Mafia Mobile</div>
                <div class="company-sub">Buy · Sell · Exchange</div>
            </div>
            <div class="export-meta">
                Exported on: {{ $exportedAt }}<br>
                Generated by: {{ Auth::user()->name ?? 'System' }}<br>
                Total records: {{ $total }}
            </div>
        </div>
        <div class="report-title">
            {{ $tab === 'invoice' ? 'Customer Documents Report' : 'Stock Documents Report' }}
        </div>
        <div class="filter-pills">
            @if($from || $to)
                <span class="filter-pill">
                    📅 {{ $from ? \Carbon\Carbon::parse($from)->format('d M Y') : 'Start' }}
                    → {{ $to   ? \Carbon\Carbon::parse($to)->format('d M Y')   : 'Today' }}
                </span>
            @endif
            @if($label)
                <span class="filter-pill">🏷 {{ $label }}</span>
            @endif
            @if($search)
                <span class="filter-pill">🔍 "{{ $search }}"</span>
            @endif
            @if(!$from && !$to && !$label && !$search)
                <span class="filter-pill">All records</span>
            @endif
        </div>
    </div>

    {{-- ── Summary bar ─────────────────────────────────────────────────────── --}}
    <div class="summary-bar">
        <span>Records: <strong>{{ $grouped->count() }}</strong></span>
        <span>Documents: <strong>{{ $total }}</strong></span>
        <span>Type: <strong>{{ $tab === 'invoice' ? 'Invoice Documents' : 'Stock Documents' }}</strong></span>
    </div>

    {{-- ── Records ─────────────────────────────────────────────────────────── --}}
    @foreach($grouped as $parentId => $attachments)
        @php $parent = $attachments->first()->attachable; @endphp
        <div class="record-block">

            {{-- Record header --}}
            <div class="record-header">
                @if($tab === 'invoice')
                    <div class="record-id">Invoice #{{ $parent?->invoice_no ?? $parentId }}</div>
                    <span class="record-type-badge badge-invoice">Customer Document</span>
                @else
                    <div class="record-id">Stock #{{ $parentId }}
                        @if($parent?->model) — {{ $parent->model }} @endif
                    </div>
                    <span class="record-type-badge badge-purchase">Stock Document</span>
                @endif
            </div>

            {{-- Parent record details --}}
            @if($tab === 'invoice' && $parent)
                <div class="info-grid">
                    <div>
                        <div class="info-item-label">Invoice No.</div>
                        <div class="info-item-value">#{{ $parent->invoice_no }}</div>
                    </div>
                    <div>
                        <div class="info-item-label">Invoice Date</div>
                        <div class="info-item-value">
                            {{ $parent->invoice_date ? \Carbon\Carbon::parse($parent->invoice_date)->format('d-m-Y') : '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="info-item-label">Customer Name</div>
                        <div class="info-item-value">{{ $parent->customer_name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="info-item-label">Customer No.</div>
                        <div class="info-item-value">{{ $parent->customer_no ?? '—' }}</div>
                    </div>
                    @if($parent->customer_address)
                    <div style="grid-column: 1 / -1;">
                        <div class="info-item-label">Address</div>
                        <div class="info-item-value">{{ $parent->customer_address }}</div>
                    </div>
                    @endif
                    <div>
                        <div class="info-item-label">Net Amount</div>
                        <div class="info-item-value">₹{{ number_format($parent->net_amount ?? 0) }}</div>
                    </div>
                    <div>
                        <div class="info-item-label">Payment Type</div>
                        <div class="info-item-value">{{ strtoupper($parent->payment_type ?? '—') }}</div>
                    </div>
                </div>
            @elseif($tab === 'purchase' && $parent)
                <div class="info-grid">
                    <div>
                        <div class="info-item-label">Stock ID</div>
                        <div class="info-item-value">#{{ $parentId }}</div>
                    </div>
                    <div>
                        <div class="info-item-label">Model</div>
                        <div class="info-item-value">{{ $parent->model ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="info-item-label">IMEI</div>
                        <div class="info-item-value" style="font-family:monospace;">{{ $parent->imei ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="info-item-label">Purchase Date</div>
                        <div class="info-item-value">
                            {{ $parent->purchase_date ? \Carbon\Carbon::parse($parent->purchase_date)->format('d-m-Y') : '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="info-item-label">Purchase From</div>
                        <div class="info-item-value">{{ ucfirst($parent->purchase_from ?? '—') }}</div>
                    </div>
                    <div>
                        <div class="info-item-label">Color / Storage</div>
                        <div class="info-item-value">
                            {{ ucfirst($parent->color ?? '—') }} / {{ $parent->storage ? $parent->storage . ' GB' : '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="info-item-label">Purchase Cost</div>
                        <div class="info-item-value">₹{{ number_format($parent->purchase_price ?? 0) }}</div>
                    </div>
                    <div>
                        <div class="info-item-label">Condition</div>
                        <div class="info-item-value">{{ $parent->condition ?? '—' }}</div>
                    </div>
                </div>
            @endif

            {{-- Attached documents --}}
            <div class="attachments-section-title">
                Attached Documents ({{ $attachments->count() }})
            </div>
            <div class="attachments-grid">
                @foreach($attachments as $att)
                    <div class="att-item">
                        <div class="att-thumb-wrap">
                            @if($att->isImage())
                                @php
                                    $fullPath = public_path($att->file_path);
                                    $b64 = file_exists($fullPath)
                                        ? 'data:' . $att->file_type . ';base64,' . base64_encode(file_get_contents($fullPath))
                                        : null;
                                @endphp
                                @if($b64)
                                    <img src="{{ $b64 }}" class="img-thumb" alt="{{ $att->file_name }}">
                                @else
                                    <div class="pdf-placeholder">
                                        <span style="font-size:18px;">🖼</span>
                                        <span>Image not found</span>
                                    </div>
                                @endif
                            @elseif($att->isPdf())
                                <div class="pdf-placeholder">
                                    <span style="font-size:24px;">📄</span>
                                    <span>PDF Document</span>
                                    <a href="{{ $att->url }}" target="_blank"
                                        style="color:#1a56db;font-size:8px;margin-top:2px;">Open PDF</a>
                                </div>
                            @else
                                <div class="pdf-placeholder">
                                    <span style="font-size:20px;">📎</span>
                                    <span>File</span>
                                </div>
                            @endif
                        </div>

                        @if($att->label)
                            <div class="att-label">{{ $att->label }}</div>
                        @endif
                        <div class="att-name">{{ $att->file_name }}</div>
                        <div class="att-meta">
                            {{ $att->formatted_size }}
                            &nbsp;·&nbsp;
                            {{ $att->uploaded_by ? ($att->uploader?->name ?? 'PC Upload') : 'Mobile Upload' }}
                            &nbsp;·&nbsp;
                            {{ \Carbon\Carbon::parse($att->created_at)->format('d-m-Y') }}
                        </div>
                    </div>
                @endforeach
            </div>

        </div>{{-- /record-block --}}
    @endforeach

    @if($grouped->isEmpty())
        <div style="text-align:center;padding:3rem 1rem;color:#9ca3af;">
            <div style="font-size:2.5rem;margin-bottom:0.5rem;">📂</div>
            <p>No documents found matching the selected filters.</p>
        </div>
    @endif

</div>{{-- /export-wrapper --}}

<script>
// Auto-open print dialog after a short delay so images load first
window.addEventListener('load', function() {
    // Small delay to ensure base64 images are fully rendered
    setTimeout(function() {
        // Don't auto-print; let user click the button for better UX
    }, 500);
});
</script>

</body>
</html>
