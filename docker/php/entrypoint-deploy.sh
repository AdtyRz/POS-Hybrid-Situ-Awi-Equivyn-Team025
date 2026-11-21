#!/bin/sh
set -eu

cd /var/www/html

# Platform seperti Coolify kadang memberi PORT sendiri. Apache default 80.
PORT="${PORT:-80}"
if [ "$PORT" != "80" ]; then
    echo "[deploy] Apache mendengarkan port ${PORT}..."
    sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

wait_for_db() {
    if [ -z "${DB_HOST:-}" ]; then
        echo "[deploy] DB_HOST kosong; pengecekan database dilewati."
        return 0
    fi

    attempt=1
    until php -r '
        $host = getenv("DB_HOST") ?: "127.0.0.1";
        $port = getenv("DB_PORT") ?: "5432";
        $name = getenv("DB_DATABASE") ?: "laravel";
        $user = getenv("DB_USERNAME") ?: "laravel";
        $pass = getenv("DB_PASSWORD") ?: "";
        try {
            new PDO("pgsql:host={$host};port={$port};dbname={$name}", $user, $pass);
        } catch (Throwable $e) {
            exit(1);
        }
    '; do
        if [ "$attempt" -ge 30 ]; then
            echo "[deploy] Database tidak siap setelah 60 detik." >&2
            exit 1
        fi
        echo "[deploy] Menunggu database... ($attempt/30)"
        attempt=$((attempt + 1))
        sleep 2
    done
}

wait_for_db

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

php artisan storage:link >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "[deploy] Menjalankan migrasi..."
    php artisan migrate --force
fi

if [ "${RUN_SEED:-false}" = "true" ]; then
    echo "[deploy] Menjalankan seeder..."
    php artisan db:seed --force
fi

echo "[deploy] Membuat cache produksi..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache

exec "$@"
