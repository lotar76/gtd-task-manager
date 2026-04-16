<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_contact', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->string('role', 20)->default('informed'); // creator, executor, informed
            $table->timestamps();

            $table->unique(['task_id', 'contact_id']);
            $table->index('task_id');
            $table->index('contact_id');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_contact');
    }
};
