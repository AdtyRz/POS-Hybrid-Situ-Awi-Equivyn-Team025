#!/bin/sh
set -eu

CERT_DIR="/etc/nginx/certs"
DOMAIN="${DOMAIN:-localhost}"
SERVER_IP="${SERVER_IP:-}"

mkdir -p "$CERT_DIR"

if [ -s "$CERT_DIR/fullchain.pem" ] && [ -s "$CERT_DIR/privkey.pem" ]; then
    echo "[nginx] Sertifikat sudah tersedia; melewati pembuatan self-signed."
    exit 0
fi

SAN="DNS:${DOMAIN},IP:127.0.0.1"
if [ -n "$SERVER_IP" ]; then
    SAN="${SAN},IP:${SERVER_IP}"
fi

echo "[nginx] Membuat self-signed certificate untuk ${DOMAIN}..."
openssl req -x509 -nodes -newkey rsa:2048 -days 365 \
    -keyout "$CERT_DIR/privkey.pem" \
    -out "$CERT_DIR/fullchain.pem" \
    -subj "/CN=${DOMAIN}" \
    -addext "subjectAltName=${SAN}"

chmod 600 "$CERT_DIR/privkey.pem"
echo "[nginx] Self-signed certificate siap."
