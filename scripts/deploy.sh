#!/bin/bash

# Скрипт развертывания для production

set -e

echo "🚀 Начало развертывания в production..."

# Проверка окружения
if [ "$APP_ENV" != "production" ]; then
    echo "⚠️  Этот скрипт предназначен только для production окружения!"
    exit 1
fi

# Включение maintenance режима
echo "🔧 Включение maintenance режима..."
docker compose exec -T app php artisan down

# Получение последних изменений
echo "📥 Получение последних изменений из git..."
git pull origin main

# Сборка и перезапуск контейнеров
echo "🐳 Пересборка Docker контейнеров..."
docker compose up -d --build

# Установка зависимостей
echo "📦 Установка зависимостей..."
docker compose exec -T app composer install --no-dev --optimize-autoloader

# Запуск миграций
echo "🗄️  Запуск миграций..."
docker compose exec -T app php artisan migrate --force

# Очистка и кеширование
echo "🧹 Очистка и оптимизация..."
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache

# Настройка прав доступа
echo "🔐 Настройка прав доступа..."
docker compose exec -T app chmod -R 775 storage bootstrap/cache
docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache

# Выключение maintenance режима
echo "✅ Выключение maintenance режима..."
docker compose exec -T app php artisan up

echo "🎉 Развертывание завершено успешно!"

