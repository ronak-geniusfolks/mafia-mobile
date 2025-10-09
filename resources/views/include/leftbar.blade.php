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
                    <li class="{{ request()->routeIs(['allpurchases', 'purchase.create', 'purchase.create.multiple', 'purchase.edit', 'purchase-detail', 'purchase.importform']) ? 'menuitem-active' : '' }}">
                        <a data-toggle="collapse" href="#stocksMenu" role="button" aria-expanded="{{ request()->routeIs(['allpurchases', 'purchase.create', 'purchase.create.multiple', 'purchase.edit', 'purchase-detail', 'purchase.importform']) ? 'true' : 'false' }}" aria-controls="stocksMenu">
                            <i data-feather="shopping-cart"></i>
                            <span>Stocks</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs(['allpurchases', 'purchase.create', 'purchase.create.multiple', 'purchase.edit', 'purchase-detail', 'purchase.importform']) ? 'show' : '' }}" id="stocksMenu">
                            <ul class="nav-second-level">
                                @can('purchases.view')
                                <li><a href="{{ route('allpurchases') }}" class="{{ request()->routeIs('allpurchases') ? 'active' : '' }}">All Stocks</a></li> @endcan
                                @can('purchases.import')
                                <li><a href="{{ route('purchase.importform') }}" class="{{ request()->routeIs('purchase.importform') ? 'active' : '' }}">Import Stocks</a></li> @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                <!-- Sell -->
                @canany(['sales.view', 'sales.create'])
                    <li class="{{ request()->routeIs(['allsales', 'saleedit', 'saledetail', 'new-sale']) ? 'menuitem-active' : '' }}">
                        <a data-toggle="collapse" href="#salesMenu" role="button" aria-expanded="{{ request()->routeIs(['allsales', 'saleedit', 'saledetail', 'new-sale']) ? 'true' : 'false' }}" aria-controls="salesMenu">
                            <i data-feather="briefcase"></i>
                            <span>Sell</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs(['allsales', 'saleedit', 'saledetail', 'new-sale']) ? 'show' : '' }}" id="salesMenu">
                            <ul class="nav-second-level">
                                @can('sales.view')
                                <li><a href="{{ route('allsales') }}" class="{{ request()->routeIs('allsales') ? 'active' : '' }}">Sold Items</a></li> @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                <!-- Invoice -->
                @canany(['invoices.view', 'invoices.create'])
                    <li class="{{ request()->routeIs(['allinvoices', 'newinvoice', 'invoice-detail', 'print-invoice', 'invoice-edit']) ? 'menuitem-active' : '' }}">
                        <a data-toggle="collapse" href="#invoiceMenu" role="button" aria-expanded="{{ request()->routeIs(['allinvoices', 'newinvoice', 'invoice-detail', 'print-invoice', 'invoice-edit']) ? 'true' : 'false' }}"
                            aria-controls="invoiceMenu">
                            <i class="fa fa-file-invoice"></i>
                            <span>Invoice</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs(['allinvoices', 'newinvoice', 'invoice-detail', 'print-invoice', 'invoice-edit']) ? 'show' : '' }}" id="invoiceMenu">
                            <ul class="nav-second-level">
                                @can('invoices.view')
                                <li><a href="{{ route('allinvoices') }}" class="{{ request()->routeIs('allinvoices') ? 'active' : '' }}">Manage Invoice</a></li> @endcan
                                @can('invoices.create')
                                <li><a href="{{ route('newinvoice') }}" class="{{ request()->routeIs('newinvoice') ? 'active' : '' }}">New Invoice</a></li> @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                <!-- Reports -->
                @canany(['reports.sales.view', 'reports.purchases.view', 'reports.customers.view', 'reports.charts.view'])
                    <li class="{{ request()->routeIs(['sale-report', 'purchase-report', 'saleschart', 'customers']) ? 'menuitem-active' : '' }}">
                        <a data-toggle="collapse" href="#reportsMenu" role="button" aria-expanded="{{ request()->routeIs(['sale-report', 'purchase-report', 'saleschart', 'customers']) ? 'true' : 'false' }}"
                            aria-controls="reportsMenu">
                            <i class="fa fa-chart-line"></i>
                            <span>Reports</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs(['sale-report', 'purchase-report', 'saleschart', 'customers']) ? 'show' : '' }}" id="reportsMenu">
                            <ul class="nav-second-level">
                                @can('reports.sales.view')
                                <li><a href="{{ route('sale-report') }}" class="{{ request()->routeIs('sale-report') ? 'active' : '' }}">Sales</a></li> @endcan
                                @can('reports.purchases.view')
                                <li><a href="{{ route('purchase-report') }}" class="{{ request()->routeIs('purchase-report') ? 'active' : '' }}">Purchase</a></li> @endcan
                                @can('reports.charts.view')
                                <li><a href="{{ route('saleschart') }}" class="{{ request()->routeIs('saleschart') ? 'active' : '' }}">Chart</a></li> @endcan
                                @can('reports.customers.view')
                                <li><a href="{{ route('customers') }}" class="{{ request()->routeIs('customers') ? 'active' : '' }}">Customers</a></li> @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                <!-- Expenses -->
                @canany(['expenses.view', 'expenses.create'])
                    <li class="{{ request()->routeIs(['expenses', 'add-expense', 'edit-expense']) ? 'menuitem-active' : '' }}">
                        <a data-toggle="collapse" href="#expensesMenu" role="button" aria-expanded="{{ request()->routeIs(['expenses', 'add-expense', 'edit-expense']) ? 'true' : 'false' }}"
                            aria-controls="expensesMenu">
                            <i class="fa fa-wallet"></i>
                            <span>Expense Tracker</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs(['expenses', 'add-expense', 'edit-expense']) ? 'show' : '' }}" id="expensesMenu">
                            <ul class="nav-second-level">
                                @can('expenses.view')
                                <li><a href="{{ route('expenses') }}" class="{{ request()->routeIs('expenses') ? 'active' : '' }}">Expenses</a></li> @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                <!-- Finance -->
                @can('transactions.view')
                    <li class="{{ request()->routeIs('transactions.*') ? 'menuitem-active' : '' }}">
                        <a data-toggle="collapse" href="#transactionsMenu" role="button" aria-expanded="{{ request()->routeIs('transactions.*') ? 'true' : 'false' }}"
                            aria-controls="transactionsMenu">
                            <i class="fa-solid fa-dollar-sign fa"></i>
                            <span>Finance</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('transactions.*') ? 'show' : '' }}" id="transactionsMenu">
                            <ul class="nav-second-level">
                                <li><a href="{{ route('transactions.index') }}" class="{{ request()->routeIs('transactions.index') ? 'active' : '' }}">Transactions</a></li>
                            </ul>
                        </div>
                    </li>
                @endcan

                <!-- Admin Management -->
                @canany(['roles.view', 'roles.create', 'users.view', 'users.create'])
                    <li class="{{ request()->routeIs(['roles.*', 'users.*']) ? 'menuitem-active' : '' }}">
                        <a data-toggle="collapse" href="#adminMenu" role="button" aria-expanded="{{ request()->routeIs(['roles.*', 'users.*']) ? 'true' : 'false' }}" aria-controls="adminMenu">
                            <i class="fa fa-user-shield"></i>
                            <span>Admin Tools</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs(['roles.*', 'users.*']) ? 'show' : '' }}" id="adminMenu">
                            <ul class="nav-second-level">
                                @can('roles.view')
                                <li><a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.index') ? 'active' : '' }}">Role list</a></li> @endcan
                                @can('users.view')
                                <li><a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.index') ? 'active' : '' }}">Users list</a></li> @endcan
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