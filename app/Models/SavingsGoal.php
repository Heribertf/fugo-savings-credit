<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsGoal extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'goal_name', 'target_amount', 'target_date', 'allocated_amount'];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'target_date' => 'date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(GoalAllocation::class);
    }
}
