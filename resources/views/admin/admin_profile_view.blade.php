@extends('layout.app')

@section('title', 'Profile')

@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title">Profile</h4>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-lg-4 col-xl-4">
                <div class="card-box text-center">
                    @if($admin->avatar)
                        <img src="{{asset('admin/users/' . $admin->avatar)}}" class="rounded-circle avatar-lg img-thumbnail"
                            alt="{{$admin->name}}">
                    @else
                        <img src="{{asset('assets/images/users/user-5.jpg')}}" class="rounded-circle avatar-lg img-thumbnail"
                            alt="profile-image">
                    @endif
                    <h4 class="mb-0">{{ $admin->name }}</h4>

                    <div class="text-left mt-3">
                        <h4 class="font-13 text-uppercase">About Me :</h4>
                        <p class="text-muted mb-2 font-13"><strong>Username :</strong> <span
                                class="ml-2">{{ $admin->user_name }}</span></p>
                        <p class="text-muted mb-2 font-13"><strong>Full Name :</strong> <span
                                class="ml-2">{{ $admin->name }}</span></p>
                        <p class="text-muted mb-2 font-13"><strong>Email :</strong> <span
                                class="ml-2 ">{{ $admin->email }}</span></p>
                    </div>
                </div> <!-- end card-box -->
            </div> <!-- end col-->

            <div class="col-lg-8 col-xl-8">
                <div class="card-box">
                    <div class="tab-content">
                        <div class="tab-pane active" id="settings">
                            <form method="POST" action="{{ route('store.profile') }}" enctype="multipart/form-data">
                                @csrf
                                <h5 class="mb-4 text-uppercase">
                                    <i class="mdi mdi-account-circle mr-1"></i> Personal Info
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username">User Name</label>
                                            <input type="text" class="form-control" name="username" id="username"
                                                value="{{$admin->user_name}}" placeholder="Enter first name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="lastname">Name</label>
                                            <input type="text" class="form-control" name="name" id="lastname"
                                                value="{{$admin->name}}" placeholder="Enter last name">
                                        </div>
                                    </div> <!-- end col -->
                                </div> <!-- end row -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="useremail">Email Address</label>
                                            <input type="email" class="form-control" name="email" value="{{$admin->email}}"
                                                id="useremail" placeholder="Enter email" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profilephoto">Profile Photo</label>
                                            <input type="file" class="form-control" name="avatar" id="profilephoto"
                                                placeholder="">
                                            {{-- show preview when I upload the image --}}
                                            <div class="mt-2">
                                                <img src="" class="img-profile-photo" alt="Profile Photo"
                                                    style="display: none;">
                                            </div>
                                        </div>
                                    </div> <!-- end col -->
                                </div> <!-- end row -->

                                <div class="text-right">
                                    <button type="submit" class="btn btn-success waves-effect waves-light mt-2">
                                        <i class="mdi mdi-content-save"></i> Save
                                    </button>
                                </div>
                            </form>
                        </div>
                        <!-- end settings content-->
                    </div> <!-- end tab-content -->
                </div> <!-- end card-box-->

            </div> <!-- end col -->
        </div>
        <!-- end row-->
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            @if($admin->avatar)
                $('.img-profile-photo')
                    .attr('src', '{{ asset("admin/users/" . $admin->avatar) }}')
                    .css({ height: '200px', width: '200px' })
                    .show();
            @endif

            $('#profilephoto').on('change', function () {
                const file = this.files[0];
                if (!file) return;

                // If file is <= 2MB, just preview directly
                if (file.size <= 2048000) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        $('.img-profile-photo')
                            .attr('src', e.target.result)
                            .css({ height: '200px', width: '200px' })
                            .show();
                    };
                    reader.readAsDataURL(file);
                    return;
                }

                // Otherwise compress image
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = new Image();
                    img.src = e.target.result;

                    img.onload = function () {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');

                        let width = img.width;
                        let height = img.height;

                        // Resize if needed (keep proportions)
                        const maxDimension = 1000; // you can tweak this
                        if (width > height && width > maxDimension) {
                            height = height * (maxDimension / width);
                            width = maxDimension;
                        } else if (height > maxDimension) {
                            width = width * (maxDimension / height);
                            height = maxDimension;
                        }

                        canvas.width = width;
                        canvas.height = height;
                        ctx.drawImage(img, 0, 0, width, height);

                        // Try compressing until size <= 2MB
                        let quality = 0.9;
                        let dataUrl;
                        do {
                            dataUrl = canvas.toDataURL('image/jpeg', quality);
                            const fileSize = Math.round((dataUrl.length * 3) / 4);
                            if (fileSize <= 2048000) break;
                            quality -= 0.1;
                        } while (quality > 0.1);

                        // Convert base64 → Blob → File
                        fetch(dataUrl)
                            .then(res => res.blob())
                            .then(blob => {
                                const compressedFile = new File([blob], file.name, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now(),
                                });

                                // Replace the file input with the compressed file
                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(compressedFile);
                                document.getElementById('profilephoto').files = dataTransfer.files;

                                // Show preview
                                $('.img-profile-photo')
                                    .attr('src', dataUrl)
                                    .css({ height: '200px', width: '200px' })
                                    .show();
                            });
                    };
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
@endsection