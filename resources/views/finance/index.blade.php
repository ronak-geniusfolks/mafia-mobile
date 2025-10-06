@extends('layout.app')

@section('css')
    <style>
    @media (max-width: 767.98px) {
        .card.text-white {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            margin-bottom: 10px;
        }

        .page-title {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 0.85rem;
        }

        #transactionTable th, #transactionTable td {
            font-size: 0.85rem;
        }

        table.dataTable {
            width: 100% !important;
        }

        .dataTables_wrapper {
            overflow-x: auto;
        }
    }
    </style>
@endsection

@section('title')
    Transaction List
@endsection
@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row align-items-center">

            <!-- Title & Buttons for Mobile -->
            <div class="col-12 d-flex justify-content-between align-items-center mb-2 d-md-none">
                <h4 class="page-title mb-0">Transaction Management</h4>
                <div class="d-flex align-items-center mt-2">
                    <a href="{{ route('transactions.resync') }}" class="btn btn-info btn-sm mr-2">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#transactionModal">
                        Add
                    </button>
                </div>
            </div>

            <!-- Title for Desktop -->
            <div class="col-md-6 mt-2 mb-2 d-none d-md-block">
                <h4 class="page-title">Transaction Management</h4>
            </div>

            <!-- Buttons for Desktop -->
            <div class="col-md-6 d-none d-md-flex justify-content-end">
                <a href="{{ route('transactions.resync') }}" class="btn btn-info mr-2">
                    <i class="fas fa-sync-alt"></i>
                </a>
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#transactionModal">
                    Add Transaction
                </button>
            </div>
        </div>

        <!-- end page title -->
        <div class="row">
            <!-- Total Balance -->
            <div class="col-6 col-sm-6 col-md-2 mb-2">
                <div class="card text-white" style="background:#6f42c1;">
                    <div class="card-body text-center p-2">
                        <p class="font-weight-bold text-uppercase mb-1">Total Balance</p>
                        <h5 style="color: #fff">{{ config('constant.currency') }} <span id="openingBalance">0</span></h5>
                    </div>
                </div>
            </div>

            <!-- Cash Balance -->
            <div class="col-6 col-sm-6 col-md-2 mb-2">
                <div class="card text-white" style="background:#007bff;">
                    <div class="card-body text-center p-2">
                        <p class="font-weight-bold text-uppercase mb-1">Cash Balance</p>
                        <h5 style="color: #fff">{{ config('constant.currency') }} <span id="cashBalance">0</span></h5>
                    </div>
                </div>
            </div>

            <!-- Bank Balance -->
            <div class="col-6 col-sm-6 col-md-2 mb-2">
                <div class="card text-white" style="background:#17a2b8;">
                    <div class="card-body text-center p-2">
                        <p class="font-weight-bold text-uppercase mb-1">Bank Balance</p>
                        <h5 style="color: #fff">{{ config('constant.currency') }} <span id="bankBalance">0</span></h5>
                    </div>
                </div>
            </div>

            <!-- Total IN -->
            <div class="col-6 col-sm-6 col-md-2 mb-2">
                <div class="card text-white" style="background:#28a745;">
                    <div class="card-body text-center p-2">
                        <p class="font-weight-bold text-uppercase mb-1">Total In</p>
                        <h5 style="color: #fff">{{ config('constant.currency') }} <span id="totalIn">0</span></h5>
                    </div>
                </div>
            </div>

            <!-- Total OUT -->
            <div class="col-6 col-sm-6 col-md-2 mb-2">
                <div class="card text-white" style="background:#dc3545;">
                    <div class="card-body text-center p-2">
                        <p class="font-weight-bold text-uppercase mb-1">Total Out</p>
                        <h5 style="color: #fff">{{ config('constant.currency') }} <span id="totalOut">0</span></h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @if (session('status'))
                <div class="alert alert-success" id="statusMessage">
                    {{ session('status') }}
                </div>
                @endif
                @if (session('error'))
                <div class="alert alert-danger" id="errorMessage">
                    {{ session('error') }}
                </div>
                @endif
                <div class="card-box">
                    <form action="{{ route('expenses') }}" method="GET" id="filterexpense">
                        <div class="form-row">
                        </div>
                    </form>

                    <div class="card-box">
                        <form id="filterForm" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_from">Date From</label>
                                        <input type="date" class="form-control" id="date_from" name="date_from">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_to">Date To</label>
                                        <input type="date" class="form-control" id="date_to" name="date_to">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="transaction_type">Transaction Type</label>
                                        <select class="form-control" id="transaction_type" name="transaction_type">
                                            <option value="">All</option>
                                            <option value="credit">IN</option>
                                            <option value="debit">OUT</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="payment_method">Payment Method</label>
                                        <select class="form-control" id="payment_method" name="payment_method">
                                            <option value="">All</option>
                                            @foreach($paymentMethods as $method)
                                                <option value="{{ $method->id }}">{{ $method->method_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" id="applyFilter" class="btn btn-primary btn-block">Apply Filter</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <table id="transactionTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 11%;">Payment Date</th>
                                    <th style="width: 12%;">Transaction Type</th>
                                    <th style="width: 14%;">Payment Method</th>
                                    <th style="width: 15%;">Payment Amount</th>
                                    <th style="width: 18%;">Payment Note</th>
                                    {{-- <th style="width: 13%;">Created At</th> --}}
                                    <th style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Modal -->
    <div class="modal fade" id="transactionModal" tabindex="-1" role="dialog" aria-labelledby="transactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionModalLabel">Add Transaction</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="transactionForm" action="{{ route('transactions.store') }}" method="POST">
                    <input type="hidden" name="id" id="transactionId">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_date">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="payment_date" name="payment_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="transaction_type">Transaction Type <span class="text-danger">*</span></label>
                                    <select class="form-control" id="transaction_type" name="transaction_type" required>
                                        <option value="">Select Type</option>
                                        <option value="credit">IN</option>
                                        <option value="debit">OUT</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_method">Payment Method <span class="text-danger">*</span></label>
                                    <select class="form-control" id="payment_method" name="payment_method" required>
                                        <option value="">Select Method</option>
                                        @foreach($paymentMethods as $method)
                                            <option value="{{ $method->id }}">{{ $method->method_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">{{ config('constant.currency') }}</span>
                                        </div>
                                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="note">Note</label>
                                    <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(function () {
            const currency = '{{ config('constant.currency') }}';
            let table; // Declare table variable in higher scope

            // Initialize Bootstrap modal
            $('#transactionModal').modal({
                show: false,
                backdrop: 'static',
                keyboard: false
            });

            // Function to get filter parameters
            function getFilterParams() {
                return {
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val(),
                    transaction_type: $('#transaction_type').val(),
                    payment_method: $('#payment_method').val()
                };
            }

            table = $('#transactionTable').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: "{{ route('transactions.index') }}",
                    data: function(d) {
                        return $.extend({}, d, getFilterParams());
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center'},
                    {data: 'payment_date', name: 'payment_date', className: 'text-center', render: function(data) {
                        return moment(data).format('DD/MM/YYYY');
                    }},
                    {data: 'transaction_type', name: 'transaction_type', className: 'text-center', render: function(data, type, row) {
                        return row.transaction_type === 'debit' ? 'OUT' : 'IN';
                    }},
                    {data: 'payment_method', name: 'payment_method', className: 'text-center', render: function(data) {
                        return data.method_name;
                    }},
                    {data: 'amount', name: 'amount', className: 'text-center', render: function(data, type, row) {
                        const color = row.transaction_type === 'debit' ? 'red' : 'green';
                        return '<span style="color: ' + color + '">' + (row.transaction_type === 'debit' ? '-   ' : '') + currency + ' ' + data + '</span>';
                    }},
                    {data: 'note', name: 'note', className: 'text-center', render: function(data) {
                        if (data && data.length > 50) {
                            return '<span class="note-tooltip" data-toggle="tooltip" data-placement="top" title="' + data + '">' + data.substring(0, 15) + '...</span>';
                        }
                        return data || '';
                    }},
                    // {
                    //     data: 'created_at',
                    //     name: 'created_at',
                    //     className: 'text-center',
                    //     render: function(data) {
                    //         return moment(data).format('DD/MM/YYYY HH:mm:ss');
                    //     }
                    // },
                    {data: 'action', name: 'action', className: 'text-center', render: function(data, type, row) {
                        return '<button class="btn btn-primary btn-sm edit-transaction mr-2" data-id="' + row.id + '"><i class="fas fa-edit"></i></button>' +
                               '<button class="btn btn-danger btn-sm delete-transaction" data-id="' + row.id + '"><i class="fas fa-trash"></i></button>';
                    }},
                ],
                initComplete: function(settings, json) {
                    if (json) {
                        if (json.openingBalance !== undefined) {
                            $('#openingBalance').text(json.openingBalance);
                        }
                        if (json.cashBalance !== undefined) {
                            $('#cashBalance').text(json.cashBalance);
                        }
                        if (json.bankBalance !== undefined) {
                            $('#bankBalance').text(json.bankBalance);
                        }
                        if (json.totalIn !== undefined) {
                            $('#totalIn').text(json.totalIn);
                        }
                        if (json.totalOut !== undefined) {
                            $('#totalOut').text(json.totalOut);
                        }
                    }
                }
            });

            // Apply filter button click handler
            $('#applyFilter').on('click', function() {
                table.ajax.reload(function(json) {
                    if (json) {
                        if (json.openingBalance !== undefined) {
                            $('#openingBalance').text(json.openingBalance);
                        }
                        if (json.cashBalance !== undefined) {
                            $('#cashBalance').text(json.cashBalance);
                        }
                        if (json.bankBalance !== undefined) {
                            $('#bankBalance').text(json.bankBalance);
                        }
                        if (json.totalIn !== undefined) {
                            $('#totalIn').text(json.totalIn);
                        }
                        if (json.totalOut !== undefined) {
                            $('#totalOut').text(json.totalOut);
                        }
                    }
                });
            });

            // Delete transaction handler
            $(document).on('click', '.delete-transaction', function() {
                const transactionId = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/admin/transactions/delete/" + transactionId,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                table.ajax.reload(function(json) {
                                    if (json) {
                                        if (json.openingBalance !== undefined) {
                                            $('#openingBalance').text(json.openingBalance);
                                        }
                                        if (json.cashBalance !== undefined) {
                                            $('#cashBalance').text(json.cashBalance);
                                        }
                                        if (json.bankBalance !== undefined) {
                                            $('#bankBalance').text(json.bankBalance);
                                        }
                                        if (json.totalIn !== undefined) {
                                            $('#totalIn').text(json.totalIn);
                                        }
                                        if (json.totalOut !== undefined) {
                                            $('#totalOut').text(json.totalOut);
                                        }
                                    }
                                });
                                $('#openingBalance').text(response.data.openingBalance);
                                $('#totalIn').text(response.data.totalIn);
                                $('#totalOut').text(response.data.totalOut);
                                Swal.fire(
                                    'Deleted!',
                                    'Transaction has been deleted.',
                                    'success'
                                );
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error!',
                                    'Something went wrong while deleting the transaction.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Edit transaction handler
            $(document).on('click', '.edit-transaction', function() {
                const transactionId = $(this).data('id');

                // Reset form and clear validation errors
                $('#transactionForm')[0].reset();
                $('#transactionForm').validate().resetForm();

                // Set transaction ID in hidden field
                $('#transactionId').val(transactionId);

                // Fetch transaction data
                $.ajax({
                    url: "/admin/transactions/edit/" + transactionId,
                    type: 'GET',
                    success: function(response) {
                        response = response.data;

                        // Pre-fill form fields
                        $('#transactionForm #payment_date').val(response.payment_date);
                        $('#transactionForm #transaction_type').val(response.transaction_type).trigger('change');
                        $('#transactionForm #payment_method').val(response.payment_method).trigger('change');
                        $('#transactionForm #amount').val(response.amount);
                        $('#transactionForm #note').val(response.note);
                        $('#transactionForm #transactionId').val(response.id);

                        // Show modal
                        $('#transactionModal').modal('show');
                    },
                    error: function(xhr) {
                        toastr.error('Error fetching transaction details');
                    }
                });
            });

            // Form validation
            $("#transactionForm").validate({
                rules: {
                    payment_date: "required",
                    transaction_type: "required",
                    payment_method: "required",
                    amount: {
                        required: true,
                        min: 0.01
                    }
                },
                messages: {
                    payment_date: "Please select payment date",
                    transaction_type: "Please select transaction type",
                    payment_method: "Please select payment method",
                    amount: {
                        required: "Please enter amount",
                        min: "Amount must be greater than 0"
                    }
                },
                submitHandler: function(form) {
                    var formData = $(form).serialize();
                    $.ajax({
                        url: $(form).attr('action'),
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            toastr.success(response.message);

                            // Try normal hide
                            $('#transactionModal').modal('hide');

                            // Force hide backup
                            setTimeout(() => {
                                $('#transactionModal').removeClass('show').hide();
                                $('body').removeClass('modal-open');
                                $('.modal-backdrop').remove();
                            }, 300);

                            // Reset form and select2 fields
                            $('#transactionForm')[0].reset();

                            // Update UI
                            table.ajax.reload(function(json) {
                                if (json) {
                                    if (json.openingBalance !== undefined) {
                                        $('#openingBalance').text(json.openingBalance);
                                    }
                                    if (json.totalIn !== undefined) {
                                        $('#totalIn').text(json.totalIn);
                                    }
                                    if (json.cashBalance !== undefined) {
                                        $('#cashBalance').text(json.cashBalance);
                                    }
                                    if (json.bankBalance !== undefined) {
                                        $('#bankBalance').text(json.bankBalance);
                                    }
                                    if (json.totalOut !== undefined) {
                                        $('#totalOut').text(json.totalOut);
                                    }
                                }
                            });
                        },
                        error: function(xhr) {
                            toastr.error('Error storing transaction');
                        }
                    });
                    return false;
                }
            });

            // Set default date to today
            $('#payment_date').val(new Date().toISOString().split('T')[0]);
        });
    </script>
@endsection
