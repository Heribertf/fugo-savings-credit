<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use AfricasTalking\SDK\AfricasTalking;

class KycController extends Controller
{
    protected $africasTalking;

    public function __construct()
    {
        $this->africasTalking = new AfricasTalking(
            env('AFRICASTALKING_USERNAME'),
            env('AFRICASTALKING_FUGO_API_KEY')
        );
    }


    public function index()
    {
        return view('kyc');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|regex:/^\+254[0-9]{9}$/',
        ]);
        $phoneNumber = ltrim($request->phone_number, '+');

        $otpCode = rand(100000, 999999);

        Otp::updateOrCreate(
            ['phone_number' => $request->phone_number],
            [
                'otp' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(5),
            ]
        );

        try {
            $sms = $this->africasTalking->sms();
            $response = $sms->send([
                'to' => $phoneNumber,
                'message' => "Your FuGO verification code is $otpCode. It expires in 5 minutes.",
            ]);

            Log::info('Africa\'s Talking Response:', (array) $response);

            dd((array) $response);

            $recipients = $response['data']['SMSMessageData']['Recipients'];
            if (count($recipients) > 0 && $recipients[0]['status'] === 'UserInBlacklist') {
                return response()->json(['success' => false, 'message' => 'The phone number is blacklisted. Please contact support.']);
            }

            // if ($response['status'] === 'success') {
            //     return response()->json(['success' => true, 'message' => 'OTP sent successfully!']);
            // }

            // return response()->json(['success' => false, 'message' => 'Failed to send OTP: ' . $response['data']['SMSMessageData']['Message']]);
            return response()->json(['success' => true, 'message' => 'OTP sent successfully!']);
        } catch (\Exception $e) {
            Log::error('Error sending OTP:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to send OTP: ' . $e->getMessage()]);
        }
    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|regex:/^\+254[0-9]{9}$/',
            'otp' => 'required|digits:6',
        ]);

        $otp = Otp::where('phone_number', $request->phone_number)->first();

        if (!$otp || $otp->otp !== $request->otp) {
            return response()->json(['message' => 'Invalid verification code.'], 400);
        }

        if (Carbon::now()->isAfter($otp->expires_at)) {
            return response()->json(['message' => 'Verification code has expired.'], 400);
        }

        $otp->delete(); // OTP is valid, delete it after verification

        return response()->json(['message' => 'Phone number verified successfully.']);
    }
}
