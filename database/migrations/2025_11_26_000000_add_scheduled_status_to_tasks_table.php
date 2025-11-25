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
        // Изменяем enum для добавления статуса 'scheduled'
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('inbox', 'next_action', 'today', 'tomorrow', 'waiting', 'someday', 'scheduled', 'completed') DEFAULT 'inbox'");
        
        // Преобразуем существующие задачи с due_date (не today/tomorrow) в статус 'scheduled'
        $today = now()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');
        
        DB::table('tasks')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '!=', $today)
            ->whereDate('due_date', '!=', $tomorrow)
            ->whereNotIn('status', ['completed', 'today', 'tomorrow'])
            ->update(['status' => 'scheduled']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Перед удалением статуса 'scheduled', преобразуем все задачи с этим статусом в 'next_action'
        DB::table('tasks')->where('status', 'scheduled')->update(['status' => 'next_action']);
        
        // Возвращаем старый enum без 'scheduled'
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('inbox', 'next_action', 'today', 'tomorrow', 'waiting', 'someday', 'completed') DEFAULT 'inbox'");
    }
};

