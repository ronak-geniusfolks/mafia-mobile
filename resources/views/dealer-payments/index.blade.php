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

        <div class="card-box shadow-sm">
            <div class="table-responsive">
                <table id="dealerPaymentsTable" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Dealer Name</th>
                            <th>Contact Number</th>
                            <th>Address</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Remaining Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Bill No</th>
                                                <th>Date</th>
                                                <th>Net Amount</th>
                                                <th>Paid</th>
                                                <th>Remaining</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pendingBillsList">
                                            <!-- Bills will be populated here -->
                                        </tbody>
                                    </table>
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
                                        <option value="credit">Credit</option>
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
    .card-box {
        border-radius: 10px;
        background: #fff;
        padding: 20px;
    }

    #dealerPaymentsTable thead th {
        background-color: #343a40;
        color: #fff;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border: none;
    }

    #dealerPaymentsTable tbody tr {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    #dealerPaymentsTable tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }

    .remaining-amount {
        font-size: 1.2rem;
        font-weight: bold;
        color: #dc3545;
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
                columns: [
                    {
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        },
                        orderable: false,
                        searchable: false
                    },
                    { data: 'name', name: 'name' },
                    { data: 'contact_number', name: 'contact_number' },
                    {
                        data: 'address',
                        name: 'address',
                        render: function(data) {
                            if (!data || data.trim() === '') {
                                return '<span class="text-muted">-</span>';
                            }
                            return data.length > 50 ? data.substring(0, 50) + '...' : data;
                        }
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        render: function(data) {
                            return '<span class="total-amount">' + '{{ config('constant.currency') }} ' + parseFloat(data).toFixed(2) + '</span>';
                        }
                    },
                    {
                        data: 'paid_amount',
                        name: 'paid_amount',
                        render: function(data) {
                            return '<span class="paid-amount">' + '{{ config('constant.currency') }} ' + parseFloat(data).toFixed(2) + '</span>';
                        }
                    },
                    {
                        data: 'remaining_amount',
                        name: 'remaining_amount',
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
                        render: function(data, type, row) {
                            return '<button class="btn btn-primary btn-sm view-payment" data-id="' + data + '" title="View & Pay">' +
                                '<i class="fas fa-eye mr-1"></i> View & Pay</button>';
                        }
                    }
                ],
                order: [[4, 'desc']],
                pageLength: 10,
                responsive: true,
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
                    emptyTable: 'No dealers with pending payments found.'
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

                            // Populate bills list
                            let billsHtml = '';
                            if (res.bills.length > 0) {
                                res.bills.forEach(function(bill) {
                                    billsHtml += '<tr>';
                                    billsHtml += '<td><strong>' + bill.bill_no + '</strong></td>';
                                    billsHtml += '<td>' + bill.bill_date + '</td>';
                                    billsHtml += '<td>' + '{{ config('constant.currency') }} ' + parseFloat(bill.net_amount).toFixed(2) + '</td>';
                                    billsHtml += '<td>' + '{{ config('constant.currency') }} ' + parseFloat(bill.paid_amount).toFixed(2) + '</td>';
                                    billsHtml += '<td><strong class="text-danger">' + '{{ config('constant.currency') }} ' + parseFloat(bill.remaining_amount).toFixed(2) + '</strong></td>';
                                    billsHtml += '</tr>';
                                });
                            } else {
                                billsHtml = '<tr><td colspan="5" class="text-center text-muted">No pending bills</td></tr>';
                            }
                            $('#pendingBillsList').html(billsHtml);

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
        });
    </script>
@endsection

