# GTD Task Manager - Контекст проекта

## О проекте

**GTD Task Manager** - система управления задачами по методологии Getting Things Done.

### Стек технологий
- **Backend:** Laravel 11, PHP 8.3
- **Frontend:** Vue.js 3, Vite
- **База данных:** MySQL
- **Деплой:** Docker, nginx, Traefik (автоматический SSL)
- **Production:** https://todo.e-api.ru

### Структура проекта
```
├── app/                    # Laravel backend
│   ├── Models/            # Task, Workspace, User
│   ├── Http/Controllers/  # API контроллеры
│   └── Console/Commands/  # Artisan команды
├── resources/js/          # Vue.js frontend
│   ├── components/        # Vue компоненты
│   ├── views/            # Страницы
│   └── router/           # Vue Router
├── public/               # Собранный frontend (коммитится в git!)
├── .claude/
│   └── skills/deploy/    # Локальный skill для деплоя
└── docker/               # Docker конфигурация
```

## Production Environment

### Сервер
- **Host:** root@37.220.82.214
- **Project Path:** /home/projects/todo
- **URL:** https://todo.e-api.ru
- **SSH:** Ключи настроены, работает без пароля

### Deploy процесс
Используется локальный skill `/deploy` который:
1. Собирает фронтенд (`npm run build`)
2. Добавляет изменения в git
3. Автоматически определяет тип деплоя (fast/full)
4. Коммитит и пушит
5. Запускает нужный скрипт на сервере

**Два типа деплоя:**
- ⚡ **FAST** (~10 сек) - без rebuild Docker, для изменений кода
- 🐳 **FULL** (~60 сек) - с rebuild, когда изменились Dockerfile/docker-compose

## Local Development Environment

### Конфигурация
- **Docker Compose:** `docker-compose.local.yml`
- **Backend:** http://localhost:9090/api/
- **Frontend:** http://localhost:5173
- **База данных:** Production DB (83.147.245.158)

### Запуск локального окружения
```bash
# Запустить все контейнеры
docker-compose -f docker-compose.local.yml up -d

# Проверить статус
docker ps --filter "name=api_"

# Установить зависимости (если нужно)
docker exec --user root api_app composer install

# Логи
docker logs -f api_app      # Laravel
docker logs -f api_frontend # Vite dev server
docker logs -f api_nginx    # Nginx

# Остановить
docker-compose -f docker-compose.local.yml down
```

### Контейнеры
- **api_app** - PHP 8.2-FPM с Laravel
- **api_nginx** - Nginx (порт 9090)
- **api_frontend** - Node.js с Vite dev server (порт 5173)

### Особенности локальной разработки
1. **Локальная БД** - используется отдельная MySQL в контейнере `mysql` (база `api`), НЕ production
2. **Hot Reload** - Vite автоматически перезагружает фронтенд при изменениях
3. **Storage права** - при первом запуске нужно установить права: `docker exec --user root api_app chown -R www-data:www-data /var/www/html/storage`
4. **Vendor volume** - composer зависимости устанавливаются внутри контейнера от root

## Что реализовано

### Основной функционал
- ✅ Система задач (CRUD операции)
- ✅ Календарное представление задач
- ✅ Рабочие пространства (workspaces)
- ✅ Статусы задач: новая, в работе, выполнена, запланированная
- ✅ Автоматический пересчёт статусов на основе `due_date`
- ✅ Cron задача для ночного пересчёта (00:00 МСК)
- ✅ Градиентные фоны для задач в календаре
- ✅ Линия текущего времени в календаре
- ✅ Динамическое отображение длительности задач

### Инфраструктура
- ✅ Docker окружение (development + production)
- ✅ Локальное окружение для разработки с подключением к production БД
- ✅ Deploy система с автоопределением типа
- ✅ Локальный skill `/deploy`
- ✅ Скрипты деплоя на сервере (deploy.sh, deploy-fast.sh)
- ✅ Traefik для автоматического SSL
- ✅ Vite dev server с hot reload

## Договорённости о взаимодействии

### Как работать с пользователем

**ВАЖНО:** Следующие правила помогают избежать зацикливания на неправильном понимании задачи.

#### 0. НИКОГДА не использовать эмодзи
- НИ В КОДЕ, НИ В ОТВЕТАХ — никаких эмодзи, никогда
- Это касается и HTML-entities типа `&#127942;`, и unicode-символов

#### 1. Перефразирование задачи
Перед началом работы сформулируй своими словами что понял и получи подтверждение:
- "Правильно ли я понял: тебе нужно... [своими словами]?"
- Не лезь сразу делать, сначала убедись что понял правильно

#### 2. Проверочные вопросы
При малейшей неясности - спрашивай, не додумывай:
- Если не уверен где создавать файл - спроси явно
- Если не понятно какой подход использовать - спроси

