# Deployment — Docker, Coolify, dan CI/CD

Panduan ini melengkapi tugas workshop untuk proyek **Saung Situ Awi
(POS-Hybrid-Situ-Awi-Equivyn-Team025)**. Stack aplikasi: **PHP 8.4 +
PostgreSQL 16** (bukan MySQL).

Ada dua image Docker dengan tujuan berbeda:

| File | Kegunaan |
| --- | --- |
| `Dockerfile` (root) | Image mandiri Apache + PHP 8.4 untuk Tugas 1 dan Coolify. |
| `docker/php/Dockerfile` | Image PHP-FPM untuk stack LAN Nginx + PHP-FPM + PostgreSQL + Redis. |

`docker-compose.yml` memakai `docker/php/Dockerfile`; Coolify memakai
`Dockerfile` root.

## Tugas 1 — Docker di laptop

Root `Dockerfile` sekarang menghasilkan **satu image mandiri** (Apache + PHP
8.4) yang menyajikan Laravel langsung dari folder `public/`.

```bash
# Bangun image
docker build -t ukk-rpl-tim-025:v1 .

# Pastikan database & Redis lokal aktif
docker compose up -d db redis

# Jalankan aplikasi di port 1001
docker run -d --name app-tim-025 \
  -p 1001:1001 \
  --network situawi_default \
  --env-file .env \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e APP_URL=http://localhost:1001 \
  -e DB_CONNECTION=pgsql \
  -e DB_HOST=db \
  -e DB_PORT=5432 \
  -e REDIS_CLIENT=phpredis \
  -e REDIS_HOST=redis \
  -e CACHE_STORE=redis \
  -e SESSION_DRIVER=redis \
  -e QUEUE_CONNECTION=sync \
  -e BROADCAST_CONNECTION=log \
  -e SESSION_SECURE_COOKIE=false \
  -e RUN_MIGRATIONS=false \
  ukk-rpl-tim-025:v1

docker ps
docker logs app-tim-025
```

Buka `http://localhost:1001/aktif` dan `http://localhost:1001/login`.

### Verifikasi cepat Tugas 1

```bash
docker ps --filter name=app-tim-025
curl -sS -o /dev/null -w 'aktif: %{http_code}\n' http://localhost:1001/aktif
curl -sS -o /dev/null -w 'login: %{http_code}\n' http://localhost:1001/login
docker logs --tail=30 app-tim-025
```

Hasil yang diharapkan: container `Up` dan kedua URL merespons `200`. Jika muncul
`500`, periksa `APP_KEY`, koneksi `DB_HOST=db`, dan log container.

Bukti yang perlu diambil: tangkapan layar `docker images`, `docker ps`, serta
halaman `http://localhost:1001` di browser. Panduan workshop memakai port
`8080`; proyek ini memakai port **1001** sesuai penugasan.

Hentikan container uji (database & Redis jangan dihapus):

```bash
docker rm -f app-tim-025
```

> Jika platform memberi port container sendiri, set `-e PORT=1001` dan publish
> `-p 1001:1001`. Default internal image adalah port 80.

## Tugas 2 — Deploy ke Coolify

### Prasyarat

1. Merge `feature/backend` ke `main` lalu push. Branch `main` lama belum
   memiliki `Dockerfile`.
2. Login ke Coolify sekolah: <https://server.smkbudibakti.sch.id>.
3. Buat project, misalnya `UKK-TIM-025-SITUAWI`.

### Database

Buat **PostgreSQL 16** (bukan MySQL) dari menu **+ New → Database**. Setelah
running, catat `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`,
`DB_PASSWORD`.

> Panduan workshop mencontohkan MySQL/MariaDB. Proyek ini memakai PostgreSQL
> karena seluruh migration dan konfigurasi Laravel memakai driver `pgsql`.
> Memaksakan MySQL akan memerlukan perubahan kode dan berisiko gagal migrasi.

### Aplikasi

1. **+ New → Public Repository**.
2. URL: `https://github.com/AdtyRz/POS-Hybrid-Situ-Awi-Equivyn-Team025.git`
3. Branch: `main`.
4. Build Pack: **Dockerfile** (root `Dockerfile`).
5. Port yang di-expose: **80** untuk domain HTTPS, atau **1001** jika panitia
   memberi port host 1001.
