<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'referral_code',
        'referred_by',
        'is_active',
        'kyc_status',
        'kyc_submitted_at',
        'last_login',
        'is_system_admin',
        'verification_code',
        'email_verified',
        'email_verified_at',
        'verification_code_expiry',
        'password_reset_code',
        'profile_picture',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'timezone',
        'currency',
        'language',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function savingsWallet()
    {
        return $this->hasOne(SavingsWallet::class);
    }

    public function getTotalSavingsAttribute()
    {
        return $this->savingsWallet->balance ?? 0;
    }
}
