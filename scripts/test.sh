#!/bin/bash

# Скрипт запуска тестов

set -e

echo "🧪 Запуск тестов..."

docker compose exec -T app php artisan test "$@"

echo "✅ Тесты завершены!"

