<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_mirror_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->string('period', 10);
            $table->string('period_key', 20);
            $table->json('response_json');
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->unique(['workspace_id', 'period', 'period_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_mirror_cache');
    }
};
