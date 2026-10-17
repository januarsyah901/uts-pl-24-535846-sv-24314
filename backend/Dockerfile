# Gunakan base image resmi FrankenPHP dengan versi spesifik
FROM dunglas/frankenphp:1.4.4-php8.4-bookworm

# Konfigurasi direktori kerja
WORKDIR /app

# Pasang ekstensi PHP yang dibutuhkan Laravel dan MySQL
RUN install-php-extensions \
    pdo_mysql \
    pcntl \
    opcache \
    bcmath \
    zip

# Salin binary Composer resmi
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Salin berkas definisi dependensi lebih dulu untuk memanfaatkan caching layer Docker
COPY composer.json composer.lock ./

# Pasang dependensi tanpa menjalankan skrip dan autoloader awal
RUN composer install --no-dev --no-interaction --no-autoloader --no-scripts --prefer-dist

# Salin seluruh kode aplikasi ke dalam container
COPY . ./

# Hasilkan autoloader teroptimasi untuk mode produksi
RUN composer dump-autoload --optimize --no-dev

# Salin Caddyfile kustom untuk konfigurasi port dan root web
COPY Caddyfile /etc/caddy/Caddyfile

# Atur perizinan folder storage dan cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x docker-entrypoint.sh

# Port internal FrankenPHP
EXPOSE 8000

# Eksekusi melalui entrypoint script
ENTRYPOINT ["/app/docker-entrypoint.sh"]
