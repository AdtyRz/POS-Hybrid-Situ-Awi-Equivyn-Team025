# Kebijakan Keamanan

Keamanan aplikasi dan data pengguna adalah prioritas. Terima kasih telah membantu melindungi **POS Hybrid Situ Awi — Equivyn Team 025**.

## Versi yang Didukung

| Versi | Didukung |
| --- | --- |
| Branch `feature/backend` (HEAD) | Ya |
| Tag rilis terbaru, bila tersedia | Ya |
| Branch lain dan commit lama | Tidak |

Perbaikan keamanan diterapkan pada branch utama yang sedang dikembangkan. Kami menyarankan selalu memakai versi terbaru.

## Melaporkan Celah Keamanan

**Jangan membuka issue publik, diskusi, atau pull request yang mengungkap detail celah keamanan.**

Gunakan salah satu jalur berikut:

1. Buka tab **Security** di repositori GitHub, lalu pilih **Advisories** dan **Report a vulnerability** (GitHub Private Vulnerability Reporting).
2. Jika fitur tersebut belum tersedia, hubungi maintainer proyek secara privat melalui fitur kontak pada profil GitHub maintainer repositori ini.

Sertakan sebanyak mungkin informasi berikut:

- Deskripsi celah dan komponen yang terdampak.
- Langkah reproduksi yang jelas.
- Dampak yang mungkin terjadi, termasuk data atau fitur yang dapat diakses.
- Commit hash, branch, dan versi lingkungan yang digunakan.
- Proof of concept atau tangkapan layar, bila ada.
- Saran mitigasi, bila ada.

Gunakan data uji, bukan data pelanggan asli. Jangan mengakses, mengubah, menghapus, atau menyebarkan data milik orang lain.

## Cakupan

Laporan yang relevan meliputi, antara lain:

- Autentikasi staf, otorisasi berbasis peran, dan pengelolaan sesi.
- Verifikasi sesi meja, kode akses, URL bertanda tangan, dan kebocoran akses pesanan.
- Alur pembayaran QRIS/Midtrans, verifikasi status pembayaran, replay, dan pembayaran ganda.
- Token IoT, endpoint API, pembatasan laju, dan penyalahgunaan perangkat.
- Cross-site scripting (XSS), cross-site request forgery (CSRF), SQL injection, dan kerentanan upload.
- Kebocoran secret, kredensial, atau data pribadi.
- Otorisasi channel Reverb dan kebocoran data realtime.
- Dependency dengan kerentanan yang diketahui disertai bukti eksploitasi.

## Di Luar Cakupan

- Rekayasa sosial terhadap anggota tim atau pengguna.
- Serangan denial of service, spam, atau pemindaian otomatis tanpa bukti dampak.
- Akses fisik ke perangkat atau perangkat ESP32 yang sudah dikuasai penyerang.
- Kerentanan pada layanan pihak ketiga (Midtrans, Supabase, penyedia hosting, dan sejenisnya); laporkan langsung kepada penyedia terkait.
- Laporan scanner otomatis tanpa langkah reproduksi atau proof of concept.
- Ketiadaan header keamanan tanpa dampak yang dapat ditunjukkan.

## Respons Kami

Kami berusaha merespons laporan secepatnya, umumnya dalam 3 hari kerja. Karena proyek ini dikelola oleh tim kecil, waktu respons dapat berbeda. Kami akan:

1. Mengonfirmasi penerimaan laporan.
2. Menilai dampak dan menentukan prioritas.
3. Menyusun perbaikan dan menguji perubahan.
4. Memberi tahu Anda setelah perbaikan tersedia.
5. Mencantumkan kredit atas nama Anda bila diinginkan.

Mohon beri waktu yang wajar sebelum mempublikasikan detail celah. Kami akan berkoordinasi mengenai tanggal pengungkapan.

## Safe Harbor

Kami menganggap penelitian keamanan yang dilakukan dengan iktikad baik sebagai aktivitas yang sah, selama Anda:

- Tidak melanggar privasi, merusak data, atau mengganggu layanan.
- Tidak mengakses data yang bukan milik Anda.
- Tidak mempublikasikan detail celah sebelum ada kesepakatan.
- Mematuhi hukum yang berlaku.

## Jika Secret Bocor

Jika Anda menemukan `.env`, kunci Midtrans, kredensial Supabase, secret Reverb, atau `IOT_API_TOKEN` yang terekspos:

1. Jangan menyebarkan atau memakai kredensial tersebut.
2. Laporkan segera melalui jalur privat di atas.
3. Rotasi kredensial terkait setelah laporan ditindaklanjuti.
4. Periksa riwayat commit dan log akses untuk memastikan tidak ada penyalahgunaan.

Untuk pertanyaan non-keamanan, gunakan [SUPPORT.md](SUPPORT.md).
