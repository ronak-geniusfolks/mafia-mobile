@extends('layout.app')

@section('title', 'User Management')

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row align-items-center">

            <!-- Title & Buttons for Mobile -->
            <div class="col-12 d-flex justify-content-between align-items-center mb-2 d-md-none">
                <h4 class="page-title mb-0">User Management</h4>
                <div class="d-flex align-items-center mt-2">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#userModal">
                        Add
                    </button>
                </div>
            </div>

            <!-- Title for Desktop -->
            <div class="col-md-6 mt-2 mb-2 d-none d-md-block">
                <h4 class="page-title">User Management</h4>
            </div>

            <!-- Buttons for Desktop -->
            <div class="col-md-6 d-none d-md-flex justify-content-end">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#userModal">
                    Add User
                </button>
            </div>
        </div>
        <!-- end page title -->

        <div class="card-box">
            <table id="userTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Avatar</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Add user Modal -->
        <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content p-3">
                    <div class="modal-header">
                        <h5 class="modal-title">Add User</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ old('id') }}">
                        <div class="modal-body row">
                            <div class="form-group col-md-6">
                                <label>Name</label>
                                <input value="{{ old('name') }}" type="text" name="name" class="form-control" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input value="{{ old('email') }}" type="email" name="email" class="form-control" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label>User Name</label>
                                <input value="{{ old('user_name') }}" type="text" name="user_name" class="form-control"
                                    required>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Assign Roles</label>
                                <select name="roles[]" class="form-control select2" required>
                                    <option value="">-- Assign Roles --</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-6 password-fields">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="form-group col-md-6 password-fields">
                                <label>Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Avatar (optional)</label>
                                <input type="file" name="avatar" class="form-control-file" id="avatarInput" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                                <div id="avatarPreview" class="mt-2">
                                    <img src="" alt="Avatar Preview" class="img-thumbnail" style="max-height: 120px; display: none;">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary modal-button">Create User</button>
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
            $('#userTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('users.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    {
                        data: 'avatar',
                        name: 'avatar',
                        render: function (data) {
                            return `<img src="${data}" alt="Avatar" class="rounded-circle" width="40" height="40">`;
                        }
                    },
                    { data: 'name', name: 'name' },
                    { data: 'user_name', name: 'user_name' },
                    { data: 'email', name: 'email' },
                    {
                        data: 'roles',
                        name: 'roles',
                        render: function (data) {
                            // Ensure it's an array before looping
                            if (!Array.isArray(data) || data.length === 0) {
                                return `<span class="text-muted">No role assigned</span>`;
                            }

                            let html = '';
                            data.forEach(function (role) {
                                // Encode role as JSON string to safely store in data-role
                                html += `<span class="badge badge-info mr-1 view-role"
                                    data-role='${JSON.stringify(role)}'
                                    style="cursor:pointer;">
                                    ${role.name}
                                </span>`;
                            });
                            return html;
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function (data) {
                            return moment(data).format('DD/MM/YYYY HH:mm:ss');
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center',
                        render: function (data, type, row) {
                            return '<button class="btn btn-primary btn-sm edit-user mr-2" data-id="' + row.id + '"><i class="fas fa-edit"></i></button>' +
                                '<button class="btn btn-danger btn-sm delete-user" data-id="' + row.id + '"><i class="fas fa-trash"></i></button>';
                        }
                    }
                ]
            });

            $(document).on('click', '.view-role', function () {
                const role = $(this).data('role');

                let html = `<h5>Permissions for <strong>${role.name}</strong></h5><div>`;
                if (role.permissions.length > 0) {
                    role.permissions.forEach(function (perm) {
                        html += `<span class="badge badge-soft-blue m-1">${perm}</span>`;
                    });
                } else {
                    html += '<p>No permissions assigned.</p>';
                }
                html += '</div>';

                Swal.fire({
                    title: 'Role Permissions Details',
                    html: html,
                    icon: 'info',
                    showConfirmButton: false,
                });
            });

            $(document).ready(function () {
                $('#avatarInput').on('change', function () {
                    const file = this.files[0];
                    const preview = $('#avatarPreview img');

                    if (file) {
                        const validTypes = ['image/jpeg', 'image/png'];
                        if (!validTypes.includes(file.type)) {
                            alert('Only JPG and PNG images are allowed.');
                            $(this).val('');
                            preview.hide().attr('src', '');
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function (e) {
                            preview.attr('src', e.target.result).show();
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.hide().attr('src', '');
                    }
                });

                // Reset on modal close
                $('#userModal').on('hidden.bs.modal', function () {
                    $('#avatarInput').val('');
                    $('#avatarPreview img').attr('src', '').hide();
                });

                $('#userModal form').on('submit', function (e) {
                    e.preventDefault();
                    const form = $(this)[0];
                    const formData = new FormData(form);
                    const url = $(this).attr('action');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function (response) {
                            if (response.status) {
                                toastr.success(response.message);

                                $('#userModal').modal('hide');
                                $('body').removeClass('modal-open');
                                $('.modal-backdrop').remove();

                                form.reset();
                                $('#userModal form input[name="id"]').val('');
                                $('#userModal .modal-title').text('Add User');
                                $('#userModal .modal-button').text('Create User');
                                $('#userTable').DataTable().ajax.reload();
                            } else {
                                toastr.error(response.message || 'Failed to save user.');
                            }
                        },
                        error: function (xhr) {
                            const msg = xhr.responseJSON?.message || 'Something went wrong.';
                            toastr.error(msg);
                        }
                    });
                });

                $(document).on('click', '.edit-user', function () {
                    const id = $(this).data('id');

                    $.ajax({
                        url: '{{ url('users') }}/' + id + '/edit',
                        type: 'GET',
                        success: function (res) {
                            const user = res.data;
                            const form = $('#userModal form')[0];

                            // Reset form
                            form.reset();
                            $('#userModal form input[name="id"]').val(user.id);
                            $('#userModal form input[name="name"]').val(user.name);
                            $('#userModal form input[name="email"]').val(user.email);
                            $('#userModal form input[name="user_name"]').val(user.user_name);
                            $('#userModal form select[name="roles[]"]').val(user.roles.map(r => r.name)).trigger('change');

                            // Set avatar preview if available
                            if (user.avatar_url) {
                                $('#avatarPreview img').attr('src', user.avatar_url).show();
                            } else {
                                $('#avatarPreview img').hide();
                            }

                            $('#userModal .modal-title').text('Edit User');
                            $('#userModal .modal-button').text('Update User');
                            $('#userModal').modal('show');
                        },
                        error: function () {
                            toastr.error('Failed to fetch user data');
                        }
                    });
                });

                $(document).on('click', '.delete-user', function () {
                    const id = $(this).data('id');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This user will be permanently deleted!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '{{ route("users.destroy", ":id") }}'.replace(':id', id),
                                type: 'DELETE',
                                data: { _token: '{{ csrf_token() }}' },
                                success: function () {
                                    toastr.success('User deleted successfully');
                                    $('#userTable').DataTable().ajax.reload(); // Ensure you use correct table ID
                                },
                                error: function () {
                                    toastr.error('Failed to delete user');
                                    $('#userTable').DataTable().ajax.reload();
                                }
                            });
                        }
                    });
                });
            });
        });
    </script>
@endsection