<?php

namespace App\Http\Controllers;

use App\Http\Controllers\MpesaController;

use App\Models\SavingsWallet;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function index()
    {
        $mainWalletBal = Wallet::where('user_id', Auth::id())
            ->value('balance');

        $savingsWallet = null;
        $savingsWalletBal = null;
        $wallet = Wallet::where('user_id', Auth::id())->first();
        if ($wallet) {
            $savingsWallet = $wallet->savingsWallet;
        }

        $savingsWalletBal =  $savingsWallet ? $savingsWallet->balance : 0;

        $recentTransactions = Transaction::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('wallet_type')
            ->take(2);

        return view('wallets', compact('mainWalletBal', 'savingsWalletBal', 'recentTransactions'));
    }


    public function deposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deposit_amount' => 'required|numeric|min:100',
            // 'payment_method' => 'required|in:mpesa,card,bank',
            'mpesa_phone_number' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $mpesaController = new MpesaController();

        $amount = $request->deposit_amount;
        $phoneNumber = '254' . $request->mpesa_phone_number;
        $accountReference = 'Deposit';
        $transactionDescription = 'Main wallet deposit';

        $stkResponse = $mpesaController->initiateSTKPush($amount, $phoneNumber, $accountReference, $transactionDescription);

        if ($stkResponse['success']) {
            $stkDetails = $stkResponse['response'];

            $merchantRequestId = $stkDetails['MerchantRequestID'] ?? null;
            $checkoutRequestId = $stkDetails['CheckoutRequestID'] ?? null;
            $responseCode = $stkDetails['ResponseCode'] ?? null;

            if ($responseCode == '0') {
                $transaction = Transaction::create([
                    'user_id' => Auth::id(),
                    'type' => 'deposit',
                    'wallet_type' => 'available',
                    'amount' => $request->deposit_amount,
                    'description' => 'Main wallet deposit',
                    'tracking_id_one' => $merchantRequestId,
                    'tracking_id_two' => $checkoutRequestId,
                ]);

                if (!$transaction) {
                    throw new \Exception('Failed to create transaction.');
                }
                return response()->json([
                    'success' => true,
                    'message' => $stkResponse['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'STK Push initiation failed'
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => $stkResponse['message'],
        ],);
    }


    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'withdrawal_amount' => 'required|numeric|min:50',
            // 'withdrawal_method' => 'required|in:mpesa,bank',
            // 'wallet_id' => 'required|exists:wallets,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $activeReferrals = User::where('referred_by', Auth::id())
            ->where('activated_savings', 1)
            ->count();

        if ($activeReferrals < 1) {
            return response()->json([
                'success' => false,
                'message' => "Kindly get your first referral inorder to withdraw"
            ]);
        }

        try {
            DB::beginTransaction();

            $wallet = Wallet::where('user_id', Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->balance < $request->withdrawal_amount) {
                throw new \Exception('Insufficient funds');
            }

            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'type' => 'withdrawal',
                'wallet_type' => 'available',
                'amount' => $request->withdrawal_amount,
                'description' => 'Main wallet withdrawal',
            ]);

            $wallet->last_transaction_date = now();
            $wallet->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal initiated'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal failed: ' . $e->getMessage()
            ], 500);
        }
    }


    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source_wallet' => 'required',
            'destination_wallet' => 'required',
            'transfer_amount' => 'required|numeric|min:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();

            switch ($request->source_wallet) {
                case 'main':
                    $sourceWallet = Wallet::where('user_id', Auth::id())
                        ->lockForUpdate()
                        ->firstOrFail();

                    $walletType = 'available';
                    break;
                case 'savings':
                    $sourceWallet = SavingsWallet::where('user_id', Auth::id())
                        ->lockForUpdate()
                        ->firstOrFail();

                    $walletType = 'savings';
                    break;
                default:
                    throw new \Exception('Invalid wallet selection');
            }

            switch ($request->destination_wallet) {
                case 'main':
                    $destinationWallet = Wallet::where('user_id', Auth::id())
                        ->lockForUpdate()
                        ->firstOrFail();
                    break;
                case 'savings':
                    $destinationWallet = SavingsWallet::where('user_id', Auth::id())
                        ->lockForUpdate()
                        ->firstOrFail();
                    break;
                default:
                    throw new \Exception('Invalid wallet selection');
            }

            if ($sourceWallet->balance < $request->transfer_amount) {
                throw new \Exception('Insufficient funds in source wallet');
            }

            Transaction::create([
                'user_id' => Auth::id(),
                'type' => 'transfer',
                'wallet_type' => $walletType,
                'amount' => $request->transfer_amount,
                'description' => 'Funds transfer from ' . $request->source_wallet . ' wallet to ' . $request->destination_wallet . ' wallet',
                'reference' => $sourceWallet->id . '-' . $destinationWallet->id,
                'status' => 'completed'
            ]);

            $sourceWallet->balance -= $request->transfer_amount;
            $destinationWallet->balance += $request->transfer_amount;

            $sourceWallet->save();
            $destinationWallet->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transfer successful',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Transfer failed: ' . $e->getMessage()
            ], 500);
        }
    }


    private function processPayment($amount, $phoneNumber)
    {
        // Configuration
        $consumerKey = env('MPESA_CONSUMER_KEY');
        $consumerSecret = env('MPESA_CONSUMER_SECRET');
        $shortCode = env('MPESA_SHORTCODE'); // Your Paybill or Till number
        $passkey = env('MPESA_PASSKEY'); // Provided by Safaricom
        $callbackUrl = "https://sheltarhub.com/fugocallback.php"; // Your callback route
        $timestamp = now()->format('YmdHis'); // Format: YYYYMMDDHHMMSS

        // Generate Password
        $password = base64_encode($shortCode . $passkey . $timestamp);

        try {
            // Step 1: Get Access Token
            $tokenResponse = Http::withBasicAuth($consumerKey, $consumerSecret)
                ->get('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');


            if (!$tokenResponse->ok()) {
                throw new \Exception('Failed to fetch access token.');
            }

            $accessToken = $tokenResponse->json()['access_token'];

            // Step 2: Initiate STK Push
            $stkResponse = Http::withToken($accessToken)
                ->post('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest', [
                    'BusinessShortCode' => $shortCode,
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    'TransactionType' => 'CustomerPayBillOnline', // Can be 'CustomerBuyGoodsOnline' for Till numbers
                    'Amount' => 1,
                    'PartyA' => $phoneNumber, // The phone number initiating the payment
                    'PartyB' => $shortCode, // The Paybill or Till number
                    'PhoneNumber' => $phoneNumber,
                    'CallBackURL' => $callbackUrl,
                    'AccountReference' => 'AccountRef', // Reference for the transaction
                    'TransactionDesc' => 'Payment for goods/services',
                ]);

            if (!$stkResponse->ok()) {
                throw new \Exception('STK Push failed: ' . $stkResponse->json()['errorMessage']);
            }

            return [
                'success' => true,
                'message' => 'STK Push initiated successfully.',
                'response' => $stkResponse->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }


    private function processWithdrawal($amount, $method, $reference)
    {

        return true;
    }
}
