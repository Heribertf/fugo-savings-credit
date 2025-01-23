<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralsController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/loans', [LoanController::class, 'index'])->name('loans');
    Route::post('/loans/request', [LoanController::class, 'request'])->name('loans.request');
    Route::post('/loans/{loan}/repay', [LoanController::class, 'repay'])->name('loans.repay');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');


    Route::get('/savings', [SavingsController::class, 'index'])->name('savings');
    Route::post('/savings/activate', [SavingsController::class, 'activate'])->name('savings.activate');
    Route::post('/savings/{savings}/lock', [SavingsController::class, 'setLockPeriod'])->name('savings.lockPeriod');

    Route::post('/savings', [SavingsController::class, 'store'])->name('savings.store');
    Route::post('/savings/goals', [SavingsController::class, 'storeGoal'])->name('savings.goals.store');
    Route::put('/savings/goals/{goal}', [SavingsController::class, 'updateGoal'])->name('savings.goals.update');
    Route::post('/savings/goals/{goal}/allocate', [SavingsController::class, 'allocateFunds'])->name('savings.goals.allocate');


    Route::get('/referrals', [ReferralsController::class, 'index'])->name('referrals');
    Route::get('/referrals/load-more', [ReferralsController::class, 'loadMore'])->name('referrals.load-more');

    Route::get('/wallets', [WalletController::class, 'index'])->name('wallets');
    Route::post('/wallets/deposit', [WalletController::class, 'deposit'])->name('wallets.deposit');
    Route::post('/wallets/withdraw', [WalletController::class, 'withdraw'])->name('wallets.withdraw');
    Route::post('/wallets/transfer', [WalletController::class, 'transfer'])->name('wallets.transfer');


    Route::get('/support', [SupportController::class, 'index'])->name('support');
    Route::get('/support/get-messages', [SupportController::class, 'getMessages']);
    Route::post('/support/send-message', [SupportController::class, 'sendMessage']);
    Route::post('/support/typing', [SupportController::class, 'typing']);

    Route::get('/kyc', [KycController::class, 'index'])->name('kyc');
    Route::post('/kyc/send-otp', [KycController::class, 'sendOtp']);
    Route::post('/kyc/verify-otp', [KycController::class, 'verifyOtp']);
});


Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

    Route::get('/profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::post('/profile/update', [AdminController::class, 'updateProfile'])->name('admin.profile.update');
    Route::post('/profile/change-password', [AdminController::class, 'changePassword'])->name('admin.profile.change-password');

    // User management
    Route::get('/users', [AdminController::class, 'getUsers'])->name('admin.users');
    Route::post('/users/{id}/activate', [AdminController::class, 'activateUser'])->name('admin.users.activate');
    Route::post('/users/{id}/deactivate', [AdminController::class, 'deactivateUser'])->name('admin.users.deactivate');
    Route::post('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/users/{user}/reset-password', [AdminController::class, 'resetUserPassword'])->name('admin.users.reset-password');

    // Withdrawal management
    Route::get('/withdrawals', [AdminController::class, 'getWithdrawals'])->name('admin.withdrawals');
    Route::post('/withdrawals/{id}/approve', [AdminController::class, 'approveWithdrawals'])->name('admin.withdrawals.approve');
    Route::post('/withdrawals/{id}/reject', [AdminController::class, 'rejectWithdrawals'])->name('admin.withdrawals.reject');

    // Loan management
    Route::get('/loans', [AdminController::class, 'getLoans'])->name('admin.loans');
    Route::post('/loans/{id}/approve', [LoanController::class, 'approveLoans'])->name('admin.loans.approve');
    Route::post('/loans/{id}/reject', [LoanController::class, 'rejectLoans'])->name('admin.loans.reject');

    // Support management
    Route::get('/support', [AdminController::class, 'support'])->name('admin.support');

    Route::get('/support/active-chats', [AdminController::class, 'getActiveChats'])->name('admin.getActiveChats');
    Route::get('/support/messages/{userId}', [AdminController::class, 'getMessages'])->name('admin.getMessages');
    Route::post('/support/mark-read/{userId}', [AdminController::class, 'markAsRead'])->name('admin.markAsRead');
    Route::post('/support/send-message', [AdminController::class, 'sendMessage'])->name('admin.sendMessage');

    Route::get('/support/user-info/{userId}', [AdminController::class, 'getUserInfo'])->name('admin.getUserInfo');
    Route::post('/support/end-chat/{userId}', [AdminController::class, 'endChat'])->name('admin.endChat');
    // Route::get('/support/send-message/{userId}', [AdminController::class, 'isUserOnline'])->name('admin.isUserOnline');
    Route::post('/support/{id}/respond', [AdminController::class, 'respond'])->name('admin.support.respond');
});

require __DIR__ . '/auth.php';
