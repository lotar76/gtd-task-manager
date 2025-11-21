# Настройка Traefik для Laravel API

## Если у вас еще нет Traefik

### Вариант 1: Базовая настройка Traefik с Docker Compose

Создайте отдельную директорию для Traefik:

```bash
mkdir traefik
cd traefik
```

Создайте `docker-compose.yml`:

```yaml
version: '3.8'

services:
  traefik:
    image: traefik:v2.10
    container_name: traefik
    restart: unless-stopped
    command:
      - "--api.dashboard=true"
      - "--providers.docker=true"
      - "--providers.docker.exposedbydefault=false"
      - "--entrypoints.web.address=:80"
      - "--entrypoints.websecure.address=:443"
      # Для локальной разработки (самоподписанные сертификаты)
      - "--certificatesresolvers.myresolver.acme.tlschallenge=true"
      - "--certificatesresolvers.myresolver.acme.email=admin@example.com"
      - "--certificatesresolvers.myresolver.acme.storage=/letsencrypt/acme.json"
      # Для разработки используйте staging сервер
      - "--certificatesresolvers.myresolver.acme.caserver=https://acme-staging-v02.api.letsencrypt.org/directory"
    ports:
      - "80:80"
      - "443:443"
      - "8080:8080"  # Dashboard
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - ./letsencrypt:/letsencrypt
    networks:
      - traefik
    labels:
      # Включение dashboard
      - "traefik.enable=true"
      - "traefik.http.routers.traefik.rule=Host(`traefik.local.test`)"
      - "traefik.http.routers.traefik.service=api@internal"
      - "traefik.http.routers.traefik.entrypoints=web"

networks:
  traefik:
    name: traefik
    driver: bridge
```

Запустите Traefik:

```bash
mkdir letsencrypt
touch letsencrypt/acme.json
chmod 600 letsencrypt/acme.json
docker compose up -d
```

### Вариант 2: Production настройка с Let's Encrypt

Для production используйте:

```yaml
version: '3.8'

services:
  traefik:
    image: traefik:v2.10
    container_name: traefik
    restart: unless-stopped
    command:
      - "--api.dashboard=true"
      - "--providers.docker=true"
      - "--providers.docker.exposedbydefault=false"
      - "--entrypoints.web.address=:80"
      - "--entrypoints.websecure.address=:443"
      # Редирект HTTP -> HTTPS
      - "--entrypoints.web.http.redirections.entrypoint.to=websecure"
      - "--entrypoints.web.http.redirections.entrypoint.scheme=https"
      # Let's Encrypt
      - "--certificatesresolvers.letsencrypt.acme.tlschallenge=true"
      - "--certificatesresolvers.letsencrypt.acme.email=admin@yourdomain.com"
      - "--certificatesresolvers.letsencrypt.acme.storage=/letsencrypt/acme.json"
      # Логирование
      - "--log.level=INFO"
      - "--accesslog=true"
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - ./letsencrypt:/letsencrypt
    networks:
      - traefik
    labels:
      - "traefik.enable=true"
      # Dashboard (защищен basic auth)
      - "traefik.http.routers.traefik.rule=Host(`traefik.yourdomain.com`)"
      - "traefik.http.routers.traefik.service=api@internal"
      - "traefik.http.routers.traefik.entrypoints=websecure"
      - "traefik.http.routers.traefik.tls.certresolver=letsencrypt"
      - "traefik.http.routers.traefik.middlewares=auth"
      # Basic Auth (username: admin, password: admin) - ИЗМЕНИТЕ!
      - "traefik.http.middlewares.auth.basicauth.users=admin:$$apr1$$h7jz6j8q$$XLIHv4fPY6b6q2q2q2q2q2"

networks:
  traefik:
    name: traefik
    driver: bridge
```

## Настройка Laravel API для работы с Traefik

В `docker-compose.yml` вашего Laravel проекта уже настроены labels:

```yaml
labels:
  - "traefik.enable=true"
  - "traefik.docker.network=traefik"
  - "traefik.http.routers.api.rule=Host(`api.local.test`)"
  - "traefik.http.routers.api.entrypoints=web"
  - "traefik.http.routers.api-secure.rule=Host(`api.local.test`)"
  - "traefik.http.routers.api-secure.entrypoints=websecure"
  - "traefik.http.routers.api-secure.tls=true"
  - "traefik.http.services.api.loadbalancer.server.port=9000"
```

## Изменение домена

Чтобы использовать другой домен (например, `api.example.com`), замените в labels:

```yaml
- "traefik.http.routers.api.rule=Host(`api.example.com`)"
- "traefik.http.routers.api-secure.rule=Host(`api.example.com`)"
- "traefik.http.routers.api-secure.tls.certresolver=letsencrypt"  # Для Let's Encrypt
```

И обновите `.env`:

```env
APP_URL=https://api.example.com
```

## Локальная разработка

Для локальной разработки добавьте в `/etc/hosts`:

```
127.0.0.1 api.local.test
127.0.0.1 traefik.local.test
```

## Проверка работы Traefik

```bash
# Проверка логов
docker logs traefik -f

# Открыть dashboard (если включен)
open http://traefik.local.test:8080

# Проверка сети
docker network inspect traefik
```

## Дополнительные middleware для Traefik

### Rate Limiting

```yaml
labels:
  - "traefik.http.middlewares.api-ratelimit.ratelimit.average=100"
  - "traefik.http.middlewares.api-ratelimit.ratelimit.burst=50"
  - "traefik.http.routers.api-secure.middlewares=api-ratelimit"
```

### GZIP Compression

```yaml
labels:
  - "traefik.http.middlewares.api-compress.compress=true"
  - "traefik.http.routers.api-secure.middlewares=api-compress"
```

### IP Whitelist

```yaml
labels:
  - "traefik.http.middlewares.api-ipwhitelist.ipwhitelist.sourcerange=127.0.0.1/32,192.168.1.0/24"
  - "traefik.http.routers.api-secure.middlewares=api-ipwhitelist"
```

### Несколько middleware одновременно

```yaml
labels:
  - "traefik.http.routers.api-secure.middlewares=api-ratelimit,api-compress,redirect-to-https"
```

## Решение проблем

### API недоступен через Traefik

1. Проверьте, что контейнеры в одной сети:
```bash
docker network inspect traefik
```

2. Проверьте логи Traefik:
```bash
docker logs traefik
```

3. Убедитесь, что порт 9000 открыт в контейнере app

### SSL сертификаты не работают

1. Для Let's Encrypt проверьте, что домен доступен из интернета
2. Проверьте файл acme.json имеет права 600
3. Проверьте email в настройках Let's Encrypt

### 502 Bad Gateway

1. Проверьте, что контейнер app запущен:
```bash
docker compose ps
```

2. Проверьте, что php-fpm слушает порт 9000:
```bash
docker compose exec app netstat -tlnp | grep 9000
```

## Дополнительная информация

- [Официальная документация Traefik](https://doc.traefik.io/traefik/)
- [Docker Provider](https://doc.traefik.io/traefik/providers/docker/)
- [Let's Encrypt](https://doc.traefik.io/traefik/https/acme/)

