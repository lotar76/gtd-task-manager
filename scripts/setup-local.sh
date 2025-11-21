#!/bin/bash

# Скрипт настройки для локальной разработки (БЕЗ Traefik)

set -e

echo "🚀 Начало настройки для ЛОКАЛЬНОЙ разработки..."

# Проверка наличия .env файла
if [ ! -f .env ]; then
    echo "📝 Создание .env файла из .env.local..."
    cp .env.local .env
fi

# Сборка и запуск контейнеров
echo "🐳 Сборка и запуск Docker контейнеров (с Nginx)..."
docker-compose -f docker-compose.local.yml up -d --build

# Ожидание запуска контейнера
echo "⏳ Ожидание запуска контейнеров..."
sleep 5

# Проверка статуса
docker-compose -f docker-compose.local.yml ps

# Установка зависимостей
echo "📦 Установка Composer зависимостей..."
docker-compose -f docker-compose.local.yml exec -T app composer install --optimize-autoloader

# Генерация ключа приложения
echo "🔑 Генерация ключа приложения..."
docker-compose -f docker-compose.local.yml exec -T app php artisan key:generate

# Создание директорий storage
echo "📁 Настройка прав доступа..."
docker-compose -f docker-compose.local.yml exec -T app chmod -R 775 storage bootstrap/cache || true

# Запуск миграций
echo "🗄️  Запуск миграций базы данных..."
docker-compose -f docker-compose.local.yml exec -T app php artisan migrate --force

# Запуск сидеров
echo "🌱 Запуск сидеров (роли и права)..."
docker-compose -f docker-compose.local.yml exec -T app php artisan db:seed --force

echo "✅ Настройка завершена!"
echo ""
echo "🌐 API доступен по адресу: http://localhost:8000"
echo ""
echo "📚 Примеры использования API:"
echo "  - Регистрация:"
echo "    curl -X POST http://localhost:8000/api/v1/register \\"
echo "      -H 'Content-Type: application/json' \\"
echo "      -d '{\"name\":\"Test\",\"email\":\"test@example.com\",\"password\":\"password123\",\"password_confirmation\":\"password123\"}'"
echo ""
echo "📖 Полная документация: см. README.md"
echo ""
echo "🛠️  Полезные команды:"
echo "  - Просмотр логов: docker-compose -f docker-compose.local.yml logs -f"
echo "  - Остановка: docker-compose -f docker-compose.local.yml down"
echo "  - Перезапуск: docker-compose -f docker-compose.local.yml restart"

