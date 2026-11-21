FROM nginx:1.27-alpine

RUN apk add --no-cache openssl

# Konfigurasi di-bake ke image agar tidak bergantung pada bind mount relatif.
# Coolify menjalankan compose dari /artifacts, sehingga mount ./docker/... kosong.
COPY templates/ /etc/nginx/templates/
COPY snippets/ /etc/nginx/snippets/
COPY entrypoint.d/ /docker-entrypoint.d/
RUN chmod +x /docker-entrypoint.d/*.sh

EXPOSE 1001 80
