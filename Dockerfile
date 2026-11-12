FROM php:8.3-cli
RUN apt-get update -y && apt-get install -y git unzip libpq-dev libcurl4-gnutls-dev libzip-dev
RUN docker-php-ext-install pdo pdo_mysql bcmath zip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /app
COPY . /app
RUN composer install --optimize-autoloader --no-dev

# Cấp quyền và đảm bảo liên kết lưu trữ công khai (storage link) chính xác
RUN rm -rf /app/public/storage && php artisan storage:link
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache
RUN chmod -R 775 /app/storage /app/bootstrap/cache

EXPOSE 10000
CMD sh -c "php artisan storage:link && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"