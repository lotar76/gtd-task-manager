#!/bin/bash

# Скрипт для пересборки фронтенда БЕЗ localhost:9090

set -e

echo "🔄 Пересборка фронтенда без VITE_API_URL..."

# Убеждаемся, что VITE_API_URL не установлен
unset VITE_API_URL

# Удаляем .env файлы, которые могут содержать VITE_API_URL
if [ -f .env ]; then
    echo "⚠️  Временное переименование .env файла..."
    mv .env .env.backup
    ENV_BACKUP=true
else
    ENV_BACKUP=false
fi

# Сборка через Docker с явной очисткой переменной
docker run --rm \
  -v "$(pwd):/app" \
  -w /app \
  --env-file /dev/null \
  -e VITE_API_URL= \
  node:20-alpine \
  sh -c "unset VITE_API_URL && npm install && npm run build"

# Восстанавливаем .env файл если был
if [ "$ENV_BACKUP" = true ]; then
    echo "📝 Восстановление .env файла..."
    mv .env.backup .env
fi

# Проверка, что localhost:9090 не попал в сборку
if grep -r "localhost:9090" public/assets/*.js 2>/dev/null; then
    echo "❌ ОШИБКА: localhost:9090 все еще в собранных файлах!"
    exit 1
fi

echo "✅ Фронтенд пересобран успешно!"
echo "   Проверка: grep localhost:9090 public/assets/*.js"
grep -o "baseURL:\"[^\"]*\"" public/assets/app-*.js | head -1 || echo "   baseURL не найден (это нормально)"

