#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

URL="${1:-http://127.0.0.1/aktif}"
FAILED=0

echo "== Container =="
docker compose ps

echo
echo "== Health status =="
for id in $(docker compose ps -q); do
    name=$(docker inspect -f '{{.Name}}' "$id")
    status=$(docker inspect -f '{{if .State.Health}}{{.State.Health.Status}}{{else}}{{.State.Status}}{{end}}' "$id")
    printf '%-32s %s\n' "$name" "$status"
    case "$status" in
        healthy|running) ;;
        *) FAILED=1 ;;
    esac
done

echo
echo "== HTTP $URL =="
CODE=$(curl -k -L -s -o /dev/null -w '%{http_code}' "$URL" || true)
echo "HTTP $CODE"
[ "$CODE" = "200" ] || FAILED=1

echo
echo "== Database =="
if docker compose exec -T db sh -c 'pg_isready -U "$POSTGRES_USER" -d "$POSTGRES_DB"' >/dev/null; then
    echo "PostgreSQL: OK"
else
    echo "PostgreSQL: GAGAL"
    FAILED=1
fi
if docker compose exec -T app php artisan migrate:status >/dev/null 2>&1; then
    echo "Koneksi aplikasi ke database: OK"
else
    echo "Koneksi aplikasi ke database: GAGAL"
    FAILED=1
fi

echo
echo "== Redis =="
if docker compose exec -T redis redis-cli ping 2>/dev/null | grep -q PONG; then
    echo "Redis: OK"
else
    echo "Redis: GAGAL"
    FAILED=1
fi

if [[ "$URL" == https://* ]]; then
    HOST=$(echo "$URL" | sed -E 's#https://([^/:]+).*#\1#')
    echo
    echo "== Sertifikat SSL ($HOST) =="
    if echo | openssl s_client -connect "$HOST:443" -servername "$HOST" 2>/dev/null \
        | openssl x509 -noout -dates -checkend 604800; then
        echo "SSL: OK (berlaku lebih dari 7 hari)"
    else
        echo "SSL: PERINGATAN/GAGAL"
        FAILED=1
    fi
fi

echo
if [ "$FAILED" -eq 0 ]; then
    echo "SEMUA PEMERIKSAAN LULUS."
else
    echo "ADA PEMERIKSAAN YANG GAGAL."
    exit 1
fi
