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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(table: 'users')->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdrawal', 'referral_bonus', 'loan_disbursement', 'loan_repayment', 'fee', 'interest_bonus']);
            $table->enum('wallet_type', ['available', 'savings']);
            $table->decimal('amount', 10, 2);
            $table->decimal('fee', 10, 2)->default(0);
            $table->string('description')->nullable();
            $table->string('reference')->unique();
            $table->enum('status', ['pending', 'rejected', 'approved', 'completed', 'failed'])->default('pending');
            $table->foreignId('approved_by')->constrained(table: 'users')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
