<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Savings extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'amount', 'is_locked', 'first_deposit_date'];
}
