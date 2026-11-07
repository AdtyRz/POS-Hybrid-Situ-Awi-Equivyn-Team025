# Dukungan

Butuh bantuan menjalankan, mengonfigurasi, atau memakai **POS Hybrid Situ Awi**? Mulai dari langkah berikut agar pertanyaan Anda cepat terjawab.

## Sebelum Bertanya

1. Baca [README.md](README.md), terutama bagian instalasi, realtime, sesi meja, dan integrasi IoT.
2. Periksa [FAQ.md](FAQ.md) untuk pertanyaan yang sering muncul.
3. Cari issue atau diskusi yang sudah ada; mungkin masalah Anda sudah dibahas.
4. Coba langkah pemeriksaan dasar:

```bash
php artisan config:clear
php artisan test
npm run build
```

Pastikan juga:

- `.env` sudah berisi koneksi database, kredensial Midtrans, konfigurasi Reverb, dan token IoT yang benar.
- `php artisan reverb:start` berjalan bila membutuhkan pembaruan realtime.
- `storage/logs/laravel.log` diperiksa untuk pesan error.
- Aset frontend sudah dibangun ulang setelah perubahan konfigurasi Vite.

## Cara Mendapatkan Bantuan

| Kebutuhan | Jalur |
| --- | --- |
| Bug atau regresi | Buka issue GitHub dengan label `bug` |
| Pertanyaan penggunaan | GitHub Discussions bila tersedia, atau issue dengan label `question` |
| Permintaan fitur | Buka issue dengan label `enhancement` |
| Masalah keamanan | Ikuti [SECURITY.md](SECURITY.md), jangan buka issue publik |
| Kontribusi kode | Baca [CONTRIBUTING.md](CONTRIBUTING.md) |

## Informasi yang Perlu Disertakan

Agar masalah dapat direproduksi, sertakan:

- Ringkasan masalah dan langkah reproduksi.
- Hasil yang diharapkan dan hasil yang sebenarnya.
- Commit hash atau branch yang digunakan.
- Versi PHP, Composer, Node.js, npm, dan sistem operasi.
- Jenis database (PostgreSQL/SQLite) dan apakah memakai Supabase.
- Status Reverb, queue, dan apakah fallback polling aktif.
- Pesan error atau potongan log yang relevan.
- Screenshot atau rekaman layar untuk masalah tampilan.

**Sensor terlebih dahulu** kredensial, token, email, nomor telepon, dan data pelanggan sebelum menempelkan log atau screenshot.

## Waktu Respons

Proyek ini dikelola oleh tim kecil secara sukarela. Kami berusaha merespons secepatnya, tetapi tidak menjamin waktu respons tertentu. Issue yang menyertakan langkah reproduksi dan informasi lengkap lebih mudah diprioritaskan.

## Di Luar Dukungan

- Pembuatan akun, verifikasi, atau masalah penagihan di Midtrans, Supabase, dan penyedia hosting.
- Konfigurasi jaringan, domain, atau sertifikat SSL di luar panduan proyek.
- Perbaikan perangkat keras ESP32 di luar panduan wiring dan upload.
- Permintaan fitur khusus atau perubahan alur bisnis tanpa diskusi dengan maintainer.

Untuk laporan keamanan, gunakan jalur privat di [SECURITY.md](SECURITY.md).
