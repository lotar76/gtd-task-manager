#!/bin/bash

# Скрипт для пересборки фронтенда БЕЗ localhost:9090

set -e

echo "🔄 Пересборка фронтенда с VITE_API_URL=/api из .env.production..."

# Проверка наличия .env.production с правильным значением
if [ -f ".env.production" ]; then
    if ! grep -q "^VITE_API_URL=/api" .env.production; then
        echo "⚠️  В .env.production нет VITE_API_URL=/api, добавляю..."
        echo "" >> .env.production
        echo "# Frontend API URL - используем относительный путь для production" >> .env.production
        echo "VITE_API_URL=/api" >> .env.production
    fi
else
    echo "⚠️  .env.production не найден, создаю..."
    cat > .env.production << EOF
# Frontend API URL - используем относительный путь для production
VITE_API_URL=/api
EOF
fi

# Сборка через Docker
# Важно: Vite автоматически читает .env.production при --mode production
docker run --rm \
  -v "$(pwd):/app" \
  -w /app \
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

