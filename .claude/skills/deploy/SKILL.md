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
- Выполни `git push origin main`

## 5. Запуск на сервере
Сначала сбрось локальные изменения на сервере (они могут быть в `public/`), затем запусти деплой:
- Если тип деплоя **FULL rebuild**: выполни `ssh root@37.220.82.214 "cd /home/projects/todo && git checkout -- . && git clean -fd public/assets/ && ./scripts/deploy.sh"`
- Если тип деплоя **FAST deploy**: выполни `ssh root@37.220.82.214 "cd /home/projects/todo && git checkout -- . && git clean -fd public/assets/ && ./scripts/deploy-fast.sh"`

## 6. Сообщи результат
После завершения сообщи пользователю:
- ✅ Тип деплоя (FAST ~10 сек или FULL ~60 сек)
- ✅ Статус завершения
- 🌐 Сайт: https://todo.e-api.ru
