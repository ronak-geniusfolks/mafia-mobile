<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add the pending-upload token column.
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('upload_token', 64)->nullable()->index()->after('uploaded_by');
        });

        // Allow a "pending" attachment to exist before its parent (invoice) is saved.
        Schema::table('attachments', function (Blueprint $table) {
            $table->unsignedBigInteger('attachable_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropIndex(['upload_token']);
            $table->dropColumn('upload_token');
        });

        // Restore NOT NULL. (Any leftover pending rows must be cleared first.)
        Schema::table('attachments', function (Blueprint $table) {
            $table->unsignedBigInteger('attachable_id')->nullable(false)->change();
        });
    }
};
