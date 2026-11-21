#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

DUMP="${1:-}"
if [ -z "$DUMP" ] || [ ! -f "$DUMP" ]; then
    echo "Usage: $0 backups/db_YYYY-MM-DD_HHMMSS.dump"
    exit 1
fi

TEST_DB="${TEST_DB:-restore_test}"

echo "[restore] Membuat database uji $TEST_DB..."
docker compose exec -T -e TEST_DB="$TEST_DB" db sh -c \
    'psql -U "$POSTGRES_USER" -d postgres -v ON_ERROR_STOP=1 -c "DROP DATABASE IF EXISTS \"$TEST_DB\";"'
docker compose exec -T -e TEST_DB="$TEST_DB" db sh -c \
    'psql -U "$POSTGRES_USER" -d postgres -v ON_ERROR_STOP=1 -c "CREATE DATABASE \"$TEST_DB\";"'

echo "[restore] Memulihkan dump..."
docker compose exec -T -e TEST_DB="$TEST_DB" db sh -c \
    'pg_restore -U "$POSTGRES_USER" -d "$TEST_DB" --no-owner --no-acl' < "$DUMP"

echo "[restore] Verifikasi jumlah baris..."
for table in users menu pesanan; do
    count=$(docker compose exec -T -e TEST_DB="$TEST_DB" db sh -c \
        "psql -U \"\$POSTGRES_USER\" -d \"\$TEST_DB\" -tAc \"SELECT count(*) FROM $table;\"")
    echo "  $table: $count"
done

echo "[restore] Selesai. Database uji: $TEST_DB"