#### 3. Красные флаги = СТОП
Если пользователь пишет капсом, много восклицательных знаков, или спрашивает "почему ты не слушаешь":
- ПОЛНОСТЬЮ остановись
- НЕ пытайся исправить детали
- Переосмысли всю задачу с нуля
- Спроси "давай начнём заново - что именно тебе нужно?"

#### 4. Не зацикливаться
**Корень проблемы:** слишком быстрое "решение" задачи в голове, затем упорное движение в этом направлении.

**Что делать:**
- Не привязываться к первому пониманию задачи
- Фокусироваться на "ЧТО нужно", а не на "КАК сделать"
- При негативных сигналах - пересматривать весь подход, а не чинить детали
- Спрашивать подтверждение понимания ПЕРЕД началом работы

### Примеры правильного поведения

**❌ Неправильно:**
```
Пользователь: "Мне нужна локальная команда"
Claude: *Создаёт глобальный skill в ~/.claude/skills/*
```

**✅ Правильно:**
```
Пользователь: "Мне нужна локальная команда"
Claude: "Правильно ли я понял: тебе нужен skill в .claude/skills/
        (локальный для проекта), а не в ~/.claude/skills/ (глобальный)?"
```

## Текущая работа

### Последние изменения (2024-02-09)
- ✅ Исправлена критическая проблема с деплоем
  - Добавлена проверка запуска контейнера `api_frontend` перед сборкой
  - Добавлена обязательная проверка `git status public/` после сборки
  - Обновлён skill `/deploy` с fail-fast логикой
  - Задокументирована проблема в "Известных граблях"
- ✅ Успешно задеплоен коммит 5991b10 с логотипом и фавиконками
- ✅ Настроена система деплоя с автоопределением типа (fast/full)
- ✅ Создан локальный skill `/deploy` в `.claude/skills/deploy/`
- ✅ Договорились о правилах взаимодействия
- ✅ Настроено локальное окружение для разработки
  - Docker Compose с тремя контейнерами (app, nginx, frontend)
  - Подключение к production БД
  - Vite dev server с hot reload
  - Все зависимости установлены
  - Storage права настроены

### Следующие шаги
- Ожидаем новых задач от пользователя

## Полезные команды

### Development (Локально)
```bash
# Запуск dev окружения
docker-compose -f docker-compose.local.yml up -d

# Остановка
docker-compose -f docker-compose.local.yml down

# Artisan команды
docker exec api_app php artisan migrate
docker exec api_app php artisan tasks:recalculate-statuses

# Установка зависимостей
docker exec --user root api_app composer install

# Логи
docker logs -f api_app
docker logs -f api_frontend

# Доступ
# Backend: http://localhost:9090/api/
# Frontend: http://localhost:5173
# Test API: http://localhost:9090/api/test
```

### Production Deploy
```bash
# Просто вызови skill
/deploy
```

### Logs
```bash
# Логи на production
ssh root@37.220.82.214 "docker logs -f api_app_prod"

# Статус контейнеров
ssh root@37.220.82.214 "docker ps | grep todo"
```

## Важные файлы

- `.claude/skills/deploy/SKILL.md` - Локальный skill для деплоя
- `PROJECT_CONTEXT.md` - Детальная документация проекта
- `.env` - Локальное окружение (подключение к production БД)
- `.env.production` - Production environment (на сервере)
- `docker-compose.local.yml` - Локальное Docker окружение
- `docker-compose.prod.yml` - Production Docker конфигурация
- `public/` - Собранный frontend (ВАЖНО: коммитится в git!)

## Известные особенности

1. **Frontend собирается локально в Docker контейнере** - `npm run build` выполняется внутри контейнера `api_frontend`, результат коммитится в `public/` и пушится в git. **КРИТИЧНО:** Перед деплоем контейнер должен быть запущен! Если `api_frontend` не запущен, команда `docker exec` завершается без ошибки, но сборка НЕ выполняется.
2. **APP_KEY критичен** - без него Laravel не работает, находится в `.env.production` на сервере
3. **Два типа деплоя** - система автоматически определяет нужен ли rebuild Docker образа
4. **Cron для статусов** - задачи автоматически переходят из "запланированных" в "новые" в полночь по МСК
5. **Локальная БД** - при разработке используется отдельная MySQL в контейнере (база `api`), НЕ production
6. **Storage директории** - при первом запуске нужно создать структуру директорий `storage/` и установить права для www-data
7. **Composer в контейнере** - зависимости устанавливаются внутри контейнера от root пользователя из-за volume mount
8. **Проверка результата сборки обязательна** - после `npm run build` всегда нужно проверять `git status public/`. Если изменений нет, значит сборка не выполнилась и деплоить нечего
