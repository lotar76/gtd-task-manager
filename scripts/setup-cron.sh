#!/bin/bash

# Скрипт для настройки cron для Laravel Scheduler

set -e

PROJECT_PATH="/home/projects/api"
CRON_JOB="* * * * * cd $PROJECT_PATH && php artisan schedule:run >> /dev/null 2>&1"

echo "🔧 Настройка cron для Laravel Scheduler..."
echo ""

# Получаем текущий crontab
CURRENT_CRON=$(crontab -l 2>/dev/null || echo "")

# Проверяем существующий crontab
if echo "$CURRENT_CRON" | grep -q "artisan schedule:run"; then
    echo "⚠️  Cron задача для Laravel Scheduler уже существует"
    echo ""
    echo "Удаляем старую задачу и добавляем новую..."
    # Удаляем старую задачу
    echo "$CURRENT_CRON" | grep -v "artisan schedule:run" | crontab -
fi

# Добавляем новую задачу
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

echo "✅ Cron задача добавлена успешно!"
echo ""
echo "Добавленная задача:"
echo "$CRON_JOB"
echo ""
echo "Текущие задачи cron:"
crontab -l
echo ""
echo "📋 Проверка расписания Laravel:"
cd "$PROJECT_PATH" && php artisan schedule:list
echo ""
echo "✅ Готово! Laravel Scheduler будет запускаться каждую минуту."

