@extends('layout.app')
@section('title', 'Documents Manager')

@section('css')
<style>
/* ── Page header ──────────────────────────────────────────────────────────── */
.dm-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}
.dm-header h4 { margin: 0; font-size: 1.15rem; font-weight: 700; }

/* ── Filter card ──────────────────────────────────────────────────────────── */
.dm-filter-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
}
.dm-filter-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 2fr auto;
    gap: 0.65rem;
    align-items: end;
}
@media (max-width: 991.98px) {
    .dm-filter-grid { grid-template-columns: 1fr 1fr; }
    .dm-filter-actions { grid-column: 1 / -1; display: flex; gap: 0.5rem; }
}
@media (max-width: 575.98px) {
    .dm-filter-grid { grid-template-columns: 1fr; }
}
.dm-filter-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
    margin-bottom: 3px;
}

/* ── Stats bar ────────────────────────────────────────────────────────────── */
.dm-stats {
    display: flex;
    /* flex-wrap: wrap; */
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.dm-stat {
    background: #f8f9fc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
}
.dm-stat i { font-size: 1rem; }

/* ── Export bar ───────────────────────────────────────────────────────────── */
.dm-export-bar {
    background: #eef2ff;
    border: 1px solid #c7d2fe;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 1.25rem;
}
.dm-export-bar .export-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #4338ca;
    white-space: nowrap;
}
.dm-export-bar select { min-width: 130px; }

/* ── Tabs ─────────────────────────────────────────────────────────────────── */
.dm-tabs {
    display: flex;
    flex-wrap: nowrap;
}
.dm-tabs .nav-item {
    flex: 1;
}
.dm-tabs .nav-link {
    font-size: 0.85rem;
    font-weight: 600;
    color: #6b7280;
    border-radius: 6px 6px 0 0;
    padding: 0.55rem 1.1rem;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}
.dm-tabs .nav-link.active {
    color: #1a56db;
    background: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}
.dm-tabs .badge { font-size: 0.68rem; }
@media (max-width: 575.98px) {
    .dm-tabs .nav-link { font-size: 0.78rem; padding: 0.5rem 0.5rem; }
    .dm-tabs .nav-link .d-sm-inline { display: none !important; }
}

