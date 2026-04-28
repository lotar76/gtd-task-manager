<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: remove old columns, add new ones
        if (!Schema::hasColumn('articles', 'content')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropColumn(['author', 'link']);
                $table->longText('content')->nullable()->after('title');
                $table->foreignId('article_author_id')->nullable()->after('content')
                    ->constrained('article_authors')->nullOnDelete();
            });
        }

        // Step 2: make article_folder_id NOT NULL - drop old FK (SET NULL), change column, re-add FK (CASCADE)
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['article_folder_id']);
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->unsignedBigInteger('article_folder_id')->nullable(false)->change();
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->foreign('article_folder_id')->references('id')->on('article_folders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['article_folder_id']);
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->unsignedBigInteger('article_folder_id')->nullable()->change();
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->foreign('article_folder_id')->references('id')->on('article_folders')->nullOnDelete();
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('article_author_id');
            $table->dropColumn('content');
            $table->string('author')->nullable();
            $table->string('link', 2048)->nullable();
        });
    }
};
