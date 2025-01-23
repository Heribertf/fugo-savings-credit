@extends('layouts.app')
@section('title', 'FuGo - My Wallets')

@push('styles')
    <style>
        .wallet-card {
            border: none;
            border-radius: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .main-wallet {
            background: linear-gradient(45deg, #2193b0, #6dd5ed);
            color: white;
        }

        .savings-wallet {
            background: linear-gradient(45deg, #11998e, #38ef7d);
            color: white;
        }

        .wallet-balance {
            font-size: 1.8rem;
            font-weight: 700px;
            margin: 1rem 0;
        }

        .action-btn {
            border-radius: 50px;
            padding: 0.8rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .transaction-card {
            border-radius: 5px;
        }

        .transaction-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .transaction-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .transaction-item:last-child {
            border-bottom: none;
        }

        .transaction-details {
            flex: 1;
        }

        .transaction-amount {
            text-align: right;
            font-weight: bold;
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <!-- Wallet Cards -->
        <div class="wallet-card main-wallet">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Main Wallet</h5>
                    <i class="fas fa-wallet fa-lg"></i>
                </div>
                <div class="wallet-balance">KSh {{ number_format($mainWalletBal, 2) }} </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-light action-btn flex-grow-1">
                        <i class="fas fa-plus-circle"></i>
                        Deposit
                    </button>
                    <button class="btn btn-light action-btn flex-grow-1">
                        <i class="fas fa-minus-circle"></i>
                        Withdraw
                    </button>
                </div>
            </div>
        </div>

        <div class="wallet-card savings-wallet">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Savings Wallet</h5>
                    <i class="fas fa-piggy-bank fa-lg"></i>
                </div>
                <div class="wallet-balance">KSh {{ number_format($savingsWalletBal, 2) }}</div>
                <div class="d-flex gap-2">
                    <a href="{{ route('savings') }}" class="btn btn-light action-btn flex-grow-1">
                        <i class="fas fa-chart-line"></i>
                        Manage
                    </a>
                    <button class="btn btn-light action-btn flex-grow-1">
                        <i class="fas fa-exchange-alt"></i>
                        Transfer
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card mt-4 transaction-card">
            <div class="card-body">
                <h6 class="mb-3">Recent Transactions</h6>
                <ul class="transaction-list">
                    @forelse($recentTransactions as $transaction)
                        <li class="transaction-item">
                            <div class="transaction-details">
                                <div class="fw-bold">{{ $transaction->description }}
                                    {{-- ({{ ucwords(str_replace('_', ' ', $transaction->wallet_type == 'available' ? 'Main' : $transaction->wallet_type)) }}) --}}
                                </div>
                                <small
                                    class="text-muted">{{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y') }}</small>
                            </div>
                            <div
                                class="transaction-amount {{ in_array($transaction->type, ['deposit', 'referral_bonus', 'interest_bonus', 'loan_disbursement']) ? 'text-success' : (in_array($transaction->type, ['transfer', 'allocation']) ? 'text-secodary' : 'text-danger') }}">
                                {{ $transaction->type === 'withdrawal' || $transaction->type === 'fee' || $transaction->type === 'loan_repayment' ? '-' : '+' }}
                                KSh
                                {{ number_format(abs($transaction->amount), 2) }}
                                <span
                                    class="badge rounded-pill bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'failed' ? 'danger' : 'secondary') }}">{{ ucwords(str_replace('_', ' ', $transaction->status)) }}</span>
                            </div>
                        </li>
                    @empty
                        <p class="text-muted">No recent activities found.</p>
                    @endforelse

                </ul>
            </div>
        </div>
    </div>
    @include('layouts.partials.modals')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/mpesa-deposit.js') }}"></script>

    <script></script>
@endpush
