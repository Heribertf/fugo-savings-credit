<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Transaction::where('user_id', $user->id);

        if ($request->type) {
            if ($request->type == 'loan') {
                $query->where('type', 'like', '%' . $request->type . '%');
            } else {
                $query->where('type', $request->type);
            }
        }

        if ($request->date_range) {
            $days = (int)$request->date_range;
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }

        if ($request->min_amount) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->max_amount) {
            $query->where('amount', '<=', $request->max_amount);
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        $totalDeposits = $this->calculateTotal($user->id, ['deposit']);
        $totalWithdrawals = $this->calculateTotal($user->id, ['withdrawal']);
        $totalEarnings = $this->calculateTotal($user->id, ['referral_bonus', 'interest_bonus']);
        $totalFees = $this->calculateTotalFees($user->id);

        if ($request->ajax()) {
            return response()->json([
                'data' => $transactions->items(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
            ]);
        }

        return view('transactions', compact(
            'transactions',
            'totalDeposits',
            'totalWithdrawals',
            'totalFees',
            'totalEarnings'
        ));
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100'
        ]);

        $user = auth()->user();
        $amount = $request->amount;
        $fee = $this->calculateWithdrawalFee($amount);

        $availableBalance = $this->getAvailableBalance($user);

        if (($amount + $fee) > $availableBalance) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance including withdrawal fee'
            ]);
        }

        // Initiate MPesa withdrawal
        $mpesaResponse = $this->initiateMpesaWithdrawal($amount, $user->phone);

        if ($mpesaResponse->success) {
            // Record withdrawal transaction
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => -$amount,
                'fee' => $fee,
                'wallet_type' => 'available',
                'description' => 'Withdrawal to M-Pesa',
                'reference' => $mpesaResponse->reference,
                'status' => 'completed'
            ]);

            // Record fee transaction
            if ($fee > 0) {
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'fee',
                    'amount' => -$fee,
                    'wallet_type' => 'available',
                    'description' => 'Withdrawal fee',
                    'reference' => $mpesaResponse->reference,
                    'status' => 'completed'
                ]);
            }

            $this->sendPushNotification(
                $user,
                'Withdrawal Successful',
                "KSh {$amount} has been sent to your M-Pesa. Fee charged: KSh {$fee}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal processed successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to process withdrawal'
        ]);
    }

    private function calculateWithdrawalFee($amount)
    {
        if ($amount < 1000) {
            return 30;
        } elseif ($amount <= 5000) {
            return 50;
        } else {
            return $amount * 0.03; // 3% fee
        }
    }

    private function calculateTotal($userId, array $types)
    {
        return Transaction::where('user_id', $userId)
            ->whereIn('type', $types)
            ->where('status', 'completed')
            ->sum('amount');
    }

    private function calculateTotalFees($userId)
    {
        return Transaction::where('user_id', $userId)
            ->where('type', 'fee')
            ->where('status', 'completed')
            ->sum('fee');
    }

    private function getAvailableBalance($user)
    {
        return Wallet::where('user_id', $user->id)
            ->value('balance');
    }

    private function initiateMpesaWithdrawal($amount, $phone)
    {
        // Implement MPesa B2C logic here
        return (object)[
            'success' => true,
            'reference' => 'MPW' . time()
        ];
    }

    private function sendPushNotification($user, $title, $message)
    {
        // Implement push notification logic
    }
}
