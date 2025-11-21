#!/bin/bash

# Скрипт настройки для production (с Traefik)

set -e

echo "🚀 Начало настройки для PRODUCTION (Traefik + e-api.ru)..."

# Проверка наличия .env файла
if [ ! -f .env ]; then
    echo "📝 Создание .env файла из .env.production..."
    cp .env.production .env
    echo "⚠️  ВНИМАНИЕ: Не забудьте настроить переменные окружения в .env файле!"
else
    echo "ℹ️  Файл .env уже существует. Проверьте настройки."
fi

# Проверка наличия сети traefik
if ! docker network inspect traefik &> /dev/null; then
    echo "🌐 Создание Docker сети traefik..."
    docker network create traefik
fi

# Сборка и запуск контейнеров
echo "🐳 Сборка и запуск Docker контейнеров (с Traefik)..."
docker-compose -f docker-compose.prod.yml up -d --build

# Ожидание запуска контейнера
echo "⏳ Ожидание запуска контейнера..."
sleep 5

# Проверка статуса
docker-compose -f docker-compose.prod.yml ps

# Установка зависимостей
echo "📦 Установка Composer зависимостей..."
docker-compose -f docker-compose.prod.yml exec -T app composer install --no-dev --optimize-autoloader

# Генерация ключа приложения (если не существует)
echo "🔑 Генерация ключа приложения..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan key:generate --force

# Создание директорий storage
echo "📁 Настройка прав доступа..."
docker-compose -f docker-compose.prod.yml exec -T app chmod -R 775 storage bootstrap/cache || true

# Запуск миграций
echo "🗄️  Запуск миграций базы данных..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate --force

# Запуск сидеров
echo "🌱 Запуск сидеров (роли и права)..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan db:seed --force

# Кеширование для production
echo "⚡ Оптимизация для production..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan view:cache

echo "✅ Настройка завершена!"
echo ""
echo "🌐 API будет доступен по адресу: https://e-api.ru"
echo ""
echo "⚠️  ВАЖНО: Убедитесь что:"
echo "  1. DNS записи для e-api.ru указывают на ваш сервер"
echo "  2. Traefik запущен и работает"
echo "  3. Traefik настроен с Let's Encrypt для SSL"
echo ""
echo "📖 Полная документация: см. README.md"
echo ""
echo "🛠️  Полезные команды:"
echo "  - Просмотр логов: docker-compose -f docker-compose.prod.yml logs -f"
echo "  - Остановка: docker-compose -f docker-compose.prod.yml down"
echo "  - Перезапуск: docker-compose -f docker-compose.prod.yml restart"

