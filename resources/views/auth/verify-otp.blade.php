<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FuGo - OTP Verification</title>
    <link rel="stylesheet" href="{{ asset('assets/css/src/bootstrap/bootstrap.min.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/notyf/3.10.0/notyf.min.css" rel="stylesheet">

    <style>
        #loader {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }

        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #1B5E20;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-light">
    <div id="loader">
        <div class="loader"></div>
    </div>
    <div class="d-flex min-vh-100 flex-column justify-content-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Verify Your Email</h2>
                        <h3 class="text-secondary">Enter the 6-digit verification code sent to your email.</h3>
                    </div>
                    <div class="card shadow-sm">
                        <div class="card-body p-4">

                            <form action="{{ route('verification.otp.verify') }}" method="POST" id="otp-form">
                                @csrf

                                <div class="mb-3">
                                    <label for="otp" class="form-label">Verification Code</label>
                                    <input id="otp" name="otp" type="text" class="form-control" required
                                        autofocus>
                                    @error('otp')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Verify Email</button>
                            </form>

                            <hr class="my-4">
                            <div class="text-center">
                                <form method="POST" action="{{ route('verification.otp.resend') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-link text-primary">
                                        Resend OTP
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/notyf/3.10.0/notyf.min.js"></script>

    <script>
        const notyf = new Notyf({
            duration: 3000,
            position: {
                x: 'right',
                y: 'top'
            }
        });

        document.getElementById('otp-form').addEventListener('submit', function(event) {
            event.preventDefault();

            document.getElementById("loader").style.display = "block";

            let form = new FormData(this);

            fetch("{{ route('verification.otp.verify') }}", {
                    method: 'POST',
                    body: form,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                    }
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById("loader").style.display = "none";

                    if (data.success) {
                        notyf.success(data.message);
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 3000);
                    } else {
                        notyf.error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById("loader").style.display = "none";
                    notyf.error('An unexpected error occurred. Please try again.');
                });
        });
    </script>
</body>

</html>
