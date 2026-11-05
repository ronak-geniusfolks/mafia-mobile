<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // check if sync_contact column exists
        if (! Schema::hasColumn('invoices', 'sync_contact')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->boolean('sync_contact')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // check if sync_contact column exists
        if (Schema::hasColumn('invoices', 'sync_contact')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('sync_contact');
            });
        }
    }
};
