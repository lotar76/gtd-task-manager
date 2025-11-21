# Обзор структуры проекта Laravel REST API

## Архитектура проекта

```
api/
├── 📁 app/
│   ├── Console/
│   │   └── Kernel.php                    # Console kernel
│   ├── Exceptions/
│   │   └── Handler.php                   # Обработчик исключений с форматированием для API
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php            # Базовый контроллер
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── AuthController.php     # Аутентификация (регистрация, вход, выход)
│   │   │           └── FileController.php     # Работа с S3 файлами
│   │   ├── Middleware/
│   │   │   ├── Authenticate.php          # Проверка аутентификации
│   │   │   ├── EncryptCookies.php        # Шифрование cookies
│   │   │   ├── LogApiRequests.php        # Логирование API запросов
│   │   │   ├── RedirectIfAuthenticated.php
│   │   │   ├── TrimStrings.php
│   │   │   ├── TrustProxies.php          # Доверие прокси (для Traefik)
│   │   │   ├── ValidateSignature.php
│   │   │   └── VerifyCsrfToken.php
│   │   ├── Requests/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── LoginRequest.php       # Валидация входа
│   │   │           ├── RegisterRequest.php    # Валидация регистрации
│   │   │           └── UploadFileRequest.php  # Валидация загрузки файлов
│   │   ├── Responses/
│   │   │   └── ApiResponse.php           # Единый формат JSON-ответов
│   │   └── Kernel.php                    # HTTP kernel с middleware
│   ├── Models/
│   │   └── User.php                      # Модель пользователя (Sanctum + Permissions)
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   └── RouteServiceProvider.php      # Rate limiting настройки
│   └── Services/
│       └── FileStorageService.php        # Сервис для работы с S3
│
├── 📁 bootstrap/
│   ├── app.php                           # Инициализация приложения
│   └── cache/                            # Кеш для оптимизации
│
├── 📁 config/
│   ├── app.php                           # Основные настройки приложения
│   ├── auth.php                          # Настройки аутентификации
│   ├── cache.php                         # Настройки кеширования
│   ├── cors.php                          # CORS настройки для API
│   ├── database.php                      # Подключение к внешней БД
│   ├── filesystems.php                   # S3 конфигурация
│   ├── logging.php                       # Логирование
│   ├── permission.php                    # Spatie Permission
│   ├── queue.php                         # Очереди
│   ├── sanctum.php                       # Laravel Sanctum
│   └── session.php                       # Сессии
│
├── 📁 database/
│   ├── factories/
│   │   └── UserFactory.php               # Фабрика для тестов
│   ├── migrations/
│   │   ├── 2014_10_12_000000_create_users_table.php
│   │   └── 2019_12_14_000001_create_personal_access_tokens_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── RoleAndPermissionSeeder.php   # Создание ролей и разрешений
│
├── 📁 public/
│   └── index.php                         # Входная точка приложения
│
├── 📁 routes/
│   ├── api.php                           # API маршруты (версия v1)
│   └── console.php                       # Console маршруты
│
├── 📁 scripts/
│   ├── setup.sh                          # Скрипт первоначальной настройки
│   ├── test.sh                           # Запуск тестов
│   └── deploy.sh                         # Развертывание в production
│
├── 📁 storage/
│   ├── app/                              # Локальные файлы
│   ├── framework/                        # Фреймворк файлы (кеш, сессии, views)
│   └── logs/                             # Логи приложения
│
├── 📁 tests/
│   ├── Feature/
│   │   ├── AuthTest.php                  # Тесты аутентификации
│   │   └── FileTest.php                  # Тесты работы с файлами
│   ├── CreatesApplication.php
│   └── TestCase.php
│
├── 📄 Dockerfile                         # PHP-FPM 8.2 с расширениями
├── 📄 docker-compose.yml                 # Docker Compose с Traefik
├── 📄 composer.json                      # PHP зависимости
├── 📄 phpunit.xml                        # Конфигурация тестов
├── 📄 pint.json                          # Code style (Laravel Pint)
├── 📄 artisan                            # Artisan CLI
│
├── 📄 README.md                          # Полная документация
├── 📄 QUICK_START.md                     # Быстрый старт
├── 📄 API_EXAMPLES.md                    # Примеры использования API
├── 📄 TRAEFIK_SETUP.md                   # Настройка Traefik
└── 📄 PROJECT_OVERVIEW.md                # Этот файл
```

## Ключевые компоненты

### 1. Docker Infrastructure

**Dockerfile:**
- PHP 8.2 FPM
- Расширения: pdo, pdo_mysql, gd, zip, intl, bcmath
- Composer
- Правильные права доступа для storage

**docker-compose.yml:**
- Сервис app (php-fpm)
- Две сети: traefik (внешняя) и internal
- Traefik labels для HTTP/HTTPS проксирования
- Volume для кода и storage

### 2. API Structure

**Версионирование:**
- Префикс `/api/v1` для всех эндпоинтов
- Контроллеры в `App\Http\Controllers\Api\V1`
- Requests в `App\Http\Requests\Api\V1`

**Единый формат ответов:**
```php
// Успех
ApiResponse::success($data, 'Success message', 200);

// Ошибка
ApiResponse::error('Error message', 400, $errors);

// Пагинация
ApiResponse::paginated($paginator, 'Success');
```

### 3. Аутентификация и авторизация

