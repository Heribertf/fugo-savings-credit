<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ReferralsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $referralCode = $user->referral_code;
        $directEarnings = $this->getDirectEarnings($user);
        $indirectEarnings = $this->getIndirectEarnings($user);

        // Get referral statistics
        $totalReferrals = User::where('referred_by', $user->id)->count();
        $activeReferrals = User::where('referred_by', $user->id)
            ->where('activated_savings', 1)
            ->count();
        $pendingReferrals = User::where('referred_by', $user->id)
            ->where('activated_savings', 0)
            ->count();

        // Get initial referral history
        $referralHistory = $this->getReferralHistory($user, 0);

        return view('referrals', compact(
            'referralCode',
            'directEarnings',
            'indirectEarnings',
            'totalReferrals',
            'activeReferrals',
            'pendingReferrals',
            'referralHistory'
        ));
    }

    public function loadMore(Request $request)
    {
        $page = $request->input('page', 0);
        $user = auth()->user();

        $referralHistory = $this->getReferralHistory($user, $page);

        return response()->json([
            'html' => view('partials.referral-history-items', compact('referralHistory'))->render(),
            'hasMore' => count($referralHistory) == 10
        ]);
    }

    private function getReferralHistory($user, $page)
    {
        $perPage = 10;
        $offset = $page * $perPage;

        $directReferrals = User::where('referred_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($perPage)
            ->get();

        $indirectReferrals = User::whereIn('referred_by', $directReferrals->pluck('id'))
            ->where('referred_by', '!=', $user->id)
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($perPage)
            ->get();

        return $directReferrals->map(function ($referral) {
            return [
                'name' => $referral->fullname,
                'created_at' => $referral->created_at,
                'referralBonus' => 100,
                'type' => 'direct',
                'status' => $referral->activated_savings == 1 ? 'active' : 'pending'
            ];
        })->merge(
            $indirectReferrals->map(function ($referral) {
                return [
                    'name' => $referral->name,
                    'created_at' => $referral->created_at,
                    'referralBonus' => 50,
                    'type' => 'indirect',
                    'status' => $referral->activated_savings == 1 ? 'active' : 'pending'
                ];
            })
        )->sortByDesc('created_at')
            ->values()
            ->take($perPage)
            ->toArray();
    }

    private function getDirectEarnings($user)
    {
        return Transaction::where('user_id', $user->id)
            ->where('type', 'referral_bonus')
            ->where('description', 'Direct referral bonus')
            ->where('status', 'completed')
            ->sum('amount');
    }

    private function getIndirectEarnings($user)
    {
        return Transaction::where('user_id', $user->id)
            ->where('type', 'referral_bonus')
            ->where('description', 'Indirect referral bonus')
            ->where('status', 'completed')
            ->sum('amount');
    }

    private function paginateArray($items, $perPage = 10)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($items->toArray(), ($currentPage - 1) * $perPage, $perPage);
        return new LengthAwarePaginator($currentItems, count($items), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);
    }
}
