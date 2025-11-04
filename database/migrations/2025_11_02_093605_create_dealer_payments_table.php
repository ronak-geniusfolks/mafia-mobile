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
        Schema::create('dealer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealer_id')->constrained('dealers')->onDelete('restrict');
            $table->foreignId('bill_id')->nullable()->constrained('bills')->onDelete('restrict');
            $table->float('payment_amount', 10, 2);
            $table->date('payment_date');
            $table->string('payment_type')->default('cash')->comment('cash or credit');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['dealer_id', 'payment_date']);
            $table->index('bill_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealer_payments');
    }
};
