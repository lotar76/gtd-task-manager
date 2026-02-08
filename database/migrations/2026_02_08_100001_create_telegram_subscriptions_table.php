<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('chat_id')->nullable();
            $table->string('link_token', 64)->unique();
            $table->boolean('is_active')->default(false);
            $table->string('morning_digest_time')->default('08:00');
            $table->integer('reminder_minutes_before')->default(30);
            $table->boolean('notify_overdue')->default(true);
            $table->boolean('notify_morning_digest')->default(true);
            $table->boolean('notify_reminders')->default(true);
            $table->timestamps();

            $table->unique(['workspace_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_subscriptions');
    }
};
