@extends('layout.app')

@section('title', 'Dealer Management')

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row align-items-center">
            <!-- Title & Buttons for Mobile -->
            <div class="col-12 d-flex justify-content-between align-items-center mb-2 d-md-none">
                <h4 class="page-title mb-0">Dealer Management</h4>
                <div class="d-flex align-items-center mt-2">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#dealerModal">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>

            <!-- Title for Desktop -->
            <div class="col-md-6 mt-2 mb-2 d-none d-md-block">
                <h4 class="page-title">Dealer Management</h4>
            </div>

            <!-- Buttons for Desktop -->
            <div class="col-md-6 d-none d-md-flex justify-content-end">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#dealerModal">
                    <i class="fas fa-plus"></i> Add Dealer
                </button>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dealerTable" class="table table-centered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="font-weight-bold">#</th>
                                        <th class="font-weight-bold">Name</th>
                                        <th class="font-weight-bold">Contact Number</th>
                                        <th class="font-weight-bold">Address</th>
                                        <th class="font-weight-bold">Created At</th>
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

        <!-- Add/Edit Dealer Modal -->
        <div class="modal fade" id="dealerModal" tabindex="-1" role="dialog" aria-labelledby="dealerModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-store mr-2"></i><span id="modalTitle">Add Dealer</span>
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="dealerForm" action="{{ route('dealers.store') }}" method="POST">
                        @csrf
                        @method('POST')
                        <input type="hidden" name="id" id="dealerId" value="">
                        <div class="modal-body p-4">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="name" class="font-weight-bold">
                                        Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           placeholder="Enter dealer name"
                                           autocomplete="off">
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-12">
                                    <label for="contact_number" class="font-weight-bold">
                                        Contact Number
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('contact_number') is-invalid @enderror" 
                                           id="contact_number" 
                                           name="contact_number" 
                                           placeholder="Enter contact number (optional)"
                                           autocomplete="off">
                                    @error('contact_number')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-12">
                                    <label for="address" class="font-weight-bold">
                                        Address
                                    </label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3" 
                                              placeholder="Enter dealer address (optional)"></textarea>
                                    @error('address')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top">
                            <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-save mr-1"></i> <span id="submitBtnText">Create Dealer</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
<style>
    #dealerTable {
        width: 100% !important;
    }
    #dealerTable td {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    /* Disable horizontal scroll on desktop/laptop screens */
    @media screen and (min-width: 992px) {
        .table-responsive {
            overflow-x: visible !important;
        }
        #dealerTable {
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
        #dealerTable thead th {
            font-size: 0.85rem;
            padding: 0.5rem 0.25rem;
            white-space: normal;
            line-height: 1.2;
        }
        
        #dealerTable tbody td {
            font-size: 0.85rem;
            padding: 0.5rem 0.25rem;
        }
        
        /* Hide less important columns on mobile */
        #dealerTable tbody tr.child td.dtr-control::before {
            margin-right: 0.5rem;
        }
        
        /* Improve button sizing on mobile */
        #dealerTable tbody td .btn-sm {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
        }
    }
