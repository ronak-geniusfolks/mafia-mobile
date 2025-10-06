@extends('layout.app')

@section('css')
    <style>
        @media (max-width: 767.98px) {
            .card.text-white {
                border-radius: 10px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
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

            table.dataTable {
                width: 100% !important;
            }

            .dataTables_wrapper {
                overflow-x: auto;
            }

            table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before {
                background-color: #007bff; /* Customize the expand icon */
                border-radius: 50%;
                color: white;
            }
        }
        /* Change text color of selected items in Select2 multi-select */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            color: #333 !important; /* Use any readable color */
            background-color: #e4e6eb !important; /* Optional: soften background */
            border: 1px solid #ccc !important;
            font-weight: 500;
        }
    </style>
@endsection

@section('title', 'Role Management')

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row align-items-center">

            <!-- Title & Buttons for Mobile -->
            <div class="col-12 d-flex justify-content-between align-items-center mb-2 d-md-none">
                <h4 class="page-title mb-0">Role Management</h4>
                <div class="d-flex align-items-center mt-2">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#roleModal">
                        Add
                    </button>
                </div>
            </div>

            <!-- Title for Desktop -->
            <div class="col-md-6 mt-2 mb-2 d-none d-md-block">
                <h4 class="page-title">Role Management</h4>
            </div>

            <!-- Buttons for Desktop -->
            <div class="col-md-6 d-none d-md-flex justify-content-end">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#roleModal">
                    Add Role
                </button>
            </div>
        </div>

        <div class="card-box">
            <table id="roleTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 25%;">Role Name</th>
                        <th>Permissions</th>
                        <th style="width: 10%;">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Add Role Modal -->
        <div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="roleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content p-3">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Role</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ old('id') }}">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Role Name</label>
                                <input value="{{ old('name') }}" type="text" name="name" class="form-control" required>
                            </div>

                            <!-- Select2 Permissions Dropdown -->
                            <div class="form-group">
                                <label>Select Existing Permissions</label>
                                <select name="permissions[]" class="form-control select2-permissions" multiple="multiple">
                                    @foreach ($permissions as $permission)
                                        <option value="{{ $permission->name }}" {{ in_array($permission->name, $selectedPermissions ?? []) ? 'selected' : '' }}>
                                            {{ $permission->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- <div class="form-group">
                                <label>Add New Permissions</label>
                                <input value="{{ old('new_permissions[]') }}" type="text" name="new_permissions[]" class="form-control mb-2"
                                    placeholder="Permission 1">
                                <input value="{{ old('new_permissions[]') }}" type="text" name="new_permissions[]" class="form-control" placeholder="Permission 2">
                            </div> --}}
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary modal-button">Create Role</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    <script>
        $(function () {
            $('#roleTable').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: "{{ route('roles.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center' },
                    { data: 'name', name: 'name' },
                    {
                        data: 'permissions',
                        name: 'permissions',
                        className: 'text-left',
                        render: function (data) {
                            if (!data || data.length === 0) {
                                return '<span class="text-muted">No permissions</span>';
                            }

                            return data.map(p => `<span class="badge badge-primary mr-1">${p}</span>`).join('');
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center',
                        render: function (data, type, row) {
                            return  '<button class="btn btn-primary btn-sm edit-role mr-2" data-id="' + row.id + '"><i class="fas fa-edit"></i></button>' +
                                    '<button class="btn btn-danger btn-sm delete-role" data-id="' + row.id + '"><i class="fas fa-trash"></i></button>';
                        }
                    }
                ]
            });
        });

        $(document).ready(function () {
            $('.select2-permissions').select2({
                width: '100%',
                placeholder: "Select permissions"
            });
        });

        $('#roleModal form').on('submit', function (e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const formData = form.serialize();

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (response.status) {
                        toastr.success(response.message);

                        // Hide modal and remove backdrop properly
                        $('#roleModal').modal('hide');
                        $('body').removeClass('modal-open');
                        $('.modal-backdrop').remove();

                        // Reset and reload
                        form[0].reset();
                        form.find('select[name="permissions[]"]').val([]).trigger('change');
                        form.find('input[name="id"]').remove();
                        form.attr('action', "{{ route('roles.store') }}");
                        $('#roleModal .modal-title').text('Add Role');

                        // Reload table
                        $('#roleTable').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.message || 'Failed to save role.');
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || 'Something went wrong.';
                    toastr.error(msg);
                }
            });
        });

        $(document).on('click', '.edit-role', function () {
            const id = $(this).data('id');
            $.ajax({
                url: '{{ url('roles') }}/' + id + '/edit',
                type: 'GET',
                success: function (res) {
                    const role = res.data;
                    const form = $('#roleModal form');

                    form[0].reset();
                    form.find('input[name="id"]').remove(); // Remove previous hidden input
                    form.find('input[name="name"]').val(role.name);
                    form.find('select[name="permissions[]"]').val(role.permissions).trigger('change');

                    form.attr('action', "{{ route('roles.store') }}"); // Always post to store
                    form.append('<input type="hidden" name="id" value="' + id + '">');

                    $('#roleModal .modal-title').text('Edit Role');
                    $('#roleModal .modal-button').text('Update Role');
                    $('#roleModal').modal('show');
                },
                error: function () {
                    toastr.error('Failed to fetch role data');
                }
            });
        });

        $(document).on('click', '.delete-role', function () {
            const id = $(this).data('id');
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
                        url: '{{ route("roles.destroy", ":id") }}'.replace(':id', id),
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function () {
                            toastr.success('Role deleted successfully');
                            // Reload table
                            $('#roleTable').DataTable().ajax.reload();
                        },
                        error: function () {
                            toastr.error('Failed to delete role');
                            // Reload table
                            $('#roleTable').DataTable().ajax.reload();
                        }
                    });
                }
            });
        });

        $('#roleModal').on('hidden.bs.modal', function () {
            const form = $('#roleModal form');
            form[0].reset();
            form.attr('action', '{{ route('roles.store') }}');
            form.find('input[name="_method"]').remove();
            form.find('select[name="permissions[]"]').val(null).trigger('change');
            $('#roleModal .modal-title').text('Add Role');
            $('#roleModal .modal-button').text('Create Role');
        });
    </script>
@endsection