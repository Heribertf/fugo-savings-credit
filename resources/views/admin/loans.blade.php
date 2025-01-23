@extends('layouts.admin')

@section('title', 'Loans')

@push('styles')
    <style>

    </style>
@endpush

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Loan Applications</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Loans</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search loans...">
                    <button class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                {{-- <button class="btn btn-success">
                    <i class="fas fa-download me-1"></i> Export
                </button> --}}
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Loans</h6>
                        <h3 class="mb-0">{{ $totalLoans }}</h3>
                        <div class="text-success small mt-1">
                            <i
                                class="fas fa-arrow-up me-1"></i>{{ round(($totalLoans / max(1, $totalLoans - $approvedToday)) * 100 - 100, 2) }}%
                            increase
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Pending Approval</h6>
                        <h3 class="mb-0">{{ $pendingLoans }}</h3>
                        <div class="text-warning small mt-1">
                            <i class="fas fa-clock me-1"></i>Awaiting Review
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Approved Today</h6>
                        <h3 class="mb-0">{{ $approvedToday }}</h3>
                        <div class="text-success small mt-1">
                            <i class="fas fa-check-circle me-1"></i>Processed
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Amount</h6>
                        <h3 class="mb-0">KSh {{ number_format($totalAmount, 2) }}</h3>
                        <div class="text-info small mt-1">
                            <i class="fas fa-chart-line me-1"></i>Monthly Volume
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">User</th>
                            <th scope="col">Phone No</th>
                            <th scope="col">Savings (KSh)</th>
                            <th scope="col">Amount (KSh)</th>
                            <th scope="col">Interest (KSh)</th>
                            <th scope="col">Disbursable (KSh)</th>
                            <th scope="col">Status</th>
                            <th scope="col">Requested At</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $i = 1;
                        @endphp
                        @foreach ($loans as $loan)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $loan->user->fullname }}</td>
                                <td>{{ $loan->user->phone_number }}</td>
                                <td>{{ $loan->user->total_savings }}</td>
                                <td>{{ number_format($loan->amount, 2) }}</td>
                                <td>{{ number_format($loan->interest, 2) }}</td>
                                <td>{{ number_format($loan->amount - $loan->interest, 2) }}</td>
                                <td>
                                    <span
                                        class="status-badge {{ $loan->status === 'pending' ? 'status-pending' : (in_array($loan->status, ['approved', 'paid']) ? 'status-approved' : 'status-rejected') }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </td>
                                <td>{{ $loan->created_at->format('d M Y, h:i A') }}</td>
                                <td class="action-buttons">

                                    @if ($loan->status === 'pending')
                                        <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#approveModal{{ $loan->id }}">
                                            <i class="fas fa-check me-1"></i>Approve
                                        </button>
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#rejectModal{{ $loan->id }}">
                                            <i class="fas fa-times me-1"></i>Reject
                                        </button>
                                    @endif
                                </td>
                            </tr>


                            <!-- Approve Modal -->
                            <div class="modal fade" id="approveModal{{ $loan->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.loans.approve', $loan->id) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirm Loan Disbursement</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Confirm disbursement of KSh
                                                    {{ number_format($loan->amount - $loan->interest, 2) }}</p>
                                                <div class="mb-3">
                                                    <label for="reference" class="form-label">Reference Code</label>
                                                    <input type="text" name="reference" id="reference"
                                                        class="form-control" required placeholder="Enter reference code">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Confirm</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $loan->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.loans.reject', $loan->id) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Loan</h5>
                                                <button type="button" class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to reject this loan request?</p>
                                                <div class="mb-3">
                                                    <label for="rejectReason" class="form-label">Reason for
                                                        Rejection</label>
                                                    <textarea class="form-control" id="rejectReason" rows="3" placeholder="Enter reason for rejection"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Yes, Reject</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="mt-4">
                {{ $loans->links('pagination::bootstrap-5') }}
            </div>
        </div>

    </div>
@endsection
