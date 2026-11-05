<div class="navbar-custom">
    <div class="container-fluid">
        <ul class="list-unstyled topnav-menu float-right mb-0">
            <li class="dropdown d-none d-lg-inline-block">
                <a class="nav-link dropdown-toggle arrow-none waves-effect waves-light" data-toggle="fullscreen"
                    href="#">
                    <i class="fe-maximize noti-icon"></i>
                </a>
            </li>

            <li class="dropdown notification-list topbar-dropdown">
                <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect waves-light" data-toggle="dropdown"
                    href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    @if(auth()->user()->avatar)
                        <img src="{{asset('admin/users/' . auth()->user()->avatar)}}" alt="user-image"
                            class="mobile-user-image" height="60">
                    @else
                        <img src="{{asset('assets/images/users/user-5.jpg')}}" alt="user-image"
                            class="mobile-user-image" height="60">
                    @endif
                    <span class="pro-user-name ml-1">
                        {{ ucfirst(str_replace('_', ' ', auth()->user()->user_name)) }}
                        <i class="mdi mdi-chevron-down"></i>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                    <!-- item-->
                    <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">Welcome !</h6>
                    </div>

                    <!-- item-->
                    <a href="{{route('admin.profile')}}" class="dropdown-item notify-item">
                        <i class="fe-user"></i>
                        <span>My Profile</span>
                    </a>
                    <a href="{{route('admin.changepassword')}}" class="dropdown-item notify-item">
                        <i class="fe-user"></i>
                        <span>Change Password</span>
                    </a>

                    <div class="dropdown-divider"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();"
                            class="dropdown-item notify-item">
                            <i class="fe-log-out"></i><span>{{ __('Log Out') }}</span>
                        </a>
                    </form>
                </div>
            </li>
        </ul>

        <!-- LOGO -->
        {{-- hide this in mobile view --}}
        <div class="d-none d-lg-block">
            <div class="logo-box">
                <a href="{{ route('dashboard')}}" class="logo logo-light text-center">
                    <span class="logo-lg">
                    <img src="{{asset('assets/images/new_logo/main_logo.png')}}" alt="" height="65">
                    </span>
                </a>
            </div>
        </div>

        <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
            <li>
                <button class="button-menu-mobile waves-effect waves-light">
                    <i class="fe-menu"></i>
                </button>
            </li>
        </ul>
        <div class="clearfix"></div>
    </div>
</div>