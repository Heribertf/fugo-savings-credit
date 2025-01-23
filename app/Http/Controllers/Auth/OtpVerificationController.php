<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class OtpVerificationController extends Controller
{
    public function show()
    {
        return view('auth.verify-otp');
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $userid = auth()->id();
        $user = User::find($userid);

        if (!$user || $user->verification_code !== $request->otp || Carbon::now()->greaterThan($user->verification_code_expiry)) {
            // return redirect()->back()->with('error', 'Invalid or expired OTP.');
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ]);
        }

        $user->update([
            'is_active' => 1,
            'verification_code' => null,
            'verification_code_expiry' => null,
            'email_verified' => 1,
            'email_verified_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your email has been verified.',
            'redirect' => route('dashboard'),
        ]);

        // return redirect()->route('dashboard')->with('success', 'Your email has been verified.');
    }

    public function resend(Request $request)
    {
        $userid = auth()->id();
        $user = User::find($userid);

        $otp = mt_rand(100000, 999999);
        $otpExpiry = Carbon::now()->addMinutes(60);

        $user->update([
            'verification_code' => $otp,
            'verification_code_expiry' => $otpExpiry,
        ]);

        $this->sendOtpEmail($user);

        return redirect()->route('verification.otp')->with('status', 'A new OTP has been sent to your email.');
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
