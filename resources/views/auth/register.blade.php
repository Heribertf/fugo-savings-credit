{{-- <x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout> --}}

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FuGo - Register</title>
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
                        <h2 class="fw-bold">Create your account</h2>
                        <h3 class="text-secondary">Join FuGo Savings & Credit today</h3>
                    </div>
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <form action="{{ route('register') }}" method="POST" id="register-form">
                                @csrf

                                <!-- First Name -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-6 mb-x-3">
                                            <label for="first_name" class="form-label">First Name</label>
                                            <input id="first_name" name="first_name" type="text" class="form-control"
                                                value="{{ old('first_name') }}" required>
                                            <div id="first_name-error" class="text-danger mt-2" style="display: none;">
                                            </div>
                                        </div>

                                        <!-- Last Name -->
                                        <div class="col-md-6">
                                            <label for="last_name" class="form-label">Last Name</label>
                                            <input id="last_name" name="last_name" type="text" class="form-control"
                                                value="{{ old('last_name') }}" required>
                                            <div id="last_name-error" class="text-danger mt-2" style="display: none;">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Username -->
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input id="username" name="username" type="text" class="form-control"
                                        value="{{ old('username') }}" required>
                                    <div id="username-error" class="text-danger mt-2" style="display: none;"></div>
                                </div>

                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input id="email" name="email" type="email" class="form-control"
                                        value="{{ old('email') }}" required>
                                    <div id="email-error" class="text-danger mt-2" style="display: none;"></div>
                                </div>

                                <!-- Phone Number -->
                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Phone Number</label>
                                    <input id="phone_number" name="phone_number" type="tel" class="form-control"
                                        value="{{ old('phone_number') }}" required>
                                    <div id="phone_number-error" class="text-danger mt-2" style="display: none;"></div>
                                </div>

                                <!-- Referral Code -->
                                <div class="mb-3">
                                    <label for="referral" class="form-label">Referral Code (Optional)</label>
                                    <input id="referral" name="referral_code" type="text" class="form-control"
                                        value="{{ old('referral_code') }}">
                                    <div id="referral_code-error" class="text-danger mt-2" style="display: none;"></div>
                                </div>

                                <!-- Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input id="password" name="password" type="password" class="form-control" required>
                                    <div id="password-error" class="text-danger mt-2" style="display: none;"></div>
                                </div>

                                <!-- Confirm Password -->
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password"
                                        class="form-control" required>
                                    <div id="password_confirmation-error" class="text-danger mt-2"
                                        style="display: none;"></div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Create Account</button>
                            </form>
                            <hr class="my-4">
                            <div class="text-center">
                                <p class="text-secondary">Already have an account?</p>
                                <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100">Sign in
                                    instead</a>
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

        document.getElementById('register-form').addEventListener('submit', function(event) {
            event.preventDefault();

            document.getElementById("loader").style.display = "block";

            let form = new FormData(this);

            fetch("{{ route('register') }}", {
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
                        clearErrorMessages();
                        displayValidationErrors(data.errors);

                        let errors = data.errors;
                        for (let key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                let errorDiv = document.querySelector(`#${key}-error`);
                                if (errorDiv) {
                                    errorDiv.innerHTML = errors[key][0];
                                }
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById("loader").style.display = "none";
                    notyf.error('An unexpected error occurred. Please try again.');
                });

        });

        function displayValidationErrors(errors) {
            for (let field in errors) {
                if (errors.hasOwnProperty(field)) {
                    let errorDiv = document.querySelector(`#${field}-error`);
                    if (errorDiv) {
                        errorDiv.innerHTML = errors[field][0]; // Show the first error for each field
                        errorDiv.style.display = 'block'; // Ensure error message is visible
                    }
                }
            }
        }

        // Function to clear existing error messages
        function clearErrorMessages() {
            let errorDivs = document.querySelectorAll('.text-danger');
            errorDivs.forEach(div => {
                div.innerHTML = '';
                div.style.display = 'none';
            });
        }
    </script>
</body>

</html>