6. Health check path: `/aktif`.

Jika panitia memberi **port host 1001** tanpa subdomain HTTPS (sesuai
penugasan proyek ini):

```env
PORT=1001
APP_URL=http://server.smkbudibakti.sch.id:1001
SESSION_SECURE_COOKIE=false
```

Lalu isi **Ports Mappings** di Coolify dengan `1001:1001` (host:container).
Jika memakai domain HTTPS, biarkan `PORT=80`, `APP_URL=https://DOMAIN-TIM-ANDA`,
dan `SESSION_SECURE_COOKIE=true`.

> Build pertama mengompilasi ekstensi PHP dan bisa 10–20 menit. Jika Coolify
> memutus build karena timeout, naikkan **Build Timeout** menjadi 1800 detik.
> Jika `DB_HOST` internal tidak bisa di-resolve dari container aplikasi,
> aktifkan **Connect to Predefined Network** pada aplikasi.

### Alternatif: Docker Compose (Nginx + PHP-FPM)

Jika ingin memakai `docker-compose.yml` yang sama seperti stack LAN:

1. Buat resource **Docker Compose** di Coolify.
2. Repository dan branch sama (`main`).
3. Docker Compose Location: `/docker-compose.yml`.
4. Ports: `1001:1001` (sudah ada di service `nginx`).
5. Isi environment:

```env
APP_KEY=base64:...
APP_URL_DOCKER=http://100.86.161.155:1001
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.bplbtqpvcfzuahxhwavr
DB_PASSWORD=Zerobug-dev's Project
DB_SSLMODE=require
SESSION_SECURE_COOKIE_DOCKER=false
TRUSTED_PROXIES_DOCKER=0.0.0.0/0,::/0
RUN_SEED=false
```

Catatan:

- `APP_URL_DOCKER` dan `SESSION_SECURE_COOKIE_DOCKER` diperlukan karena
  `docker-compose.yml` memakai nama variabel tersebut.
- `DB_HOST` diarahkan ke Supabase. Service `db` di compose tetap berjalan
  tetapi tidak dipakai aplikasi.
- Untuk memakai database lokal compose, set `DB_HOST=db`, lalu isi
  `RUN_SEED=true` sekali pada database kosong. Jangan jalankan seeder dua kali
  karena seeder menggunakan `insert`, bukan `updateOrCreate`.
- Nginx menunggu service `app` **healthy**. Healthcheck `app` diberi waktu
  120 detik agar migrasi pertama tidak dianggap gagal.
- Jangan set `RUN_SEED=true` saat memakai Supabase yang sudah berisi user;
  seeder akan gagal karena email duplikat.

### Environment Variables

Gunakan `.env.coolify.example` sebagai daftar variabel. Nilai wajib:

```env
APP_NAME=Saung Situ Awi
APP_ENV=production
APP_KEY=base64:...            # php artisan key:generate --show
APP_DEBUG=false
APP_URL=https://DOMAIN-TIM-ANDA

DB_CONNECTION=pgsql
DB_HOST=...                   # dari database Coolify
DB_PORT=5432
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

CACHE_STORE=database
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=sync
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
TRUSTED_PROXIES=0.0.0.0/0,::/0
RUN_MIGRATIONS=true
RUN_SEED=false
```

`SESSION_DRIVER=database` dan `CACHE_STORE=database` membuat aplikasi hanya
butuh PostgreSQL, tanpa Redis.

`APP_KEY` dibuat sekali dari proyek lokal:

```bash
php artisan key:generate --show
# atau jika stack Docker sedang aktif:
docker compose exec app php artisan key:generate --show
```

Salin nilai `base64:...` ke Environment Variables Coolify. Pastikan `APP_URL`
sama persis dengan URL publik (termasuk `http`/`https` dan port), karena
Laravel memakainya untuk redirect dan cookie.

### Storage persisten

Tambahkan persistent storage di Coolify:

- Container path: `/var/www/html/storage/app/public`

Tanpa ini, foto/upload akan hilang setiap redeploy.

### Domain & SSL

Isi **Domains** dengan subdomain tim dari panitia, misalnya
`https://tim025.server.smkbudibakti.sch.id`. Coolify menerbitkan sertifikat
Let's Encrypt otomatis. Pastikan DNS subdomain mengarah ke server.

