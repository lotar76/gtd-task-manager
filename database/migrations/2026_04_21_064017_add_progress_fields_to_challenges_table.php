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
            $table->integer('progress_start')->nullable()->after('subtasks');
            $table->integer('progress_step')->nullable()->after('progress_start');
            $table->integer('progress_end')->nullable()->after('progress_step');
            $table->integer('progress_sets')->nullable()->after('progress_end');
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn(['progress_start', 'progress_step', 'progress_end', 'progress_sets']);
        });
    }
};
