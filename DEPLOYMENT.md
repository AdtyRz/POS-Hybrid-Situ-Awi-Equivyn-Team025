# Panduan Deploy Produksi

Dokumen ini menjelaskan cara memasang Saung Situ Awi di server produksi. Aplikasi memakai Laravel 13, PostgreSQL (Supabase), Vite, dan Laravel Reverb untuk realtime.

## Rekomendasi ArenHost

Gunakan **VPS KVM** atau **Cloud VPS**, bukan shared hosting/cPanel biasa. Aplikasi ini membutuhkan:

- PHP 8.3 atau lebih baru dengan ekstensi `pdo_pgsql`.
- Proses long-running `php artisan reverb:start`.
- Build aset Node.js (Vite 8 membutuhkan Node.js 20.19+ atau 22.12+).
- Akses terminal/SSH untuk migrasi, cache, dan permission storage.

Shared hosting ArenHost memakai cPanel/LiteSpeed dan umumnya menyediakan MySQL, bukan PostgreSQL. Aplikasi ini juga memakai query analitik khusus PostgreSQL (`EXTRACT(EPOCH ...)`), sehingga tidak bisa langsung dipindahkan ke MySQL. Shared hosting hanya layak dipakai jika penyedia mengaktifkan `pdo_pgsql` dan mengizinkan proses Reverb. Jika tidak, realtime harus dimatikan dan aplikasi hanya memakai polling fallback.

Spesifikasi VPS minimum yang disarankan:

- 1 vCPU, RAM 1 GB (2 GB lebih nyaman).
- Disk 20 GB.
- Ubuntu 24.04 LTS.
- Domain dan SSL aktif.

## 1. Persiapan Server VPS

Masuk sebagai root, lalu buat user aplikasi:

```bash
adduser saung
usermod -aG sudo saung
su - saung
```

Install paket dasar:

```bash
sudo apt update
sudo apt install -y nginx supervisor git unzip curl \
    php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mbstring php8.3-xml \
    php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl php8.3-gd \
    postgresql-client certbot python3-certbot-nginx
```

Install Composer:

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer -V
```

Install Node.js 22 LTS:

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
```

Pastikan ekstensi PostgreSQL tersedia:

```bash
php -m | grep -i pgsql
```

## 2. Ambil Kode dan Install Dependency

```bash
sudo mkdir -p /var/www/saung-situ-awi
sudo chown -R saung:www-data /var/www/saung-situ-awi
git clone <URL-REPOSITORY> /var/www/saung-situ-awi
cd /var/www/saung-situ-awi
git checkout feature/backend
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
```

Salin template produksi:

```bash
cp .env.production.example .env
php artisan key:generate
```

Isi `.env`:

- `APP_URL` harus `https://domain-anda.com`.
- `DB_*` diisi kredensial PostgreSQL/Supabase. Gunakan `DB_SSLMODE=require` untuk Supabase. Port `6543` adalah connection pooler Supabase; jika migrasi bermasalah, gunakan port direct `5432` saat menjalankan migrasi.
- `SESSION_SECURE_COOKIE=true`.
- Isi `REVERB_APP_ID`, `REVERB_APP_KEY`, dan `REVERB_APP_SECRET` dengan nilai acak yang kuat.
- `VITE_REVERB_HOST` diisi domain publik, `VITE_REVERB_PORT=443`, `VITE_REVERB_SCHEME=https`.
- Isi `SEED_*_PASSWORD` dengan sandi staf yang kuat sebelum menjalankan seeder.
- Isi `MIDTRANS_*` dengan kredensial produksi dan set `MIDTRANS_IS_PRODUCTION=true`.
- Isi `IOT_API_TOKEN` dengan token acak panjang dan samakan di sketch ESP32.

Jangan pernah commit file `.env`.

## 3. Migrasi, Seed, dan Build

Untuk instalasi pertama:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
npm ci
npm run build
```

`db:seed` hanya dijalankan sekali pada database kosong. Seeder membuat akun staf, meja, kategori, dan menu awal. Jangan jalankan ulang pada database yang sudah berisi data karena dapat menimbulkan duplikasi.

Setelah build, `public/build` harus ada. Folder ini tidak masuk Git, jadi harus dibuat di server atau diunggah terpisah.

Nilai `VITE_REVERB_*` dibaca saat `npm run build`, bukan saat aplikasi berjalan. Jika domain atau kredensial Reverb berubah, build ulang aset dengan `npm run build`.

Atur permission:

```bash
sudo chown -R saung:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
sudo chown saung:www-data .env
sudo chmod 600 .env
```

## 4. Konfigurasi Nginx

Salin contoh konfigurasi:

```bash
sudo cp deploy/nginx.conf.example /etc/nginx/sites-available/saung-situ-awi
sudo ln -s /etc/nginx/sites-available/saung-situ-awi /etc/nginx/sites-enabled/saung-situ-awi
sudo nginx -t
sudo systemctl reload nginx
```

Sesuaikan `server_name`, `root`, dan versi PHP-FPM di file tersebut. Document root wajib mengarah ke folder `public`, bukan root proyek.

Aktifkan HTTPS:

```bash
sudo certbot --nginx -d domain-anda.com -d www.domain-anda.com
```

Jika memakai Cloudflare, gunakan mode **Full (strict)** dan pasang origin certificate di Nginx. Jangan memakai mode Flexible karena Laravel akan menganggap request sebagai HTTP dan cookie aman bisa gagal.

Jika domain diproksikan Cloudflare, isi `TRUSTED_PROXIES` dengan rentang IP Cloudflare. Tanpa ini, Laravel melihat semua pengunjung memakai IP Cloudflare sehingga rate limit dan log IP menjadi tidak akurat. Gunakan `TRUSTED_PROXIES=*` hanya jika origin tidak bisa diakses langsung dari internet. Daftar IP terbaru tersedia di `https://www.cloudflare.com/ips/`.

