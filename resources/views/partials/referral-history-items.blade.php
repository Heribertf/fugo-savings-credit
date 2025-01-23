@foreach ($referralHistory as $referral)
    <div class="history-item">
        <div>
            <div class="fw-bold">{{ $referral['name'] }}</div>
            <small class="text-muted">
                {{ \Carbon\Carbon::parse($referral['created_at'])->format('M d, Y') }} •
                <span class="{{ $referral['status'] == 'active' ? 'text-success' : 'text-warning' }}">
                    {{ ucfirst($referral['status']) }}
                </span>
            </small>
        </div>
        <div class="text-end">
            <div class="fw-bold">KSh {{ number_format($referral['referralBonus'], 2) }}</div>
            <span class="badge {{ $referral['type'] == 'direct' ? 'bg-success' : 'bg-info' }}">
                {{ ucfirst($referral['type']) }}
            </span>
        </div>
    </div>
@endforeach
