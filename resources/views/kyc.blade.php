@extends('layouts.app')

@section('title', 'FuGo - KYC Verification')

@push('styles')
    <style>
        .form-floating {
            position: relative;
            margin-bottom: 1rem;
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .otp-input {
            letter-spacing: 0.5em;
            text-align: center;
        }

        .progress-steps {
            position: relative;
            margin-bottom: 2rem;
        }

        .step {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background-color: #e9ecef;
            border: 2px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .step.active {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }

        .step-connector {
            flex: 1;
            height: 2px;
            background-color: #dee2e6;
            margin: 0 0.5rem;
        }

        .loading-spinner {
            display: none;
        }

        .card-container {
            border-radius: 7px;
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">KYC Status</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Status:</strong> Pending</p>
                        <p><strong>Last Attempt:</strong> January 13, 2025, 3:45 PM</p>
                        <p><strong>Verification Step:</strong> Phone number verification</p>
                    </div>
                </div>

                <!-- Progress Steps -->
                <div class="progress-steps d-flex align-items-center justify-content-center mb-4">
                    <div class="step active">1</div>
                    <div class="step-connector"></div>
                    <div class="step" id="step-2">2</div>
                </div>

                <!-- Card Container -->
                <div class="card shadow-sm card-container mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">KYC Verification</h5>
                    </div>
                    <div class="card-body">
                        <form id="kyc-form" method="POST" class="needs-validation" novalidate>
                            @csrf
                            <!-- Phone Number Input -->
                            <div class="form-floating mb-3">
                                <input type="tel" class="form-control" id="phone_number" name="phone_number"
                                    placeholder="+2547XXXXXXXX" pattern="^\+254[0-9]{9}$" required>
                                <label for="phone_number">Phone Number</label>
                                <div class="invalid-feedback">
                                    Please enter a valid phone number starting with +254
                                </div>
                            </div>

                            <!-- Send OTP Button -->
                            <div class="d-grid mb-3">
                                <button type="button" id="send-otp" class="btn btn-primary">
                                    <span class="spinner-border spinner-border-sm loading-spinner" role="status"></span>
                                    Send OTP
                                </button>
                            </div>

                            <!-- OTP Section -->
                            <div id="otp-section" style="display:none;">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control otp-input" id="otp" name="otp"
                                        maxlength="6" placeholder="Enter OTP" required>
                                    <label for="otp">Enter OTP</label>
                                    <div class="invalid-feedback">
                                        Please enter the 6-digit OTP
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="button" id="verify-otp" class="btn btn-success">
                                        <span class="spinner-border spinner-border-sm loading-spinner"
                                            role="status"></span>
                                        Verify OTP
                                    </button>
                                </div>

                                <div class="text-center mt-3">
                                    <button type="button" id="resend-otp" class="btn btn-link" disabled>
                                        Resend OTP in <span id="timer">60</span>s
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- <div class="alert alert-info mb-4">
                    <p><strong>Why is KYC verification necessary?</strong></p>
                    <ul>
                        <li>It's a required process to confirm your identity.</li>
                        <li>Helps us comply with financial regulations.</li>
                        <li>Ensures the security and safety of your account.</li>
                    </ul>
                    <p><strong>What to expect:</strong></p>
                    <ul>
                        <li>We will send an OTP to your registered phone number for verification.</li>
                        <li>The OTP is valid for 5 minutes.</li>
                        <li>If you face any issues, please reach out to our support team.</li>
                    </ul>
                </div>

                <!-- Security and Privacy Assurance -->
                <div class="alert alert-warning mb-4">
                    <strong>Privacy Notice:</strong> Your personal information, including phone numbers and OTPs, is stored
                    securely. We respect your privacy and will never share your data without your consent.
                </div>

                <p class="text-center mt-3">
                    Need help with KYC verification? <a href="/support" class="text-decoration-underline">Contact
                        Support</a>
                    or call us at <strong>+2547XXXXXXX</strong>.
                </p> --}}

                <div class="alert alert-success mt-3">
                    <strong>Why should you complete KYC?</strong>
                    <ul>
                        <li>Access all platform features such as savings, loans, and more.</li>
                        <li>Ensure the safety of your account and personal data.</li>
                        <li>The verified phone number will be used for all withdrawal transactions.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('kyc-form');
            const phoneInput = document.getElementById('phone_number');
            const otpInput = document.getElementById('otp');
            const sendOtpBtn = document.getElementById('send-otp');
            const verifyOtpBtn = document.getElementById('verify-otp');
            const resendOtpBtn = document.getElementById('resend-otp');
            const otpSection = document.getElementById('otp-section');
            const step2 = document.getElementById('step-2');

            let timerInterval;

            // Form validation
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });

            // Phone number formatting
            phoneInput.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                if (!value.startsWith('254')) {
                    value = '254' + value;
                }
                e.target.value = '+' + value;
            });

            // OTP input formatting
            otpInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/\D/g, '').substr(0, 6);
            });

            function startResendTimer() {
                let timeLeft = 60;
                resendOtpBtn.disabled = true;

                timerInterval = setInterval(() => {
                    timeLeft--;
                    document.getElementById('timer').textContent = timeLeft;

                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        resendOtpBtn.disabled = false;
                        resendOtpBtn.textContent = 'Resend OTP';
                    }
                }, 1000);
            }

            async function handleRequest(url, data, button) {
                button.disabled = true;
                button.querySelector('.loading-spinner').style.display = 'inline-block';

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Request failed');
                    }

                    return result;
                } catch (error) {
                    throw error;
                } finally {
                    button.disabled = false;
                    button.querySelector('.loading-spinner').style.display = 'none';
                }
            }

            // Send OTP
            sendOtpBtn.addEventListener('click', async () => {
                if (!phoneInput.value.match(/^\+254[0-9]{9}$/)) {
                    notyf.error('Please enter a valid phone number');
                    return;
                }

                try {
                    const result = await handleRequest('/kyc/send-otp', {
                        phone_number: phoneInput.value
                    }, sendOtpBtn);

                    console.log(result);
                    if (result.success) {
                        otpSection.style.display = 'block';
                        step2.classList.add('active');
                        startResendTimer();

                        notyf.success('OTP sent successfully!');
                    } else {
                        notyf.error(result.message || 'Failed to send OTP');
                    }
                } catch (error) {
                    notyf.error(error.message || 'Failed to send OTP');
                }
            });

            // Verify OTP
            verifyOtpBtn.addEventListener('click', async () => {
                if (otpInput.value.length !== 6) {
                    alert('Please enter a valid 6-digit OTP');
                    return;
                }

                try {
                    const result = await handleRequest('/kyc/verify-otp', {
                        phone_number: phoneInput.value,
                        otp: otpInput.value
                    }, verifyOtpBtn);

                    notyf.success(result.message);
                    // Redirect or show success state
                } catch (error) {
                    notyf.error(error.message || 'Failed to verify OTP');
                }
            });

            // Resend OTP
            resendOtpBtn.addEventListener('click', async () => {
                try {
                    const result = await handleRequest('/kyc/send-otp', {
                        phone_number: phoneInput.value
                    }, resendOtpBtn);

                    startResendTimer();
                    notyf.success('OTP resent successfully!');
                } catch (error) {
                    notyf.error(error.message || 'Failed to resend OTP');
                }
            });
        });
    </script>
@endpush
