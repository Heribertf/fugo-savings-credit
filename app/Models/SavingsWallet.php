<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'balance',
        'last_savings_date',
        'is_active',
        'is_locked',
        'date_locked',
        'lock_period',
        'locked_until',
        'first_deposit_date',
        'allocated_funds',
        'unallocated_funds'
    ];

    /**
     * Get the main wallet that owns this savings wallet.
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
