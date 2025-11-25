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
docker-compose -f docker-compose.prod.yml exec -T app php artisan down

# Получение последних изменений
echo "📥 Получение последних изменений из git..."
git pull origin main

# Проверка наличия собранного фронтенда
if [ ! -f "public/index.html" ] || [ ! -d "public/assets" ]; then
    echo "❌ ОШИБКА: Фронтенд не собран!"
    echo "   Запустите локально: npm run build"
    echo "   Затем закоммитьте: git add public/ && git commit -m 'Build frontend' && git push"
    exit 1
fi
echo "✅ Фронтенд найден (public/index.html и public/assets/)"

# Сборка и перезапуск контейнеров
echo "🐳 Пересборка Docker контейнеров..."
docker-compose -f docker-compose.prod.yml up -d --build

# Настройка прав доступа
echo "📁 Настройка прав доступа..."
docker-compose -f docker-compose.prod.yml exec -T --user root app chown -R www-data:www-data /var/www/html
docker-compose -f docker-compose.prod.yml exec -T --user root app git config --global --add safe.directory /var/www/html

# Установка зависимостей
echo "📦 Установка зависимостей..."
docker-compose -f docker-compose.prod.yml exec -T --user root app composer install --no-dev --optimize-autoloader

# Исправление прав после установки
docker-compose -f docker-compose.prod.yml exec -T --user root app chown -R www-data:www-data /var/www/html
docker-compose -f docker-compose.prod.yml exec -T --user root app chmod -R 775 storage bootstrap/cache

# Запуск миграций
echo "🗄️  Запуск миграций..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate --force

# Очистка и кеширование
echo "🧹 Очистка и оптимизация..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan view:cache

# Выключение maintenance режима
echo "✅ Выключение maintenance режима..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan up

echo "🎉 Развертывание завершено успешно!"

