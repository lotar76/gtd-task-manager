# 📋 GTD TODO Трекер

Полнофункциональный TODO трекер построенный по методологии GTD (Getting Things Done) с поддержкой совместной работы команд.

## 🎯 Особенности

### Backend (Laravel 11 API)
- ✅ **REST API** - полноценное API на Laravel 11
- ✅ **GTD методология** - Inbox, Next Actions, Waiting, Someday, Completed
- ✅ **Иерархия**: Цели → Проекты → Задачи
- ✅ **Командная работа** - Workspaces с ролями (owner, admin, member, viewer)
- ✅ **Авторизация** - Laravel Sanctum (токены)
- ✅ **Контексты** - @home, @work, @phone, @email
- ✅ **Теги** - гибкая система меток
- ✅ **Комментарии** - обсуждение задач
- ✅ **Вложения** - файлы через S3
- ✅ **Приоритеты** - low, medium, high, urgent
- ✅ **Даты** - due_date, просроченные, на сегодня
- ✅ **Policies** - гранулярные права доступа

### Frontend (Vue 3)
- ✅ **Vue 3 Composition API** - современный подход
- ✅ **Pinia** - управление состоянием
- ✅ **Vue Router** - маршрутизация
- ✅ **TailwindCSS** - стильный дизайн
- ✅ **Mobile-First** - адаптивная верстка
- ✅ **2 вида отображения**:
  - 📋 **Списочный** - классический список задач с фильтрами
  - 📅 **Календарь** - месячный вид с задачами
- ✅ **Heroicons** - красивые иконки
- ✅ **Dayjs** - работа с датами

## 🗂️ Структура проекта

```
/home/projects/api/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/V1/
│   │   │   ├── AuthController.php
│   │   │   ├── WorkspaceController.php
│   │   │   ├── TaskController.php
│   │   │   ├── ProjectController.php
│   │   │   ├── GoalController.php
│   │   │   ├── ContextController.php
│   │   │   ├── TagController.php
│   │   │   ├── CommentController.php
│   │   │   └── AttachmentController.php
│   │   ├── Policies/
│   │   └── Responses/ApiResponse.php
│   ├── Models/
│   │   ├── Workspace.php
│   │   ├── Task.php
│   │   ├── Project.php
│   │   ├── Goal.php
│   │   ├── Context.php
│   │   ├── Tag.php
│   │   ├── Comment.php
│   │   └── Attachment.php
│   └── Services/
│       ├── TaskService.php
│       └── WorkspaceService.php
├── resources/
│   └── js/
│       ├── components/
│       │   ├── tasks/TaskList.vue
│       │   └── common/NavLink.vue
│       ├── views/
│       │   ├── Auth/
│       │   │   ├── Login.vue
│       │   │   └── Register.vue
│       │   ├── Layout/MainLayout.vue
│       │   ├── Tasks/
│       │   │   ├── Inbox.vue
│       │   │   ├── NextActions.vue
│       │   │   ├── Today.vue
│       │   │   ├── Waiting.vue
│       │   │   ├── Someday.vue
│       │   │   └── Calendar.vue
│       │   ├── Projects/Index.vue
│       │   └── Goals/Index.vue
│       ├── stores/
│       │   ├── auth.js
│       │   ├── workspace.js
│       │   └── tasks.js
│       ├── services/api.js
│       ├── router/index.js
│       └── App.vue
├── database/
│   ├── migrations/
│   └── seeders/GtdDemoSeeder.php
└── routes/api.php
```

## 🚀 Быстрый старт

### 1. Установка зависимостей

#### Backend
```bash
cd /home/projects/api
docker compose -f docker-compose.local.yml up -d
docker compose -f docker-compose.local.yml exec app composer install
```

#### Frontend
```bash
npm install
```

### 2. Настройка окружения

Убедитесь что `.env` настроен:
```env
# Database
DB_CONNECTION=mysql
DB_HOST=83.147.245.158
DB_PORT=3306
DB_DATABASE=api
DB_USERNAME=admin_api
DB_PASSWORD=XsJJf+t@U;Q0D$

# S3 Storage
AWS_ACCESS_KEY_ID=BQ4LNIIBTS06VYRJX35Q
AWS_SECRET_ACCESS_KEY=R7OL2KcuBH5G689T7JtasemUJqabSkSzUOXjywyP
AWS_DEFAULT_REGION=ru-1
AWS_BUCKET=356a5ee6-1eea7a8c-09e6-48c3-b4e0-1b4b9ac07797
AWS_ENDPOINT=https://s3.twcstorage.ru
AWS_URL=https://s3.twcstorage.ru
AWS_USE_PATH_STYLE_ENDPOINT=true
```

### 3. Миграции и демо-данные

```bash
docker compose -f docker-compose.local.yml exec app php artisan migrate
docker compose -f docker-compose.local.yml exec app php artisan db:seed --class=GtdDemoSeeder
```

**Тестовые пользователи:**
- 📧 `owner@example.com` / 🔑 `password`
- 📧 `anna@example.com` / 🔑 `password`
- 📧 `sergey@example.com` / 🔑 `password`

### 4. Запуск

#### Backend (уже запущен в Docker)
```bash
# Проверка
curl http://localhost:9090/api/test
```

#### Frontend (Vite dev server)
```bash
npm run dev
# Откроется на http://localhost:5173
```

## 📡 API Endpoints

### Авторизация
```
POST /api/v1/register
POST /api/v1/login
POST /api/v1/logout
GET  /api/v1/me
```

