<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->string('type', 20)->default('checkbox')->after('title');
            $table->integer('timer_minutes')->nullable()->after('type');
            $table->json('subtasks')->nullable()->after('timer_minutes');
        });

        Schema::table('challenge_entries', function (Blueprint $table) {
            $table->json('subtask_states')->nullable()->after('completed');
            $table->integer('timer_seconds')->nullable()->after('subtask_states');
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn(['type', 'timer_minutes', 'subtasks']);
        });

        Schema::table('challenge_entries', function (Blueprint $table) {
            $table->dropColumn(['subtask_states', 'timer_seconds']);
        });
    }
};
