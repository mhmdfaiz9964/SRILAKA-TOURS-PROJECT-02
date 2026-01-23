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
            --sidebar-width: 280px;
            --primary-color: #6366f1;
            --primary-light: #eef2ff;
            --bg-light: #f9fafb;
            --text-dark: #111827;
            --text-muted: #6b7280;
            --sidebar-bg: #ffffff;
            --sidebar-hover: #f3f4f6;
            --sidebar-active: #eef2ff;
            --sidebar-active-text: #6366f1;
            --border-color: #f3f4f6;
            --card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        #app {
            display: flex;
            min-height: 100vh;
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
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-header {
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
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
            font-size: 14px;
        }

        .brand-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -0.025em;
        }

        .sidebar-actions {
            padding: 0 24px 16px;
        }

        .btn-add-new {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 600;
            color: var(--text-dark);
            transition: all 0.2s;
            text-decoration: none;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        }

        .btn-add-new:hover {
            background: var(--sidebar-hover);
        }

        .sidebar-search {
            padding: 0 24px 20px;
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
            font-size: 0.9rem;
        }

        .search-input {
            width: 100%;
            padding: 8px 12px 8px 36px;
            background: #fdfdfd;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .search-input:focus {
            border-color: var(--primary-color);
        }

        .sidebar-menu {
            flex-grow: 1;
            padding: 0 12px 20px;
            overflow-y: auto;
        }

        .menu-label {
            padding: 20px 12px 8px;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .menu-item {
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s;
            border-radius: 8px;
            margin-bottom: 2px;
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
            color: #000;
        }

        .menu-item:hover {
            background: var(--sidebar-hover);
            color: var(--text-dark);
        }

        .menu-item.active {
            background: var(--sidebar-active);
            color: var(--primary-color);
        }

        .badge {
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 6px;
        }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border-color);
            background: #fff;
        }

        .user-profile {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar-box {
            position: relative;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-weight: 700;
            border: 1px solid #e0e7ff;
        }

        .status-dot {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 10px;
            height: 10px;
            background: #10b981;
            border: 2px solid white;
            border-radius: 50%;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-email {
            font-size: 0.75rem;
            color: var(--text-muted);
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
</head>
<body>
    <div id="app">
        @auth
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo-section">
                    <div class="logo-box">AP</div>
                    <div class="brand-name">APEX CRM</div>
                </div>
                <button class="btn btn-sm text-muted">
                    <i class="fa-solid fa-square-poll-vertical"></i>
                </button>
            </div>

            <div class="sidebar-actions">
                <a href="#" class="btn-add-new">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add New</span>
                </a>
            </div>

            <div class="sidebar-search">
                <div class="search-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" class="search-input" placeholder="Search...">
                </div>
            </div>

            <div class="sidebar-menu">
                <a href="{{ route('home') }}" class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-house"></i>
                        <span>Dashboard</span>
                    </div>
                </a>

                <a href="{{ route('users.index') }}" class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-user-gear"></i>
                        <span>Users</span>
                    </div>
                </a>

                <a href="{{ route('cheques.index') }}" class="menu-item {{ request()->routeIs('cheques.index') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-money-check-dollar"></i>
                        <span>Cheque Management</span>
                    </div>
                </a>

                <a href="{{ route('cheques.payment') }}" class="menu-item {{ request()->routeIs('cheques.payment') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Payment Cheques</span>
                    </div>
                </a>

                <a href="{{ route('cheques.paid') }}" class="menu-item {{ request()->routeIs('cheques.paid') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Paid Cheques</span>
                    </div>
                </a>

                <a href="{{ route('banks.index') }}" class="menu-item {{ request()->routeIs('banks.*') ? 'active' : '' }}">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-building-columns"></i>
                        <span>Banks</span>
                    </div>
                </a>

                <a href="#" class="menu-item">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-box"></i>
                        <span>Product</span>
                    </div>
                </a>

                <a href="#" class="menu-item">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        <span>Investors</span>
                    </div>
                </a>

                <a href="#" class="menu-item">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-truck-field"></i>
                        <span>Supplier</span>
                    </div>
                </a>

                <a href="#" class="menu-item">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-user-tag"></i>
                        <span>Customer</span>
                    </div>
                </a>

                <a href="#" class="menu-item">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-layer-group"></i>
                        <span>Stage</span>
                    </div>
                </a>

                <div class="menu-label">System</div>
                <a href="javascript:void(0)" onclick="updateSystem()" class="menu-item">
                    <div class="menu-item-content">
                        <i class="fa-solid fa-rotate"></i>
                        <span>System Update</span>
                    </div>
                </a>
            </div>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-info">
                        <div class="avatar-box">
                            <div class="user-avatar">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div class="status-dot"></div>
                        </div>
                        <div class="user-details">
                            <span class="user-name">{{ Auth::user()->name }}</span>
                            <span class="user-email">Pro Member</span>
                        </div>
                    </div>
                    <a href="javascript:void(0)" 
                       class="text-muted"
                       onclick="confirmLogout()">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </a>
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
                        <i class="fa-regular fa-bell text-black cursor-pointer"></i>
                        <i class="fa-regular fa-comment-dots text-black cursor-pointer"></i>
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
                        <div>&copy; {{ date('Y') }} <strong>Apex Web Innovations</strong>. All rights reserved.</div>
                        <div class="d-flex gap-3">
                            <a href="#" class="text-muted text-decoration-none">Privacy Policy</a>
                            <a href="#" class="text-muted text-decoration-none">Terms of Service</a>
                        </div>
                    </div>
                </div>
            </footer>
        </main>

    </div>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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

        function confirmDelete(id, formId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4d4d',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            })
        }

        function confirmLogout() {
            Swal.fire({
                title: 'Sign Out',
                text: "Are you sure you want to log out of the system?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Sign Out'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            })
        }

        function updateSystem() {
            Swal.fire({
                title: 'System Update',
                html: `
                    <div class="text-start">
                        <p class="fw-bold mb-2">Changelog (v2.1.0):</p>
                        <ul class="small text-muted ps-3">
                            <li>Redesigned Dashboard & Sidebar with Purple Theme</li>
                            <li>Enhanced Table views for Users, Banks and Cheques</li>
                            <li>Fixed Cheque Number linking issues</li>
                            <li>Implemented Automated System Update System</li>
                            <li>Initial backend support for Products and Investors</li>
                        </ul>
                        <p class="small mt-3 text-warning"><i class="fa-solid fa-triangle-exclamation"></i> This will run <code>git pull</code> and <code>php artisan migrate</code>.</p>
                    </div>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Start Update',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch("{{ route('system.update') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(response.statusText)
                        }
                        return response.json()
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error}`)
                    })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'System updated successfully with latest changes.',
                            icon: 'success',
                            confirmButtonColor: '#6366f1'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', result.value.message, 'error');
                    }
                }
            })
        }
    </script>
</body>
</html>