</style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#dealerTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('dealers.index') }}",
                autoWidth: false,
                responsive: true,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '8%', responsivePriority: 1 },
                    { data: 'name', name: 'name', width: '20%', responsivePriority: 1 },
                    { 
                        data: 'contact_number', 
                        name: 'contact_number',
                        width: '15%',
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
                        width: '27%',
                        responsivePriority: 3,
                        render: function(data) {
                            if (!data || data.trim() === '') {
                                return '<span class="text-muted">-</span>';
                            }
                            return data.length > 40 ? data.substring(0, 40) + '...' : data;
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        width: '15%',
                        responsivePriority: 4,
                        render: function(data) {
                            return moment(data).format('DD/MM/YYYY HH:mm');
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '15%',
                        responsivePriority: 1,
                        render: function(data, type, row) {
                            return '<button class="btn btn-primary btn-sm edit-dealer mr-2" data-id="' + row.id + '" title="Edit">' +
                                '<i class="fas fa-edit"></i></button>' +
                                '<button class="btn btn-danger btn-sm delete-dealer" data-id="' + row.id + '" title="Delete">' +
                                '<i class="fas fa-trash"></i></button>';
                        }
                    }
                ],
                order: [[4, 'desc']],
                pageLength: 10,
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
                    emptyTable: 'No dealers found',
                    zeroRecords: 'No matching dealers found'
                }
            });

            // Reset modal on close
            $('#dealerModal').on('hidden.bs.modal', function() {
                // Remove any backdrop that might remain
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
                
                // Reset form
                $('#dealerForm')[0].reset();
                $('#dealerId').val('');
                
                // Reset form action and method
                $('#dealerForm').attr('action', '{{ route('dealers.store') }}');
                $('#dealerForm input[name="_method"]').val('POST');
                
                // Reset modal title and button
                $('#modalTitle').text('Add Dealer');
                $('#submitBtnText').text('Create Dealer');
                $('#submitBtn').removeClass('btn-warning').addClass('btn-primary');
                
                // Reset button HTML and state
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> <span id="submitBtnText">Create Dealer</span>');
                
                // Clear validation errors
                $('.invalid-feedback').remove();
                $('.is-invalid').removeClass('is-invalid');
            });

            // Form submission
            $('#dealerForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const method = form.find('input[name="_method"]').val() || 'POST';
                const submitBtn = $('#submitBtn');
                const originalHtml = submitBtn.html();

                // Clear previous validation errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                const formData = form.serialize();

                // Disable submit button
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.status) {
                            toastr.success(response.message || 'Dealer saved successfully!');
                            
                            // Reset form
                            form[0].reset();
                            $('#dealerForm').attr('action', '{{ route('dealers.store') }}');
                            $('#dealerForm input[name="_method"]').val('POST');
                            $('#dealerId').val('');
                            $('.invalid-feedback').remove();
                            $('.is-invalid').removeClass('is-invalid');
                            
                            // Reset button state to default "Create Dealer"
                            submitBtn.prop('disabled', false)
                                .removeClass('btn-warning')
                                .addClass('btn-primary')
                                .html('<i class="fas fa-save mr-1"></i> <span id="submitBtnText">Create Dealer</span>');
                            
                            // Reset modal title
                            $('#modalTitle').text('Add Dealer');
                            $('#submitBtnText').text('Create Dealer');
                            
                            // Close modal properly
                            $('#dealerModal').modal('hide');
                            
                            // Remove backdrop manually if needed (as backup)
                            setTimeout(function() {
                                $('.modal-backdrop').remove();
                                $('body').removeClass('modal-open').css('padding-right', '');
                            }, 300);
                            
                            // Reload DataTable
                            $('#dealerTable').DataTable().ajax.reload(null, false);
                        } else {
                            toastr.error(response.message || 'Failed to save dealer.');
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
                            } else if (xhr.responseText) {
                                try {
                                    const parsed = JSON.parse(xhr.responseText);
                                    if (parsed.errors) {
                                        errors = parsed.errors;
                                    }
                                } catch (e) {
                                    console.error('Failed to parse error response', e);
                                }
                            }
                            
                            if (Object.keys(errors).length > 0) {
                                // Collect all error messages
                                const errorMessages = [];
                                $.each(errors, function(key, value) {
                                    if (Array.isArray(value)) {
                                        value.forEach(function(msg) {
                                            errorMessages.push(msg);
                                        });
                                    } else if (typeof value === 'string') {
                                        errorMessages.push(value);
                                    }
                                });
                                
                                // Format as HTML list
                                if (errorMessages.length > 0) {
                                    errorMsg = '<ul style="margin: 0; padding-left: 20px; text-align: left;">';
                                    errorMessages.forEach(function(msg) {
                                        errorMsg += '<li>' + msg + '</li>';
                                    });
                                    errorMsg += '</ul>';
                                }
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        
                        // Display error in toast
                        toastr.error(errorMsg, 'Validation Error', {
                            timeOut: 5000,
                            closeButton: true,
                            progressBar: true,
                            escapeHtml: false
                        });
                        
                        submitBtn.prop('disabled', false).html(originalHtml);
                    },
                    complete: function() {
                        // Always re-enable button in case of any issues
                        submitBtn.prop('disabled', false);
                    }
                });
            });

            // Edit dealer
            $(document).on('click', '.edit-dealer', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: '{{ url('dealers') }}/' + id + '/edit',
                    type: 'GET',
                    success: function(res) {
                        if (res.status && res.data) {
                            const dealer = res.data;
                            
                            $('#dealerId').val(dealer.id);
                            $('#name').val(dealer.name);
                            $('#contact_number').val(dealer.contact_number);
                            $('#address').val(dealer.address);
                            
                            // Update form action and method for update
                            $('#dealerForm').attr('action', '{{ url('dealers') }}/' + id);
                            $('#dealerForm input[name="_method"]').val('PUT');
                            
                            $('#modalTitle').text('Edit Dealer');
                            $('#submitBtnText').text('Update Dealer');
                            $('#submitBtn').removeClass('btn-primary').addClass('btn-warning');
                            
                            $('#dealerModal').modal('show');
                        } else {
                            toastr.error(res.message || 'Failed to fetch dealer data');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to fetch dealer data';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.status === 404) {
                            errorMsg = 'Dealer not found';
                        } else if (xhr.status === 0) {
                            errorMsg = 'Network error. Please check your connection.';
                        }
                        toastr.error(errorMsg, 'Error', {
                            timeOut: 5000,
                            closeButton: true,
                            progressBar: true
                        });
                    }
                });
            });

            // Delete dealer
            $(document).on('click', '.delete-dealer', function() {
                const id = $(this).data('id');
                const deleteBtn = $(this);

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This dealer will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait while we delete the dealer',
                            icon: 'info',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Make the delete request
                        $.ajax({
                            url: '{{ route("dealers.destroy", ":id") }}'.replace(':id', id),
                            type: 'DELETE',
                            data: { 
                                _token: '{{ csrf_token() }}'
                            },
                            dataType: 'json',
                            success: function(response) {
                                // Close the loading popup
                                Swal.close();

                                console.log(response);
                                
                                if (response && response.status) {
                                    // Show success message
                                    toastr.success(response.message || 'Dealer deleted successfully!');
                                    // Reload DataTable without page refresh
                                    $('#dealerTable').DataTable().ajax.reload(null, false);
                                } else {
                                    // Show error message
                                    const errorMsg = response.message || 'Failed to delete dealer';
                                    toastr.error(errorMsg, 'Error', {
                                        timeOut: 5000,
                                        closeButton: true,
                                        progressBar: true
                                    });
                                }
                            },
                            error: function(xhr) {
                                // Close the loading popup
                                Swal.close();
                                
                                let errorMsg = 'Failed to delete dealer';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                } else if (xhr.statusText) {
                                    errorMsg = xhr.statusText;
                                } else if (xhr.status === 0) {
                                    errorMsg = 'Network error. Please check your connection.';
                                }
                                
                                // Show error message
                                toastr.error(errorMsg, 'Error', {
                                    timeOut: 5000,
                                    closeButton: true,
                                    progressBar: true
                                });
                            }
                        });
                    }
                });
            });

        });
    </script>
@endsection