/* ── Table ────────────────────────────────────────────────────────────────── */
.dm-table th {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
    background: #f8f9fc;
    color: #374151;
    font-weight: 700;
}
.dm-table td {
    font-size: 0.84rem;
    vertical-align: middle;
}
.dm-thumb {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border-radius: 5px;
    border: 1px solid #e2e8f0;
}
.dm-thumb-pdf {
    width: 30px;
    height: 38px;
    object-fit: contain;
}
.dm-file-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.dm-filename {
    font-size: 0.78rem;
    color: #374151;
    word-break: break-word;
    max-width: 140px;
    line-height: 1.3;
}
.dm-filesize { font-size: 0.7rem; color: #9ca3af; }

/* ── Empty state ──────────────────────────────────────────────────────────── */
.dm-empty {
    text-align: center;
    padding: 3rem 1rem;
    color: #9ca3af;
}
.dm-empty i { font-size: 3rem; display: block; margin-bottom: 0.75rem; }

/* ── Upload badge ─────────────────────────────────────────────────────────── */
.badge-mobile { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.badge-pc     { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }

/* ── Export bar: select + button always side-by-side ─────────────────────── */
.dm-export-controls {
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
}
.dm-export-controls select { flex: 1; min-width: 0; }
.dm-export-hint {
    font-size: 0.72rem;
    color: #6b7280;
    white-space: nowrap;
}
@media (max-width: 575.98px) {
    .dm-export-bar { flex-direction: column; align-items: stretch; }
    .dm-export-controls { flex-wrap: nowrap; }
    .dm-export-hint { white-space: normal; }
}

/* ── Mobile document cards (shown only on xs) ────────────────────────────── */
.dmc-list { display: flex; flex-direction: column; gap: 0.65rem; }
.dmc {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.85rem;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.dmc-top {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    margin-bottom: 0.5rem;
}
.dmc-thumb-wrap {
    flex-shrink: 0;
}
.dmc-thumb {
    width: 52px; height: 52px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    display: block;
}
.dmc-thumb-pdf {
    width: 36px; height: 46px;
    object-fit: contain;
    display: block;
}
.dmc-info { flex: 1; min-width: 0; }
.dmc-record {
    font-size: 0.88rem;
    font-weight: 700;
    color: #1a56db;
    margin-bottom: 1px;
}
.dmc-record-green { color: #16a34a; }
.dmc-parent {
    font-size: 0.8rem;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dmc-sub {
    font-size: 0.72rem;
    color: #6b7280;
    font-family: monospace;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dmc-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    margin-bottom: 0.5rem;
}
.dmc-chip {
    font-size: 0.7rem;
    padding: 2px 8px;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    background: #f1f5f9;
    color: #374151;
    font-weight: 500;
}
.dmc-file-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #f1f5f9;
    padding-top: 0.45rem;
    margin-top: 0.1rem;
}
.dmc-filename {
    font-size: 0.75rem;
    color: #374151;
    word-break: break-word;
    flex: 1;
    margin-right: 0.5rem;
    line-height: 1.3;
}
.dmc-filesize { font-size: 0.68rem; color: #9ca3af; }
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- ── Page Header ──────────────────────────────────────────────────────── --}}
    <div class="dm-header mt-1">
        <h4>
            <i class="mdi mdi-folder-multiple-outline text-primary mr-1"></i>
            Documents Manager
        </h4>
        <div>
            <small class="text-muted">All customer ID proofs &amp; stock documents in one place</small>
        </div>
    </div>

    {{-- ── Filters ──────────────────────────────────────────────────────────── --}}
    <div class="dm-filter-card">
        <form method="GET" action="{{ route('attachments.index') }}" id="filterForm">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="dm-filter-grid">

                <div>
                    <div class="dm-filter-label">From Date</div>
                    <input type="date" name="from" value="{{ $from }}"
                        class="form-control form-control-sm">
                </div>

                <div>
                    <div class="dm-filter-label">To Date</div>
                    <input type="date" name="to" value="{{ $to }}"
                        class="form-control form-control-sm">
                </div>

                <div>
                    <div class="dm-filter-label">Document Type</div>
                    <select name="label" class="form-control form-control-sm">
                        <option value="">— All Labels —</option>
                        @foreach($allLabels as $lbl)
                            <option value="{{ $lbl }}" {{ $label === $lbl ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <div class="dm-filter-label">
                        Search
                        <small class="text-muted font-weight-normal">(customer / IMEI / model)</small>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Name, number, IMEI, model…"
                        class="form-control form-control-sm">
                </div>

                <div class="dm-filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-magnify mr-1"></i>Apply
                    </button>
                    <a href="{{ route('attachments.index', ['tab' => $tab]) }}"
                        class="btn btn-outline-secondary btn-sm">
                        <i class="mdi mdi-close mr-1"></i>Clear
                    </a>
                </div>

            </div>
        </form>
    </div>

    {{-- ── Stats ────────────────────────────────────────────────────────────── --}}
    <div class="dm-stats">
        <div class="dm-stat">
            <i class="mdi mdi-receipt text-primary"></i>
            Invoice Docs: <strong>{{ $invoiceAtts->total() }}</strong>
        </div>
        <div class="dm-stat">
            <i class="mdi mdi-cellphone-android text-success"></i>
            Stock Docs: <strong>{{ $purchaseAtts->total() }}</strong>
        </div>
        @if($from || $to || $label || $search)
            <div class="dm-stat" style="background:#fff7ed;border-color:#fed7aa;">
                <i class="mdi mdi-filter text-warning"></i>
                Filters active
                <a href="{{ route('attachments.index', ['tab' => $tab]) }}"
                    class="small text-danger ml-1">Clear</a>
            </div>
        @endif
    </div>

    {{-- ── Export Bar ───────────────────────────────────────────────────────── --}}
    @can('attachments.export')
    <div class="dm-export-bar">
        <span class="export-label"><i class="mdi mdi-export mr-1"></i>Export</span>
        <form method="GET" action="{{ route('attachments.export') }}"
            id="exportForm" style="flex:1;min-width:0;">
            <input type="hidden" name="tab"    value="{{ $tab }}">
            <input type="hidden" name="from"   value="{{ $from }}">
            <input type="hidden" name="to"     value="{{ $to }}">
            <input type="hidden" name="label"  value="{{ $label }}">
            <input type="hidden" name="search" value="{{ $search }}">

            <div class="dm-export-controls">
                <select name="format" class="form-control form-control-sm">
                    <option value="pdf">📄 PDF (Print-ready)</option>
                    <option value="excel">📊 Excel / XLSX</option>
                    <option value="zip">🗜 ZIP (original files)</option>
                </select>
                <button type="submit" class="btn btn-sm flex-shrink-0"
                    style="background:#4338ca;color:#fff;border:none;white-space:nowrap;">
                    <i class="mdi mdi-download mr-1"></i>Export
                    {{ $tab === 'invoice' ? 'Invoice Docs' : 'Stock Docs' }}
                </button>
            </div>
            <div class="dm-export-hint mt-1">
                Exports <strong>{{ $tab === 'invoice' ? 'Invoice Documents' : 'Stock Documents' }}</strong>
                with current filters applied
            </div>
        </form>
    </div>
    @endcan

    {{-- ── Tabs ─────────────────────────────────────────────────────────────── --}}
    <ul class="nav nav-tabs dm-tabs" id="docTabs">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'invoice' ? 'active' : '' }}"
                href="{{ request()->fullUrlWithQuery(['tab' => 'invoice', 'ipage' => 1]) }}">
                <i class="mdi mdi-receipt mr-1"></i>
                <span class="d-none d-sm-inline">Invoice </span>Documents
                <span class="badge badge-primary ml-1">{{ $invoiceAtts->total() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'purchase' ? 'active' : '' }}"
                href="{{ request()->fullUrlWithQuery(['tab' => 'purchase', 'ppage' => 1]) }}">
                <i class="mdi mdi-cellphone-android mr-1"></i>
                <span class="d-none d-sm-inline">Stock </span>Documents
                <span class="badge badge-success ml-1">{{ $purchaseAtts->total() }}</span>
            </a>
        </li>
    </ul>

    {{-- ── Tab Content ──────────────────────────────────────────────────────── --}}
    <div class="tab-content">

        {{-- ── Invoice Documents Tab ─────────────────────────────────────────── --}}
        <div class="tab-pane {{ $tab === 'invoice' ? 'active' : '' }}"
            id="invoiceTab">
            <div class="card border-top-0" style="border-radius:0 0 10px 10px;">
                <div class="card-body p-0">
                    @if($invoiceAtts->count())
                        {{-- Desktop table (hidden on xs) --}}
                        <div class="d-none d-sm-block">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 dm-table">
                                    <thead>
                                        <tr>
                                            <th style="width:40px;">#</th>
                                            <th>Invoice No.</th>
                                            <th>Invoice Date</th>
                                            <th>Customer</th>
                                            <th>Document</th>
                                            <th>File</th>
                                            <th>Uploaded</th>
                                            <th style="width:70px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoiceAtts as $i => $att)
                                            @php $inv = $att->attachable; @endphp
                                            <tr>
                                                <td class="text-muted">
                                                    {{ ($invoiceAtts->currentPage() - 1) * $invoiceAtts->perPage() + $i + 1 }}
                                                </td>
                                                <td>
                                                    @if($inv)
                                                        <a href="{{ route('invoice-detail', $inv->id) }}"
                                                            class="font-weight-bold text-primary">
                                                            #{{ $inv->invoice_no }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $inv?->invoice_date
                                                        ? \Carbon\Carbon::parse($inv->invoice_date)->format('d-m-Y')
                                                        : '—' }}
                                                </td>
                                                <td>
                                                    <div style="font-weight:600;">{{ $inv?->customer_name ?? '—' }}</div>
                                                    @if($inv?->customer_no)
                                                        <div class="text-muted" style="font-size:0.75rem;">
                                                            <a href="tel:{{ $inv->customer_no }}">{{ $inv->customer_no }}</a>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($att->label)
                                                        <span class="badge badge-info">{{ $att->label }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="dm-file-cell">
                                                        @if($att->isImage())
                                                            <a href="{{ $att->url }}" target="_blank">
                                                                <img src="{{ $att->url }}" class="dm-thumb" alt="{{ $att->file_name }}">
                                                            </a>
                                                        @else
                                                            <a href="{{ $att->url }}" target="_blank">
                                                                <img src="{{ asset('assets/images/pdf-file.svg') }}" class="dm-thumb-pdf" alt="PDF">
                                                            </a>
                                                        @endif
                                                        <div>
                                                            <div class="dm-filename">{{ Str::limit($att->file_name, 28) }}</div>
                                                            <div class="dm-filesize">{{ $att->formatted_size }}</div>
                                                            @if(! $att->uploaded_by)
                                                                <span class="badge badge-mobile" style="font-size:9px;">Mobile</span>
                                                            @else
                                                                <span class="badge badge-pc" style="font-size:9px;">PC</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="font-size:0.78rem;">
                                                        {{ $att->uploader?->name ?? ($att->uploaded_by ? 'User' : 'Mobile') }}
                                                    </div>
                                                    <div class="text-muted" style="font-size:0.72rem;">
                                                        {{ \Carbon\Carbon::parse($att->created_at)->format('d-m-Y') }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="{{ $att->url }}" target="_blank"
                                                        class="btn btn-xs btn-outline-primary">
                                                        <i class="mdi mdi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Mobile cards (visible only on xs) --}}
                        <div class="d-block d-sm-none p-2">
                            <div class="dmc-list">
                                @foreach($invoiceAtts as $i => $att)
                                    @php $inv = $att->attachable; @endphp
                                    <div class="dmc">
                                        <div class="dmc-top">
                                            <div class="dmc-thumb-wrap">
                                                @if($att->isImage())
                                                    <a href="{{ $att->url }}" target="_blank">
                                                        <img src="{{ $att->url }}" class="dmc-thumb" alt="{{ $att->file_name }}">
                                                    </a>
                                                @else
                                                    <a href="{{ $att->url }}" target="_blank">
                                                        <img src="{{ asset('assets/images/pdf-file.svg') }}" class="dmc-thumb-pdf" alt="PDF">
                                                    </a>
                                                @endif
                                            </div>
                                            <div class="dmc-info">
                                                @if($inv)
                                                    <div class="dmc-record">
                                                        <a href="{{ route('invoice-detail', $inv->id) }}" class="dmc-record">
                                                            #{{ $inv->invoice_no }}
                                                        </a>
                                                    </div>
                                                @endif
                                                <div class="dmc-parent">{{ $inv?->customer_name ?? '—' }}</div>
                                                @if($inv?->customer_no)
                                                    <div class="dmc-sub">
                                                        <a href="tel:{{ $inv->customer_no }}">{{ $inv->customer_no }}</a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="dmc-chips">
                                            @if($inv?->invoice_date)
                                                <span class="dmc-chip">
                                                    📅 {{ \Carbon\Carbon::parse($inv->invoice_date)->format('d-m-Y') }}
                                                </span>
                                            @endif
                                            @if($att->label)
                                                <span class="dmc-chip" style="background:#dbeafe;color:#1e40af;border-color:#bfdbfe;">
                                                    {{ $att->label }}
                                                </span>
                                            @endif
                                            <span class="dmc-chip" style="font-size:0.65rem;">
                                                {{ $att->uploaded_by ? 'PC' : 'Mobile' }}
                                            </span>
                                        </div>
                                        <div class="dmc-file-row">
                                            <div>
                                                <div class="dmc-filename">{{ Str::limit($att->file_name, 30) }}</div>
                                                <div class="dmc-filesize">{{ $att->formatted_size }}
                                                    · {{ \Carbon\Carbon::parse($att->created_at)->format('d-m-Y') }}
                                                </div>
                                            </div>
                                            <a href="{{ $att->url }}" target="_blank"
                                                class="btn btn-xs btn-outline-primary flex-shrink-0">
                                                <i class="mdi mdi-eye"></i> View
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="px-3 py-2">
                            {{ $invoiceAtts->appends(request()->except('ipage'))->fragment('invoiceTab')->links() }}
                        </div>
                    @else
                        <div class="dm-empty">
                            <i class="mdi mdi-file-document-outline"></i>
                            <p class="mb-0 font-weight-600">No invoice documents found</p>
                            <small>
                                @if($from || $to || $label || $search)
                                    Try clearing your filters.
                                @else
                                    Upload ID proof documents from any invoice's detail page.
                                @endif
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Stock Documents Tab ────────────────────────────────────────────── --}}
        <div class="tab-pane {{ $tab === 'purchase' ? 'active' : '' }}"
            id="purchaseTab">
            <div class="card border-top-0" style="border-radius:0 0 10px 10px;">
                <div class="card-body p-0">
                    @if($purchaseAtts->count())
                        {{-- Desktop table (hidden on xs) --}}
                        <div class="d-none d-sm-block">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 dm-table">
                                    <thead>
                                        <tr>
                                            <th style="width:40px;">#</th>
                                            <th>Stock</th>
                                            <th>Purchase Date</th>
                                            <th>Device</th>
                                            <th>Document</th>
                                            <th>File</th>
                                            <th>Uploaded</th>
                                            <th style="width:70px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseAtts as $i => $att)
                                            @php $pur = $att->attachable; @endphp
                                            <tr>
                                                <td class="text-muted">
                                                    {{ ($purchaseAtts->currentPage() - 1) * $purchaseAtts->perPage() + $i + 1 }}
                                                </td>
                                                <td>
                                                    @if($pur)
                                                        <a href="{{ route('purchase-detail', $pur->id) }}"
                                                            class="font-weight-bold text-success">
                                                            #{{ $pur->id }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">#{{ $att->attachable_id }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $pur?->purchase_date
                                                        ? \Carbon\Carbon::parse($pur->purchase_date)->format('d-m-Y')
                                                        : '—' }}
                                                </td>
                                                <td>
                                                    <div style="font-weight:600;">{{ $pur?->model ?? '—' }}</div>
                                                    @if($pur?->imei)
                                                        <div class="text-muted" style="font-family:monospace;font-size:0.72rem;">
                                                            {{ $pur->imei }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($att->label)
                                                        <span class="badge badge-warning text-dark">{{ $att->label }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="dm-file-cell">
                                                        @if($att->isImage())
                                                            <a href="{{ $att->url }}" target="_blank">
                                                                <img src="{{ $att->url }}" class="dm-thumb" alt="{{ $att->file_name }}">
                                                            </a>
                                                        @else
                                                            <a href="{{ $att->url }}" target="_blank">
                                                                <img src="{{ asset('assets/images/pdf-file.svg') }}" class="dm-thumb-pdf" alt="PDF">
                                                            </a>
                                                        @endif
                                                        <div>
                                                            <div class="dm-filename">{{ Str::limit($att->file_name, 28) }}</div>
                                                            <div class="dm-filesize">{{ $att->formatted_size }}</div>
                                                            @if(! $att->uploaded_by)
                                                                <span class="badge badge-mobile" style="font-size:9px;">Mobile</span>
                                                            @else
                                                                <span class="badge badge-pc" style="font-size:9px;">PC</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="font-size:0.78rem;">
                                                        {{ $att->uploader?->name ?? ($att->uploaded_by ? 'User' : 'Mobile') }}
                                                    </div>
                                                    <div class="text-muted" style="font-size:0.72rem;">
                                                        {{ \Carbon\Carbon::parse($att->created_at)->format('d-m-Y') }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="{{ $att->url }}" target="_blank"
                                                        class="btn btn-xs btn-outline-success">
                                                        <i class="mdi mdi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Mobile cards (visible only on xs) --}}
                        <div class="d-block d-sm-none p-2">
                            <div class="dmc-list">
                                @foreach($purchaseAtts as $i => $att)
                                    @php $pur = $att->attachable; @endphp
                                    <div class="dmc">
                                        <div class="dmc-top">
                                            <div class="dmc-thumb-wrap">
                                                @if($att->isImage())
                                                    <a href="{{ $att->url }}" target="_blank">
                                                        <img src="{{ $att->url }}" class="dmc-thumb" alt="{{ $att->file_name }}">
                                                    </a>
                                                @else
                                                    <a href="{{ $att->url }}" target="_blank">
                                                        <img src="{{ asset('assets/images/pdf-file.svg') }}" class="dmc-thumb-pdf" alt="PDF">
                                                    </a>
                                                @endif
                                            </div>
                                            <div class="dmc-info">
                                                @if($pur)
                                                    <a href="{{ route('purchase-detail', $pur->id) }}"
                                                        class="dmc-record dmc-record-green">
                                                        Stock #{{ $pur->id }}
                                                    </a>
                                                @endif
                                                <div class="dmc-parent">{{ $pur?->model ?? '—' }}</div>
                                                @if($pur?->imei)
                                                    <div class="dmc-sub">{{ $pur->imei }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="dmc-chips">
                                            @if($pur?->purchase_date)
                                                <span class="dmc-chip">
                                                    📅 {{ \Carbon\Carbon::parse($pur->purchase_date)->format('d-m-Y') }}
                                                </span>
                                            @endif
                                            @if($att->label)
                                                <span class="dmc-chip" style="background:#fef9c3;color:#854d0e;border-color:#fde68a;">
                                                    {{ $att->label }}
                                                </span>
                                            @endif
                                            <span class="dmc-chip" style="font-size:0.65rem;">
                                                {{ $att->uploaded_by ? 'PC' : 'Mobile' }}
                                            </span>
                                        </div>
                                        <div class="dmc-file-row">
                                            <div>
                                                <div class="dmc-filename">{{ Str::limit($att->file_name, 30) }}</div>
                                                <div class="dmc-filesize">{{ $att->formatted_size }}
                                                    · {{ \Carbon\Carbon::parse($att->created_at)->format('d-m-Y') }}
                                                </div>
                                            </div>
                                            <a href="{{ $att->url }}" target="_blank"
                                                class="btn btn-xs btn-outline-success flex-shrink-0">
                                                <i class="mdi mdi-eye"></i> View
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="px-3 py-2">
                            {{ $purchaseAtts->appends(request()->except('ppage'))->fragment('purchaseTab')->links() }}
                        </div>
                    @else
                        <div class="dm-empty">
                            <i class="mdi mdi-file-document-outline"></i>
                            <p class="mb-0 font-weight-600">No stock documents found</p>
                            <small>
                                @if($from || $to || $label || $search)
                                    Try clearing your filters.
                                @else
                                    Upload original bills from any stock entry's detail page.
                                @endif
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- /tab-content --}}
</div>
@endsection

@section('scripts')
<script>
// Keep the export form's tab hidden input in sync with the active tab
document.querySelectorAll('#docTabs .nav-link').forEach(function(link) {
    link.addEventListener('click', function() {
        const tab = new URL(this.href).searchParams.get('tab') || 'invoice';
        const hiddenTab = document.querySelector('#exportForm input[name="tab"]');
        if (hiddenTab) hiddenTab.value = tab;
        const filterTab = document.querySelector('#filterForm input[name="tab"]');
        if (filterTab) filterTab.value = tab;
    });
});
</script>
@endsection
