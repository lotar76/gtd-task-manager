<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Изменяем enum для добавления статуса 'tomorrow'
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('inbox', 'next_action', 'today', 'tomorrow', 'waiting', 'someday', 'completed') DEFAULT 'inbox'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Перед удалением статуса 'tomorrow', преобразуем все задачи с этим статусом в 'next_action'
        DB::table('tasks')->where('status', 'tomorrow')->update(['status' => 'next_action']);
        
        // Возвращаем старый enum без 'tomorrow'
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('inbox', 'next_action', 'today', 'waiting', 'someday', 'completed') DEFAULT 'inbox'");
    }
};


