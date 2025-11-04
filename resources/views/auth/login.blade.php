<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Log In | Mafia Mobile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Mafia Mobile" name="description" />
    <meta content="Coderthemes" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/new_logo/main_logo.png')}}">
    <!-- App css -->
    <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"
        id="bs-default-stylesheet" />
    <link href="{{ asset('assets/css/app.min.css')}}" rel="stylesheet" type="text/css" id="app-default-stylesheet" />

    <!-- Theme overrides -->
    <link href="{{ asset('assets/css/theme-overrides.css')}}" rel="stylesheet" type="text/css" />

    <!-- icons -->
    <link href="{{ asset('assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />

</head>

<body class="authentication-bg authentication-bg-pattern">
    <div class="account-pages mt-5 mb-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card bg-pattern">
                        <div class="card-body p-4">
                            <div class="text-center mb-2">
                                <div class="auth-logo">
                                    <a href="{{route('login')}}" class="logo logo-dark text-center">
                                        <span class="logo-lg">
                                            <img src="{{asset('assets/images/new_logo/main_logo.png')}}" alt=""
                                                height="200">
                                        </span>
                                    </a>
                                </div>
                            </div>
                            @include('include.alert')
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="form-group mb-3">
                                    <label for="email">Email:</label>
                                    <input class="form-control" type="email" id="email" name="email"
                                        value="{{old('email')}}" placeholder="Enter email" required autofocus
                                        autocomplete="username">
                                    @error('email')
                                        <ul class="parsley-errors-list filled" id="parsley-id-25" aria-hidden="false">
                                            <li class="parsley-required">{{ $message}}</li>
                                        </ul>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="password">Password:</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" id="password" name="password" required
                                            autocomplete="current-password" class="form-control"
                                            placeholder="Enter Password">
                                        <div class="input-group-append" data-password="false">
                                            <div class="input-group-text">
                                                <span class="password-eye"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="remember" class="custom-control-input"
                                            id="remember_me" checked>
                                        <label class="custom-control-label"
                                            for="checkbox-signin">{{ __('Remember me') }}</label>
                                    </div>
                                </div>

                                <div class="form-group mb-0 text-center">
                                    <button class="btn btn-primary btn-block" type="submit">{{ __('Log in') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <p class="text-white-50">Don't have an account? <a href="{{ route('register') }}"
                                    class="text-white ml-1"><b>{{ __('Sign Up') }}</b></a></p>
                        </div> <!-- end col -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer footer-alt">
        2024 -
        <script>document.write(new Date().getFullYear())</script> &copy; <a href="https://www.geniusfolks.in"
            class="text-white-50" target="_blank">Geniusfolks</a>. All rights reserved.
    </footer>

    <!-- Vendor js -->
    <script src="{{ asset('assets/js/vendor.min.js')}}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.min.js')}}"></script>
</body>

</html>