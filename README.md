# Laravel REST API с Docker и Traefik

Полнофункциональный Laravel REST API проект, развёрнутый в Docker с интеграцией Traefik для production, внешней базой данных и S3-хранилищем.

> **Примечание**: Проект поддерживает два режима:
> - **Локальная разработка**: Nginx на порту 9090 (http://localhost:9090)
> - **Production**: Traefik для домена e-api.ru (https://e-api.ru)

## Основные возможности

- ✅ Laravel 10 (LTS) - API-only режим
- ✅ Docker с PHP-FPM 8.2
- ✅ Интеграция с Traefik для HTTP/HTTPS проксирования
- ✅ Подключение к внешней базе данных (MySQL/PostgreSQL)
- ✅ Интеграция с S3-хранилищем (AWS S3, MinIO и т.д.)
- ✅ Laravel Sanctum для аутентификации токенами
- ✅ Spatie Laravel Permission для управления ролями и правами
- ✅ CORS настройки
- ✅ Rate Limiting для API эндпоинтов
- ✅ Логирование запросов и ответов
- ✅ Версионирование API (v1)
- ✅ Единый формат JSON-ответов
- ✅ Полный набор тестов (PHPUnit)

## Структура проекта

```
api/
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/          # API v1 контроллеры
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   │   └── Api/
│   │   │       └── V1/          # API v1 запросы
│   │   └── Responses/           # Единый формат ответов
│   ├── Models/
│   ├── Providers/
│   └── Services/                # Бизнес-логика (например, FileStorageService)
├── config/                      # Конфигурационные файлы
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docker-compose.yml           # Docker Compose конфигурация
├── Dockerfile                   # Dockerfile для PHP-FPM
├── routes/
│   └── api.php                  # API маршруты
└── tests/                       # PHPUnit тесты
    └── Feature/
```

## Требования

- Docker и Docker Compose
- Traefik (должен быть запущен отдельно в сети `traefik`)
- Внешняя база данных (MySQL/PostgreSQL)
- S3-совместимое хранилище (AWS S3, MinIO и т.д.)

## Установка и настройка

### 1. Клонирование репозитория

```bash
git clone <repository-url>
cd api
```

### 2. Настройка переменных окружения

Создайте файл `.env` на основе примера:

```bash
cp .env.example .env
```

#### Основные переменные окружения:

```env
# Основные настройки приложения
APP_NAME="Laravel API"
APP_ENV=production
APP_KEY=base64:...  # Будет сгенерирован при первом запуске
APP_DEBUG=false
APP_URL=https://api.local.test

# База данных (внешняя)
DB_CONNECTION=mysql
DB_HOST=your-database-host.com
DB_PORT=3306
DB_DATABASE=laravel_api
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# S3 хранилище
AWS_ACCESS_KEY_ID=your-access-key-id
AWS_SECRET_ACCESS_KEY=your-secret-access-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_ENDPOINT=https://s3.amazonaws.com  # Или URL вашего S3-совместимого хранилища
AWS_USE_PATH_STYLE_ENDPOINT=false      # true для MinIO

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,api.local.test

# CORS (разрешённые источники)
CORS_ALLOWED_ORIGINS=http://localhost:3000,https://app.example.com
```

### 3. Настройка Traefik

Убедитесь, что Traefik запущен и настроен с сетью `traefik`. В `docker-compose.yml` уже настроены labels для Traefik:

```yaml
labels:
  - "traefik.enable=true"
  - "traefik.docker.network=traefik"
  - "traefik.http.routers.api.rule=Host(`api.local.test`)"
  - "traefik.http.routers.api.entrypoints=web"
  - "traefik.http.routers.api-secure.rule=Host(`api.local.test`)"
  - "traefik.http.routers.api-secure.entrypoints=websecure"
  - "traefik.http.routers.api-secure.tls=true"
```

**Настройка хоста:**

Добавьте в `/etc/hosts` (Linux/Mac) или `C:\Windows\System32\drivers\etc\hosts` (Windows):

```
127.0.0.1 api.local.test
```

### 4. Создание внешней сети Traefik

Если сеть `traefik` ещё не создана:

```bash
docker network create traefik
```

### 5. Запуск приложения

```bash
# Сборка и запуск контейнеров
docker compose up -d --build

# Генерация ключа приложения
docker compose exec app php artisan key:generate

# Запуск миграций
docker compose exec app php artisan migrate

# Запуск сидеров (создание ролей и разрешений)
docker compose exec app php artisan db:seed
```

### 6. Установка зависимостей (опционально, если не было при сборке)

```bash
docker compose exec app composer install --optimize-autoloader
```

## API Эндпоинты

### Аутентификация

#### Регистрация пользователя
```bash
curl -X POST https://api.local.test/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Ответ:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz"
  }
}
```

#### Вход пользователя
```bash
curl -X POST https://api.local.test/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### Получение профиля
```bash
curl -X GET https://api.local.test/api/v1/me \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Выход
```bash
curl -X POST https://api.local.test/api/v1/logout \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Работа с файлами (S3)

#### Загрузка файла
```bash
curl -X POST https://api.local.test/api/v1/files/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/file.jpg" \
  -F "directory=uploads"
```

**Ответ:**
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "data": {
    "path": "uploads/550e8400-e29b-41d4-a716-446655440000.jpg",
    "url": "https://bucket.s3.amazonaws.com/uploads/550e8400-e29b-41d4-a716-446655440000.jpg",
    "filename": "550e8400-e29b-41d4-a716-446655440000.jpg",
    "original_name": "file.jpg",
    "mime_type": "image/jpeg",
    "size": 102400
  }
}
```

#### Получение информации о файле
```bash
curl -X GET "https://api.local.test/api/v1/files/show?path=uploads/550e8400-e29b-41d4-a716-446655440000.jpg" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Получение временной ссылки для скачивания
```bash
curl -X GET "https://api.local.test/api/v1/files/download?path=uploads/550e8400-e29b-41d4-a716-446655440000.jpg&minutes=60" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Удаление файла
```bash
curl -X DELETE https://api.local.test/api/v1/files/delete \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "path": "uploads/550e8400-e29b-41d4-a716-446655440000.jpg"
  }'
```

## Формат ответов API

### Успешный ответ
```json
{
  "success": true,
  "message": "Success message",
  "data": {
    // данные
  }
}
```

### Ответ с ошибкой
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    // детали ошибок (опционально)
  }
}
```

### Ответ с пагинацией
```json
{
  "success": true,
  "message": "Success",
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

## Роли и разрешения

По умолчанию создаются следующие роли:

### Admin
- Все разрешения

### User
- `files.upload` - загрузка файлов
- `files.view` - просмотр файлов
- `files.download` - скачивание файлов

### Использование в коде

```php
// Проверка разрешения
if ($user->can('files.upload')) {
    // действие
}

// Использование в маршрутах
Route::middleware(['permission:files.upload'])->group(function () {
    // маршруты
});

// Использование в контроллерах
$this->authorize('files.upload');
```

## Rate Limiting

Настроены следующие лимиты:

- **api** - 60 запросов в минуту (общий)
- **auth** - 5 запросов в минуту (регистрация/вход)
- **uploads** - 10 запросов в минуту (загрузка файлов)

## Тестирование

### Запуск всех тестов
```bash
docker compose exec app php artisan test
```

### Запуск конкретного теста
```bash
docker compose exec app php artisan test --filter=AuthTest
```

### Запуск с покрытием кода
```bash
docker compose exec app php artisan test --coverage
```

## Логирование

Все API запросы автоматически логируются через middleware `LogApiRequests`. Логи сохраняются в `storage/logs/laravel.log`:

```
[2024-01-01 12:00:00] local.INFO: API Request {
  "method": "POST",
  "url": "https://api.local.test/api/v1/login",
  "ip": "192.168.1.1",
  "user_id": null,
  "status": 200,
  "duration": "125.45ms"
}
```

## Отладка и разработка

### Просмотр логов контейнера
```bash
docker compose logs -f app
```

### Вход в контейнер
```bash
docker compose exec app bash
```

### Очистка кеша
```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
```

### Оптимизация для production
```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

## Дополнительные команды

### Создание нового пользователя с ролью admin
```bash
docker compose exec app php artisan tinker
>>> $user = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password')]);
>>> $user->assignRole('admin');
```

### Создание новой миграции
```bash
docker compose exec app php artisan make:migration create_posts_table
```

### Создание нового контроллера
```bash
docker compose exec app php artisan make:controller Api/V1/PostController
```

### Создание нового Request
```bash
docker compose exec app php artisan make:request Api/V1/CreatePostRequest
```

## Безопасность

- ✅ Все пароли хешируются с помощью bcrypt
- ✅ CSRF защита для stateful запросов
- ✅ Настроен TrustProxies middleware для работы за Traefik
- ✅ Rate Limiting для предотвращения брутфорса
- ✅ Валидация всех входящих данных
- ✅ Аутентификация через токены Sanctum
- ✅ CORS настройки

## Производственное развёртывание

### Рекомендации для production:

1. **Установите APP_DEBUG=false** в `.env`
2. **Используйте сильный APP_KEY**
3. **Настройте SSL сертификаты в Traefik**
4. **Используйте внешний Redis для кеша и сессий**
5. **Настройте очереди (queue workers)**
6. **Настройте мониторинг и алертинг**
7. **Регулярно делайте бэкапы базы данных**
8. **Используйте CDN для статических файлов**

### Пример конфигурации Traefik для production:

```yaml
# traefik-docker-compose.yml
version: '3.8'

services:
  traefik:
    image: traefik:v2.10
    command:
      - "--api.insecure=false"
      - "--providers.docker=true"
      - "--entrypoints.web.address=:80"
      - "--entrypoints.websecure.address=:443"
      - "--certificatesresolvers.letsencrypt.acme.email=admin@example.com"
      - "--certificatesresolvers.letsencrypt.acme.storage=/letsencrypt/acme.json"
      - "--certificatesresolvers.letsencrypt.acme.httpchallenge.entrypoint=web"
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - ./letsencrypt:/letsencrypt
    networks:
      - traefik

networks:
  traefik:
    external: true
```

## Поддержка

При возникновении проблем:

1. Проверьте логи: `docker compose logs -f app`
2. Проверьте подключение к базе данных
3. Проверьте настройки S3
4. Убедитесь, что Traefik работает корректно
5. Проверьте файл `.env`

## Лицензия

MIT

## Автор

Laravel REST API Project

