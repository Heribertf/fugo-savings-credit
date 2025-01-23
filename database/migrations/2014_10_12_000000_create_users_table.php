<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 255);
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('phone_number', 255);
            $table->string('referral_code', 10)->nullable();
            $table->bigInteger('referred_by')->nullable();
            $table->boolean('is_active')->default(0);
            $table->enum('kyc_status', ['PENDING', 'SUBMITTED', 'VERIFIED', 'REJECTED'])->default('PENDING');
            $table->timestamp('kyc_submitted_at')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->boolean('is_system_admin')->default(0);
            $table->string('verification_code', 255)->nullable();
            $table->boolean('email_verified')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->dateTime('verification_code_expiry')->nullable();
            $table->string('password_reset_code', 255)->nullable();
            $table->string('profile_picture', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->string('timezone', 100)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('language', 10)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
