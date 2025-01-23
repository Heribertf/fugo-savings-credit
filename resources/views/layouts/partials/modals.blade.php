<!-- M-Pesa Integration Modal -->
<div class="modal fade" id="mpesaModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">M-Pesa Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="mpesaForm">
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" placeholder="254700000000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (KSh)</label>
                        <input type="number" class="form-control" min="1">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send STK Push</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Referral Modal -->
<div class="modal fade" id="referralModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Share Your Referral Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <h3 class="mb-3">Your Code: FUGO123</h3>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary">
                        <i class="fab fa-whatsapp me-2"></i>Share via WhatsApp
                    </button>
                    <button class="btn btn-outline-primary">
                        <i class="far fa-copy me-2"></i>Copy Link
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Deposit Modal -->
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Deposit Funds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="mpesaDepositForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text">254</span>
                            <input type="tel" name="mpesa_phone_number" maxlength="9"
                                class="form-control form-control-lg" placeholder="700000000" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">KSh</span>
                            <input type="number" class="form-control form-control-lg" name="deposit_amount" required
                                min="100">
                        </div>
                        <small class="text-muted">Minimum deposit: KSh 100</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Proceed to Deposit</button>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- Withdraw Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Withdraw Funds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="mpesaWithdrawalForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Amount (KSh)</label>
                        <input type="number" name="withdrawal_amount" class="form-control form-control-lg"
                            placeholder="Enter amount">
                    </div>
                    {{-- <div class="mb-3">
                    <label class="form-label">Withdraw to</label>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary text-start">
                            <i class="fas fa-mobile-alt me-2"></i>M-PESA
                        </button>
                        <button class="btn btn-outline-primary text-start">
                            <i class="fas fa-university me-2"></i>Bank Account
                        </button>
                    </div>
                </div> --}}
                    <button type="submit" class="btn btn-primary w-100">Withdraw Now</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Transfer Funds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="fundTransferForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">From Wallet</label>
                        <select class="form-select form-select-lg mb-3" name="source_wallet">
                            <option value="main">Main Wallet</option>
                            <option value="savings">Savings Wallet</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">To Wallet</label>
                        <select class="form-select form-select-lg mb-3" name="destination_wallet">
                            <option value="savings">Savings Wallet</option>
                            <option value="main">Main Wallet</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (KSh)</label>
                        <input type="number" name="transfer_amount" class="form-control form-control-lg"
                            placeholder="Enter amount">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Transfer Now</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Add Savings Modal -->
<div class="modal fade" id="unlockSavingsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Activate Savings Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="unlockSavingsForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Activation fee</label>
                        <div class="input-group">
                            <span class="input-group-text">KSh</span>
                            <input type="number" class="form-control form-control-lg" value="350"
                                name="fee_amount" required readonly>
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

                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <h6 class="mb-3">What you need to know</h6>
                            <ul class="mb-0 ps-3">
                                <li class="mb-2">A 350 Ksh activation fee is required to start using the savings
                                    account
                                </li>
                                <li class="mb-2">For your first savings deposit(activation fee does not count), the
                                    savings account will be locked for a period of 1 year(365 days)</li>
                                <li>You can have multiple saving goals each with different target amount and target
                                    dates
                                </li>
                            </ul>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Proceed to payment</button>

                </form>

            </div>
            <div class="modal-footer border-0">
            </div>
        </div>
    </div>
</div>
