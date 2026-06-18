@extends('layout.app')

@section('title')
    Sales Analytics
@endsection

@section('content')
@php
    $displayMonth = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y');
@endphp

<script src="https://cdn.plot.ly/plotly-2.26.1.min.js"></script>

<style>
    /* ── Page Header ── */
    .analytics-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563a8 100%);
        border-radius: 14px;
        padding: 22px 28px;
        margin-top: 24px;
        margin-bottom: 24px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        position: relative;
    }
    .analytics-header-title h3 {
        font-size: 1.4rem;
        font-weight: 800;
        margin: 0 0 4px 0;
        color: #ffffff !important;
    }
    .analytics-header-title p {
        margin: 0;
        color: rgba(255,255,255,0.72);
        font-size: 0.85rem;
    }

    /* ── Custom Month Picker ── */
    .month-picker-container { position: relative; }

    .month-picker-trigger {
        display: flex;
        align-items: center;
        gap: 9px;
        background: rgba(255,255,255,0.15);
        border-radius: 10px;
        padding: 9px 16px;
        cursor: pointer;
        transition: background 0.15s;
        user-select: none;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .month-picker-trigger:hover { background: rgba(255,255,255,0.24); }
    .month-picker-trigger .mpt-label {
        color: rgba(255,255,255,0.7);
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .month-picker-trigger .mpt-value {
        color: #fff;
        font-size: 0.92rem;
        font-weight: 700;
    }
    .month-picker-trigger .mpt-arrow {
        color: rgba(255,255,255,0.6);
        font-size: 1rem;
        transition: transform 0.2s;
    }
    .month-picker-trigger.open .mpt-arrow { transform: rotate(180deg); }

    /* Dropdown Panel */
    .cmp-panel {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 290px;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 12px 40px rgba(0,0,0,0.18), 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        z-index: 9999;
        overflow: hidden;
        display: none;
        animation: cmpFadeIn 0.15s ease;
    }
    @keyframes cmpFadeIn {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .cmp-panel.open { display: block; }

    /* Panel header */
    .cmp-head {
        background: linear-gradient(135deg, #1e3a5f, #2563a8);
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .cmp-year-display {
        font-size: 1rem;
        font-weight: 800;
        color: #fff;
    }
    .cmp-nav-btn {
        background: rgba(255,255,255,0.15);
        border: none;
        border-radius: 7px;
        width: 30px; height: 30px;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.15s;
    }
    .cmp-nav-btn:hover { background: rgba(255,255,255,0.28); }
    .cmp-nav-btn:disabled { opacity: 0.3; cursor: not-allowed; }

    /* Month grid */
    .cmp-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        padding: 14px 14px 10px;
    }
    .cmp-month-btn {
        border: 1.5px solid transparent;
        border-radius: 9px;
        padding: 10px 4px;
        font-size: 0.84rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.14s;
        background: #f4f5f7;
        color: #374151;
        text-align: center;
    }
    .cmp-month-btn:hover:not(.cmp-disabled):not(.cmp-selected) {
        background: #e0e7ff;
        color: #4f46e5;
        border-color: #c7d2fe;
    }
    .cmp-month-btn.cmp-selected {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 3px 10px rgba(79,70,229,0.35);
    }
    .cmp-month-btn.cmp-current:not(.cmp-selected) {
        border-color: #4f46e5;
        color: #4f46e5;
        background: #ede9fe;
    }
    .cmp-month-btn.cmp-disabled {
        opacity: 0.32;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* Panel footer */
    .cmp-footer {
        padding: 10px 14px 12px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .cmp-this-month-btn {
        background: none;
        border: 1.5px solid #4f46e5;
        border-radius: 7px;
        padding: 5px 14px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #4f46e5;
        cursor: pointer;
        transition: all 0.15s;
    }
    .cmp-this-month-btn:hover { background: #4f46e5; color: #fff; }

    /* ── KPI Cards ── */
    .kpi-card {
        border-radius: 14px;
        padding: 20px 18px 16px;
        margin-bottom: 20px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(0,0,0,0.16); }
    .kpi-card::before {
        content: ''; position: absolute;
        top: -30px; right: -30px;
        width: 90px; height: 90px;
        border-radius: 50%; background: rgba(255,255,255,0.1);
    }
    .kpi-card.revenue { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
    .kpi-card.profit  { background: linear-gradient(135deg, #059669 0%, #0d9488 100%); }
    .kpi-card.count   { background: linear-gradient(135deg, #0284c7 0%, #0891b2 100%); }
    .kpi-card.avg     { background: linear-gradient(135deg, #d97706 0%, #dc2626 100%); }
    .kpi-icon { font-size: 1.3rem; opacity: 0.65; margin-bottom: 10px; display: block; }
    .kpi-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; font-weight: 700; margin-bottom: 5px; }
    .kpi-value { font-size: 1.5rem; font-weight: 800; line-height: 1; color: #fff; }

    /* ── Chart Cards ── */
    .chart-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.06);
        overflow: hidden;
        margin-bottom: 24px;
        border: 1px solid #f0f2f5;
    }
    .chart-card-header {
        padding: 13px 20px;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        background: #fafbfc;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
    .dot-blue  { background: #4f46e5; }
    .dot-green { background: #059669; }
    .dot-amber { background: #f59e0b; }
    .chart-card-body { padding: 4px 2px 2px; }

    /* ── Empty State ── */
    .empty-chart {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        height: 240px; color: #9ca3af;
        font-size: 0.88rem; gap: 8px;
    }
    .empty-chart i { font-size: 2.2rem; opacity: 0.35; }
</style>

{{-- Hidden form that gets submitted by the custom picker --}}
<form method="GET" action="{{ route('saleschart') }}" id="monthForm" style="display:none">
    <input type="hidden" id="monthValue" name="month" value="{{ $selectedMonth }}">
</form>

{{-- ── Page Header ─────────────────────────────────────────── --}}
<div class="analytics-header">
    <div class="analytics-header-title">
        <h3>Sales Analytics</h3>
        <p>Month-to-date performance &nbsp;&middot;&nbsp; Live data</p>
    </div>

    <div class="month-picker-container">
        <div class="month-picker-trigger" id="mptTrigger" onclick="cmpToggle()">
            <span class="mpt-label"><i class="mdi mdi-calendar-month"></i> Viewing</span>
            <span class="mpt-value" id="mptDisplay">{{ $displayMonth }}</span>
            <i class="mdi mdi-chevron-down mpt-arrow"></i>
        </div>

        <div class="cmp-panel" id="cmpPanel">
            {{-- Year navigation --}}
            <div class="cmp-head">
                <button class="cmp-nav-btn" id="cmpPrevBtn" onclick="cmpPrevYear()">
                    <i class="mdi mdi-chevron-left"></i>
                </button>
                <span class="cmp-year-display" id="cmpYearDisplay"></span>
                <button class="cmp-nav-btn" id="cmpNextBtn" onclick="cmpNextYear()">
                    <i class="mdi mdi-chevron-right"></i>
                </button>
            </div>

            {{-- Month grid --}}
            <div class="cmp-grid" id="cmpGrid"></div>

            {{-- Footer --}}
            <div class="cmp-footer">
                <span style="font-size:0.78rem;color:#9ca3af;">Select a month</span>
                <button class="cmp-this-month-btn" onclick="cmpGoToday()">This month</button>
            </div>
        </div>
    </div>
</div>

{{-- ── KPI Cards ───────────────────────────────────────────── --}}
<div class="row mb-1">
    <div class="col-6 col-md-3">
        <div class="kpi-card revenue">
            <span class="kpi-icon"><i class="mdi mdi-currency-inr"></i></span>
            <div class="kpi-label">Total Revenue</div>
            <div class="kpi-value" id="kpi-revenue">—</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card profit">
            <span class="kpi-icon"><i class="mdi mdi-trending-up"></i></span>
            <div class="kpi-label">Total Profit</div>
            <div class="kpi-value" id="kpi-profit">—</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card count">
            <span class="kpi-icon"><i class="mdi mdi-receipt"></i></span>
            <div class="kpi-label">Total Invoices</div>
            <div class="kpi-value" id="kpi-count">—</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card avg">
            <span class="kpi-icon"><i class="mdi mdi-calculator-variant"></i></span>
            <div class="kpi-label">Avg Sale Value</div>
            <div class="kpi-value" id="kpi-avg">—</div>
        </div>
    </div>
</div>

{{-- ── Row 1: Daily Sales + Payment Mode ──────────────────── --}}
<div class="row">
    <div class="col-12 col-md-8">
        <div class="chart-card">
            <div class="chart-card-header">
                <span class="dot dot-blue"></span> Daily Sales Overview
            </div>
            <div class="chart-card-body">
                <div id="dailySales"></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="chart-card">
            <div class="chart-card-header">
                <span class="dot dot-amber"></span> Payment Mode Breakdown
            </div>
            <div class="chart-card-body">
                <div id="paymentMode"></div>
            </div>
        </div>
    </div>
</div>

{{-- ── Row 2: Daily Profit ──────────────────────────────────── --}}
<div class="row">
    <div class="col-12">
        <div class="chart-card">
            <div class="chart-card-header">
                <span class="dot dot-green"></span> Daily Profit Analysis
            </div>
            <div class="chart-card-body">
                <div id="profitAnalysis"></div>
            </div>
        </div>
    </div>
</div>

<script>
/* ════════════════════════════════════════
   Custom Month Picker
════════════════════════════════════════ */
(function () {
    const MONTHS     = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const MONTHS_FULL= ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const MIN_YEAR   = 2020;

    const now        = new Date();
    const maxYear    = now.getFullYear();
    const maxMonth   = now.getMonth(); // 0-indexed

    // Parse selected month from server
    const parts      = '{{ $selectedMonth }}'.split('-');
    let cmpYear      = parseInt(parts[0]);
    let cmpSelected  = parseInt(parts[1]) - 1; // 0-indexed

    function render() {
        document.getElementById('cmpYearDisplay').textContent = cmpYear;
        document.getElementById('cmpPrevBtn').disabled = (cmpYear <= MIN_YEAR);
        document.getElementById('cmpNextBtn').disabled = (cmpYear >= maxYear);

        const grid = document.getElementById('cmpGrid');
        grid.innerHTML = '';
        MONTHS.forEach((name, idx) => {
            const btn     = document.createElement('button');
            btn.className = 'cmp-month-btn';
            btn.textContent = name;

            const isFuture   = cmpYear > maxYear || (cmpYear === maxYear && idx > maxMonth);
            const isSelected = (cmpYear === parseInt(parts[0]) && idx === cmpSelected);
            const isCurrent  = (cmpYear === maxYear && idx === maxMonth);

            if (isFuture)   btn.classList.add('cmp-disabled');
            if (isSelected) btn.classList.add('cmp-selected');
            if (isCurrent && !isSelected) btn.classList.add('cmp-current');

            btn.addEventListener('click', () => cmpSelect(idx));
            grid.appendChild(btn);
        });
    }

    window.cmpToggle = function () {
        const panel   = document.getElementById('cmpPanel');
        const trigger = document.getElementById('mptTrigger');
        const isOpen  = panel.classList.contains('open');
        panel.classList.toggle('open', !isOpen);
        trigger.classList.toggle('open', !isOpen);
        if (!isOpen) render();
    };

    window.cmpPrevYear = function () {
        if (cmpYear > MIN_YEAR) { cmpYear--; render(); }
    };
    window.cmpNextYear = function () {
        if (cmpYear < maxYear) { cmpYear++; render(); }
    };

    window.cmpSelect = function (monthIdx) {
        const mm    = String(monthIdx + 1).padStart(2, '0');
        document.getElementById('monthValue').value = `${cmpYear}-${mm}`;
        document.getElementById('monthForm').submit();
    };

    window.cmpGoToday = function () {
        const mm = String(maxMonth + 1).padStart(2, '0');
        document.getElementById('monthValue').value = `${maxYear}-${mm}`;
        document.getElementById('monthForm').submit();
    };

    // Close on outside click
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#mptTrigger') && !e.target.closest('#cmpPanel')) {
            document.getElementById('cmpPanel').classList.remove('open');
            document.getElementById('mptTrigger').classList.remove('open');
        }
    });
})();

/* ════════════════════════════════════════
   Charts
════════════════════════════════════════ */
(function () {
    const salesData = @json($allSales);

    const fmt    = n => '₹' + Number(n).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    const toDate = s => (s || '').toString().substring(0, 10);

    const groupBy = (arr, fn) => arr.reduce((r, i) => {
        const k = typeof fn === 'function' ? fn(i) : i[fn];
        (r[k] = r[k] || []).push(i); return r;
    }, {});
    const sumBy = (arr, key) => arr.reduce((t, i) => t + (parseFloat(i[key]) || 0), 0);

    /* KPIs */
    const totalRevenue = sumBy(salesData, 'net_amount');
    const totalProfit  = salesData.reduce((t, s) =>
        t + (s.items || []).reduce((st, it) => st + (parseFloat(it.profit) || 0), 0), 0);
    const totalCount   = salesData.length;
    const avgSale      = totalCount ? totalRevenue / totalCount : 0;

    document.getElementById('kpi-revenue').textContent = fmt(totalRevenue);
    document.getElementById('kpi-profit').textContent  = fmt(totalProfit);
    document.getElementById('kpi-count').textContent   = totalCount;
    document.getElementById('kpi-avg').textContent     = fmt(avgSale);

    const plotCfg = { displayModeBar: false, responsive: true };
    const baseLayout = {
        height: 320,
        margin: { t: 16, r: 16, b: 64, l: 68 },
        paper_bgcolor: 'rgba(0,0,0,0)',
        plot_bgcolor:  'rgba(0,0,0,0)',
        font: { family: 'Arial, sans-serif', size: 11, color: '#6b7280' },
        xaxis: { gridcolor: '#f3f4f6', linecolor: '#e5e7eb', tickangle: -35, automargin: true },
        yaxis: { gridcolor: '#f3f4f6', linecolor: '#e5e7eb', zeroline: false },
        hoverlabel: { bgcolor: '#1f2937', bordercolor: '#1f2937', font: { color: '#fff', size: 12 } },
        showlegend: false,
    };

    /* 1. Daily Sales Bar */
    const groupedSales = groupBy(salesData, s => toDate(s.invoice_date));
    const salesDates   = Object.keys(groupedSales).sort();
    const salesRev     = salesDates.map(d => sumBy(groupedSales[d], 'net_amount'));
    const salesCounts  = salesDates.map(d => groupedSales[d].length);

    if (salesDates.length) {
        Plotly.newPlot('dailySales', [{
            x: salesDates, y: salesRev, type: 'bar',
            marker: { color: '#4f46e5', opacity: 0.82, line: { width: 0 } },
            customdata: salesCounts,
            hovertemplate: '<b>%{x}</b><br>Revenue: <b>₹%{y:,.0f}</b><br>Invoices: %{customdata}<extra></extra>',
        }], {
            ...baseLayout,
            xaxis: { ...baseLayout.xaxis, title: { text: 'Date', standoff: 14 } },
            yaxis: { ...baseLayout.yaxis, title: { text: 'Revenue (₹)' } },
        }, plotCfg);
    } else {
        document.getElementById('dailySales').innerHTML =
            '<div class="empty-chart"><i class="mdi mdi-chart-bar"></i>No sales data for this period</div>';
    }

    /* 2. Payment Mode Donut */
    const payGroups = groupBy(salesData, 'payment_type');
    const payModes  = Object.keys(payGroups);
    const payRevs   = payModes.map(m => sumBy(payGroups[m], 'net_amount'));
    const payCounts = payModes.map(m => payGroups[m].length);

    if (payModes.length) {
        Plotly.newPlot('paymentMode', [{
            labels:     payModes.map(m => m.charAt(0).toUpperCase() + m.slice(1)),
            values:     payRevs, type: 'pie', hole: 0.5,
            marker:     { colors: ['#4f46e5','#059669','#f59e0b','#ef4444','#06b6d4','#8b5cf6','#ec4899'], line: { color: '#fff', width: 2 } },
            customdata: payCounts,
            hovertemplate: '<b>%{label}</b><br>Revenue: <b>₹%{value:,.0f}</b><br>Invoices: %{customdata}<br>Share: %{percent}<extra></extra>',
            textinfo: 'label+percent', textfont: { size: 10 }, pull: payModes.map(() => 0.02),
        }], {
            height: 320, margin: { t: 20, r: 10, b: 30, l: 10 },
            paper_bgcolor: 'rgba(0,0,0,0)',
            font: { family: 'Arial, sans-serif', size: 11 },
            hoverlabel: baseLayout.hoverlabel,
            showlegend: true, legend: { orientation: 'h', y: -0.1, font: { size: 10 } },
        }, plotCfg);
    } else {
        document.getElementById('paymentMode').innerHTML =
            '<div class="empty-chart"><i class="mdi mdi-chart-pie"></i>No payment data</div>';
    }

    /* 3. Daily Profit Line */
    const profitByDate = groupBy(salesData, s => toDate(s.invoice_date));
    const profitDates  = Object.keys(profitByDate).sort();
    const dailyProfit  = profitDates.map(date =>
        profitByDate[date].reduce((t, sale) =>
            t + (sale.items || []).reduce((s, it) => s + (parseFloat(it.profit) || 0), 0), 0)
    );

    if (profitDates.length) {
        Plotly.newPlot('profitAnalysis', [{
            x: profitDates, y: dailyProfit, type: 'scatter', mode: 'lines+markers',
            line:      { shape: 'spline', color: '#059669', width: 2.5, smoothing: 1.1 },
            marker:    { color: '#059669', size: 7, line: { color: '#fff', width: 2 } },
            fill:      'tozeroy', fillcolor: 'rgba(5,150,105,0.08)',
            hovertemplate: '<b>%{x}</b><br>Profit: <b>₹%{y:,.0f}</b><extra></extra>',
        }], {
            ...baseLayout,
            height: 280, margin: { t: 16, r: 16, b: 64, l: 76 },
            xaxis: { ...baseLayout.xaxis, title: { text: 'Date', standoff: 14 } },
            yaxis: { ...baseLayout.yaxis, title: { text: 'Profit (₹)' } },
        }, plotCfg);
    } else {
        document.getElementById('profitAnalysis').innerHTML =
            '<div class="empty-chart"><i class="mdi mdi-trending-up"></i>No profit data for this period</div>';
    }
})();
</script>

@endsection
