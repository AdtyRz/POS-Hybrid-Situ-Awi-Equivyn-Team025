#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

DOMAIN="${1:-${DOMAIN:-}}"
EMAIL="${EMAIL:-}"

if [ -z "$DOMAIN" ]; then
    echo "DOMAIN kosong. Nginx akan memakai self-signed certificate."
    echo "Contoh: DOMAIN=team025.example.com EMAIL=admin@example.com $0"
    exit 0
fi

case "$DOMAIN" in
    localhost|127.*|10.*|192.168.*|172.1[6-9].*|172.2[0-9].*|172.3[0-1].*)
        echo "DOMAIN=$DOMAIN adalah alamat lokal/private."
        echo "Let's Encrypt tidak dapat menerbitkan sertifikat untuk alamat ini."
        echo "Nginx akan memakai self-signed certificate. Tidak ada perubahan."
        exit 0
        ;;
esac

if [ -z "$EMAIL" ]; then
    echo "EMAIL wajib diisi untuk pendaftaran Let's Encrypt."
    echo "Contoh: DOMAIN=$DOMAIN EMAIL=admin@example.com $0"
    exit 1
fi

mkdir -p docker/certbot/conf docker/certbot/www docker/nginx/certs

echo "Memastikan nginx berjalan dengan DOMAIN=$DOMAIN..."
DOMAIN="$DOMAIN" docker compose up -d nginx

echo "Meminta sertifikat Let's Encrypt untuk $DOMAIN..."
docker compose --profile ssl run --rm certbot certonly \
    --webroot -w /var/www/certbot \
    -d "$DOMAIN" \
    --email "$EMAIL" \
    --agree-tos \
    --no-eff-email \
    --non-interactive

echo "Menyalin sertifikat ke volume nginx..."
cp -L "docker/certbot/conf/live/$DOMAIN/fullchain.pem" docker/nginx/certs/fullchain.pem
cp -L "docker/certbot/conf/live/$DOMAIN/privkey.pem" docker/nginx/certs/privkey.pem
chmod 600 docker/nginx/certs/privkey.pem

echo "Reload nginx..."
docker compose exec nginx nginx -t
docker compose exec nginx nginx -s reload

echo "Selesai. Uji: curl -I https://$DOMAIN"
