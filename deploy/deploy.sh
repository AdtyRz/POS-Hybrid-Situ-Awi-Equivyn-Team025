#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/saung-situ-awi}"
PHP_BIN="${PHP_BIN:-php}"

cd "$APP_DIR"

selesaikan() {
    $PHP_BIN artisan up >/dev/null 2>&1 || true
}

trap selesaikan EXIT

$PHP_BIN artisan down --retry=60 || true

composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci --no-audit --no-fund
npm run build
$PHP_BIN artisan migrate --force
$PHP_BIN artisan storage:link --force

$PHP_BIN artisan optimize:clear
$PHP_BIN artisan optimize
$PHP_BIN artisan queue:restart || true
$PHP_BIN artisan reverb:restart || true

$PHP_BIN artisan up
trap - EXIT
