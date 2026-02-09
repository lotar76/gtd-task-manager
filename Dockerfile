FROM php:8.2-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка PHP расширений
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установка рабочей директории
WORKDIR /var/www/html

# Копирование файлов проекта
COPY . .

# Создание необходимых директорий если их нет
RUN mkdir -p /var/www/html/storage/framework/{sessions,views,cache} \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache

# Установка прав доступа
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Аргумент для определения окружения
ARG APP_ENV=production

# Установка зависимостей Composer (пропускаем если vendor уже есть)
RUN if [ ! -d "vendor" ]; then \
        if [ "$APP_ENV" = "production" ]; then \
            COMPOSER_PROCESS_TIMEOUT=600 composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist; \
        else \
            COMPOSER_PROCESS_TIMEOUT=600 composer install --optimize-autoloader --no-interaction --prefer-dist; \
        fi \
    fi

# Копирование кастомного конфига PHP-FPM (без user/group директив)
COPY docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf

USER www-data

EXPOSE 9000

CMD ["php-fpm"]

