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
        // Изменяем enum для добавления статуса 'today'
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('inbox', 'next_action', 'today', 'waiting', 'someday', 'completed') DEFAULT 'inbox'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Перед удалением статуса 'today', преобразуем все задачи с этим статусом в 'next_action'
        DB::table('tasks')->where('status', 'today')->update(['status' => 'next_action']);
        
        // Возвращаем старый enum без 'today'
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('inbox', 'next_action', 'waiting', 'someday', 'completed') DEFAULT 'inbox'");
    }
};

