<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration transfers existing invoice data to the new invoice_items table structure.
     */
    public function up(): void
    {
        // Get all existing invoices with item data
        $invoices = DB::table('invoices')
            ->where('deleted', 0)
            ->whereNotNull('item_id')
            ->get();

        foreach ($invoices as $invoice) {
            // Get the purchase to calculate unit price
            $purchase = DB::table('purchases')->where('id', $invoice->item_id)->first();
            
            $unitPrice = $invoice->total_amount ?? 0;
            
            // Calculate profit if purchase exists
            $profit = 0;
            if ($purchase && isset($purchase->purchase_price)) {
                $profit = $unitPrice - $purchase->purchase_price;
            } else {
                $profit = $invoice->profit ?? 0;
            }

            // Create invoice item
            DB::table('invoice_items')->insert([
                'invoice_id' => $invoice->id,
                'item_id' => $invoice->item_id,
                'item_description' => $invoice->item_description,
                'quantity' => $invoice->quantity ?? 1,
                'unit_price' => $unitPrice,
                'total_amount' => $invoice->total_amount ?? 0,
                'profit' => $profit,
                'deleted' => $invoice->deleted ?? 0,
                'created_at' => $invoice->created_at ?? now(),
                'updated_at' => $invoice->updated_at ?? now(),
            ]);
        }

        // Handle invoices without item_id (if any exist)
        $invoicesWithoutItems = DB::table('invoices')
            ->where('deleted', 0)
            ->whereNull('item_id')
            ->get();

        foreach ($invoicesWithoutItems as $invoice) {
            DB::table('invoice_items')->insert([
                'invoice_id' => $invoice->id,
                'item_id' => null,
                'item_description' => $invoice->item_description,
                'quantity' => $invoice->quantity ?? 1,
                'unit_price' => 0,
                'total_amount' => $invoice->total_amount ?? 0,
                'profit' => $invoice->profit ?? 0,
                'deleted' => $invoice->deleted ?? 0,
                'created_at' => $invoice->created_at ?? now(),
                'updated_at' => $invoice->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete all invoice items
        DB::table('invoice_items')->truncate();
    }
};
