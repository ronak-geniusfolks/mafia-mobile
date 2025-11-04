@extends('layout.app')

@section('title', 'Dealer Payments')

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row align-items-center">
            <!-- Title & Buttons for Mobile -->
            <div class="col-12 d-flex justify-content-between align-items-center mb-2 d-md-none">
                <h4 class="page-title mb-0">Dealer Payments</h4>
            </div>

            <!-- Title for Desktop -->
            <div class="col-md-12 mt-2 mb-2 d-none d-md-block">
                <h4 class="page-title">Dealer Payments</h4>
            </div>
        </div>
        <!-- end page title -->

        <!-- Statistics Cards -->
        <div class="row">
            <!-- Total Remaining -->
            <div class="col-6 col-sm-6 col-md-2 mb-2">
                <div class="card text-white" style="background:#dc3545;">
                    <div class="card-body text-center p-2">
                        <p class="font-weight-bold text-uppercase mb-1" style="font-size: 0.7rem;">Total Remaining</p>
                        <h5 style="color: #fff">{{ config('constant.currency') }} <span id="totalRemainingStat">{{ number_format($statistics['total_remaining'] ?? 0, 2) }}</span></h5>
                    </div>
                </div>
            </div>

            <!-- Total Pending Bills -->
            <div class="col-6 col-sm-6 col-md-2 mb-2">
                <div class="card text-white" style="background:#ffc107;">
                    <div class="card-body text-center p-2">
                        <p class="font-weight-bold text-uppercase mb-1" style="font-size: 0.7rem;">Pending Bills</p>
                        <h5 style="color: #fff"><span id="totalPendingBillsStat">{{ $statistics['total_pending_bills'] ?? 0 }}</span></h5>
                    </div>
                </div>
            </div>

            <!-- Dealers with Pending -->
            <div class="col-6 col-sm-6 col-md-2 mb-2">
                <div class="card text-white" style="background:#17a2b8;">
                    <div class="card-body text-center p-2">
                        <p class="font-weight-bold text-uppercase mb-1" style="font-size: 0.7rem;">Dealers Pending</p>
                        <h5 style="color: #fff"><span id="dealersPendingStat">{{ $statistics['dealers_with_pending'] ?? 0 }}</span></h5>
                    </div>
                </div>
            </div>

            <!-- Today's Payments -->
            <div class="col-6 col-sm-6 col-md-2 mb-2">
                <div class="card text-white" style="background:#28a745;">
                    <div class="card-body text-center p-2">
                        <p class="font-weight-bold text-uppercase mb-1" style="font-size: 0.7rem;">Today's Payments</p>
                        <h5 style="color: #fff">{{ config('constant.currency') }} <span id="todayPaymentsStat">{{ number_format($statistics['today_payments'] ?? 0, 2) }}</span></h5>
                    </div>
                </div>
            </div>

            <!-- This Month's Payments -->
            <div class="col-6 col-sm-6 col-md-2 mb-2">
                <div class="card text-white" style="background:#6f42c1;">
                    <div class="card-body text-center p-2">
                        <p class="font-weight-bold text-uppercase mb-1" style="font-size: 0.7rem;">Month Payments</p>
                        <h5 style="color: #fff">{{ config('constant.currency') }} <span id="monthPaymentsStat">{{ number_format($statistics['month_payments'] ?? 0, 2) }}</span></h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dealerPaymentsTable" class="table table-centered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="font-weight-bold">#</th>
                                        <th class="font-weight-bold">Dealer Name</th>
                                        <th class="font-weight-bold">Contact Number</th>
                                        <th class="font-weight-bold">Address</th>
                                        <th class="font-weight-bold">Total Amount</th>
                                        <th class="font-weight-bold">Paid Amount</th>
                                        <th class="font-weight-bold">Remaining Amount</th>
                                        <th class="font-weight-bold">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-money-bill-wave mr-2"></i><span id="modalTitle">Dealer Payment</span>
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="paymentForm" action="{{ route('dealer-payments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="dealer_id" id="dealerId" value="">
                        <div class="modal-body p-4">
                            <!-- Dealer Info -->
                            <div class="alert alert-info mb-4">
                                <h6 class="mb-2"><strong>Dealer Information</strong></h6>
                                <p class="mb-1"><strong>Name:</strong> <span id="dealerName"></span></p>
                                <p class="mb-0"><strong>Contact:</strong> <span id="dealerContact"></span></p>
                            </div>

                            <!-- Total Remaining -->
                            <div class="alert alert-warning mb-4">
                                <h6 class="mb-0"><strong>Total Remaining Amount: {{ config('constant.currency') }} <span id="totalRemainingAmount">0.00</span></strong></h6>
                            </div>

                            <!-- Pending Bills List -->
                            <div class="mb-4">
                                <h6 class="mb-3"><strong>Pending Bills</strong></h6>
                                <div id="pendingBillsList" style="max-height: 400px; overflow-y: auto;">
                                    <!-- Bills accordion will be populated here -->
                                </div>
                            </div>

                            <!-- Payment Form -->
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="payment_amount" class="font-weight-bold">
                                        Payment Amount <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                           step="0.01"
                                           min="0.01"
                                           class="form-control form-control-lg @error('payment_amount') is-invalid @enderror"
                                           id="payment_amount"
                                           name="payment_amount"
                                           placeholder="Enter payment amount"
                                           autocomplete="off"
                                           required>
                                    <small class="form-text text-muted">Payment will be allocated to oldest bills first</small>
                                    @error('payment_amount')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="payment_date" class="font-weight-bold">
                                        Payment Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control form-control-lg @error('payment_date') is-invalid @enderror"
                                           id="payment_date"
                                           name="payment_date"
                                           placeholder="DD/MM/YYYY"
                                           autocomplete="off"
                                           value="{{ date('d/m/Y') }}"
                                           required>
                                    @error('payment_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-12">
                                    <label for="payment_type" class="font-weight-bold">
                                        Payment Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control form-control-lg @error('payment_type') is-invalid @enderror"
                                            id="payment_type"
                                            name="payment_type"
                                            required>
                                        <option value="cash">Cash</option>
                                    </select>
                                    @error('payment_type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-12">
                                    <label for="note" class="font-weight-bold">
                                        Note
                                    </label>
                                    <textarea class="form-control @error('note') is-invalid @enderror"
                                              id="note"
                                              name="note"
                                              rows="2"
                                              placeholder="Add a note (optional)"></textarea>
                                    @error('note')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top">
                            <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary btn-lg" id="submitPaymentBtn">
                                <i class="fas fa-save mr-1"></i> Process Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">
<style>
    #dealerPaymentsTable {
        width: 100% !important;
    }
    #dealerPaymentsTable td {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    #dealerPaymentsTable tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    /* Disable horizontal scroll on desktop/laptop screens */
    @media screen and (min-width: 992px) {
        .table-responsive {
            overflow-x: visible !important;
        }
        #dealerPaymentsTable {
            table-layout: auto;
        }
    }
    
    /* Enable horizontal scroll only on tablet and mobile */
    @media screen and (max-width: 991px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }
    
    /* Mobile responsive improvements */
    @media screen and (max-width: 767px) {
        #dealerPaymentsTable thead th {
            font-size: 0.85rem;
            padding: 0.5rem 0.25rem;
            white-space: normal;
            line-height: 1.2;
        }
        
        #dealerPaymentsTable tbody td {
            font-size: 0.85rem;
            padding: 0.5rem 0.25rem;
        }
        
        /* Hide less important columns on mobile */
        #dealerPaymentsTable tbody tr.child td.dtr-control::before {
            margin-right: 0.5rem;
        }
        
        /* Improve button sizing on mobile */
        #dealerPaymentsTable tbody td .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }
    
    /* Tablet responsive improvements */
    @media screen and (min-width: 768px) and (max-width: 991px) {
        #dealerPaymentsTable tbody td .btn-sm {
            padding: 0.3rem 0.6rem;
            font-size: 0.8rem;
        }
    }

    .modal-content {
        border-radius: 15px;
        overflow: hidden;
    }

    .modal-header {
        border-bottom: none;
        padding: 1.5rem;
    }

    .modal-body {
        padding: 2rem;
    }

    .remaining-amount {
        font-size: 1.2rem;
        font-weight: bold;
        color: #dc3545;
    }

    .bill-accordion {
        margin-bottom: 12px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        background: #fff;
        border: 1px solid #e0e0e0;
        transition: box-shadow 0.3s ease;
    }

    .bill-accordion:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }

    .bill-accordion-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        padding: 16px 20px;
        cursor: pointer;
        border: none;
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .bill-accordion-header::after {
        content: '\f078';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        font-size: 0.875rem;
        color: #6c757d;
        transition: transform 0.3s ease, color 0.3s ease;
        margin-left: 15px;
    }

    .bill-accordion-header:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
    }

    .bill-accordion-header.active {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(0,123,255,0.3);
    }

    .bill-accordion-header.active::after {
        transform: rotate(180deg);
        color: white;
    }

    .bill-accordion-header.active .bill-title,
    .bill-accordion-header.active .bill-date,
    .bill-accordion-header.active .bill-amount,
    .bill-accordion-header.active .bill-remaining {
        color: white;
    }

    .bill-accordion-body {
        display: none;
        padding: 20px;
        background-color: #ffffff;
        border-top: 2px solid #f0f0f0;
    }

    .bill-accordion-body.show {
        display: block;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .bill-header-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        flex: 1;
        gap: 15px;
    }

    .bill-header-left {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex: 1;
        min-width: 200px;
    }

    .bill-header-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 6px;
    }

    .bill-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: #212529;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .bill-title::before {
        content: '\f02d';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        color: #007bff;
        font-size: 0.9rem;
    }

    .bill-accordion-header.active .bill-title::before {
        color: white;
    }

    .bill-date {
        font-size: 0.875rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .bill-date::before {
        content: '\f073';
        font-family: 'Font Awesome 5 Free';
        font-weight: 400;
        font-size: 0.75rem;
    }

    .bill-amount {
        font-size: 1.15rem;
        font-weight: 700;
        color: #28a745;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .bill-amount::before {
        content: '\f0d6';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        font-size: 0.875rem;
    }

    .bill-remaining {
        font-size: 0.9rem;
        color: #dc3545;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .bill-remaining::before {
        content: '\f071';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        font-size: 0.75rem;
    }

    .payment-row {
        display: grid;
        grid-template-columns: 120px 140px 100px 1fr;
        gap: 15px;
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        align-items: center;
        transition: background-color 0.2s ease;
    }

    .payment-row:hover {
        background-color: #f8f9fa;
    }

    .payment-row:last-child {
        border-bottom: none;
    }

    .payment-row-header {
        font-weight: 700;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 12px 15px;
        border-radius: 8px;
        color: #495057;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .payment-amount {
        font-weight: 700;
        color: #28a745;
        font-size: 1rem;
    }

    .payment-type {
        text-transform: capitalize;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        display: inline-block;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    .payment-type.cash {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .payment-type.credit {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .bill-details-section h6 {
        color: #495057;
        font-weight: 700;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #007bff;
        display: inline-block;
    }

    .bill-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        margin-bottom: 20px;
        padding: 16px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 10px;
        border: 1px solid #e9ecef;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: white;
        border-radius: 6px;
        border-left: 3px solid #007bff;
    }

    .summary-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.875rem;
    }

    .summary-value {
        font-weight: 700;
        color: #212529;
        font-size: 0.95rem;
    }

    .summary-value.text-danger {
        color: #dc3545;
    }

    .no-payments {
        text-align: center;
        padding: 30px;
        color: #6c757d;
        font-style: italic;
        background: #f8f9fa;
        border-radius: 8px;
        border: 2px dashed #dee2e6;
    }

    .paid-amount {
        font-size: 1.2rem;
        font-weight: bold;
        color: #28a745;
    }

    .total-amount {
        font-size: 1.2rem;
        font-weight: bold;
        color:rgb(255, 119, 7);
    }

    @media (max-width: 768px) {
        .modal-dialog {
            margin: 10px;
        }

        .modal-body {
            padding: 1rem;
        }

        .bill-header-info {
            flex-direction: column;
            align-items: flex-start;
        }

        .bill-header-right {
            align-items: flex-start;
            width: 100%;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
        }

        .bill-summary {
            grid-template-columns: 1fr;
        }

        .payment-row {
            grid-template-columns: 1fr;
            gap: 8px;
            padding: 12px;
        }

        .payment-row-header {
            display: none;
        }

        .payment-row > div::before {
            content: attr(data-label) ': ';
            font-weight: 600;
            color: #6c757d;
            margin-right: 8px;
        }

        .bill-accordion-header {
            padding: 12px 15px;
        }
    }
</style>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize datepicker
            $('#payment_date').datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                changeYear: true
            });

            // Function to update statistics
            function updateStatistics(statistics) {
                if (statistics) {
                    if (statistics.total_remaining !== undefined) {
                        $('#totalRemainingStat').text(parseFloat(statistics.total_remaining).toFixed(2));
                    }
                    if (statistics.total_pending_bills !== undefined) {
                        $('#totalPendingBillsStat').text(statistics.total_pending_bills);
                    }
                    if (statistics.dealers_with_pending !== undefined) {
                        $('#dealersPendingStat').text(statistics.dealers_with_pending);
                    }
                    if (statistics.today_payments !== undefined) {
                        $('#todayPaymentsStat').text(parseFloat(statistics.today_payments).toFixed(2));
                    }
                    if (statistics.month_payments !== undefined) {
                        $('#monthPaymentsStat').text(parseFloat(statistics.month_payments).toFixed(2));
                    }
                }
            }

            // Initialize DataTable
            const table = $('#dealerPaymentsTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('dealer-payments.data') }}",
                    type: 'GET',
                    dataSrc: function(json) {
                        // Update statistics from response
                        if (json.statistics) {
                            updateStatistics(json.statistics);
                        }
                        return json.data;
                    }
                },
                autoWidth: false,
                responsive: true,
                columns: [
                    {
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        },
                        orderable: false,
                        searchable: false,
                        width: '5%',
                        responsivePriority: 1
                    },
                    { 
                        data: 'name', 
                        name: 'name',
                        width: '15%',
                        responsivePriority: 1
                    },
                    { 
                        data: 'contact_number', 
                        name: 'contact_number',
                        width: '12%',
                        responsivePriority: 2,
                        render: function(data) {
                            if (!data || data.trim() === '') {
                                return '<span class="text-muted">-</span>';
                            }
                            return '<a href="tel:' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: 'address',
                        name: 'address',
                        width: '20%',
                        responsivePriority: 3,
                        render: function(data) {
                            if (!data || data.trim() === '') {
                                return '<span class="text-muted">-</span>';
                            }
                            return data.length > 40 ? data.substring(0, 40) + '...' : data;
                        }
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        width: '12%',
                        responsivePriority: 3,
                        render: function(data) {
                            return '<span class="total-amount">' + '{{ config('constant.currency') }} ' + parseFloat(data).toFixed(2) + '</span>';
                        }
                    },
                    {
                        data: 'paid_amount',
                        name: 'paid_amount',
                        width: '12%',
                        responsivePriority: 4,
                        render: function(data) {
                            return '<span class="paid-amount">' + '{{ config('constant.currency') }} ' + parseFloat(data).toFixed(2) + '</span>';
                        }
                    },
                    {
                        data: 'remaining_amount',
                        name: 'remaining_amount',
                        width: '12%',
                        responsivePriority: 2,
                        render: function(data) {
                            return '<span class="remaining-amount">' + '{{ config('constant.currency') }} ' + parseFloat(data).toFixed(2) + '</span>';
                        }
                    },
                    {
                        data: 'id',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '12%',
                        responsivePriority: 1,
                        render: function(data, type, row) {
                            return '<button class="btn btn-primary btn-sm view-payment" data-id="' + data + '" title="View & Pay">' +
                                '<i class="fas fa-eye mr-1 d-none d-md-inline"></i>' +
                                '<i class="fas fa-money-bill-wave mr-1 d-md-none"></i>' +
                                '<span class="d-none d-md-inline">View & Pay</span>' +
                                '<span class="d-md-none">Pay</span>' +
                                '</button>';
                        }
                    }
                ],
                order: [[4, 'desc']],
                pageLength: 10,
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
                    emptyTable: 'No dealers with pending payments found.',
                    zeroRecords: 'No matching dealers found'
                }
            });

            // Reset modal on close
            $('#paymentModal').on('hidden.bs.modal', function() {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
                $('#paymentForm')[0].reset();
                $('#dealerId').val('');
                $('#pendingBillsList').html('');
                $('#dealerName').text('');
                $('#dealerContact').text('');
                $('#totalRemainingAmount').text('0.00');
                $('.invalid-feedback').remove();
                $('.is-invalid').removeClass('is-invalid');
                $('#payment_date').datepicker('setDate', new Date());
            });

            // View payment button click
            $(document).on('click', '.view-payment', function() {
                const dealerId = $(this).data('id');
                const submitBtn = $('#submitPaymentBtn');
                const originalHtml = submitBtn.html();

                // Disable button
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Loading...');

                $.ajax({
                    url: '{{ url('dealer-payments') }}/' + dealerId + '/bills',
                    type: 'GET',
                    success: function(res) {
                        if (res.status && res.dealer && res.bills) {
                            // Populate dealer info
                            $('#dealerId').val(res.dealer.id);
                            $('#dealerName').text(res.dealer.name);
                            $('#dealerContact').text(res.dealer.contact_number || '-');
                            $('#totalRemainingAmount').text(parseFloat(res.total_remaining).toFixed(2));

                            // Note: Overpayments are now allowed, so no max limit is set

                            // Populate bills accordion
                            let billsHtml = '';
                            if (res.bills.length > 0) {
                                res.bills.forEach(function(bill, index) {
                                    const billId = 'bill-' + bill.id;
                                    billsHtml += '<div class="bill-accordion">';
                                    
                                    // Accordion Header
                                    billsHtml += '<div class="bill-accordion-header" data-toggle="accordion" data-target="#' + billId + '">';
                                    billsHtml += '<div class="bill-header-info">';
                                    billsHtml += '<div class="bill-header-left">';
                                    billsHtml += '<span class="bill-title">' + bill.bill_no + '</span>';
                                    billsHtml += '<span class="bill-date">' + bill.bill_date + '</span>';
                                    billsHtml += '</div>';
                                    billsHtml += '<div class="bill-header-right">';
                                    billsHtml += '<div class="bill-amount">' + '{{ config('constant.currency') }} ' + parseFloat(bill.net_amount).toFixed(2) + '</div>';
                                    billsHtml += '<div class="bill-remaining">' + '{{ config('constant.currency') }} ' + parseFloat(bill.remaining_amount).toFixed(2) + ' remaining</div>';
                                    billsHtml += '</div>';
                                    billsHtml += '</div>';
                                    billsHtml += '</div>';
                                    
                                    // Accordion Body
                                    billsHtml += '<div class="bill-accordion-body" id="' + billId + '">';
                                    
                                    // Bill Summary
                                    // billsHtml += '<div class="bill-summary">';
                                    // billsHtml += '<div class="summary-item">';
                                    // billsHtml += '<span class="summary-label">Net Amount:</span>';
                                    // billsHtml += '<span class="summary-value">' + '{{ config('constant.currency') }} ' + parseFloat(bill.net_amount).toFixed(2) + '</span>';
                                    // billsHtml += '</div>';
                                    // billsHtml += '<div class="summary-item">';
                                    // billsHtml += '<span class="summary-label">Credit Amount:</span>';
                                    // billsHtml += '<span class="summary-value">' + '{{ config('constant.currency') }} ' + parseFloat(bill.credit_amount).toFixed(2) + '</span>';
                                    // billsHtml += '</div>';
                                    // billsHtml += '<div class="summary-item">';
                                    // billsHtml += '<span class="summary-label">Paid Amount:</span>';
                                    // billsHtml += '<span class="summary-value">' + '{{ config('constant.currency') }} ' + parseFloat(bill.paid_amount).toFixed(2) + '</span>';
                                    // billsHtml += '</div>';
                                    // billsHtml += '<div class="summary-item">';
                                    // billsHtml += '<span class="summary-label">Remaining Amount:</span>';
                                    // billsHtml += '<span class="summary-value text-danger">' + '{{ config('constant.currency') }} ' + parseFloat(bill.remaining_amount).toFixed(2) + '</span>';
                                    // billsHtml += '</div>';
                                    // billsHtml += '</div>';
                                    
                                    // Payment Rows
                                    if (bill.payments && bill.payments.length > 0) {
                                        billsHtml += '<div class="bill-details-section">';
                                        billsHtml += '<h6 class="mb-3"><strong>Payment History</strong></h6>';
                                        billsHtml += '<div class="payment-row payment-row-header">';
                                        billsHtml += '<div>Date</div>';
                                        billsHtml += '<div>Amount</div>';
                                        billsHtml += '<div>Type</div>';
                                        billsHtml += '<div>Note</div>';
                                        billsHtml += '</div>';
                                        
                                        bill.payments.forEach(function(payment, paymentIndex) {
                                            billsHtml += '<div class="payment-row">';
                                            billsHtml += '<div data-label="Date">' + payment.payment_date + '</div>';
                                            billsHtml += '<div class="payment-amount" data-label="Amount">' + '{{ config('constant.currency') }} ' + parseFloat(payment.payment_amount).toFixed(2) + '</div>';
                                            billsHtml += '<div data-label="Type"><span class="payment-type ' + payment.payment_type + '">' + payment.payment_type + '</span></div>';
                                            billsHtml += '<div data-label="Note">' + (payment.note || '-') + '</div>';
                                            billsHtml += '</div>';
                                        });
                                        billsHtml += '</div>';
                                    } else {
                                        billsHtml += '<div class="bill-details-section">';
                                        billsHtml += '<div class="no-payments">No payments made yet</div>';
                                        billsHtml += '</div>';
                                    }
                                    
                                    billsHtml += '</div>'; // Close accordion body
                                    billsHtml += '</div>'; // Close accordion
                                });
                            } else {
                                billsHtml = '<div class="text-center text-muted p-4">No pending bills</div>';
                            }
                            $('#pendingBillsList').html(billsHtml);
                            
                            // Initialize accordion click handlers
                            initializeAccordion();

                            // Show modal
                            $('#paymentModal').modal('show');
                        } else {
                            toastr.error(res.message || 'Failed to fetch dealer bills');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to fetch dealer bills';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        toastr.error(errorMsg);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Form submission
            $('#paymentForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const submitBtn = $('#submitPaymentBtn');
                const originalHtml = submitBtn.html();

                // Clear previous validation errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                const formData = form.serialize();

                // Disable submit button
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.status) {
                            toastr.success(response.message || 'Payment processed successfully!');

                            // Reset form
                            form[0].reset();
                            $('#paymentModal').modal('hide');

                            // Reload DataTable (which will also update statistics)
                            table.ajax.reload(function(json) {
                                if (json.statistics) {
                                    updateStatistics(json.statistics);
                                }
                            }, false);
                            submitBtn.prop('disabled', false).html(originalHtml);
                        } else {
                            toastr.error(response.message || 'Failed to process payment.');
                            submitBtn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Something went wrong. Please try again.';

                        // Clear previous errors
                        $('.is-invalid').removeClass('is-invalid');
                        $('.invalid-feedback').remove();

                        // Handle validation errors (422 status)
                        if (xhr.status === 422) {
                            let errors = {};
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errors = xhr.responseJSON.errors;
                            }

                            if (Object.keys(errors).length > 0) {
                                $.each(errors, function(key, value) {
                                    const field = $('#' + key);
                                    field.addClass('is-invalid');
                                    if (Array.isArray(value)) {
                                        field.after('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                                    } else {
                                        field.after('<div class="invalid-feedback d-block">' + value + '</div>');
                                    }
                                });

                                errorMsg = 'Please correct the errors in the form.';
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        toastr.error(errorMsg);
                        submitBtn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Remove max attribute on payment amount to allow overpayments
            $(document).on('shown.bs.modal', '#paymentModal', function() {
                $('#payment_amount').removeAttr('max');
            });

            // Initialize accordion functionality
            function initializeAccordion() {
                $('.bill-accordion-header').off('click').on('click', function() {
                    const target = $(this).data('target');
                    const accordionBody = $(target);
                    const accordionHeader = $(this);
                    
                    // Toggle active class
                    accordionHeader.toggleClass('active');
                    
                    // Toggle body visibility
                    if (accordionBody.hasClass('show')) {
                        accordionBody.removeClass('show').slideUp(300);
                    } else {
                        // Close all other accordions (optional - remove if you want multiple open)
                        $('.bill-accordion-body').removeClass('show').slideUp(300);
                        $('.bill-accordion-header').removeClass('active');
                        
                        // Open clicked accordion
                        accordionHeader.addClass('active');
                        accordionBody.addClass('show').slideDown(300);
                    }
                });
            }
        });
    </script>
@endsection

