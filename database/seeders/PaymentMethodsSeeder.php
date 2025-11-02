<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $paymentMethods = [
        //     [
        //         'method_name' => 'Cash',
        //         'slug' => 'cash',
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        //     [
        //         'method_name' => 'Credit Card',
        //         'slug' => 'CC',
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        //     [
        //         'method_name' => 'UPI',
        //         'slug' => 'UPI',
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        //     [
        //         'method_name' => 'Other',
        //         'slug' => 'other',
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        // ];

        // DB::table('payment_methods')->insert($paymentMethods);
    }
}