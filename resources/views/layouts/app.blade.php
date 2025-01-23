{{-- <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html> --}}



<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-id" content="{{ Auth::id() }}">
    @endauth
    <title>@yield('title', 'FuGo Savings & Credit')</title>
    <meta name="description" content="@yield('description')">
    {{-- <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}"> --}}

    <link rel="stylesheet" href="{{ asset('assets/css/src/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/notyf/3.10.0/notyf.min.css" rel="stylesheet">

    @stack('styles')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #00B4DB, #0083B0);
            --text-color: #2D3748;
            --success: #38A169;
            --danger: #E53E3E;
            --warning: #D69E2E;
        }

        body {
            background-color: #F7FAFC;
            color: var(--text-color);
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .bottom-nav a {
            color: #6c757d;
            text-decoration: none;
            font-size: 0.8rem;
            padding: 8px 0;
            transition: color 0.3s;
        }

        .bottom-nav a:hover,
        .bottom-nav a.active {
            color: #0d6efd;
        }

        .bottom-nav i {
            font-size: 1.4rem;
        }

        .app-header {
            position: fixed;
            top: 0;
            width: 100%;
            background: white;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        main {
            padding-top: 60px;
            padding-bottom: 80px;
        }

        /* More Menu Styles */
        .more-menu {
            position: fixed;
            max-height: 80vh;
            overflow-y: auto;
            bottom: 55px;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            transform: translateY(100%);
            transition: transform 0.3s ease-out;
            z-index: 999;
        }

        .more-menu.show {
            transform: translateY(0);
        }

        .more-menu-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .more-menu-section {
            border-bottom: 1px solid #eee;
        }

        .more-menu-section:last-child {
            border-bottom: none;
        }


        .more-menu-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s;
            z-index: 998;
        }

        .more-menu-backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .modal.fade .modal-dialog {
            transition: transform 0.2s ease-out;
        }

        #loader {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }

        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #1B5E20;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-light">
    {{-- @include('layouts.partials.nav') --}}

    <header class="app-header">
        <div class="container py-3">
            <h1 class="h4 mb-0 text-center">FuGo</h1>
        </div>
    </header>

    <main>
        <div id="loader">
            <div class="loader"></div>
        </div>

        @yield('content')
    </main>

    <!-- More Menu -->
    <div class="more-menu-backdrop"></div>
    <div class="more-menu">
        <div class="container">
            <!-- Primary Actions -->
            <div class="more-menu-section">
                <a href="{{ route('kyc') }}" class="more-menu-item d-flex align-items-center">
                    <i class="bi bi-person-badge me-3"></i>
                    <div>
                        <div>KYC Verification</div>
                        <small class="text-muted">Complete your verification</small>
                    </div>
                </a>
            </div>

            <!-- Core Features -->
            <div class="more-menu-section">
                <a href="{{ route('loans') }}" class="more-menu-item d-flex align-items-center">
                    <i class="bi bi-wallet2 me-3"></i>
                    <span>Loans</span>
                </a>
                <a href="/referrals" class="more-menu-item d-flex align-items-center">
                    <i class="bi bi-people me-3"></i>
                    <span>Referrals</span>
                </a>
                <a href="/transactions" class="more-menu-item d-flex align-items-center">
                    <i class="bi bi-bar-chart me-3"></i>
                    <span>Transactions</span>
                </a>
                <a href="/savings" class="more-menu-item d-flex align-items-center">
                    <i class="bi bi-piggy-bank me-3"></i>
                    <span>Savings</span>
                </a>
            </div>

            <!-- Support & Settings -->
            <div class="more-menu-section">
                <a href="{{ route('support') }}" class="more-menu-item d-flex align-items-center">
                    <i class="bi bi-headset me-3"></i>
                    <span>Support</span>
                </a>
                <a href="/settings" class="more-menu-item d-flex align-items-center">
                    <i class="bi bi-gear me-3"></i>
                    <span>Settings</span>
                </a>
            </div>

            <!-- Logout -->
            <div class="more-menu-section border-top mt-2">

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                                this.closest('form').submit();"
                        class="more-menu-item d-flex align-items-center text-danger">
                        <i class="bi bi-box-arrow-right me-3"></i>
                        <span>Logout</span>
                    </a>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <div class="container">
            <div class="row text-center g-0">
                <div class="col">
                    <a href="/dashboard" class="d-block">
                        <i class="bi bi-house-door"></i>
                        <div>Home</div>
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('wallets') }}" class="d-block">
                        <i class="bi bi-wallet"></i>
                        <div>Wallets</div>
                    </a>
                </div>
                <div class="col">
                    <a href="#" class="d-block" id="moreMenuBtn">
                        <i class="bi bi-three-dots"></i>
                        <div>More</div>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- @include('partials.footer') --}}

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.35.0/apexcharts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/notyf/3.10.0/notyf.min.js"></script>

    @vite('resources/js/app.js')
    @vite('resources/js/chat.js')

    <script>
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const action = this.textContent.trim().toLowerCase();
                if (action.includes('deposit')) {
                    new bootstrap.Modal(document.getElementById('depositModal')).show();
                } else if (action.includes('withdraw')) {
                    new bootstrap.Modal(document.getElementById('withdrawModal')).show();
                } else if (action.includes('transfer')) {
                    new bootstrap.Modal(document.getElementById('transferModal')).show();
                } else if (action.includes('activate savings')) {
                    new bootstrap.Modal(document.getElementById('unlockSavingsModal')).show();
                }
            });
        });

        const notyf = new Notyf({
            duration: 3000,
            position: {
                x: 'right',
                y: 'top'
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const path = window.location.pathname;
            const navLinks = document.querySelectorAll('.bottom-nav a');

            // Handle active states
            navLinks.forEach(link => {
                if (link.getAttribute('href') === path) {
                    link.classList.add('active');
                }
            });

            // More menu functionality
            const moreMenuBtn = document.getElementById('moreMenuBtn');
            const moreMenu = document.querySelector('.more-menu');
            const backdrop = document.querySelector('.more-menu-backdrop');

            function toggleMoreMenu() {
                moreMenu.classList.toggle('show');
                backdrop.classList.toggle('show');
            }

            moreMenuBtn.addEventListener('click', function(e) {
                e.preventDefault();
                toggleMoreMenu();
            });

            backdrop.addEventListener('click', toggleMoreMenu);

            // Close more menu when clicking a menu item
            const moreMenuItems = document.querySelectorAll('.more-menu-item');
            moreMenuItems.forEach(item => {
                item.addEventListener('click', function() {
                    toggleMoreMenu();
                });
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
