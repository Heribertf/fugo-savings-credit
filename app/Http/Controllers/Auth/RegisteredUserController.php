<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SavingsWallet;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    // public function store(Request $request): RedirectResponse
    // {
    //     $request->validate([
    //         'name' => ['required', 'string', 'max:255'],
    //         'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    //         'password' => ['required', 'confirmed', Rules\Password::defaults()],
    //     ]);

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //     ]);

    //     event(new Registered($user));

    //     Auth::login($user);

    //     return redirect(RouteServiceProvider::HOME);
    // }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'phone_number' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => ['nullable', 'string', 'max:10'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ]);
        }

        $referrer = null;
        if ($request->referred_by) {
            $referrer = User::where('referral_code', $request->referral_code)->value('id');
        }

        do {
            $referralCode = strtoupper(Str::random(10));
        } while (User::where('referral_code', $referralCode)->exists());

        $otp = mt_rand(100000, 999999);
        $otpExpiry = Carbon::now()->addMinutes(60);

        $user = User::create([
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'referral_code' => $referralCode,
            'referred_by' => $referrer,
            'verification_code' => $otp,
            'verification_code_expiry' => $otpExpiry,
            'kyc_status' => 'PENDING',
        ]);

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => 0.00,
        ]);

        $savingsWallet = SavingsWallet::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'balance' => 0.00,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'wallet_type' => 'available',
            'amount' => 0.00,
            'description' => 'Initial wallet creation',
            'reference' => $user->id . '-INITIAL-WALLET-CREATION',
            'status' => 'completed',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'wallet_type' => 'savings',
            'amount' => 0.00,
            'description' => 'Initial wallet creation',
            'reference' => $user->id . '-1-INITIAL-WALLET-CREATION',
            'status' => 'completed',
        ]);

        $this->sendOtpEmail($user);

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful, OTP sent to your email.',
            'redirect' => route('verification.otp'),
        ]);

        // return redirect()->route('verification.otp')->with('status', 'OTP sent to your email.');
    }

    private function sendOtpEmail(User $user)
    {
        $data = [
            'otp' => $user->verification_code,
            'name' => $user->first_name,
        ];

        Mail::send('emails.verify_otp', $data, function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Your FuGo Verification Code');
        });
    }
}
