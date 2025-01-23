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
        Schema::table('savings', function (Blueprint $table) {
            $table->date('first_deposit_date')->nullable();
            $table->boolean('is_locked')->default(true);
        });

        Schema::table('savings_wallets', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->boolean('is_active')->default(false)->after('last_savings_date');
            $table->boolean('is_locked')->default(true);
            $table->date('first_deposit_date')->nullable();
        });

        Schema::table('savings_wallets', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('savings_goals', function (Blueprint $table) {
            $table->string('goal_name')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings', function (Blueprint $table) {
            $table->dropColumn('is_locked');
            $table->dropColumn('first_deposit_date');
        });

        Schema::table('savings_wallets', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropColumn('user_id');
            $table->dropColumn('is_locked');
            $table->dropColumn('first_deposit_date');
        });

        Schema::table('savings_goals', function (Blueprint $table) {
            $table->dropColumn('goal_name');
        });
    }
};
