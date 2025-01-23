<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'first_deposit_date',
        'last_transaction_date',
        'is_active',
    ];

    /**
     * Get the user that owns the wallet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the savings wallet associated with the main wallet.
     */
    public function savingsWallet()
    {
        return $this->hasOne(SavingsWallet::class);
    }
}
