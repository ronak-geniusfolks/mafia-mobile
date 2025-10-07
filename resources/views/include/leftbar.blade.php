<div class="left-side-menu">

    <div class="h-100" data-simplebar>

        <!-- User box -->
        <div class="user-box text-center">
            <img src="{{ asset('assets/images/users/user-1.jpg') }}" alt="user-img" title="Mat Helme"
                class="rounded-circle avatar-md">
            <div class="dropdown">
                <a href="javascript: void(0);" class="text-dark dropdown-toggle h5 mt-2 mb-1 d-block"
                    data-toggle="dropdown">Geneva Kennedy</a>
                <div class="dropdown-menu user-pro-dropdown">

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-user mr-1"></i>
                        <span>My Account</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-settings mr-1"></i>
                        <span>Settings</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-lock mr-1"></i>
                        <span>Lock Screen</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-log-out mr-1"></i>
                        <span>Logout</span>
                    </a>

                </div>
            </div>
            <p class="text-muted">Admin Head</p>
        </div>

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <ul id="side-menu">
                <!-- Dashboard -->
                @can('dashboard.view')
                    <li>
                        <a href="{{ route('dashboard') }}">
                            <i data-feather="airplay"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                @endcan

                <!-- Stocks -->
                @canany(['purchases.view', 'purchases.create', 'purchases.import'])
                    <li>
                        <a data-toggle="collapse" href="#stocksMenu" role="button" aria-expanded="false" aria-controls="stocksMenu">
                            <i data-feather="shopping-cart"></i>
                            <span>Stocks</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="stocksMenu">
                            <ul class="nav-second-level">
                                @can('purchases.view')
                                <li><a href="{{ route('allpurchases') }}">All Stocks</a></li> @endcan
                                {{-- @can('purchases.create')
                                <li><a href="{{ route('purchase.create') }}">Add Stock</a></li> @endcan --}}
                                @can('purchases.import')
                                <li><a href="{{ route('purchase.importform') }}">Import Stocks</a></li> @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                <!-- Sell -->
                @canany(['sales.view', 'sales.create'])
                    <li>
                        <a data-toggle="collapse" href="#salesMenu" role="button" aria-expanded="false" aria-controls="salesMenu">
                            <i data-feather="briefcase"></i>
                            <span>Sell</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="salesMenu">
                            <ul class="nav-second-level">
                                @can('sales.view')
                                <li><a href="{{ route('allsales') }}">Sold Items</a></li> @endcan
                                @can('sales.create')
                                <li><a href="{{ route('new-sale') }}">New Sale</a></li> @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                <!-- Invoice -->
                @canany(['invoices.view', 'invoices.create'])
                    <li>
                        <a data-toggle="collapse" href="#invoiceMenu" role="button" aria-expanded="false"
                            aria-controls="invoiceMenu">
                            <i class="fa fa-file-invoice"></i>
                            <span>Invoice</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="invoiceMenu">
                            <ul class="nav-second-level">
                                @can('invoices.view')
                                <li><a href="{{ route('allinvoices') }}">Manage Invoice</a></li> @endcan
                                @can('invoices.create')
                                <li><a href="{{ route('newinvoice') }}">New Invoice</a></li> @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                <!-- Reports -->
                @canany(['reports.sales.view', 'reports.purchases.view', 'reports.customers.view', 'reports.charts.view'])
                    <li>
                        <a data-toggle="collapse" href="#reportsMenu" role="button" aria-expanded="false"
                            aria-controls="reportsMenu">
                            <i class="fa fa-chart-line"></i>
                            <span>Reports</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="reportsMenu">
                            <ul class="nav-second-level">
                                @can('reports.sales.view')
                                <li><a href="{{ route('sale-report') }}">Sales</a></li> @endcan
                                @can('reports.purchases.view')
                                <li><a href="{{ route('purchase-report') }}">Purchase</a></li> @endcan
                                @can('reports.charts.view')
                                <li><a href="{{ route('saleschart') }}">Chart</a></li> @endcan
                                @can('reports.customers.view')
                                <li><a href="{{ route('customers') }}">Customers</a></li> @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                <!-- Expenses -->
                {{-- @canany(['expenses.view', 'expenses.create'])
                    <li>
                        <a data-toggle="collapse" href="#expensesMenu" role="button" aria-expanded="false"
                            aria-controls="expensesMenu">
                            <i class="fa fa-wallet"></i>
                            <span>Expense Tracker</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="expensesMenu">
                            <ul class="nav-second-level">
                                @can('expenses.view')
                                <li><a href="{{ route('expenses') }}">Expenses</a></li> @endcan
                                @can('expenses.create')
                                <li><a href="{{ route('add-expense') }}">Add Expense</a></li> @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany --}}

                <!-- Finance -->
                @can('transactions.view')
                    <li>
                        <a data-toggle="collapse" href="#transactionsMenu" role="button" aria-expanded="false"
                            aria-controls="transactionsMenu">
                            <i class="fa-solid fa-dollar-sign fa"></i>
                            <span>Finance</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="transactionsMenu">
                            <ul class="nav-second-level">
                                <li><a href="{{ route('transactions.index') }}">Transactions</a></li>
                            </ul>
                        </div>
                    </li>
                @endcan

                <!-- Admin Management -->
                @canany(['roles.view', 'roles.create', 'users.view', 'users.create'])
                    <li>
                        <a data-toggle="collapse" href="#adminMenu" role="button" aria-expanded="false" aria-controls="adminMenu">
                            <i class="fa fa-user-shield"></i>
                            <span>Admin Tools</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="adminMenu">
                            <ul class="nav-second-level">
                                @can('roles.view')
                                <li><a href="{{ route('roles.index') }}">Role list</a></li> @endcan
                                @can('users.view')
                                <li><a href="{{ route('users.index') }}">Users list</a></li> @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

            </ul>
        </div>

        <!-- End Sidebar -->
        <div class="clearfix"></div>
    </div>
    <!-- Sidebar -left -->
</div>