<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'type',
        'wallet_type',
        'amount',
        'fee',
        'description',
        'reference',
        'status',
        'approved_by',
        'transaction_id',
        'tracking_id_one',
        'tracking_id_two'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // public function getCreatedAtAttribute($value)
    // {
    //     return Carbon::parse($value)->format('d-M-Y');
    // }
}
