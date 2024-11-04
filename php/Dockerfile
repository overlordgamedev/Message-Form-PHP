# Указываем базовый образ
FROM php:8.0-apache

# Устанавливаем необходимые пакеты
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zlib1g-dev

# Устанавливаем расширения PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd pdo pdo_mysql zip

# Копируем php.ini, если требуется
COPY php.ini /usr/local/etc/php/
