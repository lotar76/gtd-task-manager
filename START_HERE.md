# 🚀 Запуск проекта - Инструкция

## ✅ Что уже готово

### 1. База данных
- **Статус:** ✅ Подключение проверено и работает
- **Хост:** 83.147.245.158
- **База:** api
- **SSL:** Активно (TLS_AES_256_GCM_SHA384)
- **Версия MySQL:** 8.4.4-4

### 2. S3 Хранилище (TimewEB Cloud Storage)
- **Статус:** ✅ Конфигурация проверена
- **Endpoint:** https://s3.twcstorage.ru
- **Bucket:** 356a5ee6-1eea7a8c-09e6-48c3-b4e0-1b4b9ac07797

### 3. Конфигурация
- **Статус:** ✅ Файл .env создан и настроен
- **Режим:** local (для разработки)
- **Debug:** включен

## ⚠️ Что нужно сделать

### Шаг 1: Настроить Docker в WSL 2

У вас WSL 2, но Docker не интегрирован. Выберите один из вариантов:

#### Вариант А: Docker Desktop (рекомендуется)

1. Скачайте и установите [Docker Desktop для Windows](https://www.docker.com/products/docker-desktop/)

2. Запустите Docker Desktop

3. Откройте Settings → Resources → WSL Integration

4. Включите:
   - ☑️ "Enable integration with my default WSL distro"
   - ☑️ Ваш дистрибутив Ubuntu/Debian

5. Нажмите "Apply & Restart"

6. В PowerShell выполните:
   ```powershell
   wsl --shutdown
   ```

7. Снова откройте WSL и проверьте:
   ```bash
   docker --version
   docker-compose --version
   ```

#### Вариант Б: Docker внутри WSL

```bash
# Обновление пакетов
sudo apt update

# Установка Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Установка Docker Compose
sudo apt install docker-compose-plugin

# Запуск Docker
sudo service docker start

# Добавление пользователя в группу docker
sudo usermod -aG docker $USER

# Перезайти в WSL
exit
# И открыть заново
```

### Шаг 2: Настроить Traefik

После установки Docker, создайте и запустите Traefik:

```bash
# Создание директории для Traefik
mkdir -p ~/traefik
cd ~/traefik

# Создание docker-compose.yml для Traefik
cat > docker-compose.yml << 'TRAEFIK_EOF'
version: '3.8'

services:
  traefik:
    image: traefik:v2.10
    container_name: traefik
    restart: unless-stopped
    command:
      - "--api.dashboard=true"
      - "--api.insecure=true"
      - "--providers.docker=true"
      - "--providers.docker.exposedbydefault=false"
      - "--entrypoints.web.address=:80"
      - "--entrypoints.websecure.address=:443"
      - "--log.level=INFO"
    ports:
      - "80:80"
      - "443:443"
      - "8080:8080"
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
    networks:
      - traefik

networks:
  traefik:
    name: traefik
    driver: bridge
TRAEFIK_EOF

# Запуск Traefik
docker-compose up -d

# Проверка
docker ps | grep traefik
```

**Dashboard будет доступен:** http://localhost:8080

### Шаг 3: Добавить домен в hosts

**В Windows** откройте PowerShell от администратора и выполните:

```powershell
Add-Content C:\Windows\System32\drivers\etc\hosts "127.0.0.1 api.local.test"
```

Или вручную отредактируйте файл `C:\Windows\System32\drivers\etc\hosts` и добавьте:
```
127.0.0.1 api.local.test
```

### Шаг 4: Запустить Laravel API

```bash
cd /home/projects/api

# Запуск автоматической установки
./scripts/setup.sh
```

**Или вручную:**

```bash
cd /home/projects/api

# Запуск контейнеров
docker-compose up -d --build

# Установка зависимостей
docker-compose exec app composer install --optimize-autoloader

# Генерация ключа приложения
docker-compose exec app php artisan key:generate

# Запуск миграций
docker-compose exec app php artisan migrate

# Создание ролей и разрешений
docker-compose exec app php artisan db:seed

# Настройка прав
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Шаг 5: Проверить работу

```bash
# Проверка статуса контейнеров
docker-compose ps

# Просмотр логов
docker-compose logs -f app

# Тестирование API
curl -X POST http://api.local.test/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

## 📱 Быстрые команды после запуска

### Просмотр логов
```bash
docker-compose logs -f app
```

### Запуск миграций
```bash
docker-compose exec app php artisan migrate
```

### Запуск тестов
```bash
docker-compose exec app php artisan test
```

### Создание админа
```bash
docker-compose exec app php artisan tinker
# В tinker:
>>> $user = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('admin123')]);
>>> $user->assignRole('admin');
```

### Остановка проекта
```bash
docker-compose down
```

### Перезапуск проекта
```bash
docker-compose restart
```

## 🔧 Полезные ссылки

- **API:** http://api.local.test
- **Traefik Dashboard:** http://localhost:8080
- **Документация:** README.md
- **Примеры API:** API_EXAMPLES.md

## ❓ Проблемы?

### Docker не запускается
```bash
# Проверка статуса
sudo service docker status

# Запуск
sudo service docker start
```

### Контейнер не стартует
```bash
# Просмотр логов
docker-compose logs app

# Пересборка
docker-compose down
docker-compose up -d --build --force-recreate
```

### База данных не подключается
```bash
# Проверка в контейнере
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### Проблемы с правами
```bash
# Исправление прав
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

## 📊 Статус компонентов

| Компонент | Статус | Примечание |
|-----------|--------|------------|
| База данных | ✅ Готово | MySQL 8.4.4-4, SSL активен |
| S3 хранилище | ✅ Готово | TimewEB Cloud Storage |
| Конфигурация .env | ✅ Готово | Все параметры настроены |
| Docker | ⏳ Требуется | Установите Docker Desktop |
| Traefik | ⏳ Требуется | Запустите после Docker |
| Laravel миграции | ⏳ Ожидание | Запустятся после Docker |

---

**После выполнения всех шагов проект будет полностью готов к работе!** 🎉

