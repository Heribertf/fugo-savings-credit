@extends('layouts.app')

@section('title', 'FuGo - Savings Management')

@push('styles')
    <style>
        :root {
            --primary-gradient: linear-gradient(45deg, #0093E9, #80D0C7);
        }

        body {
            background-color: #f8f9fa;
        }

        .app-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 1rem;
        }

        .savings-card {
            background: var(--primary-gradient);
            color: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 147, 233, 0.2);
        }

        .goal-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .savings-amount {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .progress-light {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .progress-bar {
            background-color: rgba(255, 255, 255, 0.9);
        }

        .progress-bar-blue {
            background-color: var(--bs-primary);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .action-card {
            background: white;
            border-radius: 15px;
            padding: 1.25rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .action-card:active {
            transform: scale(0.98);
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0.5rem 0;
        }

        .transactions-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .transaction-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .transaction-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        .deposit-icon {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        .default-icon {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }


        .withdrawal-icon {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .neutral-icon {}

        .modal-content {
            border-radius: 20px;
        }

        .floating-button {
            position: fixed;
            bottom: 80px;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 30px;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 147, 233, 0.3);
            border: none;
            font-size: 1.5rem;
        }
    </style>
@endpush

@section('content')
    @if (session()->has('savingsaccount'))
        @if (session('savingsaccount.is_active') == 1)
            <div class="container py-4">

                <div class="savings-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="mb-1">Total Savings</h6>
                            <div class="savings-amount">KSh {{ number_format($savings->balance, 2) }}</div>
                            <small class="text-white-50">Available for allocation to goals: <strong>KSh
                                    {{ $savings->unallocated_funds }}</strong></small>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-1">Lock Period</h6>
                            <div class="h4 mb-0">{{ $lockDaysRemaining }}</div>
                            <small class="text-white-50">days remaining</small>
                        </div>
                    </div>
                </div>

                @if (session('savingsaccount.is_locked') == 1)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Savings Goals</h6>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                                <i class="fas fa-plus me-1"></i> New Goal
                            </button>
                        </div>

                        <!-- Individual Goal Cards -->
                        @forelse($goals as $goal)
                            <div class="goal-card">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="mb-1">{{ $goal->goal_name }}</h6>
                                        <small class="text-muted">Target: KSh {{ number_format($goal->target_amount, 2) }}
                                            by
                                            {{ $goal->target_date->format('M d, Y') }}</small>
                                    </div>
                                    <button class="btn btn-link text-muted p-0" data-bs-toggle="modal"
                                        data-bs-target="#updateGoalModal"
                                        onclick="prepareUpdateGoal('{{ $goal->id }}', '{{ $goal->target_amount }}', '{{ $goal->target_date->format('Y-m-d') }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <small>Progress</small>
                                        <small>{{ number_format($goal->progress, 1) }}%</small>
                                    </div>
                                    <div class="progress progress-light">
                                        <div class="progress-bar progress-bar-blue" style="width: {{ $goal->progress }}%">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Allocated: KSh
                                        {{ number_format($goal->allocations_sum_amount ?? 0, 2) }}</small>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#allocateModal"
                                        onclick="prepareAllocation('{{ $goal->id }}', '{{ $goal->goal_name }}', {{ $savings->unallocated_funds ?? 0 }})">
                                        Allocate Funds
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <p>No savings goals yet. Create your first goal to get started!</p>
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="text-center py-5">
                        <h2>Your savings account is not locked</h2>
                        <p class="text-muted">Set a lock period for your savings account to deposit and manage your goals.
                        </p>

                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#setLockPeriodModal"
                            onclick="prepareSetLock('{{ $savings->id }}')">
                            Set Lock Period Now
                        </button>

                    </div>
                @endif

                <div class="transactions-card">
                    <h6 class="mb-4">Recent Transactions</h6>

                    @forelse($transactions as $transaction)
                        <div class="transaction-item">
                            <div
                                class="transaction-icon
                                {{ $transaction->type === 'deposit'
                                    ? 'deposit-icon'
                                    : ($transaction->type === 'withdrawal'
                                        ? 'withdrawal-icon'
                                        : 'default-icon') }}
                                ">
                                <i
                                    class="fas fa-{{ $transaction->type === 'deposit'
                                        ? 'arrow-up'
                                        : ($transaction->type === 'withdrawal'
                                            ? 'arrow-down'
                                            : ($transaction->type === 'transfer'
                                                ? 'right-left'
                                                : 'circle-dot')) }}
                                    "></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div>{{ $transaction->description }}</div>
                                        <small class="text-muted">{{ $transaction->created_at->format('M d, Y') }}</small>
                                    </div>
                                    <div
                                        class="text-{{ $transaction->type === 'deposit' ? 'success' : ($transaction->type === 'withdrawal' ? 'danger' : 'secondary') }}">
                                        {{ $transaction->type === 'deposit' ? '+' : ($transaction->type === 'withdrawal' ? '-' : '') }}
                                        KSh
                                        {{ number_format($transaction->amount, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">
                            <p>No transactions yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            @if (session('savingsaccount.is_locked') == 1)
                <button class="floating-button" data-bs-toggle="modal" data-bs-target="#addSavingsModal">
                    <i class="fas fa-plus"></i>
                </button>
            @endif
        @else
            <div class="text-center py-5">
                <h2>Your savings account is inactive</h2>
                <p class="text-muted">Activate your savings account to start managing your goals and transactions.</p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Activate Now</a>
            </div>
        @endif
    @endif



    <!-- Set Lock Period -->
    <div class="modal fade" id="setLockPeriodModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Set Lock Period</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST" id="setLockPeriodForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="lockPeriod">Lock Period (Months)</label>
                            <input type="number" class="form-control" id="lockPeriod" name="lock_period" min="3"
                                required>
                            <small class="form-text text-muted">The lock period must be at least 3 months.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Saving Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">KSh</span>
                                <input type="number" class="form-control form-control-lg" name="saving_amount" required
                                    min="1000">
                            </div>
                            <small class="text-muted">Minimum deposit: KSh 1000</small>
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
                        <button type="submit" class="btn btn-primary w-100">Set lock period and proceed to
                            deposit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Goal -->
    <div class="modal fade" id="addGoalModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Create New Savings Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('savings.goals.store') }}" method="POST" id="addGoalForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Goal Name</label>
                            <input type="text" class="form-control" name="goal_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Target Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">KSh</span>
                                <input type="number" class="form-control" name="target_amount" required min="1000">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Target Date</label>
                            <input type="date" class="form-control" name="target_date" required
                                min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Create Goal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Allocate Funds -->
    <div class="modal fade" id="allocateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="allocateModalTitle">Allocate Funds to Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="allocationForm" action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Amount to Allocate</label>
                            <div class="input-group">
                                <span class="input-group-text">KSh</span>
                                <input type="number" class="form-control" name="allocation_amount"
                                    max="{{ $savings->unallocated_funds }}" required>
                            </div>
                            <small class="text-muted">Available: KSh <span
                                    id="availableAmount">{{ number_format($savings->unallocated, 2) }}</span></small>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Allocate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Savings Modal -->
    <div class="modal fade" id="addSavingsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Add Funds to Savings Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('savings.store') }}" method="POST" id="addSavingFundsForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">KSh</span>
                                <input type="number" class="form-control form-control-lg" name="amount" required
                                    min="100">
                            </div>
                            <small class="text-muted">Minimum deposit: KSh 100</small>
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

                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <input type="text" class="form-control" name="description"
                                placeholder="What's this savings for?">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary w-100">Proceed to payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Goal Modal -->
    <div class="modal fade" id="updateGoalModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Update Savings Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="updateGoalForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label">Target Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">KSh</span>
                                <input type="number" class="form-control form-control-lg" name="target_amount" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Target Date</label>
                            <input type="date" class="form-control form-control-lg" name="target_date" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script>
        var loader = document.getElementById("loader");

        function prepareSetLock(savingId) {
            const form = document.getElementById('setLockPeriodForm');
            form.action = `/savings/${savingId}/lock`;
        }

        function prepareUpdateGoal(goalId, targetAmount, targetDate) {
            const form = document.getElementById('updateGoalForm');
            form.action = `/savings/goals/${goalId}`;
            form.querySelector('[name="target_amount"]').value = targetAmount;
            form.querySelector('[name="target_date"]').value = targetDate;
        }

        function prepareAllocation(goalId, goalName, availableAmount) {
            const form = document.getElementById('allocationForm');
            form.action = `/savings/goals/${goalId}/allocate`;
            document.getElementById('allocateModalTitle').textContent = "Allocate Funds to " + goalName;
            document.getElementById('availableAmount').textContent = availableAmount.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        document.getElementById('setLockPeriodForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const actionUrl = form.action;

            loader.style.display = "block";

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                    },
                    body: formData,
                });

                loader.style.display = "none";

                const result = await response.json();

                if (result.success) {
                    notyf.success(result.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else {
                    notyf.error(result.message || 'An error occurred.');
                }
            } catch (error) {
                loader.style.display = "none";
                notyf.error('An error occurred. Please try again.');
            }
        });


        document.getElementById('addSavingFundsForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const form = e.target;

            const formData = new FormData(form);
            loader.style.display = "block";
            try {
                const response = await fetch("{{ route('savings.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: formData,
                });

                loader.style.display = "none";

                const result = await response.json();

                if (result.success) {
                    notyf.success("Deposit initiated successfully");
                    setTimeout(() => {
                        new bootstrap.Modal(document.getElementById('addSavingsModal')).hide();
                        window.location.reload();
                    }, 3000);
                } else {
                    notyf.error(result.message || 'An error occurred.');
                }
            } catch (err) {
                loader.style.display = "none";

                notyf.error('An error occurred. Please try again.');
            }
        });

        document.getElementById('addGoalForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const form = e.target;

            const formData = new FormData(form);

            loader.style.display = "block";
            try {
                const response = await fetch("{{ route('savings.goals.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: formData,
                });

                loader.style.display = "none";

                const result = await response.json();

                if (result.success) {
                    notyf.success(result.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else {
                    notyf.error(result.message || 'An error occurred.');
                }
            } catch (err) {
                loader.style.display = "none";

                notyf.error('An error occurred. Please try again.');
                console.error(err);
            }
        });

        document.getElementById('updateGoalForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const form = e.target;

            const formData = new FormData(form);
            const actionUrl = form.action;

            loader.style.display = "block";
            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: formData,
                });

                loader.style.display = "none";

                const result = await response.json();

                if (result.success) {
                    notyf.success(result.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else {
                    notyf.error(result.message || 'An error occurred.');
                }
            } catch (err) {
                loader.style.display = "none";

                notyf.error('An error occurred. Please try again.');
                console.error(err);
            }
        });


        document.getElementById('allocationForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const form = e.target;

            const formData = new FormData(form);
            const actionUrl = form.action;

            loader.style.display = "block";
            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: formData,
                });

                loader.style.display = "none";

                const result = await response.json();

                if (result.success) {
                    notyf.success(result.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else {
                    notyf.error(result.message || 'An error occurred.');
                }
            } catch (err) {
                loader.style.display = "none";

                notyf.error('An error occurred. Please try again.');
                console.error(err);
            }
        });

        @if (session('error'))
            alert("{{ session('error') }}");
        @endif

        @if (session('success'))
            alert("{{ session('success') }}");
        @endif
    </script>
@endpush
