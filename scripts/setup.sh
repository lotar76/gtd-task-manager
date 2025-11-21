#!/bin/bash

# Скрипт первоначальной настройки Laravel API проекта

set -e

echo "🚀 Начало настройки Laravel API проекта..."

# Проверка наличия .env файла
if [ ! -f .env ]; then
    echo "📝 Создание .env файла..."
    cp .env.example .env
    echo "⚠️  Не забудьте настроить переменные окружения в .env файле!"
fi

# Проверка наличия сети traefik
if ! docker network inspect traefik &> /dev/null; then
    echo "🌐 Создание Docker сети traefik..."
    docker network create traefik
fi

# Сборка и запуск контейнеров
echo "🐳 Сборка и запуск Docker контейнеров..."
docker compose up -d --build

# Ожидание запуска контейнера
echo "⏳ Ожидание запуска контейнера..."
sleep 5

# Установка зависимостей
echo "📦 Установка Composer зависимостей..."
docker compose exec -T app composer install --optimize-autoloader

# Генерация ключа приложения
echo "🔑 Генерация ключа приложения..."
docker compose exec -T app php artisan key:generate

# Создание директорий storage
echo "📁 Настройка прав доступа..."
docker compose exec -T app chmod -R 775 storage bootstrap/cache
docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache

# Запуск миграций
echo "🗄️  Запуск миграций базы данных..."
docker compose exec -T app php artisan migrate --force

# Запуск сидеров
echo "🌱 Запуск сидеров..."
docker compose exec -T app php artisan db:seed --force

echo "✅ Настройка завершена!"
echo ""
echo "🌐 API доступен по адресу: https://api.local.test"
echo ""
echo "📚 Примеры использования API:"
echo "  - Регистрация: curl -X POST https://api.local.test/api/v1/register -H 'Content-Type: application/json' -d '{\"name\":\"Test\",\"email\":\"test@example.com\",\"password\":\"password123\",\"password_confirmation\":\"password123\"}'"
echo "  - Вход: curl -X POST https://api.local.test/api/v1/login -H 'Content-Type: application/json' -d '{\"email\":\"test@example.com\",\"password\":\"password123\"}'"
echo ""
echo "📖 Полная документация: см. README.md"

