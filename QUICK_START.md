# Быстрый старт

## Минимальная настройка для запуска проекта

### 1. Предварительные требования

- Docker и Docker Compose установлены
- Traefik запущен в отдельной сети `traefik`
- Доступ к внешней базе данных (MySQL/PostgreSQL)
- Доступ к S3-хранилищу

### 2. Быстрая установка

```bash
# Создание файла .env
cp .env.example .env

# Редактирование .env (настройте DB и S3)
nano .env

# Запуск автоматической установки
./scripts/setup.sh
```

### 3. Минимальные настройки .env

```env
# База данных
DB_HOST=your-db-host
DB_DATABASE=laravel_api
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# S3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_BUCKET=your-bucket
```

### 4. Проверка работы

```bash
# Проверка статуса контейнера
docker compose ps

# Тестирование API
curl https://api.local.test/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@example.com","password":"password123","password_confirmation":"password123"}'
```

### 5. Если что-то пошло не так

```bash
# Просмотр логов
docker compose logs -f app

# Перезапуск
docker compose restart app

# Полная переустановка
docker compose down
docker compose up -d --build
```

## Полезные команды

```bash
# Запуск тестов
./scripts/test.sh

# Вход в контейнер
docker compose exec app bash

# Запуск миграций
docker compose exec app php artisan migrate

# Создание пользователя-админа
docker compose exec app php artisan tinker
>>> User::factory()->create(['email' => 'admin@example.com'])->assignRole('admin');
```

## Дальнейшая настройка

Полная документация находится в файле `README.md`.

