# Используем официальный PHP образ с PHP-FPM
FROM php:8.2-fpm

# Установка зависимостей для PHP и расширений
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    git \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer global require laravel/installer
RUN export PATH="~/.composer/vendor/bin:$PATH"

# Установка рабочей директории
WORKDIR /var/www

# Копируем исходники проекта
COPY .. .

