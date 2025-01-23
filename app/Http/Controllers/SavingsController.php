<?php

namespace App\Http\Controllers;

use App\Http\Controllers\MpesaController;

use App\Models\Savings;
use App\Models\SavingsWallet;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SavingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $savings = SavingsWallet::where('user_id', $user->id)->first();

        $lockDaysRemaining = 0;
        // if ($savings->first_deposit_date) {
        //     $lockEndDate = Carbon::parse($savings->first_deposit_date)->addDays(365);
        //     $lockDaysRemaining = max(0, Carbon::now()->diffInDays($lockEndDate));
        // }

        if ($savings->is_locked) {
            $lockEndDate = Carbon::parse($savings->locked_until);
            $lockDaysRemaining = max(0, Carbon::now()->diffInDays($lockEndDate));
        }

        $goals = SavingsGoal::where('user_id', $user->id)
            ->where('target_date', '<=', $savings->locked_until)
            ->where('created_at', '>=', $savings->date_locked)
            ->withSum('allocations', 'amount')
            ->get()
            ->map(function ($goal) {
                $goal->progress = $goal->allocations_sum_amount
                    ? round(($goal->allocations_sum_amount / $goal->target_amount) * 100, 2)
                    : 0;
                return $goal;
            });

        $transactions = Transaction::where('user_id', $user->id)
            ->where('wallet_type', 'savings')
            ->whereNot('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('savings', compact('savings', 'goals', 'transactions', 'lockDaysRemaining'));
    }


    public function activate(Request $request)
    {
        $request->validate([
            'fee_amount' => 'required|numeric|min:1',
            'mpesa_phone_number' => 'required|'
        ]);

        $mpesaController = new MpesaController();

        $amount = $request->fee_amount;
        $phoneNumber = '254' . $request->mpesa_phone_number;
        $accountReference = 'Activation fee';
        $transactionDescription = 'Savings account activation fee';

        $stkResponse = $mpesaController->initiateSTKPush($amount, $phoneNumber, $accountReference, $transactionDescription);

        // dd($stkResponse);
        if ($stkResponse['success']) {
            $stkDetails = $stkResponse['response'];

            $merchantRequestId = $stkDetails['MerchantRequestID'] ?? null;
            $checkoutRequestId = $stkDetails['CheckoutRequestID'] ?? null;
            $responseCode = $stkDetails['ResponseCode'] ?? null;

            if ($responseCode == '0') {
                $transaction = Transaction::create([
                    'user_id' => Auth::id(),
                    'type' => 'activation',
                    'wallet_type' => 'savings',
                    'amount' => $request->fee_amount,
                    'description' => 'Savings account activation fee',
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

    public function setLockPeriod(Request $request, SavingsWallet $savings)
    {
        $request->validate([
            'lock_period' => 'required|integer|min:3',
            'saving_amount' => 'required|numeric|min:1000',
            'mpesa_phone_number' => 'required'
        ]);

        if ($savings->is_active !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Savings account must be active to set a lock period.',
            ], 400);
        }

        $mpesaController = new MpesaController();

        $amount = $request->saving_amount;
        $phoneNumber = '254' . $request->mpesa_phone_number;
        $accountReference = 'Deposit';
        $transactionDescription = 'Savings wallet deposit';

        $stkResponse = $mpesaController->initiateSTKPush($amount, $phoneNumber, $accountReference, $transactionDescription);

        try {
            DB::beginTransaction();
            if ($stkResponse['success']) {
                $stkDetails = $stkResponse['response'];

                $merchantRequestId = $stkDetails['MerchantRequestID'] ?? null;
                $checkoutRequestId = $stkDetails['CheckoutRequestID'] ?? null;
                $responseCode = $stkDetails['ResponseCode'] ?? null;

                if ($responseCode == '0') {
                    if ($savings->balance > 0) {
                        $savings->balance = 0;
                        $savings->allocated_funds = 0;
                        $savings->unallocated_funds = 0;

                        $mainWallet = Wallet::where('user_id', $savings->user_id)->lockForUpdate()->first();
                        $mainWallet->balance += $savings->balance;
                        $mainWallet->save();
                    }

                    $savings->is_locked = 1;
                    $savings->lock_period = $request->lock_period;
                    $savings->date_locked = now();
                    $savings->locked_until = now()->addMonths($request->lock_period);
                    $savings->save();

                    $savingsAccount = [
                        'is_active' => $savings->is_active,
                        'is_locked' => $savings->is_locked,
                        'lock_period' => $savings->lock_period,
                        'locked_until' => $savings->locked_until
                    ];

                    session([
                        'savingsaccount' => $savingsAccount,
                    ]);

                    $transaction = Transaction::create([
                        'user_id' => Auth::id(),
                        'type' => 'deposit',
                        'wallet_type' => 'savings',
                        'amount' => $request->saving_amount,
                        'description' => 'Savings wallet deposit',
                        'tracking_id_one' => $merchantRequestId,
                        'tracking_id_two' => $checkoutRequestId,
                    ]);

                    if (!$transaction) {
                        throw new \Exception('Failed to create transaction.');
                    }

                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'message' => 'Lock period set. ' . $stkResponse['message']
                    ]);
                } else {
                    throw new \Exception('STK Push initiation failed.');
                }
            }

            throw new \Exception($stkResponse['message']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ],);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'description' => 'nullable|string|max:255',
            'mpesa_phone_number' => 'required'
        ]);

        try {
            DB::beginTransaction();
            $mpesaController = new MpesaController();

            $amount = $request->amount;
            $phoneNumber = '254' . $request->mpesa_phone_number;
            $accountReference = 'Deposit';
            $transactionDescription = 'Savings wallet deposit';

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
                'user_id' => Auth::id(),
                'type' => 'deposit',
                'wallet_type' => 'savings',
                'amount' => $amount,
                'description' => $transactionDescription,
                'tracking_id_one' => $merchantRequestId,
                'tracking_id_two' => $checkoutRequestId,
            ]);

            if (!$transaction) {
                throw new \Exception('Failed to create transaction.');
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $stkResponse['message'],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function storeGoal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'goal_name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:1000',
            'target_date' => 'required|date|after:today'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            SavingsGoal::create([
                'user_id' => Auth::id(),
                'goal_name' => $request->goal_name,
                'target_amount' => $request->target_amount,
                'target_date' => $request->target_date
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Savings goal created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create savings goal'
            ]);
        }
    }


    public function updateGoal(Request $request, SavingsGoal $goal)
    {
        $validator = Validator::make($request->all(), [
            'target_amount' => 'required|numeric|min:1000',
            'target_date' => 'required|date|after:today'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();
            $allocatedAmount = $goal->allocated_amount;

            if ($goal->allocated_amount > $request->target_amount) {
                $overAllocation = $goal->allocated_amount - $request->target_amount;
                $allocatedAmount = $goal->$request->target_amount;

                $savings = SavingsWallet::where('user_id', $goal->user_id)->first();

                $savings->allocated_funds -= $overAllocation;
                $savings->unallocated_funds += $overAllocation;
                $savings->save();
            }

            $goal->update([
                'target_amount' => $request->target_amount,
                'target_date' => $request->target_date,
                'allocated_amount' => $allocatedAmount,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Savings goal updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update savings goal'
            ]);
        }
    }


    public function allocateFunds(Request $request, SavingsGoal $goal)
    {
        $request->validate([
            'allocation_amount' => 'required|numeric|min:1'
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $savings = SavingsWallet::where('user_id', $user->id)->first();

            if ($savings->unallocated_funds < $request->allocation_amount) {
                throw new \Exception('Insufficient funds available for allocation');
            }

            // Check if savings are still locked
            // $firstDeposit = Transaction::where('user_id', $user->id)
            //     ->where('type', 'deposit')
            //     ->orderBy('created_at', 'asc')
            //     ->first();

            // if ($firstDeposit && Carbon::now()->diffInDays(Carbon::parse($firstDeposit->created_at)) < 365) {
            //     throw new \Exception('Savings are locked for 365 days from first deposit');
            // }

            $goal->allocations()->create([
                'amount' => $request->allocation_amount
            ]);

            $goal->allocated_amount += $request->allocation_amount;
            $goal->save();

            $savings->allocated_funds += $request->allocation_amount;
            $savings->unallocated_funds -= $request->allocation_amount;
            $savings->save();

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'allocation',
                'wallet_type' => 'savings',
                'amount' => $request->allocation_amount,
                'description' => "Funds allocated to goal: {$goal->goal_name}",
                'status' => 'completed'
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Funds allocated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => true,
                'message' => $e->getMessage()
            ]);
        }
    }
}
