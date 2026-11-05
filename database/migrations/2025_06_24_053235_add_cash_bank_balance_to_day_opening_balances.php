<?php

declare(strict_types=1);

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
        Schema::table('day_opening_balances', function (Blueprint $table) {
            $table->decimal('cash_balance', 15, 2)->default(0)->after('balance');
            $table->decimal('bank_balance', 15, 2)->default(0)->after('cash_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_opening_balances', function (Blueprint $table) {
            $table->dropColumn('cash_balance');
            $table->dropColumn('bank_balance');
        });
    }
};
