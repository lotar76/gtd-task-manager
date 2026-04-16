<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Файл был переименован из 2025_01_15_000000. На prod миграция уже
        // применена под старым именем — обновим запись, чтобы Laravel
        // не пытался выполнить ALTER повторно.
        DB::table('migrations')
            ->where('migration', '2025_01_15_000000_add_end_time_to_tasks_table')
            ->update(['migration' => '2025_11_22_000000_add_end_time_to_tasks_table']);

        if (!Schema::hasColumn('tasks', 'end_time')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->time('end_time')->nullable()->after('estimated_time');
            });
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'end_time')) {
                $table->dropColumn('end_time');
            }
        });
    }
};
