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
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path'); // Путь в S3
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('task_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};


