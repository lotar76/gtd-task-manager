#!/bin/bash

# Скрипт настройки для production (с Traefik)

set -e

echo "🚀 Начало настройки для PRODUCTION (Traefik + todo.e-api.ru)..."

# Проверка наличия .env файла
if [ ! -f .env ]; then
    echo "📝 Создание .env файла из .env.production..."
    cp .env.production .env
    echo "⚠️  ВНИМАНИЕ: Не забудьте настроить переменные окружения в .env файле!"
else
    echo "ℹ️  Файл .env уже существует. Проверьте настройки."
fi

# Проверка наличия сети traefik
if ! docker network inspect web &> /dev/null; then
    echo "🌐 Создание Docker сети web..."
    docker network create web
fi

# Проверка наличия собранного фронтенда
if [ ! -f "public/index.html" ] || [ ! -d "public/assets" ]; then
    echo "⚠️  ВНИМАНИЕ: Фронтенд не собран!"
    echo "   Соберите локально: npm run build"
    echo "   И закоммитьте перед деплоем: git add public/ && git commit -m 'Build frontend'"
    echo ""
    read -p "Продолжить без фронтенда? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
else
    echo "✅ Фронтенд найден (public/index.html и public/assets/)"
fi

# Сборка и запуск контейнеров
echo "🐳 Сборка и запуск Docker контейнеров (с Traefik)..."
docker-compose -f docker-compose.prod.yml up -d --build

# Ожидание запуска контейнера
echo "⏳ Ожидание запуска контейнера..."
sleep 5

# Проверка статуса
docker-compose -f docker-compose.prod.yml ps

# Настройка прав доступа перед установкой зависимостей
echo "📁 Настройка прав доступа..."
docker-compose -f docker-compose.prod.yml exec -T --user root app chown -R www-data:www-data /var/www/html
docker-compose -f docker-compose.prod.yml exec -T --user root app chmod -R 755 /var/www/html
docker-compose -f docker-compose.prod.yml exec -T --user root app mkdir -p /var/www/html/vendor
docker-compose -f docker-compose.prod.yml exec -T --user root app chown -R www-data:www-data /var/www/html/vendor

# Настройка git safe directory (для composer)
echo "🔧 Настройка git..."
docker-compose -f docker-compose.prod.yml exec -T --user root app git config --global --add safe.directory /var/www/html

# Установка зависимостей
echo "📦 Установка Composer зависимостей..."
docker-compose -f docker-compose.prod.yml exec -T --user root app composer install --no-dev --optimize-autoloader

# Исправление прав после установки зависимостей
echo "📁 Исправление прав после установки зависимостей..."
docker-compose -f docker-compose.prod.yml exec -T --user root app chown -R www-data:www-data /var/www/html
docker-compose -f docker-compose.prod.yml exec -T --user root app chmod -R 755 /var/www/html/storage
docker-compose -f docker-compose.prod.yml exec -T --user root app chmod -R 755 /var/www/html/bootstrap/cache

# Генерация ключа приложения (если не существует)
echo "🔑 Генерация ключа приложения..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan key:generate --force

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
echo "🌐 API будет доступен по адресу: https://todo.e-api.ru"
echo ""
echo "⚠️  ВАЖНО: Убедитесь что:"
echo "  1. DNS записи для todo.e-api.ru указывают на ваш сервер"
echo "  2. Traefik запущен и работает"
echo "  3. Traefik настроен с Let's Encrypt для SSL"
echo ""
echo "📖 Полная документация: см. README.md"
echo ""
echo "🛠️  Полезные команды:"
echo "  - Просмотр логов: docker-compose -f docker-compose.prod.yml logs -f"
echo "  - Остановка: docker-compose -f docker-compose.prod.yml down"
echo "  - Перезапуск: docker-compose -f docker-compose.prod.yml restart"

