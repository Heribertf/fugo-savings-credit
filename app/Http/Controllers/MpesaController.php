<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\SavingsWallet;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\MpesaTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    private $consumerKey;
    private $consumerSecret;
    private $shortCode;
    private $passkey;
    private $callbackUrl;

    public function __construct()
    {
        // if (file_exists(base_path('.env'))) {
        //     \Dotenv\Dotenv::createImmutable(base_path())->load();
        // }

        $this->consumerKey = env('MPESA_CONSUMER_KEY');
        $this->consumerSecret = env('MPESA_CONSUMER_SECRET');
        $this->shortCode = env('MPESA_SHORTCODE');
        $this->passkey = env('MPESA_PASSKEY');
        $this->callbackUrl = env('MPESA_CALLBACK_URL');
    }

    public function initiateSTKPush($amount, $phoneNumber, $accountReference, $transactionDescription)
    {
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($this->shortCode . $this->passkey . $timestamp);

        // dd($this->consumerKey . '----' . $this->consumerSecret);
        try {
            $tokenResponse = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->retry(3, 1000)
                ->get('https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');

            if (!$tokenResponse->ok()) {
                throw new \Exception('Failed to fetch access token.');
            }

            // dd($tokenResponse->json());

            $accessToken = $tokenResponse->json()['access_token'];

            $stkResponse = Http::withToken($accessToken)
                ->post('https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest', [
                    'BusinessShortCode' => $this->shortCode,
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    'TransactionType' => 'CustomerPayBillOnline',
                    'Amount' => 1,
                    'PartyA' => $phoneNumber,
                    'PartyB' => $this->shortCode,
                    'PhoneNumber' => $phoneNumber,
                    'CallBackURL' => $this->callbackUrl,
                    'AccountReference' => $accountReference,
                    'TransactionDesc' => $transactionDescription,
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
            Log::error('MPesa STK Push Error:', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function handleCallback($request)
    {
        Log::info('MPesa Callback Received:', $request->all());

        $callbackData = $request->input('Body.stkCallback', []);

        if (!empty($callbackData)) {
            $resultCode = $callbackData['ResultCode'];
            $resultDesc = $callbackData['ResultDesc'];
            $merchantRequestId = $callbackData['MerchantRequestID'];
            $checkoutRequestId = $callbackData['CheckoutRequestID'];

            if ($resultCode == 0) {
                try {
                    DB::beginTransaction();

                    $details = $callbackData['CallbackMetadata']['Item'];
                    $data = [];

                    foreach ($details as $item) {
                        $data[$item['Name']] = $item['Value'] ?? null;
                    }

                    $transactionDateRaw = $data['TransactionDate'];
                    $transactionDate = Carbon::createFromFormat('YmdHis', $transactionDateRaw);

                    MpesaTransaction::create([
                        'transaction_id' => $data['MpesaReceiptNumber'],
                        'phone_number' => $data['PhoneNumber'],
                        'amount' => $data['Amount'],
                        'account_reference' => $data['AccountReference'] ?? null,
                        'transaction_type' => 'STKPush',
                        'transaction_date' => $transactionDate,
                        'status' => 'success',
                    ]);

                    $walletTransaction = Transaction::where('tracking_id_one', $merchantRequestId)->firstOrFail();

                    if ($walletTransaction->type == 'deposit') {
                        switch ($walletTransaction->wallet_type) {
                            case 'available':
                                $wallet = Wallet::where('user_id', $walletTransaction->user_id)
                                    ->lockForUpdate()
                                    ->firstOrFail();

                                $wallet->balance += $data['Amount'];
                                $wallet->last_transaction_date = $walletTransaction->created_at;
                                $wallet->save();
                                break;
                            case 'savings':
                                $wallet = SavingsWallet::where('user_id', $walletTransaction->user_id)
                                    ->lockForUpdate()
                                    ->firstOrFail();

                                $wallet->balance += $data['Amount'];
                                $wallet->unallocated_funds += $data['Amount'];
                                $wallet->last_savings_date = $walletTransaction->created_at;
                                $wallet->save();
                                break;
                        }
                    } elseif ($walletTransaction->type == 'activation') {
                        switch ($walletTransaction->wallet_type) {
                            case 'savings':
                                $wallet = SavingsWallet::where('user_id', $walletTransaction->user_id)
                                    ->firstOrFail();

                                $wallet->is_active = 1;
                                $wallet->save();

                                $userRecord = User::where('id', $walletTransaction->user_id)->first();
                                $userRecord->update([
                                    'activated_savings' => 1,
                                ]);

                                $this->processReferralBonus($walletTransaction->user_id, $walletTransaction->transaction_id);

                                break;
                        }
                    } elseif ($walletTransaction->type == 'loan_repayment') {
                        $descArray = explode('_', $walletTransaction->description);
                        $loanId = array_shift($descArray);

                        $loan = Loan::where('id', $loanId)->first();

                        $newTotalRepaid = $loan->total_repaid + $data['Amount'];

                        $loan->total_repaid += $data['Amount'];

                        if (abs($newTotalRepaid) >= $loan->amount) {
                            $loan->status = 'paid';

                            $savingsBonus = $loan->interest * 0.5;

                            $savingWallet = SavingsWallet::where('user_id', $walletTransaction->user_id)
                                ->lockForUpdate()
                                ->firstOrFail();
                            $savingWallet->balance += $savingsBonus;
                            $savingWallet->save();

                            Transaction::create([
                                'user_id' => $walletTransaction->user_id,
                                'type' => 'interest_bonus',
                                'amount' => $savingsBonus,
                                'wallet_type' => 'savings',
                                'description' => 'Interest bonus for timely loan repayment',
                                'reference' => $loan->id . '-Interest bonus',
                                'status' => 'completed'
                            ]);
                        }
                        $loan->save();
                    }

                    $walletTransaction->update(['reference' => $data['MpesaReceiptNumber']]);

                    DB::commit();
                    return response()->json(['message' => 'Transaction saved successfully.'], 200);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('MPesa Transaction Processing Failed:', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json(['message' => 'Transaction processing failed.'], 500);
                }
            }

            Log::warning('MPesa Transaction Failed:', [
                'ResultCode' => $resultCode,
                'ResultDesc' => $resultDesc,
            ]);

            return response()->json(['message' => 'Transaction failed.'], 400);
        }

        return response()->json(['message' => 'Invalid callback data.'], 400);
    }

    private function processReferralBonus($userId, $transactionId)
    {
        $directReferrerId = User::where('id', $userId)->value('reffered_by');
        if ($directReferrerId) {
            $this->creditReferralBonus($directReferrerId, 100, $transactionId, 'Direct referral bonus');

            $indirectReferrerId = User::where('id', $directReferrerId)->value('reffered_by');
            if ($indirectReferrerId) {
                $this->creditReferralBonus($indirectReferrerId, 50, $transactionId, 'Indirect referral bonus');
            }
        }
    }

    private function creditReferralBonus($referrerId, $amount, $transactionId, $description)
    {
        $referrerWallet = Wallet::where('user_id', $referrerId)
            ->lockForUpdate()
            ->firstOrFail();

        $referrerWallet->balance += $amount;
        $referrerWallet->save();

        Transaction::create([
            'user_id' => $referrerId,
            'type' => 'referral_bonus',
            'wallet_type' => 'available',
            'amount' => $amount,
            'description' => $description,
            'reference' => $transactionId,
        ]);
    }
}
