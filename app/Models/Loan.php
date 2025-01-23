<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'amount',
        'interest',
        'disbursed_amount',
        'total_repaid',
        'due_date',
        'status',
        'approved_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getIsDefaultedAttribute()
    {
        if ($this->due_date && Carbon::parse($this->due_date)->isPast() && $this->payment_status != 'paid') {
            return true;
        }
        return false;
    }
}
