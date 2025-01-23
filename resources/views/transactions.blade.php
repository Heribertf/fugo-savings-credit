@extends('layouts.app')

@section('title', 'FuGo - Transaction History')

@push('styles')
    <style>
        :root {
            --primary-color: #0d6efd;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        .stats-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            padding: 0.5rem;
        }

        .stats-scroll::-webkit-scrollbar {
            display: none;
        }

        .stat-card {
            min-width: 160px;
            border-radius: 16px;
            border: none;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .filter-button {
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--primary-color);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
        }

        .transaction-card {
            border-radius: 12px;
            border: none;
            margin-bottom: 0.75rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .filter-modal {
            border-radius: 20px 20px 0 0;
        }

        .transaction-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .deposit {
            background-color: rgba(25, 135, 84, 0.1);
            color: var(--success-color);
        }

        .withdrawal {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }

        .loan {
            background-color: rgba(13, 110, 253, 0.1);
            color: var(--primary-color);
        }

        .referral {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
        }

        #sentinel {
            height: 1px;
            margin: 20px 0;
        }

        .loading-spinner {
            text-align: center;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
@endpush

@section('content')
    <div class="container px-3 py-4">
        <div class="stats-scroll mb-4">
            <div class="d-flex gap-3">
                <div class="stat-card p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-arrow-down text-success me-2"></i>
                        <span class="text-muted small">Deposits</span>
                    </div>
                    <h5 class="mb-0">KSh {{ number_format($totalDeposits) }}</h5>
                </div>
                <div class="stat-card p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-arrow-up text-danger me-2"></i>
                        <span class="text-muted small">Withdrawals</span>
                    </div>
                    <h5 class="mb-0">KSh {{ number_format(abs($totalWithdrawals)) }}</h5>
                </div>
                <div class="stat-card p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-coins text-warning me-2"></i>
                        <span class="text-muted small">Earnings</span>
                    </div>
                    <h5 class="mb-0">KSh {{ number_format($totalEarnings) }}</h5>
                </div>
            </div>
        </div>

        <h6 class="mb-3">Transaction History</h6>
        <div id="transactions-container">
            @foreach ($transactions as $transaction)
                <div class="transaction-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="transaction-icon {{ $transaction->type }} me-3">
                                @switch($transaction->type)
                                    @case('deposit')
                                        <i class="fas fa-arrow-down"></i>
                                    @break

                                    @case('withdrawal')
                                        <i class="fas fa-arrow-up"></i>
                                    @break

                                    @case('loan')
                                        <i class="fas fa-hand-holding-dollar"></i>
                                    @break

                                    @case('referral_bonus')
                                        <i class="fas fa-gift"></i>
                                    @break

                                    @default
                                        <i class="fas fa-exchange-alt"></i>
                                @endswitch
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0">{{ $transaction->description }}</h6>
                                    <span
                                        class="{{ $transaction->type === 'deposit' ? 'text-success' : ($transaction->type === 'withdrawal' ? 'text-danger' : 'text-secondary') }}">
                                        {{ $transaction->type === 'deposit' ? '+' : ($transaction->type === 'withdrawal' ? '-' : '') }}
                                        KSh
                                        {{ number_format($transaction->amount, 2) }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        {{ $transaction->created_at->format('M d, Y') }} •
                                        {{ $transaction->transaction_id }}
                                    </small>
                                    <span
                                        class="status-badge bg-{{ $transaction->status === 'completed' ? 'success' : (in_array($transaction->status, ['pending', 'failed']) ? 'warning' : 'secondary') }} bg-opacity-10 text-{{ $transaction->status === 'completed' ? 'success' : (in_array($transaction->status, ['pending', 'failed']) ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="loading-spinner" id="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Filter FAB Button -->
        <button class="filter-button" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fas fa-filter text-white"></i>
        </button>

        <!-- Filter Modal -->
        <div class="modal fade" id="filterModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content filter-modal">
                    <div class="modal-header">
                        <h5 class="modal-title">Filter Transactions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="filterForm">
                            <div class="mb-3">
                                <label class="form-label">Transaction Type</label>
                                <select class="form-select" name="type">
                                    <option value="">All Types</option>
                                    <option value="deposit">Deposits</option>
                                    <option value="withdrawal">Withdrawals</option>
                                    <option value="loan">Loans</option>
                                    <option value="referral_bonus">Referrals</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date Range</label>
                                <select class="form-select" name="date_range">
                                    <option value="">All time</option>
                                    <option value="7">Last 7 days</option>
                                    <option value="30">Last 30 days</option>
                                    <option value="90">Last 90 days</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Amount Range</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="min_amount" placeholder="Min">
                                    <span class="input-group-text">to</span>
                                    <input type="number" class="form-control" name="max_amount" placeholder="Max">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentPage = 1;
        let isLoading = false;
        let hasMorePages = true;
        let currentFilters = {};

        function debug(message) {
            console.log(`[Infinite Scroll Debug] ${message}`);
        }

        async function loadMoreTransactions() {
            if (isLoading || !hasMorePages) {
                // debug('Skipping load - ' + (isLoading ? 'Already loading' : 'No more pages'));
                return;
            }

            // debug(`Loading page ${currentPage + 1}`);
            isLoading = true;
            document.getElementById('loading-spinner').style.display = 'block';

            const queryParams = new URLSearchParams({
                page: currentPage + 1,
                ...currentFilters
            });

            try {
                const response = await fetch(`/transactions?${queryParams.toString()}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                // debug(`Received ${data.data?.length || 0} transactions`);

                if (data.data && data.data.length > 0) {
                    const container = document.getElementById('transactions-container');
                    data.data.forEach(transaction => {
                        container.insertAdjacentHTML('beforeend', createTransactionCard(transaction));
                    });
                    currentPage = data.current_page;
                    hasMorePages = data.current_page < data.last_page;
                    // debug(`Updated page to ${currentPage}, has more pages: ${hasMorePages}`);
                } else {
                    hasMorePages = false;
                    // debug('No more transactions received');
                }
            } catch (error) {
                console.error('Error loading transactions:', error);

                notyf.error('Failed to load transactions. Please try again.');

            } finally {
                isLoading = false;
                document.getElementById('loading-spinner').style.display = 'none';
            }
        }

        function createTransactionCard(transaction) {
            const iconClass = getTransactionIconClass(transaction.type);
            const icon = getTransactionIcon(transaction.type);
            const typeClass = transaction.type == 'deposit' ? 'text-success' : (transaction.type == 'withdrawal' ?
                'text-danger' : 'text-secondary');
            const statusClass = transaction.status === 'completed' ? 'success' : (['pending', 'failed'].includes(transaction
                .status) ? 'warning' : 'secondary');
            const formattedDate = new Date(transaction.created_at).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });

            return `
                <div class="transaction-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="transaction-icon ${iconClass} me-3">
                                <i class="fas ${icon}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0">${transaction.description}</h6>
                                    <span class="${typeClass}">
                                        ${transaction.type == 'deposit' ? '+' : (transaction.type == 'withdrawal' ? '-':'')} KSh ${Number(transaction.amount).toFixed(2).toLocaleString()}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        ${formattedDate} • ${transaction.transaction_id}
                                    </small>
                                    <span class="status-badge bg-${statusClass} bg-opacity-10 text-${statusClass}">
                                        ${transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1)}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function getTransactionIconClass(type) {
            const classes = {
                deposit: 'deposit',
                withdrawal: 'withdrawal',
                loan_repayment: 'loan',
                referral_bonus: 'referral'
            };
            return classes[type] || '';
        }

        function getTransactionIcon(type) {
            const icons = {
                deposit: 'fa-arrow-down',
                withdrawal: 'fa-arrow-up',
                loan_repayment: 'fa-hand-holding-dollar',
                referral_bonus: 'fa-gift',
                activation: 'fa-dollar-sign',
                fee: 'fa-dollar-sign'
            };
            return icons[type] || 'fa-exchange-alt';
        }

        // Infinite scroll implementation
        function setupInfiniteScroll() {
            // debug('Setting up infinite scroll');

            // Create and append a sentinel element
            const sentinel = document.createElement('div');
            sentinel.id = 'sentinel';
            sentinel.style.height = '1px'; // Make it almost invisible
            document.getElementById('transactions-container').after(sentinel);

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // debug('Sentinel is visible, loading more transactions');
                        loadMoreTransactions();
                    }
                });
            }, {
                rootMargin: '100px', // Start loading before reaching the end
                threshold: 0.5 // Trigger when even 10% is visible
            });

            observer.observe(sentinel);
            // debug('Observer set up and watching sentinel');
        }

        document.getElementById('filterForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            currentFilters = Object.fromEntries(formData.entries());

            currentPage = 0;
            hasMorePages = true;

            const container = document.getElementById('transactions-container');
            container.innerHTML = '';

            await loadMoreTransactions();

            const modalElement = document.getElementById('filterModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        });

        // Initialize everything when the DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            setupInfiniteScroll();
        });
    </script>
@endpush
