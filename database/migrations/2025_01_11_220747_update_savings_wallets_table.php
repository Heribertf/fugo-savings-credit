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
        Schema::table('savings_wallets', function (Blueprint $table) {
            $table->decimal('allocated_funds', 15, 2)->default(0.00);
            $table->decimal('unallocated_funds', 15, 2)->default(0.00);
        });

        Schema::table('savings_goals', function (Blueprint $table) {
            $table->decimal('allocated_amount', 15, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings_wallets', function (Blueprint $table) {
            $table->dropColumn('allocated_funds');
            $table->dropColumn('unallocated_funds');
        });

        Schema::table('savings_goals', function (Blueprint $table) {
            $table->dropColumn('allocated_amount');
        });
    }
};