## 5. Menjalankan Reverb

Salin konfigurasi Supervisor:

```bash
sudo cp deploy/supervisor-reverb.conf.example /etc/supervisor/conf.d/saung-reverb.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

Perintah Reverb harus berjalan terus. Setelah deploy kode baru, restart Reverb:

```bash
php artisan reverb:restart
```

Log Reverb ada di `/var/log/supervisor/saung-reverb.log`.

## 6. Cache Produksi dan Health Check

```bash
php artisan optimize:clear
php artisan optimize
```

Health check tersedia di:

```text
https://domain-anda.com/health
```

Endpoint harus mengembalikan HTTP 200. Gunakan endpoint ini untuk monitoring uptime.

## 7. Deploy Update

Gunakan script yang sudah disediakan:

```bash
cd /var/www/saung-situ-awi
bash deploy/deploy.sh
```

Script akan menghentikan aplikasi sementara, install dependency produksi, build aset, migrasi, membuat cache, dan menyalakan kembali aplikasi. Pastikan `.env` sudah benar dan backup database dibuat sebelum migrasi besar.

Untuk deploy manual:

```bash
php artisan down
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan reverb:restart
php artisan up
```

## 8. Alternatif Shared Hosting / cPanel

Gunakan opsi ini hanya jika penyedia memenuhi semua syarat berikut:

- PHP 8.3+ dengan `pdo_pgsql` aktif.
- Document root domain bisa diarahkan ke folder `public`.
- Tersedia SSH untuk `composer`, `php artisan`, dan `npm`.
- Tersedia cara menjalankan proses Reverb (jarang tersedia di shared hosting).

Langkah umum:

1. Upload proyek ke `/home/USER/saung-situ-awi` (di luar `public_html`).
2. Ubah document root domain di cPanel ke `/home/USER/saung-situ-awi/public`.
3. Jalankan `composer install --no-dev --optimize-autoloader`.
4. Salin `.env.production.example` menjadi `.env`, isi kredensial, lalu `php artisan key:generate`.
5. Jalankan `php artisan migrate --force`, `php artisan db:seed --force`, dan `php artisan storage:link`.
6. Build aset di komputer lokal dengan `npm ci && npm run build`, lalu unggah folder `public/build`.
7. Jalankan `php artisan optimize`.

Jika Reverb tidak bisa berjalan, ubah:

```env
BROADCAST_CONNECTION=null
```

Aplikasi tetap memakai polling fallback setiap beberapa detik. Fitur simpan admin tanpa reload tetap berjalan, tetapi pembaruan tidak seinstan WebSocket.

Jangan arahkan document root ke root proyek karena `.env`, `storage`, dan `vendor` bisa terekspos.

## 9. Checklist Setelah Deploy

- [ ] `https://domain-anda.com/health` mengembalikan 200.
- [ ] Halaman login staf tampil dan bisa login dengan akun seeder.
- [ ] Aset CSS/JS termuat, bukan tampilan tanpa gaya.
- [ ] Menu gambar tampil dari `/storage/...`.
- [ ] E-menu pelanggan bisa dibuka dengan kode meja.
- [ ] Checkout tunai dan QRIS berhasil.
- [ ] Webhook Midtrans diarahkan ke `https://domain-anda.com/midtrans/notification`.
- [ ] KDS dapur dan bar menerima pesanan.
- [ ] Status pesanan berubah: `diproses` → `siap` → `diantar` → `selesai`.
- [ ] Halaman pelacakan pelanggan menampilkan tahap **Sedang Diantar** saat status `diantar`, bukan **Pesanan Tiba**.
- [ ] LCD ESP32 menampilkan **Diantar** selama pesanan diantar dan **Selesai** hanya setelah status `selesai`.
- [ ] Reverb tersambung atau polling fallback berjalan.
- [ ] `php artisan about` tidak menunjukkan error konfigurasi.
- [ ] Log di `storage/logs` tidak dipenuhi error.

## 10. Catatan Keamanan

- `APP_DEBUG=false` dan `APP_ENV=production` wajib.
- Gunakan `APP_KEY` baru untuk produksi dan jangan bagikan nilainya.
- `SESSION_SECURE_COOKIE=true` jika seluruh akses memakai HTTPS.
- Batasi `TRUSTED_PROXIES` sesuai topologi. Untuk Nginx di server yang sama, `127.0.0.1,::1` cukup.
- Jangan membuka port `8080` Reverb ke publik. Cukup proxy `/app` dari Nginx.
- Ganti `IOT_API_TOKEN` secara berkala dan jangan menaruhnya di URL.
- Gunakan kredensial Midtrans produksi hanya di server.
- Backup database dan folder `storage/app/public` secara berkala.
- Setelah mengubah `.env`, jalankan `php artisan optimize:clear` lalu `php artisan optimize`.
