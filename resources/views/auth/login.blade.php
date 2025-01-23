{{-- <x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                    name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                    href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout> --}}


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FuGo - Login</title>
    <link rel="stylesheet" href="{{ asset('assets/css/src/bootstrap/bootstrap.min.css') }}">
</head>

<body class="bg-light">
    <div class="d-flex min-vh-100 flex-column justify-content-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">FuGo Savings & Credit</h2>
                        <h3 class="text-secondary">Welcome back!</h3>
                    </div>
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            @if (session('status'))
                                <div class="alert alert-success mb-4">
                                    {{ session('status') }}
                                </div>
                            @endif
                            <form action="{{ route('login') }}" method="POST" id="login-form">
                                @csrf

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input id="email" name="email" type="email" class="form-control"
                                        value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input id="password" name="password" type="password" class="form-control" required>
                                    @error('password')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="form-check">
                                        <input id="remember" name="remember" type="checkbox" class="form-check-input">
                                        <label for="remember" class="form-check-label">Remember me</label>
                                    </div>
                                    <a href="#" class="text-decoration-none">Forgot password?</a>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Sign in</button>
                            </form>

                            <hr class="my-4">

                            <div class="text-center">
                                <p class="text-secondary">Don't have an account?</p>
                                <a href="{{ route('register') }}" class="btn btn-outline-secondary w-100">Create new
                                    account</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
