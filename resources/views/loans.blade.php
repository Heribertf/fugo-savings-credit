@extends('layouts.app')

@section('title', 'FuGo - Loan Management')

@push('styles')
    <style>
        body {
            background-color: #F7FAFC;
            color: #2D3748;
        }

        .loan-card {
            background: linear-gradient(135deg, #00B4DB, #0083B0);
            border-radius: 20px;
            padding: 1.5rem;
            color: white;
            border: none;
            box-shadow: 0 10px 20px rgba(0, 131, 176, 0.15);
            margin-bottom: 1rem;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .progress-bar {
            background-color: white;
        }

        .action-button {
            background-color: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            transition: all 0.2s;
        }

        .action-button:hover {
            background-color: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .active-loan-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
        }

        .loan-history-card {
            background: white;
            border-radius: 16px;
            padding: 1rem;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 0.75rem;
        }

        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-badge.active {
            background-color: rgba(56, 161, 105, 0.1);
            color: #38A169;
        }

        .status-badge.paid {
            background-color: rgba(56, 161, 105, 0.1);
            color: #38A169;
        }

        .status-badge.defaulted {
            background-color: rgba(229, 62, 62, 0.1);
            color: #E53E3E;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .modal.fade .modal-dialog {
            transition: transform 0.2s ease-out;
        }

        @media (max-width: 576px) {
            .modal-dialog {
                margin: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <!-- Loan Eligibility Section -->
        <div class="loan-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h6 class="mb-1 text-white-50">Loan Limit</h6>
                    <h2 class="mb-0">KSh {{ number_format($savingsBalance, 2) }}</h2>
                </div>
                <button class="action-button" data-bs-toggle="modal" data-bs-target="#requestLoanModal"
                    {{ $activeLoan ? 'disabled' : '' }}>
                    <i class="fas fa-plus-circle me-2"></i>Request
                </button>
            </div>
            <div class="text-white-50 mb-2">Based on savings: KSh {{ number_format($savingsBalance, 2) }}</div>
            {{-- <div class="progress">
                <div class="progress-bar" style="width: {{ ($savingsBalance / ($savingsBalance ?: 1)) * 100 }}%"></div>
            </div> --}}
        </div>

        <!-- Active Loan Card -->
        @if ($activeLoan)
            <div class="active-loan-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="status-badge active">Active Loan</span>
                        <h3 class="mt-2 mb-0">KSh {{ number_format($activeLoan->amount, 2) }}</h3>
                    </div>
                    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal"
                        data-bs-target="#repayLoanModal">
                        <i class="fas fa-money-bill-wave me-2"></i>Repay
                    </button>
                </div>
                <div class="text-muted mb-2">Due: {{ \Carbon\Carbon::parse($activeLoan->due_date)->format('M d, Y') }}</div>
                <div class="progress">
                    <div class="progress-bar bg-primary"
                        style="width: {{ (\Carbon\Carbon::parse($activeLoan->due_date)->diffInDays(now()) / 30) * 100 }}%">
                    </div>
                </div>
            </div>
        @endif

        <!-- Loan History -->
        <h6 class="mb-3 mt-4">Loan History</h6>

        @forelse($loanHistory as $loan)
            <div class="loan-history-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-1">KSh {{ number_format($loan->amount, 2) }}</h6>
                        <small class="text-muted">{{ $loan->created_at->format('M d, Y') }}</small>
                    </div>
                    <span class="status-badge {{ $loan->status }}">{{ ucfirst($loan->status) }}</span>
                </div>
                <div class="d-flex justify-content-between text-muted small">
                    <span>Interest: KSh {{ number_format($loan->interest, 2) }}</span>
                    <span>Due: {{ \Carbon\Carbon::parse($loan->due_date)->format('M d, Y') }}</span>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">
                <p>No loan history found</p>
            </div>
        @endforelse

        {{-- {{ $loanHistory->links() }} --}}

        <!-- Request Loan Modal -->
        <div class="modal fade" id="requestLoanModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">Request Loan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="loanRequestForm" action="{{ route('loans.request') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-4">
                                <label class="form-label">Amount (Max: KSh {{ number_format($savingsBalance, 2) }})</label>
                                <div class="input-group">
                                    <span class="input-group-text">KSh</span>
                                    <input type="number" name="amount" class="form-control form-control-lg" min=""
                                        max="{{ $savingsBalance }}" required>
                                </div>
                            </div>
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="mb-3">Loan Terms</h6>
                                    <ul class="mb-0 ps-3">
                                        <li class="mb-2">10% interest deducted upfront</li>
                                        <li class="mb-2">50% interest back to savings on repayment</li>
                                        <li>30-day repayment period</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">Request Loan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Repay Loan Modal -->
        @if ($activeLoan)
            <div class="modal fade" id="repayLoanModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h5 class="modal-title">Repay Loan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="loanRepaymentForm" action="{{ route('loans.repay', ['loan' => $activeLoan->id]) }}"
                            method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-4">
                                    <label class="form-label">Outstanding Balance</label>
                                    <input type="text" class="form-control form-control-lg"
                                        value="KSh {{ number_format($activeLoan->amount, 2) }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KSh</span>
                                        <input type="number" name="amount" class="form-control form-control-lg"
                                            min="100" max="{{ $activeLoan->amount }}" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text">254</span>
                                        <input type="tel" name="mpesa_phone_number" maxlength="9"
                                            class="form-control form-control-lg" placeholder="700000000" required>
                                    </div>
                                    <small class="text-muted">Phone number to make payment</small>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary px-4">Pay Now</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        const loanRequestForm = document.getElementById('loanRequestForm');
        const loanRepaymentForm = document.getElementById('loanRepaymentForm');

        loanRequestForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loanRequestForm);

            try {
                const response = await fetch(loanRequestForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    notyf.success('Loan request submitted successfully');
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    notyf.error(result.message || 'Error processing loan request');
                }
            } catch (error) {
                notyf.error('An error occurred. Please try again.');
            }
        });

        loanRepaymentForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loanRepaymentForm);
            const actionUrl = loanRepaymentForm.action;

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    notyf.success('Payment processed successfully');
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    notyf.error(result.message || 'Error processing payment');
                }
            } catch (error) {
                notyf.error('An error occurred. Please try again.');
            }
        });
    </script>
@endpush
