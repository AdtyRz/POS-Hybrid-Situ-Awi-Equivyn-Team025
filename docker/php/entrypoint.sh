#!/bin/sh
set -eu

cd /var/www/html

wait_for_db() {
    attempt=1
    until php -r '
        $host = getenv("DB_HOST") ?: "db";
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
            echo "[entrypoint] Database tidak siap setelah 60 detik." >&2
            exit 1
        fi
        echo "[entrypoint] Menunggu database... ($attempt/30)"
        attempt=$((attempt + 1))
        sleep 2
    done
}

wait_for_db

# Volume `public` dibuat sekali dan tidak ikut berubah saat image di-rebuild.
# Salin ulang aset hasil build agar Nginx selalu menyajikan versi terbaru.
if [ -d /opt/situawi/public ]; then
    echo "[entrypoint] Menyinkronkan aset public..."
    rm -rf /var/www/html/public/build
    cp -a /opt/situawi/public/. /var/www/html/public/
    chown -R www-data:www-data /var/www/html/public
fi

mkdir -p \
    /var/www/html/storage/app/public \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage

echo "[entrypoint] Menjalankan migrasi..."
php artisan migrate --force

if [ "${RUN_SEED:-false}" = "true" ]; then
    echo "[entrypoint] Menjalankan seeder..."
    php artisan db:seed --force
fi

php artisan storage:link >/dev/null 2>&1 || true

echo "[entrypoint] Membuat cache produksi..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
