#!/bin/bash

# Скрипт для настройки cron для Laravel Scheduler

set -e

# Определяем путь к проекту автоматически (откуда запущен скрипт)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_PATH="$(cd "$SCRIPT_DIR/.." && pwd)"
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
if [ -d "$PROJECT_PATH" ] && [ -f "$PROJECT_PATH/artisan" ]; then
    cd "$PROJECT_PATH" && php artisan schedule:list
else
    echo "⚠️  Предупреждение: Не удалось найти проект по пути $PROJECT_PATH"
    echo "   Убедитесь, что путь правильный и artisan файл существует"
fi
echo ""
echo "✅ Готово! Laravel Scheduler будет запускаться каждую минуту."