**Laravel Sanctum:**
- Token-based аутентификация
- Защита API эндпоинтов через middleware `auth:sanctum`

**Spatie Permission:**
- Роли: admin, user
- Разрешения: files.*, users.*
- Проверка через middleware `permission:` или `role:`

### 4. S3 Integration

**FileStorageService:**
- `upload()` - загрузка файла
- `download()` - скачивание файла
- `delete()` - удаление файла
- `getUrl()` - постоянная ссылка
- `getTemporaryUrl()` - временная signed URL
- `getMetadata()` - информация о файле

### 5. Security Features

**Rate Limiting:**
- api: 60 req/min (общий)
- auth: 5 req/min (вход/регистрация)
- uploads: 10 req/min (загрузка файлов)

**CORS:**
- Настраивается через `config/cors.php`
- Разрешенные источники из `.env`

**Middleware Stack:**
- TrustProxies - для работы за Traefik
- HandleCors - CORS обработка
- ThrottleRequests - Rate limiting
- LogApiRequests - Логирование запросов

### 6. Logging and Monitoring

**LogApiRequests middleware:**
- Логирует каждый запрос
- Метод, URL, IP, user_id
- Код ответа и время выполнения

**Структура логов:**
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

### 7. Testing

**Feature Tests:**
- `AuthTest.php` - тесты аутентификации
  - Регистрация
  - Вход
  - Получение профиля
  - Выход
- `FileTest.php` - тесты файлов
  - Загрузка
  - Получение информации
  - Скачивание
  - Удаление

**Запуск:**
```bash
docker compose exec app php artisan test
```

## Потоки данных

### Регистрация пользователя

```
Client → Traefik → PHP-FPM → AuthController@register
                                    ↓
                              RegisterRequest (валидация)
                                    ↓
                              User::create()
                                    ↓
                              assignRole('user')
                                    ↓
                              createToken()
                                    ↓
                              ApiResponse::success()
                                    ↓
                              JSON Response
```

### Загрузка файла в S3

```
Client → Traefik → PHP-FPM → FileController@upload
                                    ↓
                              UploadFileRequest (валидация)
                                    ↓
                              FileStorageService@upload()
                                    ↓
                              S3::put() → AWS S3 / MinIO
                                    ↓
                              ApiResponse::success()
                                    ↓
                              JSON Response (URL, metadata)
```

## Конфигурация окружения

### Обязательные переменные .env

```env
# App
APP_KEY=                    # php artisan key:generate
APP_URL=                    # Ваш домен

# Database (внешняя)
DB_HOST=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

# S3 Storage
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_BUCKET=
```

### Опциональные переменные

```env
# Для разработки
APP_ENV=local
APP_DEBUG=true

# CORS
CORS_ALLOWED_ORIGINS=*

# Cache/Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
```

## Best Practices реализованные в проекте

1. ✅ **Repository Pattern** - через Services (FileStorageService)
2. ✅ **Request Validation** - отдельные FormRequest классы
3. ✅ **API Resources** - единый формат через ApiResponse
4. ✅ **Middleware** - разделение логики (auth, logging, rate limiting)
5. ✅ **Dependency Injection** - в конструкторах контроллеров
6. ✅ **Type Hinting** - везде PHP 8.2 типизация
7. ✅ **Strict Types** - `declare(strict_types=1)` во всех файлах
8. ✅ **PSR-12** - следование стандартам кодирования
9. ✅ **Testing** - Feature tests для основного функционала
10. ✅ **Docker** - контейнеризация приложения

## Масштабирование

### Horizontal Scaling

Проект готов к горизонтальному масштабированию:
- Stateless API (токены в БД через Sanctum)
- Внешняя БД
- Внешнее S3 хранилище
- Traefik load balancing (при запуске нескольких реплик)

### Рекомендации для production

1. **Используйте Redis:**
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

2. **Queue Workers для тяжелых задач:**
```bash
php artisan queue:work
```

3. **Optimization:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. **Мониторинг:**
- Laravel Telescope (для разработки)
- Laravel Horizon (для очередей)
- Sentry (для ошибок)
- New Relic / DataDog (для метрик)

5. **CDN для S3:**
- CloudFront (AWS)
- CloudFlare
- KeyCDN

## Дополнительные возможности для расширения

### Добавление WebSocket (Laravel Echo)

```bash
composer require pusher/pusher-php-server
npm install --save laravel-echo pusher-js
```

### Добавление очередей

```bash
php artisan queue:table
php artisan migrate
```

### API Documentation (OpenAPI/Swagger)

```bash
composer require darkaonline/l5-swagger
php artisan l5-swagger:generate
```

### GraphQL (вместо REST)

```bash
composer require rebing/graphql-laravel
```

## Поддержка и разработка

### Просмотр структуры проекта

```bash
docker compose exec app php artisan route:list    # Все маршруты
docker compose exec app php artisan tinker        # REPL
```

### Debugging

```bash
docker compose logs -f app                        # Логи контейнера
docker compose exec app tail -f storage/logs/laravel.log  # Логи Laravel
```

### Code Style

```bash
docker compose exec app ./vendor/bin/pint         # Форматирование кода
```

## Заключение

Проект представляет собой полнофункциональный Laravel REST API с:
- Современной архитектурой
- Docker контейнеризацией
- Traefik интеграцией
- S3 хранилищем
- Полным набором security features
- Тестами
- Подробной документацией

Готов к разработке и production deployment.

