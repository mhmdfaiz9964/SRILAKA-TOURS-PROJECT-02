<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!-- Date Range Picker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    
    <style>
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
    </style>

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #5551FF;
            --primary-light: #EEF2FF;
            --bg-light: #F8F9FA;
            --text-dark: #1A1D1F;
            --text-muted: #6F767E;
            --sidebar-bg: #FFFFFF;
            --sidebar-hover: #F4F4F4;
            --sidebar-active: #F4F4F4;
            --border-color: #EFEFEF;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
            color: var(--text-dark);
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-box {
            background: var(--primary-color);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .brand-info {
            display: flex;
            flex-direction: column;
        }

        .brand-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.2;
        }

        .brand-plan {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .sidebar-search {
            padding: 0 16px 16px;
        }

        .search-wrapper {
            position: relative;
        }

        .search-wrapper i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .search-input {
            width: 100%;
            padding: 8px 36px 8px 36px;
            background: #F4F4F4;
            border: 1px solid transparent;
            border-radius: 10px;
            font-size: 0.9rem;
            outline: none;
        }

        .search-hint {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #9A9FA5;
            font-size: 0.75rem;
            font-weight: 500;
            background: #fff;
            padding: 1px 4px;
            border-radius: 4px;
            border: 1px solid #EFEFEF;
        }

        .sidebar-menu {
            flex-grow: 1;
            padding: 8px 12px;
            overflow-y: auto;
        }

        .menu-label-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 12px 8px;
        }

        .menu-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #9A9FA5;
            text-transform: none;
            letter-spacing: normal;
        }

        .menu-label-plus {
            color: #9A9FA5;
            font-size: 0.8rem;
            cursor: pointer;
        }

        .menu-item {
            padding: 8px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #1A1D1F;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 10px;
            margin-bottom: 2px;
            transition: all 0.2s;
        }

        .menu-item-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .menu-item i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
            color: #1A1D1F;
        }

        .menu-item-hint {
            font-size: 0.75rem;
            color: #9A9FA5;
            font-weight: 500;
        }

        .menu-item:hover {
            background: var(--sidebar-hover);
        }

        .menu-item.active {
            background: var(--sidebar-active);
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border-color);
        }

        .user-profile {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .user-profile:hover {
            background: var(--sidebar-hover);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #FFD2D2;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-details {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .user-name {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .user-email {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .profile-chevron {
            color: #9A9FA5;
            font-size: 0.8rem;
        }

        .hover-bg-light:hover { background-color: #f8fafc; transition: background 0.2s; }

        /* Hover Dropdowns for Toolbar */
        .toolbar .dropdown:hover .dropdown-menu {
            display: block;
            margin-top: 0;
        }

        /* Main Content Styling */
        .main-content {
            flex-grow: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navbar */
        .content-navbar {
            padding: 12px 30px;
            background: white;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .breadcrumb-section {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-search {
            position: relative;
            width: 400px;
        }

        .nav-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .nav-search input {
            width: 100%;
            padding: 8px 12px 8px 36px;
            background: #f9fafb;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .view-pane {
            padding: 30px;
        }

        /* Card Styling Enhancement */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    <!-- jQuery (required for daterangepicker) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Moment.js (required for daterangepicker) -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <!-- Date Range Picker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
</head>
<body>
    <div id="app">
        @auth
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo-section">
                    <div class="logo-box">JS</div>
                    <div class="brand-info">
                        <span class="brand-name">JS FABRIC</span>
                        <span class="brand-plan">Enterprise Plan</span>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-up-down profile-chevron"></i>
            </div>

            <div class="sidebar-search">
                <div class="search-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" class="search-input" placeholder="Search">
                    <span class="search-hint">⌘1</span>
                </div>
            </div>

            <div class="sidebar-menu">
                <div class="menu-label-wrapper">
                    <span class="menu-label">Management</span>
                    <i class="fa-solid fa-plus menu-label-plus"></i>
                </div>

                @can('product-list')
                <a href="{{ route('products.index') }}" class="menu-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-box"></i>
                        <span>Product</span>
                    </div>
                </a>
                @endcan

                @can('customer-list')
                <a href="{{ route('customers.index') }}" class="menu-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-users"></i>
                        <span>Customer</span>
                    </div>
                </a>
                @endcan

                @can('supplier-list')
                <a href="{{ route('suppliers.index') }}" class="menu-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-truck-field"></i>
                        <span>Supplier</span>
                    </div>
                </a>
                @endcan

                @can('investor-list')
                <a href="{{ route('investors.index') }}" class="menu-item {{ request()->routeIs('investors.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        <span>Investors</span>
                    </div>
                </a>
                @endcan

                <div class="menu-label-wrapper">
                    <span class="menu-label">Transactions</span>
                </div>

                @can('sale-list')
                <a href="{{ route('sales.index') }}" class="menu-item {{ request()->routeIs('sales.index') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>Sales</span>
                    </div>
                </a>
                <a href="{{ route('sales.return.index') }}" class="menu-item {{ request()->routeIs('sales.return.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Sales Return</span>
                    </div>
                </a>
                @endcan

                @can('purchase-list')
                <a href="{{ route('purchases.index') }}" class="menu-item {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-cart-flatbed"></i>
                        <span>Purchases</span>
                    </div>
                </a>
                @endcan

                @can('cheque-list')
                <div class="menu-label-wrapper">
                    <span class="menu-label">Cheque Operation</span>
                    <i class="fa-solid fa-plus menu-label-plus"></i>
                </div>

                @can('in-cheque-list')
                <a href="{{ route('in-cheques.index') }}" class="menu-item {{ request()->routeIs('in-cheques.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-file-import"></i>
                        <span>In Cheque</span>
                    </div>
                </a>
                @endcan

                @can('out-cheque-list')
                <a href="{{ route('out-cheques.index') }}" class="menu-item {{ request()->routeIs('out-cheques.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-file-export"></i>
                        <span>Out Cheque</span>
                    </div>
                </a>
                @endcan

                @can('third-party-cheque-list')
                <a href="{{ route('third-party-cheques.index') }}" class="menu-item {{ request()->routeIs('third-party-cheques.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-users-viewfinder"></i>
                        <span>3rd Party Cheque</span>
                    </div>
                </a>
                @endcan

                <div class="menu-label-wrapper mt-3">
                    <span class="menu-label">Returns & Payments</span>
                </div>

                <a href="{{ route('cheques.index') }}" class="menu-item {{ request()->routeIs('cheques.index') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-money-check-dollar"></i>
                        <span>RTN Cheque</span>
                    </div>
                </a>

                <a href="{{ route('cheques.paid') }}" class="menu-item {{ request()->routeIs('cheques.paid') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Completed Cheque</span>
                    </div>
                </a>

                @can('bank-list')
                <a href="{{ route('banks.index') }}" class="menu-item {{ request()->routeIs('banks.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-building-columns"></i>
                        <span>Banks</span>
                    </div>
                </a>
                @endcan
                @endcan

                <div class="menu-label-wrapper">
                    <span class="menu-label">Financials</span>
                </div>

                <a href="{{ route('expenses.index') }}" class="menu-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Expenses</span>
                    </div>
                </a>

                <a href="{{ route('reports.daily-ledger') }}" class="menu-item {{ request()->routeIs('reports.daily-ledger') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Daily Ledger</span>
                    </div>
                </a>

                <a href="{{ route('reports.balance-sheet') }}" class="menu-item {{ request()->routeIs('reports.balance-sheet') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-scale-balanced"></i>
                        <span>Balance Sheet</span>
                    </div>
                </a>

                <div class="menu-label-wrapper">
                    <span class="menu-label">Main</span>
                </div>
                
                @can('dashboard-view')
                <a href="{{ route('home') }}" class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-house"></i>
                        <span>Dashboard</span>
                    </div>
                    <span class="menu-item-hint">⌘2</span>
                </a>
                @endcan

                @can('user-list')
                <a href="{{ route('users.index') }}" class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-user-gear"></i>
                        <span>Users</span>
                    </div>
                    <span class="menu-item-hint">⌘3</span>
                </a>
                @endcan

                @can('role-list')
                <a href="{{ route('roles.index') }}" class="menu-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Roles</span>
                    </div>
                </a>
                @endcan

                @if(auth()->user()->can('settings-manage') || auth()->user()->can('system-manage'))
                <div class="menu-label-wrapper">
                    <span class="menu-label">System</span>
                </div>

                @can('settings-manage')
                <a href="{{ route('settings.index') }}" class="menu-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-gear"></i>
                        <span>Settings</span>
                    </div>
                </a>
                @endcan

                @can('system-manage')
                <a href="{{ route('system.index') }}" class="menu-item {{ request()->routeIs('system.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-microchip"></i>
                        <span>System Settings</span>
                    </div>
                </a>
                
                <a href="javascript:void(0)" onclick="updateSystem()" class="menu-item">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-rotate"></i>
                        <span>System Update</span>
                    </div>
                </a>
                @endcan
                @endif
            </div>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <a href="{{ route('profile.edit') }}" class="user-info text-decoration-none">
                        <div class="user-avatar text-white fw-bold" style="background: #FF6A6A;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="user-details">
                            <span class="user-name">{{ Auth::user()->name }}</span>
                            <span class="user-email text-muted">{{ Auth::user()->email }}</span>
                        </div>
                    </a>
                    <button type="button" onclick="confirmLogout()" class="btn btn-sm btn-icon border-0 text-danger shadow-none p-0" title="Logout" style="font-size: 1.1rem;">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </aside>
        @endauth

        <main class="{{ Auth::check() ? 'main-content' : 'w-100 py-4' }}">
            @auth
            <header class="content-navbar">
                <div class="breadcrumb-section">
                    <i class="fa-solid fa-house"></i>
                    <i class="fa-solid fa-chevron-right fa-xs mx-1"></i>
                    <span>Dashboard</span>
                    <i class="fa-solid fa-chevron-right fa-xs mx-1"></i>
                    <span class="text-dark fw-bold">Overview</span>
                </div>

                <div class="navbar-actions">
                    <div class="nav-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Search in items...">
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="dropdown">
                            <div class="position-relative cursor-pointer" data-bs-toggle="dropdown">
                                <i class="fa-regular fa-bell text-black" style="font-size: 1.1rem;"></i>
                                @if(isset($reminderCount) && $reminderCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.5rem; padding: 0.25rem 0.4rem;">
                                        {{ $reminderCount }}
                                    </span>
                                @endif
                            </div>
                            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 mt-2" style="width: 300px;">
                                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold">Notifications</h6>
                                    @if(isset($reminderCount) && $reminderCount > 0)
                                        <span class="badge bg-danger-subtle text-danger rounded-pill small">{{ $reminderCount }} New</span>
                                    @endif
                                </div>
                                <div class="notification-list" style="max-height: 300px; overflow-y: auto;">
                                    @if(isset($dueReminders) && $dueReminders->count() > 0)
                                        @foreach($dueReminders as $reminder)
                                            <div class="p-3 border-bottom hover-bg-light cursor-pointer">
                                                <div class="d-flex gap-2">
                                                    <div class="flex-shrink-0">
                                                        <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                            <i class="fa-solid fa-clock text-primary small"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="small fw-bold text-dark">{{ $reminder->payer_name ?? ($reminder->cheque->payer_name ?? 'Cheque') }}</div>
                                                            <form action="{{ route('reminders.complete', $reminder) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-icon border-0 text-success p-0 shadow-none" title="Mark as Completed">
                                                                    <i class="fa-solid fa-circle-check"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                        <div class="text-muted small text-truncate" style="max-width: 180px;">{{ $reminder->notes }}</div>
                                                        <div class="text-primary mt-1" style="font-size: 0.65rem;">{{ \Carbon\Carbon::parse($reminder->reminder_date)->format('d M, H:i A') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="p-4 text-center text-muted small">
                                            <i class="fa-regular fa-bell-slash d-block mb-2 fs-4"></i>
                                            No reminders for today
                                        </div>
                                    @endif
                                </div>
                                <div class="p-2 text-center border-top">
                                    <a href="#" class="text-primary small fw-bold text-decoration-none">View All Notifications</a>
                                </div>
                            </div>
                        </div>
                        <i class="fa-regular fa-comment-dots text-black cursor-pointer"></i>
                        
                        <!-- Profile Dropdown -->
                        <div class="dropdown">
                            <div class="d-flex align-items-center gap-2 cursor-pointer" data-bs-toggle="dropdown">
                                <div class="user-avatar text-white fw-bold shadow-sm" style="background: #FF6A6A; width: 32px; height: 32px; font-size: 0.8rem;">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <i class="fa-solid fa-chevron-down text-muted small"></i>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2">
                                <li class="px-3 py-2 border-bottom mb-2">
                                    <div class="fw-bold small text-dark">{{ Auth::user()->name }}</div>
                                    <div class="text-muted small" style="font-size: 0.7rem;">{{ Auth::user()->email }}</div>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 d-flex align-items-center gap-2" href="{{ route('profile.edit') }}">
                                        <i class="fa-solid fa-user-circle text-muted"></i> Edit Profile
                                    </a>
                                </li>
                                @can('settings-manage')
                                <li>
                                    <a class="dropdown-item rounded-3 d-flex align-items-center gap-2" href="{{ route('settings.index') }}">
                                        <i class="fa-solid fa-gear text-muted"></i> Settings
                                    </a>
                                </li>
                                @endcan
                                <li><hr class="dropdown-divider bg-light"></li>
                                <li>
                                    <a class="dropdown-item rounded-3 d-flex align-items-center gap-2 text-danger" href="javascript:void(0)" onclick="confirmLogout()">
                                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>
            @endauth

            <div class="{{ Auth::check() ? 'view-pane' : 'container' }}">
                @yield('content')
            </div>

            <footer class="mt-auto py-3 px-4 border-top bg-white">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between small text-muted">
                        <div>&copy; {{ date('Y') }} <strong>JS FABRIC</strong>. All rights reserved.</div>
                        <div class="d-flex gap-3">
                            <a href="#" class="text-muted text-decoration-none">Privacy Policy</a>
                            <a href="#" class="text-muted text-decoration-none">Terms of Service</a>
                        </div>
                    </div>
                </div>
            </footer>
        </main>

    </div>

    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        @endif

        @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: "{{ session('error') }}"
            });
        @endif

        @if($errors->any())
            Toast.fire({
                icon: 'error',
                title: "{{ $errors->first() }}"
            });
        @endif

        function confirmLogout() {
            Swal.fire({
                title: 'Logout?',
                text: "Are you sure you want to exit the system?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, logout'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            })
        }

        function updateSystem() {
            Swal.fire({
                title: 'System Update',
                text: "Are you sure you want to update the system?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Update Now'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Actual update call would go here
                    Swal.fire('Updated!', 'System is already up to date.', 'success');
                }
            })
        }
    </script>
</body>
</html>

