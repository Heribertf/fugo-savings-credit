<?php

namespace App\Http\Controllers;

use App\Http\Controllers\MpesaController;

use App\Models\Loan;
use App\Models\SavingsWallet;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $savingsBalance = $this->getSavingsBalance($user);
        $eligibleAmount = $this->calculateLoanEligibility($savingsBalance);

        $activeLoan = Loan::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($activeLoan) {
            $totalRepaid = $activeLoan->total_repaid;
            $activeLoan->remaining_balance = $activeLoan->amount + abs($totalRepaid);

            $totalDays = 30; // Loan period
            $daysElapsed = Carbon::now()->diffInDays($activeLoan->created_at);
            $activeLoan->progress_percentage = min(($daysElapsed / $totalDays) * 100, 100);
        }

        $loanHistory = Loan::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // foreach ($loanHistory as $loan) {
        //     // $dueDate = Carbon::parse($loan->due_date);
        //     // $is_defaulted = $loan->status === 'active' &&
        //     //     $dueDate->isPast() &&
        //     //     $loan->total_repaid < $loan->amount;

        //     if ($loan->is_defaulted && $loan->status === 'active') {
        //         $loan->update([
        //             'status' => 'defaulted'
        //         ]);
        //     }
        // }

        return view('loans', compact(
            'eligibleAmount',
            'savingsBalance',
            'activeLoan',
            'loanHistory'
        ));
    }


    public function request(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100'
        ]);

        $user = auth()->user();

        try {
            DB::beginTransaction();

            $savingsBalance = $this->getSavingsBalance($user);
            $eligibleAmount = $this->calculateLoanEligibility($savingsBalance);

            if ($request->amount > $savingsBalance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Requested amount exceeds loan eligibility'
                ]);
            }

            if ($this->hasActiveLoan($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active loan'
                ]);
            }

            $interest = $request->amount * 0.10;
            $disbursedAmount = $request->amount - $interest;

            $loan = Loan::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'interest' => $interest,
                // 'disbursed_amount' => $disbursedAmount,
                'due_date' => Carbon::now()->addDays(30),
                'status' => 'pending'
            ]);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'loan_disbursement',
                'amount' => $disbursedAmount,
                'wallet_type' => 'savings',
                'description' => 'Loan disbursement',
                'reference' => 'LOAN' . $loan->id,
                'status' => 'pending'
            ]);

            DB::commit();

            // $this->sendPushNotification(
            //     $user,
            //     'Loan Approved',
            //     "Your loan of KSh {$disbursedAmount} has been approved and disbursed to your available balance."
            // );

            return response()->json([
                'success' => true,
                'message' => 'Your loan has been received for approval'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your loan request'
            ]);
        }
    }

    public function repay(Request $request, Loan $loan)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'mpesa_phone_number' => 'required'
        ]);

        $user = auth()->user();

        if ($loan->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to repay this loan.'
            ], 403);
        }

        if ($loan->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This loan is not active.'
            ]);
        }

        try {
            DB::beginTransaction();

            $totalRepaid = $loan->total_repaid;

            $remainingBalance = $loan->amount - $totalRepaid;

            if ($request->amount > abs($remainingBalance)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount exceeds remaining balance'
                ]);
            }

            $mpesaController = new MpesaController();

            $amount = $request->amount;
            $phoneNumber = '254' . $request->mpesa_phone_number;
            $accountReference = 'Repayment';
            $transactionDescription = 'Loan repayment';

            $stkResponse = $mpesaController->initiateSTKPush($amount, $phoneNumber, $accountReference, $transactionDescription);

            if (!$stkResponse['success']) {
                throw new \Exception($stkResponse['message']);
            }

            $stkDetails = $stkResponse['response'];
            $merchantRequestId = $stkDetails['MerchantRequestID'] ?? null;
            $checkoutRequestId = $stkDetails['CheckoutRequestID'] ?? null;
            $responseCode = $stkDetails['ResponseCode'] ?? null;

            if ($responseCode !== '0') {
                throw new \Exception('STK Push initiation failed.');
            }

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'loan_repayment',
                'wallet_type' => 'savings',
                'amount' => $amount,
                'description' => $loan->id . '_' . $transactionDescription,
                'tracking_id_one' => $merchantRequestId,
                'tracking_id_two' => $checkoutRequestId,
            ]);

            if (!$transaction) {
                throw new \Exception('Failed to create transaction.');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment is being processed'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your payment'
            ]);
        }
    }

    private function hasActiveLoan($user)
    {
        return Loan::where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    private function getSavingsBalance($user)
    {
        return SavingsWallet::where('user_id', $user->id)
            ->value('balance');
    }

    private function calculateLoanEligibility($savingsBalance)
    {
        // 90% of savings balance
        return $savingsBalance * 0.9;
    }

    public function approveLoans(Request $request, $id)
    {
        $request->validate([
            'reference' => 'required|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            $loan = Loan::findOrFail($id);

            $transaction = Transaction::where('reference', 'LOAN' . $loan->id)->first();

            if ($transaction) {
                $transaction->update([
                    'reference' => $request->reference,
                    'status' => 'completed'
                ]);
            } else {
                return redirect()->back()->with('error', 'Unable to get transaction.');
            }

            $loan->update([
                'disbursed_amount' => $loan->amount - $loan->interest,
                'status' => 'active',
                'approved_by' => Auth::id()
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Loan approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to approve loan: ' . $e->getMessage());
        }
    }

    public function rejectLoans($id)
    {
        $loan = Loan::findOrFail($id);
        $loan->update([
            'status' => 'rejected',
            'approved_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Loan rejected.');
    }

    private function initiateMpesaPayment($amount)
    {
        return (object)[
            'success' => true,
            'reference' => 'MPE' . time()
        ];
    }

    private function sendPushNotification($user, $title, $message) {}
}
