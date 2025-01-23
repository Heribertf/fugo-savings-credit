{{-- <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout> --}}


@extends('layouts.app')

@section('title', 'FuGo Savings & Credit Dashboard')

@push('styles')
    <style>
        :root {
            --primary-gradient: linear-gradient(45deg, #4158D0, #C850C0);
            --savings-gradient: linear-gradient(45deg, #0093E9, #80D0C7);
            --loan-gradient: linear-gradient(45deg, #00B4DB, #0083B0);
        }

        .balance-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        .finance-card {
            height: 100%;
            border: none;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .finance-card:hover {
            transform: translateY(-5px);
        }

        .balance-card {
            background: var(--primary-gradient);
        }

        .savings-card {
            background: var(--savings-gradient);
        }

        .loan-card {
            background: var(--loan-gradient);
        }

        .finance-card .card-body {
            color: white;
            padding: 1.5rem;
        }

        .quick-actions {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }

        .action-btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .activity-item {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            background: rgba(0, 0, 0, 0.02);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .finance-card {
                margin-bottom: 0.75rem;
            }

            .action-btn {
                padding: 0.75rem;
                font-size: 0.9rem;
            }

            .welcome-text {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">

        @if (session('status'))
            <script>
                const notyf = new Notyf({
                    duration: 3000,
                    position: {
                        x: 'right',
                        y: 'top'
                    }
                });
                notyf.success("{{ session('status') }}");
            </script>
        @elseif (session('success'))
            <script>
                const notyf = new Notyf({
                    duration: 3000,
                    position: {
                        x: 'right',
                        y: 'top'
                    }
                });
                notyf.success("{{ session('success') }}");
            </script>
        @elseif (session('error'))
            <script>
                const notyf = new Notyf({
                    duration: 3000,
                    position: {
                        x: 'center',
                        y: 'top'
                    }
                });
                notyf.error("{{ session('error') }}");
            </script>
        @endif

        <!-- Welcome Section -->
        <h2 class="welcome-text mb-4">
            <i class="fas fa-user-circle me-2"></i>Hi, {{ Auth::user()->username }}
        </h2>

        <!-- Balance Cards -->
        <div class="balance-grid mb-4">
            <div class="card finance-card balance-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75">Available Balance</h6>
                    <h3 class="card-title mb-2">KSh {{ number_format($availableBalance, 2) }} </h3>
                    <p class="card-text opacity-75"><small>Instant withdrawal</small></p>
                </div>
            </div>

            <div class="card finance-card savings-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75">Total Savings</h6>
                    <h3 class="card-title mb-2">KSh {{ number_format($savingsBalance, 2) }}</h3>

                    @if (session()->has('savingsaccount'))
                        @if (session('savingsaccount.is_active') != 1)
                            <p class="card-text opacity-75 text-danger fw-bold"><small>Needs activation</small></p>
                        @endif
                    @endif
                </div>
            </div>

            <div class="card finance-card loan-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75">Loan Limit</h6>
                    <h3 class="card-title mb-2">KSh {{ number_format($loanEligibility, 2) }}</h3>
                    <p class="card-text opacity-75"><small>90% of savings</small></p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Quick Actions</h5>
                <div class="quick-actions">
                    <button class="action-btn btn-primary">
                        <i class="fas fa-arrow-down"></i>Deposit
                    </button>

                    @if (session()->has('savingsaccount'))
                        @if (session('savingsaccount.is_active') != 1)
                            <button class="action-btn btn-info text-white">
                                <i class="fas fa-lock"></i>Activate Savings
                            </button>
                        @endif
                    @endif

                    <button class="action-btn btn-success">
                        <i class="fas fa-money-bill-wave"></i>Withdraw
                    </button>
                    <a href="{{ route('loans') }}" class="action-btn btn-warning ">
                        <i class="fas fa-hand-holding-usd"></i>Loan
                    </a>
                    {{-- <button class="action-btn btn-warning">
                        <i class="fas fa-user-plus"></i>Refer
                    </button> --}}
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Recent Activities</h5>
                <div class="activities">
                    @forelse($recentTransactions as $transaction)
                        <div class="activity-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        {{ $transaction->description }}
                                    </h6>
                                    <small class="text-muted">{{ $transaction->created_at->diffForHumans() }}</small>
                                </div>
                                <span
                                    class="{{ in_array($transaction->type, ['transfer', 'allocation'])
                                        ? 'text-secondary'
                                        : (in_array($transaction->type, ['deposit', 'activation', 'referral_bonus', 'interest_bonus', 'loan_disbursement'])
                                            ? 'text-success'
                                            : 'text-danger') }}
                                    ">
                                    {{ in_array($transaction->type, ['transfer', 'allocation'])
                                        ? '↔'
                                        : ($transaction->type === 'withdrawal' || $transaction->type === 'fee' || $transaction->type === 'loan_repayment'
                                            ? '-'
                                            : '+') }}
                                    KSh {{ number_format(abs($transaction->amount), 2) }}

                                    <span
                                        class="badge rounded-pill bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'failed' ? 'danger' : 'secondary') }}">{{ ucwords(str_replace('_', ' ', $transaction->status)) }}</span>
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No recent activities found.</p>
                    @endforelse
                </div>
            </div>
        </div>


    </div>

    @include('layouts.partials.modals')

@endsection


@push('scripts')
    <script src="{{ asset('assets/js/mpesa-deposit.js') }}"></script>

    <script></script>
@endpush
