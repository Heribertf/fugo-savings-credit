<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-id" content="{{ Auth::id() }}">
    @endauth
    <title>@yield('title', 'Admin Panel') - FuGo Admin</title>
    <link rel="stylesheet" href="{{ asset('assets/css/src/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/notyf/3.10.0/notyf.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
            --primary-color: #4e73df;
            --secondary-color: #858796;
        }

        body {
            /* font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; */
            overflow-x: hidden;
            background-color: #f8f9fc;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, #4e73df 0%, #224abe 100%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1rem;
            background: rgba(0, 0, 0, 0.1);
        }

        .sidebar-brand {
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-item {
            margin: 0.2rem 1rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            border-radius: 0.35rem;
            transition: all 0.2s ease;
            padding: 0.8rem 1rem !important;
        }

        .nav-link:hover,
        .nav-link.active {
            color: white !important;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-link i {
            width: 1.5rem;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin 0.3s ease;
        }

        .topbar {
            height: var(--header-height);
            background: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .dropdown-menu {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        /* Dashboard Cards */
        .stat-card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .chart-card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                padding-left: 1rem !important;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 0.5rem;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 0.25rem;
        }


        .table-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
            padding: 20px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #ffc107;
            color: #000;
        }

        .status-approved {
            background-color: #198754;
            color: #fff;
        }

        .status-rejected {
            background-color: #dc3545;
            color: #fff;
        }

        .table> :not(caption)>*>* {
            padding: 1rem;
        }

        .action-buttons .btn {
            margin: 0 2px;
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9rem;
            }

            .action-buttons .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">
                <i class="fas fa-laugh-wink me-2"></i>
                FuGo Admin
            </a>
        </div>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link @if (request()->routeIs('admin.dashboard')) active @endif">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.users') }}" class="nav-link @if (request()->routeIs('admin.users')) active @endif">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.withdrawals') }}"
                    class="nav-link @if (request()->routeIs('admin.withdrawals')) active @endif">
                    <i class="fas fa-fw fa-money-bill-wave"></i>
                    <span>Withdrawals</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.loans') }}" class="nav-link @if (request()->routeIs('admin.loans')) active @endif">
                    <i class="fas fa-fw fa-hand-holding-usd"></i>
                    <span>Loans</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.support') }}" class="nav-link @if (request()->routeIs('admin.support')) active @endif">
                    <i class="fas fa-fw fa-headset"></i>
                    <span>Support</span>
                </a>
            </li>
            <li class="nav-item mt-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                                this.closest('form').submit();"
                        class="nav-link text-danger">
                        <i class="fas fa-fw fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </form>
            </li>
        </ul>
    </nav>
    <div class="main-content">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand navbar-light topbar mb-4 px-4">
            <button id="sidebarToggle" class="btn btn-link d-md-none">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="navbar-nav ms-auto">
                <!-- Notifications Dropdown -->
                {{-- <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                        data-bs-toggle="dropdown">
                        <i class="fas fa-bell fa-fw"></i>
                        <span class="badge bg-danger badge-counter">3+</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Notifications</h6>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="icon-circle bg-primary text-white p-2">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="small text-muted">December 12, 2023</div>
                                    <span>A new monthly report is ready to download!</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </li> --}}

                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                        data-bs-toggle="dropdown">
                        <span class="d-none d-lg-inline text-gray-600 small me-2">Admin User</span>
                        <img class="img-profile rounded-circle" src="/api/placeholder/32/32" alt="Profile">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('admin.profile') }}">
                            <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                            Profile
                        </a>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-cogs fa-sm fa-fw me-2 text-gray-400"></i>
                            Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- Main Content -->
        @yield('content')
    </div>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ready to Leave?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Select "Logout" below if you are ready to end your current session.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    {{-- <a href="#" class="btn btn-primary">Logout</a> --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                                    this.closest('form').submit();"
                            class="btn btn-primary">
                            <span>Logout</span>
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.35.0/apexcharts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/notyf/3.10.0/notyf.min.js"></script>
    <script>
        const notyf = new Notyf({
            duration: 3000,
            position: {
                x: 'right',
                y: 'top'
            }
        });

        // Toggle Sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 768) {
                const sidebar = document.querySelector('.sidebar');
                const sidebarToggle = document.getElementById('sidebarToggle');
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target) && sidebar.classList.contains(
                        'show')) {
                    sidebar.classList.remove('show');
                }
            }
        });
        @if (session('success'))
            notyf.success('{{ session('success') }}');
        @elseif (session('error'))
            notyf.error('{{ session('error') }}');
        @endif
    </script>
    @vite('resources/js/app.js')
    @vite('resources/js/chat.js')
    @stack('scripts')
</body>

</html>
