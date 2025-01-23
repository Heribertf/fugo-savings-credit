<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fas fa-wallet me-2"></i>FuGo</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="{{ route('dashboard') }}"><i
                            class="fas fa-home me-1"></i>
                        Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('loans') }}"><i
                            class="fas fa-hand-holding-usd me-1"></i>
                        Loans</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('referrals') }}"><i
                            class="fas fa-users me-1"></i> Referrals</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="{{ route('transactions') }}"><i
                            class="fas fa-history me-1"></i>
                        Transactions</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('savings') }}"><i class="fas fa-save me-1"></i>
                        Savings</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
