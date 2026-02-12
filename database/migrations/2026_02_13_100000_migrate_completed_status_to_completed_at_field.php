<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Мигрирует задачи со status='completed' на использование поля completed_at.
     *
     * Для всех задач со status='completed':
     * - Устанавливает completed_at = updated_at (или now() если updated_at пуст)
     * - Меняет status на 'next_action'
     */
    public function up(): void
    {
        DB::table('tasks')
            ->where('status', 'completed')
            ->update([
                'completed_at' => DB::raw('COALESCE(updated_at, NOW())'),
                'status' => 'next_action',
            ]);
    }

    /**
     * Откат: возвращает задачи с completed_at обратно в status='completed'.
     */
    public function down(): void
    {
        DB::table('tasks')
            ->whereNotNull('completed_at')
            ->update([
                'status' => 'completed',
                'completed_at' => null,
            ]);
    }
};
