#!/bin/bash

# Скрипт для пересборки фронтенда БЕЗ localhost:9090

set -e

echo "🔄 Пересборка фронтенда без VITE_API_URL..."

# Сборка через Docker
# Важно: Vite автоматически читает .env файлы из корня проекта
# Используем --env-file /dev/null чтобы не передавать переменные из хоста
# mode=production гарантирует использование '/api' в vite.config.js
docker run --rm \
  -v "$(pwd):/app" \
  -w /app \
  --env-file /dev/null \
  node:20-alpine \
  sh -c "rm -rf node_modules/.vite public/.vite && npm install && npm run build -- --mode production"

# Проверка, что localhost:9090 не попал в сборку
if grep -r "localhost:9090" public/assets/*.js 2>/dev/null; then
    echo "❌ ОШИБКА: localhost:9090 все еще в собранных файлах!"
    exit 1
fi

echo "✅ Фронтенд пересобран успешно!"
echo "   Проверка: grep localhost:9090 public/assets/*.js"
grep -o "baseURL:\"[^\"]*\"" public/assets/app-*.js | head -1 || echo "   baseURL не найден (это нормально)"

