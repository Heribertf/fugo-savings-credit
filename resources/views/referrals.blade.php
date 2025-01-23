@extends('layouts.app')

@section('title', 'FuGo Savings & Credit Referral')

@push('styles')
    <style>
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
        }

        .referral-code-card {
            background: linear-gradient(45deg, #FF416C, #FF4B2B);
            color: white;
        }

        .referral-code-input {
            background: rgba(255, 255, 255, 0.2) !important;
            border: none;
            color: white;
            font-size: 1.2rem;
            text-align: center;
            letter-spacing: 2px;
        }

        .referral-code-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .stats-card {
            text-align: center;
            padding: 1rem;
            transition: transform 0.2s;
        }

        .stats-card:active {
            transform: scale(0.98);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }

        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .history-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .history-item:last-child {
            border-bottom: none;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
        }

        .share-button {
            border-radius: 50px;
            padding: 0.8rem 2rem;
            font-weight: 500;
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <!-- Referral Code Section -->
        <div class="card referral-code-card mb-4">
            <div class="card-body text-center">
                <h6 class="mb-3">Your Referral Code</h6>
                <div class="input-group mb-3">
                    <input type="text" class="form-control referral-code-input" value="{{ $referralCode }}" id="referralCode"
                        readonly>
                    <button class="btn btn-light" type="button" onclick="copyReferralCode()">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <button class="btn btn-light w-100 share-button" onclick="shareReferralCode()">
                    <i class="fas fa-share-alt"></i>
                    Share Code
                </button>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="card stats-card">
                    <div class="stats-number text-primary">KSh {{ number_format($directEarnings, 2) }}</div>
                    <div class="stats-label">Direct Earnings</div>
                </div>
            </div>
            <div class="col-6">
                <div class="card stats-card">
                    <div class="stats-number text-success">KSh {{ number_format($indirectEarnings, 2) }}</div>
                    <div class="stats-label">Indirect Earnings</div>
                </div>
            </div>
        </div>

        <!-- Network Stats -->
        <div class="card mb-4">
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-4 text-center p-3">
                        <div class="stats-number">{{ $totalReferrals }}</div>
                        <div class="stats-label">Total</div>
                    </div>
                    <div class="col-4 text-center p-3" style="border-left: 1px solid #eee; border-right: 1px solid #eee;">
                        <div class="stats-number">{{ $activeReferrals }}</div>
                        <div class="stats-label">Active</div>
                    </div>
                    <div class="col-4 text-center p-3">
                        <div class="stats-number">{{ $pendingReferrals }}</div>
                        <div class="stats-label">Pending</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent History -->
        <h6 class="mb-3">Referral History</h6>
        <div class="card">
            <div id="referralHistoryContainer">
                @include('partials.referral-history-items', ['referralHistory' => $referralHistory])
            </div>
            <div id="loadingSpinner" class="text-center p-3 d-none">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            @if (empty($referralHistory))
                <p class="text-muted p-3">No referrals found.</p>
            @endif
        </div>

    </div>

    <!-- Share Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Share Your Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <button class="btn btn-success w-100 share-button mb-2" onclick="shareWhatsApp()">
                        <i class="fab fa-whatsapp"></i>
                        Share via WhatsApp
                    </button>
                    <button class="btn btn-info text-white w-100 share-button mb-2" onclick="shareTwitter()">
                        <i class="fab fa-twitter"></i>
                        Share via Twitter
                    </button>
                    <button class="btn btn-primary w-100 share-button" onclick="shareFacebook()">
                        <i class="fab fa-facebook"></i>
                        Share via Facebook
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let page = 1;
        let loading = false;
        let hasMore = true;

        function isInViewport(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }

        function loadMoreReferrals() {
            if (loading || !hasMore) return;

            loading = true;
            const spinner = document.getElementById('loadingSpinner');
            spinner.classList.remove('d-none');

            fetch(`/referrals/load-more?page=${page}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('referralHistoryContainer');
                    container.insertAdjacentHTML('beforeend', data.html);
                    hasMore = data.hasMore;
                    page++;
                    loading = false;
                    spinner.classList.add('d-none');
                })
                .catch(error => {
                    console.error('Error:', error);
                    loading = false;
                    spinner.classList.add('d-none');
                });
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    loadMoreReferrals();
                }
            });
        }, {
            threshold: 0.5
        });

        const spinner = document.getElementById('loadingSpinner');
        if (spinner) {
            observer.observe(spinner);
        }

        function copyReferralCode() {
            const codeInput = document.getElementById('referralCode');
            navigator.clipboard.writeText(codeInput.value);
            notyf.success('Referral code copied');
        }

        function shareReferralCode() {
            const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
            shareModal.show();
        }

        function shareWhatsApp() {
            const text = encodeURIComponent(
                `Join me on FuGo! Use my referral code: ${document.getElementById('referralCode').value} and get a welcome bonus! Sign up now: https://fugo.app/register`
            );
            window.open(`https://wa.me/?text=${text}`, '_blank');
        }

        function shareTwitter() {
            const text = encodeURIComponent(
                `Join me on FuGo! Use my referral code and get a welcome bonus! Sign up now: https://fugo.app/register Code: ${document.getElementById('referralCode').value}`
            );
            window.open(`https://twitter.com/intent/tweet?text=${text}`, '_blank');
        }

        function shareFacebook() {
            const url = encodeURIComponent('https://fugo.app/register');
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
        }
    </script>
@endpush
