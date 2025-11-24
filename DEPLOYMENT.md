# 🚀 Инструкция по развертыванию

Проект поддерживает два режима развертывания:
1. **Локальная разработка** - без Traefik, с Nginx (порт 8000)
2. **Production** - с Traefik для домена `e-api.ru`

---

## 📌 Локальная разработка

### Конфигурация

- **Docker Compose:** `docker-compose.local.yml`
- **Env файл:** `.env.local` (автоматически копируется в `.env`)
- **URL:** http://localhost:9090
- **Веб-сервер:** Nginx
- **Порты:** 9090:80

### Запуск

```bash
# Автоматическая установка
./scripts/setup-local.sh
```

**Или вручную:**

```bash
# 1. Копировать конфигурацию
cp .env.local .env

# 2. Запуск контейнеров
docker-compose -f docker-compose.local.yml up -d --build

# 3. Установка зависимостей
docker-compose -f docker-compose.local.yml exec app composer install

# 4. Генерация ключа
docker-compose -f docker-compose.local.yml exec app php artisan key:generate

# 5. Миграции
docker-compose -f docker-compose.local.yml exec app php artisan migrate

# 6. Сидеры (роли и права)
docker-compose -f docker-compose.local.yml exec app php artisan db:seed
```

### Проверка работы

```bash
# Тест регистрации
curl -X POST http://localhost:9090/api/v1/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Ожидаемый результат:
# {"success":true,"message":"User registered successfully","data":{...}}
```

### Полезные команды (локально)

```bash
# Просмотр логов
docker-compose -f docker-compose.local.yml logs -f

# Просмотр логов Nginx
docker-compose -f docker-compose.local.yml logs -f nginx

# Вход в контейнер app
docker-compose -f docker-compose.local.yml exec app bash

# Запуск тестов
docker-compose -f docker-compose.local.yml exec app php artisan test

# Остановка
docker-compose -f docker-compose.local.yml down

# Перезапуск
docker-compose -f docker-compose.local.yml restart

# Полная переустановка
docker-compose -f docker-compose.local.yml down -v
./scripts/setup-local.sh
```

---

## 🌐 Production (e-api.ru)

### Конфигурация

- **Docker Compose:** `docker-compose.prod.yml`
- **Env файл:** `.env.production` (автоматически копируется в `.env`)
- **URL:** https://e-api.ru
- **Веб-сервер:** Traefik → PHP-FPM
- **SSL:** Let's Encrypt (через Traefik)

### Требования

1. ✅ На сервере уже запущен общий Traefik и доступна внешняя сеть `web`
2. ✅ В конфигурации Traefik есть сертификатный резолвер `letsencrypt`
3. ✅ DNS запись для `todo.e-api.ru` указывает на ваш сервер
4. ✅ Порты 80 и 443 открыты

### Настройка Traefik

Traefik уже работает на сервере (общая сеть `web`). Перед деплоем убедитесь:

- В его конфигурации присутствует сертификатный резолвер `letsencrypt`:
  ```
  --certificatesresolvers.letsencrypt.acme.tlschallenge=true
  --certificatesresolvers.letsencrypt.acme.email=...
  --certificatesresolvers.letsencrypt.acme.storage=/letsencrypt/acme.json
  ```
- Наша сеть `web` доступна: `docker network inspect web`
- В `docker-compose.prod.yml` сервис `app` подключен к сети `web` и использует labels с `certresolver=letsencrypt`

### Запуск Production

```bash
# Автоматическая установка
./scripts/setup-prod.sh
```

**Или вручную:**

```bash
# 1. Копировать production конфигурацию
cp .env.production .env

# 2. Отредактировать .env (если нужно)
nano .env

# 3. Проверить наличие внешней сети Traefik
docker network inspect web || docker network create web

# 4. Запуск контейнеров
docker-compose -f docker-compose.prod.yml up -d --build

# 5. Установка зависимостей (без dev)
docker-compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader

# 6. Генерация ключа
docker-compose -f docker-compose.prod.yml exec app php artisan key:generate --force

# 7. Миграции
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# 8. Сидеры
docker-compose -f docker-compose.prod.yml exec app php artisan db:seed --force

# 9. Кеширование конфигов
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec app php artisan view:cache
```

### Проверка работы (Production)

```bash
# Тест регистрации
curl -X POST https://e-api.ru/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Проверка SSL
curl -I https://e-api.ru
```

