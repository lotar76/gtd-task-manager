#!/bin/bash

# Скрипт для пересборки фронтенда БЕЗ localhost:9090

set -e

echo "🔄 Пересборка фронтенда без VITE_API_URL..."

# Сборка через Docker с явной очисткой переменной
# Важно: не передаем переменные окружения из хоста
docker run --rm \
  -v "$(pwd):/app" \
  -w /app \
  node:20-alpine \
  sh -c "unset VITE_API_URL && npm install && VITE_API_URL= npm run build"

# Проверка, что localhost:9090 не попал в сборку
if grep -r "localhost:9090" public/assets/*.js 2>/dev/null; then
    echo "❌ ОШИБКА: localhost:9090 все еще в собранных файлах!"
    exit 1
fi

echo "✅ Фронтенд пересобран успешно!"
echo "   Проверка: grep localhost:9090 public/assets/*.js"
grep -o "baseURL:\"[^\"]*\"" public/assets/app-*.js | head -1 || echo "   baseURL не найден (это нормально)"

