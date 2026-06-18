<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');          // attachable_type + attachable_id
            $table->string('file_path');            // relative path inside public/attachments/
            $table->string('file_name');            // original filename shown to user
            $table->string('file_type')->nullable(); // mime type e.g. image/jpeg
            $table->unsignedInteger('file_size')->nullable(); // bytes
            $table->string('label')->nullable();    // e.g. "Aadhaar", "PAN", "Original Bill"
            $table->integer('uploaded_by')->nullable(); // user id; null = mobile (no auth) upload
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
