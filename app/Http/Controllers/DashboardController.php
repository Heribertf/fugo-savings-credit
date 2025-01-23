<?php

namespace App\Http\Controllers;

use App\Models\SavingsWallet;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $availableBalance = $this->getAvailableBalance($user);
        $savingsBalance = $this->getSavingsBalance($user);
        $loanEligibility = $this->calculateLoanEligibility($savingsBalance);
        $savingsActivation = $this->getSavingsActivationStatus($user);

        $recentTransactions = Transaction::where('user_id', $user->id)
            ->whereIn('status', ['completed', 'approved'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('dashboard', compact(
            'availableBalance',
            'savingsBalance',
            'loanEligibility',
            'recentTransactions'
        ));
    }

    private function getAvailableBalance($user)
    {
        return Wallet::where('user_id', $user->id)
            ->value('balance');
    }

    private function getSavingsActivationStatus($user)
    {
        return SavingsWallet::where('user_id', $user->id)
            ->value('is_active');
    }

    private function getSavingsBalance($user)
    {
        $savingsWallet = null;
        $wallet = Wallet::where('user_id', $user->id)->first();
        if ($wallet) {
            $savingsWallet = $wallet->savingsWallet;
        }

        return $savingsWallet ? $savingsWallet->balance : 0;
    }

    private function calculateLoanEligibility($savingsBalance)
    {
        // 90% of savings balance
        return $savingsBalance * 0.9;
    }

    public function addSavings(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'description' => 'nullable|string|max:255'
        ]);

        $user = auth()->user();

        // Create MPesa payment request
        $mpesaResponse = $this->initiateMpesaPayment($request->amount);

        if ($mpesaResponse->success) {
            // Create transaction record
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'wallet_type' => 'savings',
                'description' => $request->description,
                'reference' => $mpesaResponse->reference,
                'status' => 'pending'
            ]);

            // Send push notification
            $this->sendPushNotification(
                $user,
                'Savings deposit initiated',
                "Your deposit of KSh {$request->amount} is being processed."
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to initiate payment'
        ]);
    }

    private function initiateMpesaPayment($amount)
    {
        // Implement MPesa STK Push logic here
        // This is a placeholder that should be replaced with actual MPesa integration
        return (object)[
            'success' => true,
            'reference' => 'MPE' . time()
        ];
    }

    private function sendPushNotification($user, $title, $message)
    {
        // Implement push notification logic here
        // This is a placeholder that should be replaced with actual notification service
        // Could use Firebase Cloud Messaging or similar service
    }
}