### Workspaces (Команды)
```
GET    /api/v1/workspaces
POST   /api/v1/workspaces
GET    /api/v1/workspaces/{id}
PUT    /api/v1/workspaces/{id}
DELETE /api/v1/workspaces/{id}
GET    /api/v1/workspaces/{id}/members
POST   /api/v1/workspaces/{id}/members
DELETE /api/v1/workspaces/{id}/members/{user}
```

### GTD Виды задач
```
GET /api/v1/workspaces/{id}/inbox          # Входящие
GET /api/v1/workspaces/{id}/next-actions   # Следующие действия
GET /api/v1/workspaces/{id}/waiting        # Ожидание
GET /api/v1/workspaces/{id}/someday        # Когда-нибудь
GET /api/v1/workspaces/{id}/today          # Сегодня
GET /api/v1/workspaces/{id}/my-tasks       # Мои задачи
GET /api/v1/workspaces/{id}/calendar       # Календарь
```

### Задачи (CRUD)
```
GET    /api/v1/workspaces/{id}/tasks
POST   /api/v1/workspaces/{id}/tasks
GET    /api/v1/workspaces/{id}/tasks/{taskId}
PUT    /api/v1/workspaces/{id}/tasks/{taskId}
DELETE /api/v1/workspaces/{id}/tasks/{taskId}
POST   /api/v1/workspaces/{id}/tasks/{taskId}/complete
POST   /api/v1/workspaces/{id}/tasks/{taskId}/move
POST   /api/v1/workspaces/{id}/tasks/{taskId}/assign
```

### Проекты
```
GET    /api/v1/workspaces/{id}/projects
POST   /api/v1/workspaces/{id}/projects
GET    /api/v1/workspaces/{id}/projects/{projectId}
PUT    /api/v1/workspaces/{id}/projects/{projectId}
DELETE /api/v1/workspaces/{id}/projects/{projectId}
```

### Цели
```
GET    /api/v1/workspaces/{id}/goals
POST   /api/v1/workspaces/{id}/goals
GET    /api/v1/workspaces/{id}/goals/{goalId}
PUT    /api/v1/workspaces/{id}/goals/{goalId}
DELETE /api/v1/workspaces/{id}/goals/{goalId}
```

### Контексты и Теги
```
GET    /api/v1/workspaces/{id}/contexts
POST   /api/v1/workspaces/{id}/contexts
GET    /api/v1/workspaces/{id}/tags
POST   /api/v1/workspaces/{id}/tags
```

### Комментарии
```
GET    /api/v1/workspaces/{id}/tasks/{taskId}/comments
POST   /api/v1/workspaces/{id}/tasks/{taskId}/comments
DELETE /api/v1/workspaces/{id}/comments/{commentId}
```

### Вложения (S3)
```
POST   /api/v1/workspaces/{id}/tasks/{taskId}/attachments
GET    /api/v1/workspaces/{id}/attachments/{attachmentId}
GET    /api/v1/workspaces/{id}/attachments/{attachmentId}/download
DELETE /api/v1/workspaces/{id}/attachments/{attachmentId}
```

## 🎨 Frontend - Основные возможности

### Авторизация
- Регистрация нового пользователя
- Вход в систему
- Автоматическая проверка токена

### GTD Workflow
1. **Inbox (Входящие)** - захват всех задач
2. **Next Actions (Следующие действия)** - готовые к выполнению
3. **Waiting (Ожидание)** - делегированные/заблокированные
4. **Someday (Когда-нибудь)** - идеи для будущего
5. **Today (Сегодня)** - задачи на сегодня

### Виды отображения
- **📋 Список** - компактный списочный вид с метаданными
- **📅 Календарь** - месячный календарь с задачами по датам

### Фильтры
- **Мои задачи** / **Все задачи** - переключатель
- По проектам
- По контекстам
- По датам

### Mobile-First дизайн
- Адаптивная верстка для мобильных устройств
- Сворачивающееся меню
- Touch-friendly интерфейс

## 🔧 Технологии

### Backend
- Laravel 11
- PHP 8.2+
- MySQL
- S3-compatible storage (TimewEB)
- Docker & Docker Compose

### Frontend
- Vue 3 (Composition API)
- Vite
- Pinia (State Management)
- Vue Router
- TailwindCSS
- Heroicons
- Axios
- Dayjs

## 📝 Примеры использования

### Создание задачи
```javascript
const task = await tasksStore.createTask({
  title: 'Написать документацию',
  description: 'Подробная документация по API',
  status: 'inbox',
  priority: 'high',
  due_date: '2025-11-25',
  project_id: 1,
  context_id: 2,
  tags: [1, 3]
})
```

### Завершение задачи
```javascript
await tasksStore.completeTask(taskId)
```

### Фильтрация задач
```javascript
// Получить только мои задачи
await tasksStore.fetchInbox(true)

// Получить все задачи
await tasksStore.fetchInbox(false)
```

## 🚢 Production

Для production используйте:
- `docker-compose.prod.yml` для backend с Traefik
- `npm run build` для frontend
- Настройте домен `e-api.ru`

## 📚 Дополнительная информация

См. подробную документацию:
- `README.md` - основная документация backend
- `DEPLOYMENT.md` - инструкции по развертыванию

## 🤝 Разработка

Создано с учетом best practices:
- PSR-12 coding standards
- SOLID principles
- Repository pattern
- Service layer
- Policy-based authorization
- API versioning
- Comprehensive testing

---

**Автор:** AI Assistant  
**Дата:** 21 ноября 2025  
**Версия:** 1.0.0


