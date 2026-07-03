@extends('layout.app')

@section('title', 'All Stocks')

@section('content')
<style>
/* ═══════════════════════════════════════════════════════════
   ALL STOCKS — Complete UI
   ═══════════════════════════════════════════════════════════ */

/* ── Page Header ── */
.stocks-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}
.stocks-page-title h4 {
    font-size: 1.35rem;
    font-weight: 800;
    color: #1d2128;
    margin: 0;
    letter-spacing: -0.3px;
}
.stocks-page-title p {
    margin: 2px 0 0;
    font-size: 0.82rem;
    color: #8a929e;
}
.stocks-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.btn-csv {
    background: #fff;
    border: 1.5px solid #e2e6ea;
    color: #495057;
    border-radius: 8px;
    padding: 7px 14px;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s;
}
.btn-csv:hover { background: #f5f6f7; color: #1d2128; text-decoration: none; }
.btn-add-stock {
    background: #d2ad61;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 7px 16px;
    font-size: 0.82rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-add-stock:hover { background: #b8953f; color: #fff; }
.btn-add-stock + .dropdown-menu { border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.12); border: none; }
.btn-add-stock + .dropdown-menu .dropdown-item { font-size: 0.84rem; padding: 9px 16px; }
.btn-add-stock + .dropdown-menu .dropdown-item:hover { background: #fdf6ea; color: #d2ad61; }

/* ── Filter Panel ── */
.filter-panel {
    background: #f8f9fb;
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 16px;
}
.filter-panel .filter-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.filter-panel .filter-search {
    flex: 1 1 220px;
    min-width: 200px;
    position: relative;
}
.filter-panel .filter-search input {
    width: 100%;
    border: 1.5px solid #e2e6ea;
    border-radius: 8px;
    padding: 8px 12px 8px 36px;
    font-size: 0.84rem;
    background: #fff;
    outline: none;
    transition: border-color 0.15s;
}
.filter-panel .filter-search input:focus { border-color: #d2ad61; }
.filter-panel .filter-search .search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #adb5bd;
    font-size: 0.9rem;
    pointer-events: none;
}
.filter-panel select {
    border: 1.5px solid #e2e6ea;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.84rem;
    background: #fff;
    color: #495057;
    outline: none;
    cursor: pointer;
    transition: border-color 0.15s;
    flex: 1 1 120px;
    min-width: 110px;
}
.filter-panel select:focus { border-color: #d2ad61; }
.btn-search {
    background: #1d2128;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 18px;
    font-size: 0.84rem;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.15s;
}
.btn-search:hover { background: #2d3340; }
.btn-filter-reset {
    background: transparent;
    color: #8a929e;
    border: 1.5px solid #e2e6ea;
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 0.82rem;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
}
.btn-filter-reset:hover { color: #ef4444; border-color: #ef4444; text-decoration: none; }

/* ── Bulk Action Bar ── */
.bulk-bar {
    background: linear-gradient(135deg, #1d2128 0%, #38414a 100%);
    border-radius: 12px;
    padding: 13px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    color: #fff;
    animation: bulkSlideIn 0.18s ease;
    gap: 12px;
    flex-wrap: wrap;
}
@keyframes bulkSlideIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.bulk-bar-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 0.9rem;
}
.bulk-bar-info .count-pill {
    background: #d2ad61;
    border-radius: 20px;
    padding: 2px 12px;
    font-weight: 800;
    font-size: 0.85rem;
    color: #fff;
}
.bulk-bar-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.btn-bulk-delete {
    background: #ef4444;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 7px 16px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: background 0.15s;
}
.btn-bulk-delete:hover { background: #dc2626; }
.btn-bulk-cancel {
    background: rgba(255,255,255,0.12);
    color: #fff;
    border: 1.5px solid rgba(255,255,255,0.25);
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-bulk-cancel:hover { background: rgba(255,255,255,0.22); }

/* ── Custom Checkboxes ── */
.custom-check {
    appearance: none;
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    border: 2px solid #d1d5db;
    border-radius: 5px;
    cursor: pointer;
    background: #fff;
    position: relative;
    transition: all 0.15s ease;
    flex-shrink: 0;
    vertical-align: middle;
}
.custom-check:hover { border-color: #d2ad61; }
.custom-check:checked {
    background: #d2ad61;
    border-color: #d2ad61;
}
.custom-check:checked::after {
    content: '';
    position: absolute;
    left: 4px; top: 1px;
    width: 6px; height: 10px;
    border: 2px solid #fff;
    border-top: none; border-left: none;
    transform: rotate(45deg);
}
.custom-check:indeterminate {
    background: #d2ad61;
    border-color: #d2ad61;
}
.custom-check:indeterminate::after {
    content: '';
    position: absolute;
    left: 3px; top: 6px;
    width: 8px; height: 2px;
    background: #fff;
}

/* ══════════════════════════════════════
   DESKTOP TABLE (≥768px)
   ══════════════════════════════════════ */
.stock-table-wrap { display: block; }
.stock-cards-wrap { display: none; }

.stocks-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.84rem;
}
.stocks-table thead tr {
    background: #f8f9fb;
}
.stocks-table thead th {
    padding: 11px 14px;
    font-weight: 700;
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #8a929e;
    border-bottom: 2px solid #eef0f3;
    white-space: nowrap;
}
.stocks-table thead th:first-child { border-radius: 8px 0 0 0; padding-left: 16px; }
.stocks-table thead th:last-child  { border-radius: 0 8px 0 0; }
.stocks-table thead th a { color: #8a929e; text-decoration: none; }
.stocks-table thead th a:hover { color: #d2ad61; }

.stocks-table tbody tr {
    border-bottom: 1px solid #f0f2f5;
    transition: background 0.1s;
}
.stocks-table tbody tr:hover td { background: #fffdf7; }
.stocks-table tbody tr.row-selected td { background: rgba(210,173,97,0.07) !important; }
.stocks-table tbody td {
    padding: 12px 14px;
    vertical-align: middle;
    color: #3d4354;
}
.stocks-table tbody td:first-child { padding-left: 16px; }

.stock-id-link { font-weight: 700; color: #1d2128; font-size: 0.83rem; text-decoration: none; }
.stock-id-link:hover { color: #d2ad61; }
.stock-model-link { color: #1d2128; font-weight: 600; text-decoration: none; }
.stock-model-link:hover { color: #d2ad61; }

.imei-cell { font-family: 'Courier New', monospace; font-size: 0.79rem; color: #495057; }
.imei-copy-btn {
    background: none; border: none; cursor: pointer; color: #adb5bd; padding: 0 4px;
    font-size: 0.8rem; transition: color 0.15s;
}
.imei-copy-btn:hover { color: #d2ad61; }

.storage-chip {
    background: #eef0f3;
    border-radius: 5px;
    padding: 2px 8px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #3d4354;
}
.color-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.82rem;
    color: #495057;
}
.price-cell { font-weight: 700; color: #1d2128; }
.date-cell  { color: #6c757d; font-size: 0.8rem; white-space: nowrap; }

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.3px;
}
.status-badge.available { background: #dcfce7; color: #15803d; }
.status-badge.sold      { background: #fee2e2; color: #dc2626; }

.action-btns { display: flex; align-items: center; gap: 6px; }
.action-btn-icon {
    width: 30px; height: 30px;
    border-radius: 7px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
}
.action-btn-icon.view   { background: #eef2ff; color: #4f46e5; }
.action-btn-icon.edit   { background: #fef9ec; color: #d2ad61; }
.action-btn-icon.delete { background: #fff0f0; color: #dc2626; }
.action-btn-icon:hover  { filter: brightness(0.92); }

/* ══════════════════════════════════════
   MOBILE CARDS (<768px)
   ══════════════════════════════════════ */
@media (max-width: 767px) {
    .stock-table-wrap { display: none; }
    .stock-cards-wrap { display: block; }
    .stocks-page-header { margin-bottom: 14px; }
    .stocks-page-title h4 { font-size: 1.1rem; }

    /* ── Compact filter panel on mobile ── */
    .filter-panel { padding: 10px 12px; }
    .filter-panel .filter-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 7px;
        align-items: center;
    }
    /* Search bar spans full width */
    .filter-panel .filter-search { grid-column: 1 / -1; min-width: auto; }
    /* Selects: compact, two per row */
    .filter-panel select {
        min-width: auto;
        padding: 7px 8px;
        font-size: 0.8rem;
        width: 100%;
    }
    /* Search & reset buttons span full width */
    .btn-search        { grid-column: 1 / -1; text-align: center; }
    .btn-filter-reset  { grid-column: 1 / -1; text-align: center; }
}

.stock-cards-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.stock-card {
    background: #fff;
    border-radius: 14px;
    border: 1.5px solid #eef0f3;
    overflow: hidden;
    transition: box-shadow 0.15s, transform 0.1s;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
}
.stock-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,.10);
    transform: translateY(-1px);
}
.stock-card.card-selected {
    border-color: #d2ad61;
    box-shadow: 0 0 0 3px rgba(210,173,97,0.18);
}

.stock-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px 10px;
    gap: 10px;
}
.stock-card__header-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.stock-card__model {
    font-size: 0.97rem;
    font-weight: 800;
    color: #1d2128;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.stock-card__id {
    font-size: 0.74rem;
    color: #adb5bd;
    font-weight: 600;
    margin-top: 1px;
}

.stock-card__divider {
    height: 1px;
    background: #f0f2f5;
    margin: 0 16px;
}

.stock-card__body {
    padding: 12px 16px;
}
.stock-card__imei {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    background: #f8f9fb;
    border-radius: 8px;
    padding: 8px 12px;
}
.stock-card__imei-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #8a929e;
    margin-bottom: 2px;
}
.stock-card__imei-value {
    font-family: 'Courier New', monospace;
    font-size: 0.8rem;
    color: #3d4354;
    font-weight: 600;
}
.stock-card__imei-copy {
    background: none;
    border: none;
    cursor: pointer;
    color: #adb5bd;
    font-size: 0.9rem;
    padding: 4px 6px;
    border-radius: 6px;
    transition: all 0.15s;
    flex-shrink: 0;
}
.stock-card__imei-copy:hover { background: #eef2ff; color: #d2ad61; }

.stock-card__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 10px;
}
.stock-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.76rem;
    font-weight: 600;
    background: #f0f2f5;
    color: #3d4354;
}
.stock-chip.chip-storage { background: #eef0f3; color: #1d2128; }
.stock-chip.chip-color   { background: #fdf6ea; color: #b8953f; }
.stock-chip.chip-price   { background: #eef2ff; color: #4338ca; font-size: 0.82rem; font-weight: 800; }

.stock-card__dates {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}
.stock-card__date-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.77rem;
    color: #8a929e;
}
.stock-card__date-item i { font-size: 0.85rem; }

.stock-card__footer {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px 14px;
    border-top: 1px solid #f0f2f5;
}
.btn-card-action {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 8px 10px;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-card-view   { background: #eef2ff; color: #4338ca; }
.btn-card-edit   { background: #fdf6ea; color: #b8953f; }
.btn-card-delete { background: #fff0f0; color: #dc2626; }
.btn-card-view:hover   { background: #e0e7ff; color: #3730a3; text-decoration: none; }
.btn-card-edit:hover   { background: #fef0ca; color: #92740a; text-decoration: none; }
.btn-card-delete:hover { background: #fecaca; color: #b91c1c; }

/* ── Empty State ── */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}
.empty-state-icon {
    width: 72px; height: 72px;
    background: #f8f9fb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 1.8rem;
    color: #ccd0d5;
}
.empty-state h5 { font-size: 1rem; font-weight: 700; color: #3d4354; margin-bottom: 6px; }
.empty-state p  { font-size: 0.84rem; color: #8a929e; margin-bottom: 20px; }
.btn-reset {
    background: #d2ad61;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 9px 20px;
    font-size: 0.84rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-reset:hover { background: #b8953f; color: #fff; text-decoration: none; }

/* ── Count badge ── */
.result-count {
    font-size: 0.82rem;
    color: #8a929e;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.result-count strong { color: #1d2128; }

/* ── Pagination wrapper ── */
.pagination-wrap {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
}
</style>

<div class="container-fluid">
    @include('include.alert')

    <div class="card-box">

        {{-- ══ Page Header ══════════════════════════════════════════════ --}}
        <div class="stocks-page-header">
            <div class="stocks-page-title">
                <h4><i class="mdi mdi-package-variant mr-1" style="color:#d2ad61;"></i> All Stocks</h4>
                <p>Manage your mobile inventory</p>
            </div>
            <div class="stocks-header-actions">
                <a href="{{ request()->fullUrlWithQuery(['download' => 'csv']) }}" class="btn-csv">
                    <i class="mdi mdi-file-excel-box"></i> Export CSV
                </a>
                @can('purchases.create')
                <div class="dropdown">
                    <button class="btn-add-stock dropdown-toggle" data-toggle="dropdown">
                        <i class="mdi mdi-plus-circle"></i> Add Stock
                    </button>
                    <div class="dropdown-menu dropdown-menu-right mt-1">
                        <a class="dropdown-item" href="{{ route('purchase.create') }}">
                            <i class="mdi mdi-cellphone mr-1"></i> Single Stock
                        </a>
                        <a class="dropdown-item" href="{{ route('purchase.create.multiple') }}">
                            <i class="mdi mdi-cellphone-multiple mr-1"></i> Multiple Stocks
                        </a>
                    </div>
                </div>
                @endcan
            </div>
        </div>

        {{-- ══ Filter Panel ══════════════════════════════════════════════ --}}
        <div class="filter-panel">
            <form action="{{ route('allpurchases') }}" method="GET" id="filterpurchase">
                <div class="filter-row">
                    <div class="filter-search">
                        <i class="mdi mdi-magnify search-icon"></i>
                        <input type="text" name="search"
                               placeholder="Search model, IMEI..."
                               value="{{ request()->input('search') }}">
                    </div>
                    <select name="year" onchange="this.form.submit()">
                        <option value="">All Years</option>
                        <option value="2026" @selected($year == 2026)>2026</option>
                        <option value="2025" @selected($year == 2025)>2025</option>
                        <option value="2024" @selected($year == 2024)>2024</option>
                        <option value="2023" @selected($year == 2023)>2023</option>
                        <option value="2022" @selected($year == 2022)>2022</option>
                    </select>
                    <select name="storage" onchange="this.form.submit()">
                        <option value="">All Storage</option>
                        <option value="64"  @selected($storage == 64)>64 GB</option>
                        <option value="128" @selected($storage == 128)>128 GB</option>
                        <option value="256" @selected($storage == 256)>256 GB</option>
                        <option value="512" @selected($storage == 512)>512 GB</option>
                    </select>
                    <select name="color" onchange="this.form.submit()">
                        <option value="">All Colors</option>
                        @foreach ($colors as $colorOption)
                            <option value="{{ $colorOption->color }}"
                                @selected(strtolower($colorOption->color) == strtolower($selectedcolor))>
                                {{ $colorOption->color }}
                            </option>
                        @endforeach
                    </select>
                    <select name="is_sold" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="2" @selected($issold == 2)>Available</option>
                        <option value="1" @selected($issold == 1)>Sold</option>
                    </select>
                    <button type="submit" class="btn-search">
                        <i class="mdi mdi-magnify"></i> Search
                    </button>
                    @if(request()->anyFilled(['search','year','storage','color','is_sold']))
                    <a href="{{ route('allpurchases') }}" class="btn-filter-reset">
                        <i class="mdi mdi-close"></i> Clear
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- ══ Bulk Action Bar ══════════════════════════════════════════ --}}
        <div id="bulkActionBar" class="bulk-bar" style="display:none;">
            <div class="bulk-bar-info">
                <i class="mdi mdi-checkbox-marked-circle-outline" style="font-size:1.2rem;"></i>
                <span class="count-pill" id="selectedCount">0</span>
                items selected
            </div>
            <div class="bulk-bar-actions">
                <button class="btn-bulk-delete" onclick="confirmBulkDelete()">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
                <button class="btn-bulk-cancel" onclick="clearSelection()">
                    <i class="mdi mdi-close"></i> Deselect All
                </button>
            </div>
        </div>

        {{-- Hidden bulk delete form --}}
        <form id="bulkDeleteForm" method="POST" action="{{ route('purchases.bulk-delete') }}" style="display:none;">
            @csrf
            <div id="bulkIdsContainer"></div>
        </form>

        @if (count($allPurchases))

            {{-- Result count --}}
            <div class="result-count">
                <i class="mdi mdi-package-variant-closed"></i>
                Showing <strong>{{ $totalItems }}</strong> stock(s)
                @if(request()->anyFilled(['search','year','storage','color','is_sold']))
                    matching your filters
                @endif
            </div>

            {{-- ══ DESKTOP TABLE ══════════════════════════════════════════ --}}
            <div class="stock-table-wrap">
                <div style="overflow-x:auto;">
                    <table class="stocks-table" id="stocks-tbl">
                        <thead>
                            <tr>
                                @can('purchases.delete')
                                <th style="width:44px;">
                                    <input type="checkbox" id="selectAllCheckbox"
                                           class="custom-check" title="Select all"
                                           onchange="toggleSelectAll(this)">
                                </th>
                                @endcan
                                <th>
                                    <a href="{{ route('allpurchases', array_merge(request()->query(), ['direction' => $sortDirection == 'asc' ? 'desc' : 'asc'])) }}">
                                        Sr.No
                                        <i class="fa fa-arrow-{{ $sortDirection == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    </a>
                                </th>
                                <th>Model</th>
                                <th>IMEI</th>
                                <th>Storage</th>
                                <th>Color</th>
                                <th>Buy Price</th>
                                <th>Buy Date</th>
                                <th>Sale Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allPurchases as $purchase)
                            <tr id="row-{{ $purchase->id }}">
                                @can('purchases.delete')
                                <td>
                                    <input type="checkbox"
                                           class="bulk-checkbox tbl-cb custom-check"
                                           value="{{ $purchase->id }}"
                                           onchange="updateBulkBar(this)">
                                </td>
                                @endcan
                                <td>
                                    <a href="{{ route('purchase-detail', $purchase->id) }}" class="stock-id-link">
                                        #{{ $purchase->id }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('purchase-detail', $purchase->id) }}" class="stock-model-link">
                                        {{ ucfirst($purchase->model) }}
                                    </a>
                                </td>
                                <td class="imei-cell">
                                    {{ $purchase->imei }}
                                    <button class="imei-copy-btn" onclick="copyToClipboard('{{ $purchase->imei }}')" title="Copy IMEI">
                                        <i class="far fa-copy"></i>
                                    </button>
                                </td>
                                <td><span class="storage-chip">{{ $purchase->storage }} GB</span></td>
                                <td>
                                    <span class="color-chip">
                                        <i class="mdi mdi-circle" style="font-size:0.7rem; color:#d2ad61;"></i>
                                        {{ $purchase->color }}
                                    </span>
                                </td>
                                <td class="price-cell">₹{{ number_format($purchase->purchase_price, 2) }}</td>
                                <td class="date-cell">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}</td>
                                <td class="date-cell">
                                    @if($purchase->sell_date && $purchase->is_sold)
                                        <span style="color:#15803d;">{{ \Carbon\Carbon::parse($purchase->sell_date)->format('d M Y') }}</span>
                                    @else
                                        <span style="color:#ccd0d5;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($purchase->is_sold == 1)
                                        <span class="status-badge sold">
                                            <i class="mdi mdi-tag-check"></i> Sold
                                        </span>
                                    @else
                                        <span class="status-badge available">
                                            <i class="mdi mdi-check-circle"></i> Available
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="{{ route('purchase-detail', $purchase->id) }}" class="action-btn-icon view" title="View">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </a>
                                        @if($purchase->is_sold == 0)
                                        <a href="{{ route('purchase.edit', $purchase->id) }}" class="action-btn-icon edit" title="Edit">
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </a>
                                        @endif
                                        @can('purchases.delete')
                                        <form method="post" action="{{ route('delete-stock', $purchase->id) }}"
                                              class="d-inline del-form" id="del-tbl-{{ $purchase->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button" class="action-btn-icon delete" title="Delete"
                                                    onclick="confirmSingleDelete({{ $purchase->id }})">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ══ MOBILE CARDS ══════════════════════════════════════════ --}}
            <div class="stock-cards-wrap">
                <div class="stock-cards-grid">
                    @foreach($allPurchases as $purchase)
                    <div class="stock-card {{ $purchase->is_sold == 1 ? 'card-sold' : 'card-available' }}"
                         id="card-{{ $purchase->id }}">

                        {{-- Card Header --}}
                        <div class="stock-card__header">
                            <div class="stock-card__header-left">
                                @can('purchases.delete')
                                <input type="checkbox"
                                       class="bulk-checkbox card-cb custom-check"
                                       value="{{ $purchase->id }}"
                                       onchange="updateBulkBar(this)">
                                @endcan
                                <div style="min-width:0;">
                                    <div class="stock-card__model">{{ ucfirst($purchase->model) }}</div>
                                    <div class="stock-card__id">#{{ $purchase->id }}</div>
                                </div>
                            </div>
                            @if($purchase->is_sold == 1)
                                <span class="status-badge sold"><i class="mdi mdi-tag-check"></i> Sold</span>
                            @else
                                <span class="status-badge available"><i class="mdi mdi-check-circle"></i> Available</span>
                            @endif
                        </div>

                        <div class="stock-card__divider"></div>

                        {{-- Card Body --}}
                        <div class="stock-card__body">
                            {{-- IMEI --}}
                            <div class="stock-card__imei">
                                <div>
                                    <div class="stock-card__imei-label">IMEI</div>
                                    <div class="stock-card__imei-value">{{ $purchase->imei }}</div>
                                </div>
                                <button class="stock-card__imei-copy"
                                        onclick="copyToClipboard('{{ $purchase->imei }}')"
                                        title="Copy IMEI">
                                    <i class="far fa-copy"></i>
                                </button>
                            </div>

                            {{-- Chips --}}
                            <div class="stock-card__chips">
                                <span class="stock-chip chip-storage">
                                    <i class="mdi mdi-memory" style="font-size:0.85rem;"></i>
                                    {{ $purchase->storage }} GB
                                </span>
                                <span class="stock-chip chip-color">
                                    <i class="mdi mdi-palette" style="font-size:0.85rem;"></i>
                                    {{ $purchase->color }}
                                </span>
                                <span class="stock-chip chip-price">
                                    ₹{{ number_format($purchase->purchase_price, 2) }}
                                </span>
                            </div>

                            {{-- Dates --}}
                            <div class="stock-card__dates">
                                <div class="stock-card__date-item">
                                    <i class="mdi mdi-calendar-arrow-right" style="color:#d2ad61;"></i>
                                    <span>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}</span>
                                </div>
                                @if($purchase->sell_date && $purchase->is_sold)
                                <div class="stock-card__date-item">
                                    <i class="mdi mdi-calendar-check" style="color:#15803d;"></i>
                                    <span style="color:#15803d;">{{ \Carbon\Carbon::parse($purchase->sell_date)->format('d M Y') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="stock-card__footer">
                            <a href="{{ route('purchase-detail', $purchase->id) }}" class="btn-card-action btn-card-view">
                                <i class="mdi mdi-eye-outline"></i> View
                            </a>
                            @if($purchase->is_sold == 0)
                            <a href="{{ route('purchase.edit', $purchase->id) }}" class="btn-card-action btn-card-edit">
                                <i class="mdi mdi-pencil-outline"></i> Edit
                            </a>
                            @endif
                            @can('purchases.delete')
                            <form method="post" action="{{ route('delete-stock', $purchase->id) }}"
                                  class="d-inline del-form" id="del-card-{{ $purchase->id }}">
                                @csrf @method('DELETE')
                                <button type="button" class="btn-card-action btn-card-delete"
                                        onclick="confirmSingleDelete({{ $purchase->id }})">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            @endcan
                        </div>

                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ══ Pagination ══════════════════════════════════════════════ --}}
            <div class="pagination-wrap">
                {{ $allPurchases->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
            </div>

        @else
            {{-- ══ Empty State ══════════════════════════════════════════════ --}}
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="mdi mdi-package-variant-closed"></i>
                </div>
                <h5>No stocks found</h5>
                <p>
                    @if(request()->anyFilled(['search','year','storage','color','is_sold']))
                        No results match your current filters.
                    @else
                        Start by adding your first stock item.
                    @endif
                </p>
                @if(request()->anyFilled(['search','year','storage','color','is_sold']))
                    <a href="{{ route('allpurchases') }}" class="btn-reset">
                        <i class="mdi mdi-refresh"></i> Clear Filters
                    </a>
                @else
                    @can('purchases.create')
                    <a href="{{ route('purchase.create') }}" class="btn-reset">
                        <i class="mdi mdi-plus-circle"></i> Add First Stock
                    </a>
                    @endcan
                @endif
            </div>
        @endif

    </div>{{-- /.card-box --}}
</div>{{-- /.container-fluid --}}

<script>
    /* ── Helpers ── */
    function getChecked() {
        // Only consider checkboxes from the currently visible view
        return Array.from(document.querySelectorAll('.bulk-checkbox:checked'))
            .filter(el => el.offsetParent !== null);
    }
    function getAllVisible() {
        return Array.from(document.querySelectorAll('.bulk-checkbox'))
            .filter(el => el.offsetParent !== null);
    }
    function getMaster() { return document.getElementById('selectAllCheckbox'); }

    /* ── Toggle all (table header checkbox) ── */
    function toggleSelectAll(master) {
        const tableView = window.innerWidth >= 768;
        document.querySelectorAll(tableView ? '.tbl-cb' : '.card-cb').forEach(cb => {
            cb.checked = master.checked;
            highlightRow(cb, master.checked);
        });
        refreshBar();
    }

    /* ── Highlight row/card ── */
    function highlightRow(cb, checked) {
        const row = cb.closest('tr');
        const card = cb.closest('.stock-card');
        if (row)  row.classList.toggle('row-selected', checked);
        if (card) card.classList.toggle('card-selected', checked);
    }

    /* ── Update bar after individual checkbox change ── */
    function updateBulkBar(cb) {
        highlightRow(cb, cb.checked);
        const allVis     = getAllVisible();
        const checkedVis = getChecked();
        const master     = getMaster();
        if (master) {
            master.checked       = checkedVis.length > 0 && checkedVis.length === allVis.length;
            master.indeterminate = checkedVis.length > 0 && checkedVis.length < allVis.length;
        }
        refreshBar();
    }

    /* ── Show / hide the bulk bar ── */
    function refreshBar() {
        const checked = getChecked();
        const bar     = document.getElementById('bulkActionBar');
        if (checked.length > 0) {
            bar.style.display = 'flex';
            document.getElementById('selectedCount').textContent = checked.length;
        } else {
            bar.style.display = 'none';
        }
    }

    /* ── Deselect everything ── */
    function clearSelection() {
        document.querySelectorAll('.bulk-checkbox').forEach(cb => {
            cb.checked = false;
            highlightRow(cb, false);
        });
        const master = getMaster();
        if (master) { master.checked = false; master.indeterminate = false; }
        refreshBar();
    }

    /* ── Bulk delete ── */
    async function confirmBulkDelete() {
        const checked = getChecked();
        if (!checked.length) return;
        const ok = await vmConfirm({
            title:       'Delete ' + checked.length + ' stock(s)?',
            text:        'All selected items will be permanently removed.',
            icon:        'warning',
            confirmText: 'Yes, Delete All',
            cancelText:  'Cancel',
        });
        if (!ok) return;
        const container = document.getElementById('bulkIdsContainer');
        container.innerHTML = '';
        checked.forEach(cb => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = 'ids[]';
            inp.value = cb.value;
            container.appendChild(inp);
        });
        document.getElementById('bulkDeleteForm').submit();
    }

    /* ── Single delete ── */
    async function confirmSingleDelete(id) {
        const ok = await vmConfirm({
            title:       'Delete this stock?',
            text:        'This action cannot be undone.',
            icon:        'warning',
            confirmText: 'Yes, Delete',
            cancelText:  'Cancel',
        });
        if (!ok) return;
        // Submit whichever delete form is currently visible
        const form = window.innerWidth >= 768
            ? document.getElementById('del-tbl-' + id)
            : document.getElementById('del-card-' + id);
        if (form) form.submit();
    }

    /* ── Copy IMEI ── */
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text);
        vmToast('IMEI copied to clipboard', 'success');
    }

    /* ── Clear selection on view change (resize) ── */
    let lastBreakpoint = window.innerWidth >= 768;
    window.addEventListener('resize', () => {
        const nowDesktop = window.innerWidth >= 768;
        if (nowDesktop !== lastBreakpoint) {
            clearSelection();
            lastBreakpoint = nowDesktop;
        }
    });
</script>
@endsection