### Полезные команды (Production)

```bash
# Просмотр логов
docker-compose -f docker-compose.prod.yml logs -f app

# Просмотр логов Laravel
docker-compose -f docker-compose.prod.yml exec app tail -f storage/logs/laravel.log

# Вход в контейнер
docker-compose -f docker-compose.prod.yml exec app bash

# Запуск миграций
docker-compose -f docker-compose.prod.yml exec app php artisan migrate

# Очистка кешей
docker-compose -f docker-compose.prod.yml exec app php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec app php artisan config:clear
docker-compose -f docker-compose.prod.yml exec app php artisan route:clear

# Перекеширование (после изменений)
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache

# Перезапуск
docker-compose -f docker-compose.prod.yml restart app

# Остановка
docker-compose -f docker-compose.prod.yml down

# Просмотр статуса
docker-compose -f docker-compose.prod.yml ps
```

---

## 🔄 Обновление Production

Скрипт для безопасного обновления production с минимальным downtime:

```bash
#!/bin/bash
# scripts/update-prod.sh

set -e

echo "🔄 Обновление Production..."

# Backup базы данных (опционально)
# docker-compose -f docker-compose.prod.yml exec app php artisan backup:run

# Включение maintenance режима
docker-compose -f docker-compose.prod.yml exec app php artisan down

# Git pull (если используете git)
git pull origin main

# Пересборка контейнеров
docker-compose -f docker-compose.prod.yml up -d --build

# Установка зависимостей
docker-compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader

# Миграции
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Очистка и кеширование
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec app php artisan view:cache

# Выключение maintenance режима
docker-compose -f docker-compose.prod.yml exec app php artisan up

echo "✅ Обновление завершено!"
```

---

## 📊 Сравнение окружений

| Параметр | Локальная | Production |
|----------|-----------|------------|
| Compose файл | `docker-compose.local.yml` | `docker-compose.prod.yml` |
| Env файл | `.env.local` | `.env.production` |
| URL | http://localhost:8000 | https://e-api.ru |
| Веб-сервер | Nginx | Traefik → PHP-FPM |
| SSL | Нет | Let's Encrypt |
| Debug | Включен | Выключен |
| Composer | `--optimize-autoloader` | `--no-dev --optimize-autoloader` |
| Кеширование | Нет | Да (config, route, view) |
| Log level | debug | error |

---

## 🛠️ Устранение проблем

### Локальная разработка

**Проблема: Порт 8000 занят**
```bash
# Найти процесс
sudo lsof -i :8000

# Или изменить порт в docker-compose.local.yml
ports:
  - "8080:80"  # вместо 8000:80
```

**Проблема: Nginx не запускается**
```bash
# Проверка логов
docker-compose -f docker-compose.local.yml logs nginx

# Проверка конфигурации
docker-compose -f docker-compose.local.yml exec nginx nginx -t
```

### Production

**Проблема: SSL сертификат не выдается**
```bash
# Проверка Traefik логов
docker logs traefik

# Убедитесь что:
# 1. DNS запись e-api.ru указывает на ваш IP
# 2. Порты 80 и 443 открыты
# 3. Email в Traefik конфигурации правильный
```

**Проблема: 502 Bad Gateway**
```bash
# Проверка статуса контейнера
docker-compose -f docker-compose.prod.yml ps

# Проверка что app запущен и слушает 9000
docker-compose -f docker-compose.prod.yml exec app netstat -tlnp | grep 9000

# Проверка сетей
docker network inspect traefik
```

---

## 🔐 Безопасность Production

1. **Измените APP_KEY после деплоя**
2. **Настройте CORS** в `.env.production`:
   ```env
   CORS_ALLOWED_ORIGINS=https://yourfrontend.com
   ```
3. **Используйте сильные пароли для БД**
4. **Регулярно обновляйте зависимости**:
   ```bash
   docker-compose -f docker-compose.prod.yml exec app composer update
   ```
5. **Мониторьте логи**:
   ```bash
   docker-compose -f docker-compose.prod.yml logs -f app
   ```

---

## 📚 Дополнительная документация

- `README.md` - Полная документация
- `API_EXAMPLES.md` - Примеры использования API
- `TRAEFIK_SETUP.md` - Подробная настройка Traefik
- `PROJECT_OVERVIEW.md` - Архитектура проекта

