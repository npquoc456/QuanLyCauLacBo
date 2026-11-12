FROM php:8.3-cli

# 1. Cài đặt các công cụ và thư viện lõi (bổ sung libzip-dev)
RUN apt-get update -y && apt-get install -y git unzip libpq-dev libcurl4-gnutls-dev libzip-dev

# 2. Cài đặt PHP extensions cần thiết cho Laravel
RUN docker-php-ext-install pdo pdo_mysql bcmath zip

# 3. Cài đặt Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 4. Copy mã nguồn vào server
WORKDIR /app
COPY . /app

# 5. Cài đặt các package của project
RUN composer install --optimize-autoloader --no-dev

# 6. Mở mạng và chạy server
EXPOSE 10000
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-10000}