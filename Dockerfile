FROM php:8.2-cli

# 1. Cài đặt các công cụ cần thiết của Ubuntu
RUN apt-get update -y && apt-get install -y git unzip libpq-dev libcurl4-gnutls-dev

# 2. Cài đặt Composer (Công cụ quản lý thư viện PHP)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 3. Cài đặt extension kết nối MySQL cho Laravel
RUN docker-php-ext-install pdo pdo_mysql bcmath

# 4. Copy toàn bộ code từ máy vào bên trong server ảo
WORKDIR /app
COPY . /app

# 5. Cài đặt các package của project
RUN composer install --optimize-autoloader --no-dev

# 6. Mở cổng mạng
EXPOSE 10000

# 7. Khởi chạy Laravel Server
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-10000}