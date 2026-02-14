<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->text('bible_verse')->nullable()->after('deadline');
            $table->string('image_path')->nullable()->after('bible_verse');
            $table->string('image_url')->nullable()->after('image_path');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('goal_id')->nullable()->after('project_id')
                ->constrained()->onDelete('set null');
            $table->index('goal_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['goal_id']);
            $table->dropIndex(['goal_id']);
            $table->dropColumn('goal_id');
        });

        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn(['bible_verse', 'image_path', 'image_url']);
        });
    }
};
