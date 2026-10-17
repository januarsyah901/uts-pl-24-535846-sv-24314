#!/bin/sh
set -e

# Generate APP_KEY jika belum terdefinisi
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY belum disetel, membuat kunci aplikasi..."
    php artisan key:generate --force || true
fi

# Tunggu database siap jika DB_HOST dikonfigurasi
if [ -n "$DB_HOST" ]; then
    echo "Menunggu koneksi database di $DB_HOST:${DB_PORT:-3306}..."
    until php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch (Exception \$e) { exit(1); }"; do
        sleep 2
    done
    echo "Koneksi database berhasil. Menjalankan migrasi dan seeder..."
    php artisan migrate --force || true
    php artisan db:seed --force || true
fi

# Cache config & route untuk performa
php artisan config:clear || true
php artisan route:clear || true

echo "Memulai server FrankenPHP pada port 8000..."
exec frankenphp run --config /etc/caddy/Caddyfile
