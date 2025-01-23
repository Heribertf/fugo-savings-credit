<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpesaTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'phone_number',
        'amount',
        'account_reference',
        'transaction_type',
        'transaction_date',
        'status',
    ];
}
