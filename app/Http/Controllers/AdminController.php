<?php

namespace App\Http\Controllers;


use App\Events\SupportMessageSent;
use App\Events\AgentTyping;
use App\Events\NewChatNotification;
use App\Models\Loan;
use App\Models\SavingsWallet;
use App\Models\SupportMessage;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalSavings = SavingsWallet::sum('balance');
        $pendingWithdrawals = Transaction::where('type', 'withdrawal')
            ->where('status', 'pending')
            ->count();
        $activeLoans = Loan::where('status', 'active')->sum('amount');

        $recentActivities = Transaction::with('user')
            ->latest()
            ->take(5)
            ->get();

        $userGrowthData = User::select(DB::raw("COUNT(*) as count, DATE_FORMAT(created_at, '%M') as month"))
            ->groupBy('month')
            ->orderBy(DB::raw("STR_TO_DATE(month, '%M')"))
            ->pluck('count');
        $userGrowthMonths = User::select(DB::raw("DATE_FORMAT(created_at, '%M') as month"))
            ->groupBy('month')
            ->orderBy(DB::raw("STR_TO_DATE(month, '%M')"))
            ->pluck('month');

        $savingsTrendData = SavingsWallet::select(DB::raw("SUM(balance) as total, DATE_FORMAT(last_savings_date, '%M') as month"))
            ->groupBy('month')
            ->orderBy(DB::raw("STR_TO_DATE(month, '%M')"))
            ->pluck('total');
        $savingsTrendMonths = Transaction::where('wallet_type', 'savings')
            ->select(DB::raw("DATE_FORMAT(created_at, '%M') as month"))
            ->groupBy('month')
            ->orderBy(DB::raw("STR_TO_DATE(month, '%M')"))
            ->pluck('month');

        return view('admin.index', compact(
            'totalUsers',
            'totalSavings',
            'pendingWithdrawals',
            'activeLoans',
            'recentActivities',
            'userGrowthData',
            'userGrowthMonths',
            'savingsTrendData',
            'savingsTrendMonths'
        ));
        // return view('admin.index');
    }

    public function profile()
    {
        $admin = Auth::user();
        return view('admin.profile', compact('admin'));
    }


    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'phone_number' => 'nullable|string|max:20',
        ]);

        $admin = User::where('is', Auth::id());
        $admin->update($request->only('email', 'phone_number'));

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $admin = User::where('is', Auth::id());

        if (!Hash::check($request->current_password, $admin->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $admin->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->back()->with('success', 'Password changed successfully.');
    }



    public function getUsers()
    {
        $users = User::paginate(10);
        return view('admin.users', compact('users'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:12'],
            'email' => ['required', 'email', 'max:50', 'unique:users,email,' . $user->id],
            'role' => ['required', 'string', 'in:user,admin,support'],
        ]);

        try {
            $user->update([
                'phone_number' => $request->phone,
                'email' => $request->email,
                'role' => $request->role
            ]);

            return redirect()->back()->with('success', 'User details updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update user details: ' . $e->getMessage());
        }
    }

    public function resetUserPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'new_password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
        ]);

        try {
            $user->update([
                'password' => Hash::make($validated['new_password']),
            ]);

            return redirect()->back()->with('success', 'Password reset successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to reset password: ' . $e->getMessage());
        }
    }

    public function activateUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => true]);

        return redirect()->back()->with('success', 'User activated successfully.');
    }

    public function deactivateUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => false]);

        return redirect()->back()->with('success', 'User deactivated successfully.');
    }

    public function support()
    {
        return view('admin.support');
    }

    public function getActiveChats()
    {
        $activeChats = DB::table('support_messages')
            ->select(
                'users.id as user_id',
                'users.username as user_name',
                DB::raw('MAX(support_messages.created_at) as last_message_time'),
                DB::raw('COUNT(CASE WHEN support_messages.read = 0 AND support_messages.sender_type = "user" THEN 1 END) as unread_count')
            )
            ->join('users', 'users.id', '=', 'support_messages.user_id')
            ->groupBy('users.id', 'users.username')
            ->orderByDesc('last_message_time')
            ->get()
            ->map(function ($chat) {
                $lastMessage = SupportMessage::where('user_id', $chat->user_id)
                    ->orderByDesc('created_at')
                    ->first();

                return [
                    'user_id' => $chat->user_id,
                    'user_name' => $chat->user_name,
                    'last_message' => Str::limit($lastMessage->content, 50),
                    'unread_count' => $chat->unread_count,
                    'unread' => $chat->unread_count > 0,
                    'online' => $this->isUserOnline($chat->user_id),
                ];
            });

        return response()->json(['chats' => $activeChats]);
    }

    public function getMessages($userId)
    {
        $user = User::findOrFail($userId);
        $messages = SupportMessage::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'content' => $message->content,
                    'timestamp' => $message->created_at,
                    'type' => $message->sender_type,
                ];
            });

        return response()->json([
            'user_name' => $user->name,
            'online' => $this->isUserOnline($user->id),
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $message = SupportMessage::create([
            'user_id' => $request->user_id,
            'content' => $request->message,
            'sender_type' => 'agent',
        ]);

        // Broadcast the message
        broadcast(new SupportMessageSent($message))->toOthers();

        return response()->json(['success' => true]);
    }

    public function markAsRead($userId)
    {
        SupportMessage::where('user_id', $userId)
            ->where('sender_type', 'user')
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json(['success' => true]);
    }

    public function getUserInfo($userId)
    {
        $user = User::findOrFail($userId);

        $data = [
            'name' => $user->fullname,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'total_savings' => $user->savingsWallet->balance ?? 0,
            'active_loans' => $user->loans()->where('status', 'active')->count(),
        ];

        return response()->json($data);
    }

    public function endChat($userId)
    {
        SupportMessage::where('user_id', $userId)->delete();

        return response()->json(['success' => true]);
    }

    private function isUserOnline($userId)
    {
        // Logic to determine if the user is online, e.g., using session or Redis
        return cache()->has("user-is-online-{$userId}");
    }


    public function getWithdrawals()
    {
        $totalWithdrawals = Transaction::where('type', 'withdrawal')->count();
        $pendingWithdrawals = Transaction::where('type', 'withdrawal')
            ->where('status', 'pending')->count();
        $approvedToday = Transaction::where('type', 'withdrawal')
            ->where('status', 'approved')
            ->whereDate('updated_at', now())
            ->count();
        $totalAmount = Transaction::where('type', 'withdrawal')
            ->whereIn('status', ['approved', 'completed'])
            ->sum('amount');


        $withdrawals = Transaction::with('user')
            ->where('type', 'withdrawal')
            // ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.withdrawals', compact(
            'totalWithdrawals',
            'pendingWithdrawals',
            'approvedToday',
            'totalAmount',
            'withdrawals'
        ));
    }

    public function approveWithdrawals(Request $request, $id)
    {
        $request->validate([
            'reference' => 'required|string|max:50',
        ]);

        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'pending') {
            return redirect()->back()->with('error', 'This withdrawal has already been processed.');
        }

        $wallet = Wallet::where('user_id', $transaction->user_id)
            ->lockForUpdate()
            ->firstOrFail();

        $wallet->balance -= $transaction->amount;
        $wallet->save();

        // if ($transaction->amount < 1000) {
        //     $fee = 30;
        //     $receivable = $transaction->amount - $fee;
        // } elseif ($transaction->amount <= 5000) {
        //     $fee = 50;
        //     $receivable = $transaction->amount - $fee;
        // } else {
        //     $fee = $transaction->amount * 0.03;
        //     $receivable = $transaction->amount - $fee;
        // }

        $transaction->update([
            'status' => 'completed',
            'reference' => $request->reference,
        ]);

        return redirect()->back()->with('success', 'Withdrawal approved successfully.');
    }


    public function rejectWithdrawals($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Withdrawal rejected.');
    }

    public function getLoans()
    {
        $totalLoans = Loan::count();
        $pendingLoans = Loan::where('status', 'pending')->count();
        $approvedToday = Loan::where('status', 'approved')
            ->whereDate('updated_at', now())
            ->count();
        $totalAmount = Loan::whereNot('status', 'pending')->sum('amount');


        $loans = Loan::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.loans', compact(
            'totalLoans',
            'pendingLoans',
            'approvedToday',
            'totalAmount',
            'loans'
        ));
    }
}
