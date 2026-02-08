<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Удаляем таблицу настроек бота (переносим в .env)
        Schema::dropIfExists('telegram_settings');

        // Перестраиваем telegram_subscriptions: убираем workspace_id, делаем user-level
        Schema::table('telegram_subscriptions', function (Blueprint $table) {
            // Удаляем старый уникальный индекс
            $table->dropUnique(['workspace_id', 'user_id']);

            // Удаляем foreign key и колонку workspace_id
            $table->dropForeign(['workspace_id']);
            $table->dropColumn('workspace_id');

            // Добавляем default_workspace_id для создания задач из Telegram
            $table->foreignId('default_workspace_id')->nullable()->after('user_id')
                ->constrained('workspaces')->onDelete('set null');

            // Делаем user_id уникальным (одна подписка на пользователя)
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('telegram_subscriptions', function (Blueprint $table) {
            $table->dropUnique(['user_id']);

            $table->dropForeign(['default_workspace_id']);
            $table->dropColumn('default_workspace_id');

            $table->foreignId('workspace_id')->after('id')
                ->constrained()->onDelete('cascade');

            $table->unique(['workspace_id', 'user_id']);
        });

        // Восстанавливаем таблицу telegram_settings
        Schema::create('telegram_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->unique()->constrained()->onDelete('cascade');
            $table->text('bot_token');
            $table->string('bot_username')->nullable();
            $table->string('webhook_secret', 64)->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }
};
