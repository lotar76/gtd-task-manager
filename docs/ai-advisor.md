# AI-советчик («Зеркало жизни»)

Временно **отключён** на фронте и бэке, чтобы не тратить токены OpenRouter
до того, как мы вернёмся к доработке. Этот файл — карта пайплайна, чтобы
быстро вспомнить «что где».

## Как включить обратно

1. **Бэк:** в `.env` поставить `AI_MIRROR_ENABLED=true` (на локали и/или на проде).
   Конфиг — [config/services.php](../config/services.php), ключ `services.ai_mirror.enabled`.
2. **Фронт:** в [resources/js/stores/dashboard.js](../resources/js/stores/dashboard.js)
   константу `AI_MIRROR_ENABLED` поменять на `true`. Без этого даже при включённом
   бэке фронт не будет дёргать API и не покажет баннер.
3. Пересобрать фронт (`npm run build`) и задеплоить.

После включения советчик начнёт работать как раньше — кэш в БД (`ai_mirror_cache`)
сохраняется, ничего не сносится.

## Текущее состояние блокировки

- **Контроллер** [app/Http/Controllers/Api/V1/DashboardController.php](../app/Http/Controllers/Api/V1/DashboardController.php),
  метод `getAiMessage` — если `services.ai_mirror.enabled = false`, сразу
  отдаёт `{ disabled: true }` без обращения к `AiMirrorService` и OpenRouter.
- **Store** [resources/js/stores/dashboard.js](../resources/js/stores/dashboard.js) —
  `fetchAiMessage` при `AI_MIRROR_ENABLED = false` ничего не делает,
  `aiEnabled` экспортируется наружу.
- **Компонент** [resources/js/components/dashboard/WorkspaceDashboard.vue](../resources/js/components/dashboard/WorkspaceDashboard.vue) —
  `<AiHeroBanner v-if="dashboardStore.aiEnabled">`. Сетка дашборда для
  периода `day` ужимается с 3 колонок до 2, чтобы не было пустого слота.

## Архитектура (для возврата позже)

### Frontend

| Файл | Что |
|------|-----|
| [resources/js/views/Dashboard.vue](../resources/js/views/Dashboard.vue) | Точка входа: `onMounted` → `store.fetchAll(silent=true)`; кнопки «Проанализировать» / «нужно проанализировать состояние» вызывают `handleAnalyzeAi(force?)` |
| [resources/js/stores/dashboard.js](../resources/js/stores/dashboard.js) | Pinia store: `fetchLifeMirror`, `fetchAiMessage(force, silent)`, `setPeriod`, `selectedPeriod` (day/week/month/year, в localStorage) |
| [resources/js/components/dashboard/WorkspaceDashboard.vue](../resources/js/components/dashboard/WorkspaceDashboard.vue) | Рендер баннера советчика (для `day` — компактная колонка, для остальных — широкий блок сверху) |
| [resources/js/components/dashboard/AiHeroBanner.vue](../resources/js/components/dashboard/AiHeroBanner.vue) | UI: текст совета, рекомендации, мetka времени, кнопки «Проанализировать» (emit `analyze`) и «нужно проанализировать состояние» (emit `refresh` → force=true) |

### Backend

| Файл | Что |
|------|-----|
| [app/Http/Controllers/Api/V1/DashboardController.php](../app/Http/Controllers/Api/V1/DashboardController.php) | `getAiMessage()` — endpoint `GET /v1/dashboard/ai-message?period=…&force=0\|1`, валидация и делегация в сервис |
| [app/Services/AiMirrorService.php](../app/Services/AiMirrorService.php) | Сердце: системный промпт, контекстные промпты по периодам, сборка `buildAiInput()` (сферы, цели, потоки), вызов OpenRouter (`callOpenRouter`), парсинг ответа, fallback на статические правила |
| [app/Services/DashboardService.php](../app/Services/DashboardService.php) | `getLifeMirrorData($workspaceId, $period)` — данные для контекста промпта и для самого дашборда |
| [app/Models/AiMirrorCache.php](../app/Models/AiMirrorCache.php) | Eloquent-модель кэша |
| [database/migrations/2026_02_15_000002_create_ai_mirror_cache_table.php](../database/migrations/2026_02_15_000002_create_ai_mirror_cache_table.php) | Миграция таблицы `ai_mirror_cache` |
| [config/openrouter.php](../config/openrouter.php) | Базовый URL, модель, API-ключ из env (`OPENROUTER_API_KEY`, `OPENROUTER_MODEL`) |
| [routes/api.php](../routes/api.php) | Роут `GET /v1/dashboard/ai-message` |

### Кэш

Таблица `ai_mirror_cache`, уникальный ключ `(workspace_id, period, period_key)`:

| Период | `period_key` | TTL |
|--------|--------------|-----|
| `day` | `Y-m-d` | 60 мин |
| `week` | дата начала недели (понедельник) | 1 день |
| `month` | `Y-m` | 1 день |
| `year` | `Y` | 1 неделя |

Если `force=true` — кэш игнорируется, генерится заново.

### Cron

В [app/Console/Kernel.php](../app/Console/Kernel.php) **нет** задач для совета —
он генерится только on-demand при запросе с фронта. Зарегистрированы только:
`tasks:recalculate-statuses`, `telegram:morning-digest`,
`telegram:task-reminders`, `telegram:overdue-alerts`.

## Что обсудить, когда вернёмся

1. **Лишние вызовы OpenRouter на каждый mount Dashboard.** Силент-режим, но
   запрос на бэк всегда идёт; если кэш протух — холодный старт ~5–30 сек.
2. **TTL для `day` = 60 минут.** Внутри одного дня может потребовать повторный
   вызов в OpenRouter — стоит ли держать совет на весь день?
3. **Нет предгенерации.** Первый посетитель утром платит за холодный старт.
   Можно добавить cron `ai-mirror:warm` в начале дня для активных воркспейсов.
4. **`faith_enabled`** идёт в промпт, но не входит в ключ кэша — изменение
   флага не инвалидирует старый совет.
5. **Стоимость.** Модель по умолчанию `anthropic/claude-sonnet-4.5`,
   `max_tokens=1024`. Прикинуть бюджет, возможно перейти на Haiku 4.5
   для регулярного советчика.

## Как полностью снести (если не хотим возвращаться)

- Удалить таблицу `ai_mirror_cache` (миграция down)
- Удалить файлы из таблиц «Frontend» и «Backend» выше
- Из `routes/api.php` убрать роут
- Из конфига `services.php` — блок `ai_mirror`
- Из env — `OPENROUTER_API_KEY`, `OPENROUTER_MODEL`, `AI_MIRROR_ENABLED`