### Migrasi & seeding

Entrypoint image menjalankan `migrate --force` otomatis (`RUN_MIGRATIONS=true`).
Untuk mengisi data demo sekali saja:

1. Isi lima `SEED_*_PASSWORD` di Environment Variables.
2. Set `RUN_SEED=true`, redeploy.
3. Set kembali `RUN_SEED=false` setelah seeding berhasil.

Atau lewat Terminal Coolify:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

### Scheduled task (opsional, production)

Aplikasi memiliki command kedaluwarsa keranjang/pesanan. Agar berjalan di
produksi, tambahkan **Scheduled Task** di Coolify:

- Command: `php artisan schedule:run`
- Frekuensi: setiap menit
- Container: service aplikasi

## Tugas 3 — CI/CD GitHub Actions

Workflow ada di `.github/workflows/deploy.yml` dan berjalan saat push ke
`main`, saat pull request ke `main` (hanya job build), atau manual dari tab
Actions.

### Secrets yang harus diisi

Repository → **Settings → Secrets and variables → Actions**:

| Secret | Isi |
| --- | --- |
| `COOLIFY_WEBHOOK_URL` | **Deploy webhook URL** dari tab **Webhooks** aplikasi Coolify (bukan manual Git webhook) |
| `COOLIFY_API_TOKEN` | API token Coolify dengan izin deploy |
| `PRODUCTION_URL` | URL publik aplikasi, mis. `https://tim025.server.smkbudibakti.sch.id` |

> Di Coolify ada dua jenis webhook. Untuk GitHub Actions gunakan **Deploy
> webhook** (bukan **Manual Git webhook**). Manual Git webhook dipakai jika
> Coolify menarik perubahan langsung dari GitHub tanpa Actions. Deploy webhook
> dipanggil dengan `POST` dan header `Authorization: Bearer <token>`.

Workflow akan:

1. checkout kode;
2. memastikan `Dockerfile` ada;
3. `docker build` image;
4. `POST` ke **Deploy webhook** Coolify dengan header
   `Authorization: Bearer $COOLIFY_API_TOKEN`;
5. menunggu `PRODUCTION_URL/aktif` merespons HTTP 200 (hingga 15 menit,
   karena build pertama bisa 10–20 menit).

Jika `COOLIFY_WEBHOOK_URL`/`COOLIFY_API_TOKEN` atau `PRODUCTION_URL` belum
diisi, langkah terkait dilewati dengan peringatan dan workflow tetap hijau.
Isi kedua secret tersebut agar deployment benar-benar berjalan.

Secret bisa diisi lewat UI atau `gh`:

```bash
gh secret set COOLIFY_WEBHOOK_URL --body 'URL_DEPLOY_WEBHOOK_DARI_COOLIFY'
gh secret set COOLIFY_API_TOKEN --body 'API_TOKEN_COOLIFY'
gh secret set PRODUCTION_URL --body 'http://server.smkbudibakti.sch.id:1001'
```

### Uji alur

Jangan push `main` lokal yang masih tertinggal dari `origin/main`. Sinkronkan
dulu, lalu merge:

```bash
git fetch origin
git checkout main
git pull --ff-only origin main

# Opsi aman: buat PR dari feature/backend ke main di GitHub,
# lalu merge PR. Merge PR tetap memicu workflow push ke main.

# Atau merge lokal setelah main sinkron:
git merge --no-ff feature/backend
git push origin main
```

Setelah push, buka tab **Actions** di GitHub dan pastikan job
`Build & Test Docker Image` serta `Deploy to Coolify` berwarna hijau. Kemudian
refresh aplikasi publik.

> Perubahan teks footer `v1.0.1 - Deployed Automatically via GitHub Actions`
> pada panduan bersifat contoh. Yang penting: push ke `main` memicu build dan
> deploy otomatis, lalu URL publik menampilkan versi terbaru.

## Checklist

- [ ] `Dockerfile` dan `.dockerignore` valid.
- [ ] Container berjalan di `http://localhost:1001`.
- [ ] Database PostgreSQL Coolify aktif dan migrasi sukses.
- [ ] `APP_DEBUG=false` dan `APP_KEY` terpasang.
- [ ] Domain HTTPS aktif.
- [ ] Workflow GitHub Actions hijau.
