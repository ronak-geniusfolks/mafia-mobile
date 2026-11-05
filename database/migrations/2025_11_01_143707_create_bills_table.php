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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealer_id')->constrained('dealers')->onDelete('restrict');
            $table->date('bill_date')->nullable();
            $table->string('bill_no')->unique();
            $table->float('total_amount', 10, 2)->nullable();
            $table->float('cgst_rate', 10, 2)->nullable();
            $table->float('sgst_rate', 10, 2)->nullable();
            $table->float('igst_rate', 10, 2)->nullable();
            $table->float('cgst_amount', 10, 2)->nullable();
            $table->float('sgst_amount', 10, 2)->nullable();
            $table->float('igst_amount', 10, 2)->nullable();
            $table->float('discount_rate', 10, 2)->nullable();
            $table->float('tax_amount', 10, 2)->nullable();
            $table->float('net_amount', 10, 2)->nullable();
            $table->float('discount', 10, 2)->nullable();
            $table->float('profit', 10, 2)->nullable();
            $table->text('declaration')->nullable();
            $table->tinyInteger('is_paid')->default(0);
            $table->integer('bill_by')->nullable();
            $table->string('payment_type')->nullable()->comment('cash or credit');
            $table->float('cash_amount', 10, 2)->default(0)->comment('Amount paid in cash');
            $table->float('credit_amount', 10, 2)->default(0)->comment('Remaining amount (credit)');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
