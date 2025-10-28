<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove columns that are now handled by the invoice_items table.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop columns that have been moved to invoice_items table
            $table->dropColumn(['item_id', 'item_description', 'quantity', 'profit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Add back the columns for rollback purposes
            $table->integer('item_id')->nullable();
            $table->text('item_description')->nullable();
            $table->integer('quantity')->default(0);
            $table->float('profit', 10, 2)->nullable();
        });
    }
};
