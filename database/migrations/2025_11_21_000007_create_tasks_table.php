<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('context_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->onDelete('cascade');
            
            $table->string('title');
            $table->text('description')->nullable();
            
            // GTD статусы
            $table->enum('status', [
                'inbox',        // Входящие
                'next_action',  // Следующие действия
                'waiting',      // Ожидание
                'someday',      // Когда-нибудь
                'completed'     // Выполнено
            ])->default('inbox');
            
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->date('due_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            
            $table->integer('position')->default(0); // Для сортировки
            
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('workspace_id');
            $table->index('project_id');
            $table->index('status');
            $table->index('assigned_to');
            $table->index('due_date');
            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};


