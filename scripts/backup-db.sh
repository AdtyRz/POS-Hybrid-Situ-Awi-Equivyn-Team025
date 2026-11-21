#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

BACKUP_DIR="${BACKUP_DIR:-$ROOT_DIR/backups}"
RETENTION_DAYS="${RETENTION_DAYS:-7}"
STAMP="$(date +%Y-%m-%d_%H%M%S)"

mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"

DB_FILE="$BACKUP_DIR/db_$STAMP.dump"
STORAGE_FILE="$BACKUP_DIR/storage_$STAMP.tar.gz"
ENV_FILE="$BACKUP_DIR/env_$STAMP.env"

echo "[backup] Database..."
docker compose exec -T db sh -c 'pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB" -Fc' > "$DB_FILE"
[ -s "$DB_FILE" ] || { echo "[backup] Dump database kosong."; exit 1; }
chmod 600 "$DB_FILE"

echo "[backup] Storage upload..."
docker compose exec -T app tar czf - -C /var/www/html storage/app/public > "$STORAGE_FILE"
chmod 600 "$STORAGE_FILE"

if [ -f .env ]; then
    cp .env "$ENV_FILE"
    chmod 600 "$ENV_FILE"
fi

echo "[backup] Checksum..."
(
    cd "$BACKUP_DIR"
    sha256sum "db_$STAMP.dump" "storage_$STAMP.tar.gz" > "checksums_$STAMP.txt"
    if [ -f "env_$STAMP.env" ]; then
        sha256sum "env_$STAMP.env" >> "checksums_$STAMP.txt"
    fi
)

if [ -n "${RCLONE_REMOTE:-}" ]; then
    if command -v rclone >/dev/null 2>&1; then
        echo "[backup] Upload ke $RCLONE_REMOTE..."
        rclone copy "$BACKUP_DIR" "$RCLONE_REMOTE/situawi/$STAMP" \
            --include "db_$STAMP.dump" \
            --include "storage_$STAMP.tar.gz" \
            --include "env_$STAMP.env" \
            --include "checksums_$STAMP.txt" \
            || echo "[backup] PERINGATAN: upload cloud gagal."
    else
        echo "[backup] PERINGATAN: rclone tidak terpasang; lewati upload cloud."
    fi
fi

echo "[backup] Retensi $RETENTION_DAYS hari..."
find "$BACKUP_DIR" -maxdepth 1 -type f -mtime +"$RETENTION_DAYS" -delete

echo "[backup] Selesai: $BACKUP_DIR"
ls -lh "$BACKUP_DIR"/*_"$STAMP".* 2>/dev/null || true
