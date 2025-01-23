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
            $table->timestamp('locked_until')->nullable()->after('is_locked');
            $table->integer('lock_period')->nullable()->after('is_locked');
            $table->timestamp('date_locked')->nullable()->after('is_locked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings_wallets', function (Blueprint $table) {
            $table->dropColumn('date_locked');
            $table->dropColumn('lock_period');
            $table->dropColumn('locked_until');
        });
    }
};
