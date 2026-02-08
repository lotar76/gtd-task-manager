---
name: deploy
description: Деплой GTD Task Manager на production сервер
---

# Инструкция для деплоя

Когда пользователь вызывает `/deploy`, выполни следующие шаги:

## 1. Сборка фронтенда
Выполни команду через Bash tool:
```bash
docker exec -e VITE_API_URL=/api api_frontend npm run build
```
**ВАЖНО:** Переменная `VITE_API_URL=/api` обязательна! Без неё контейнер использует dev-URL `http://localhost:9090/api`, который «вшивается» в бандл и ломает production.

## 2. Очистка старых assets и добавление в git
Удали старые asset-файлы, которых нет в текущем manifest, затем добавь изменения:
```bash
python3 -c "
import json, os, glob
with open('public/.vite/manifest.json') as f:
    data = json.load(f)
active = set()
for key, val in data.items():
    if 'file' in val: active.add(val['file'])
    if 'css' in val:
        for c in val['css']: active.add(c)
for filepath in glob.glob('public/assets/*'):
    relative = 'assets/' + os.path.basename(filepath)
    if relative not in active:
        os.remove(filepath)
"
```
Затем выполни `git add .` через Bash tool.

## 3. Определение типа деплоя
- Выполни `git diff --cached --name-only` чтобы получить список изменённых файлов
- Проверь список изменённых файлов:
  - Если в списке есть `Dockerfile`, `docker-compose.prod.yml` или файлы из папки `docker/*` → тип деплоя: **FULL rebuild**
  - Иначе → тип деплоя: **FAST deploy**

## 4. Commit и push
- Если тип деплоя **FULL rebuild**: выполни `git commit -m "🐳 Deploy (full rebuild)"`
- Если тип деплоя **FAST deploy**: выполни `git commit -m "⚡ Deploy"`
- Выполни `git push` (на текущую ветку)

## 5. Запуск на сервере
Сначала сбрось локальные изменения на сервере, затем запусти деплой:

**FAST deploy:**
```bash
ssh root@37.220.82.214 "cd /home/projects/todo && git checkout -- . && git clean -fd public/assets/ && git pull"
```

**FULL rebuild:**
```bash
ssh root@37.220.82.214 "cd /home/projects/todo && git checkout -- . && git clean -fd public/assets/ && git pull && docker-compose -f docker-compose.prod.yml up -d --build"
```

## 6. Очистка кэшей Laravel (ОБЯЗАТЕЛЬНО!)
**Всегда выполняй после каждого деплоя**, иначе Laravel использует старые закэшированные роуты/конфиги и всё ломается:
```bash
ssh root@37.220.82.214 "cd /home/projects/todo && docker-compose -f docker-compose.prod.yml exec -T app php artisan route:clear && docker-compose -f docker-compose.prod.yml exec -T app php artisan config:clear && docker-compose -f docker-compose.prod.yml exec -T app php artisan view:clear && docker-compose -f docker-compose.prod.yml exec -T app php artisan config:cache && docker-compose -f docker-compose.prod.yml exec -T app php artisan route:cache && docker-compose -f docker-compose.prod.yml exec -T app php artisan view:cache"
```

## 7. Миграции (если есть новые)
Проверь, есть ли в коммите новые файлы `database/migrations/`. Если да:
```bash
ssh root@37.220.82.214 "cd /home/projects/todo && docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate --force"
```

## 8. Сообщи результат
После завершения сообщи пользователю:
- Тип деплоя (FAST или FULL)
- Статус завершения
- Были ли миграции
- Сайт: https://todo.e-api.ru

---

## Известные грабли (чтобы не наступать повторно)

1. **VITE_API_URL** — контейнер `api_frontend` имеет `VITE_API_URL=http://localhost:9090/api` из docker-compose.local.yml. При билде ВСЕГДА передавать `-e VITE_API_URL=/api`.

2. **Route cache** — если удалён/переименован контроллер, а route:cache не очищен, Laravel падает с 500 на ВСЕХ запросах. Поэтому шаг 6 обязателен.

3. **Config cache** — новые env-переменные не подхватываются без `config:cache`. После добавления переменных в `.env` на сервере ОБЯЗАТЕЛЬНО пересоздать кэш.

4. **Docker env_file** — `docker-compose.prod.yml` использует `env_file: .env` (не `environment:`). Все env-переменные должны быть в `.env` на сервере.

5. **git push** — пушим в текущую ветку (не обязательно main). На сервере `git pull` подтянет правильную ветку.
