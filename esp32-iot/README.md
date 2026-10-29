# ESP32 IoT Saung Situ Awi

Sketch lengkap: [`esp32_iot_complete.ino`](esp32_iot_complete.ino)

Fitur:

- Menampilkan status pesanan, total bayar, dan kode sesi meja di LCD 16x2.
- Tap sensor sentuh untuk berganti layar.
- Tahan sensor sekitar 1,5 detik untuk memanggil pelayan.
- Buzzer dan LED sebagai feedback.
- Mengambil konfigurasi pesan LCD dan kode sesi dari API Laravel.

## 1. Komponen

- ESP32 DOIT DevKit V1 (30 pin)
- LCD 16x2 dengan modul I2C PCF8574
- Buzzer aktif 3,3 V (atau passive buzzer)
- Sensor sentuh TTP223
- Kabel jumper
- Kabel USB data

## 2. Wiring

| Komponen | Pin komponen | Pin ESP32 |
|---|---|---|
| LCD I2C | VCC | 3V3 |
| LCD I2C | GND | GND |
| LCD I2C | SDA | GPIO 21 |
| LCD I2C | SCL | GPIO 22 |
| TTP223 | VCC | 3V3 |
| TTP223 | GND | GND |
| TTP223 | I/O atau OUT | GPIO 27 |
| Buzzer 3-pin | VCC | 3V3 |
| Buzzer 3-pin | I/O / SIG | GPIO 25 |
| Buzzer 3-pin | GND | GND |
| Buzzer 2-pin | Positif | GPIO 25 |
| Buzzer 2-pin | Negatif | GND |
| LED built-in | - | GPIO 2 (tanpa kabel) |

Ilustrasi:

```text
ESP32                     LCD I2C 16x2
3V3   ------------------- VCC
GND   ------------------- GND
GPIO21 ------------------ SDA
GPIO22 ------------------ SCL

ESP32                     TTP223
3V3   ------------------- VCC
GND   ------------------- GND
GPIO27 ------------------ I/O / OUT

ESP32                     Buzzer
GPIO25 ------------------ + / I/O
GND   ------------------- - / GND
```

Catatan penting:

- Semua perangkat harus memiliki GND yang sama.
- Gunakan **3,3 V** untuk TTP223. Jangan memberi 5 V ke pin GPIO ESP32.
- Untuk LCD, coba VCC ke **3,3 V** terlebih dahulu. Jika layar blank atau terlalu redup, LCD 1602 bisa memerlukan 5 V. Jika memakai 5 V, gunakan **I2C level shifter 3,3 V ke 5 V**; jangan sambungkan SDA/SCL 5 V langsung ke ESP32.
- Jika LCD blank, putar potensiometer kontras di belakang modul I2C.
- Alamat I2C LCD umumnya `0x27` atau `0x3F`. Jika tidak tampil, ubah `LCD_ADDR` di sketch.
- Untuk buzzer 2 pin: positif ke GPIO 25, negatif ke GND. Untuk buzzer 5 V atau arus besar, gunakan transistor NPN dan catu daya terpisah.
- TTP223 umumnya aktif HIGH. Jika sensor Anda aktif LOW, ubah `TOUCH_ACTIVE_HIGH` menjadi `0`.
- Jika buzzer aktif LOW, ubah `BUZZER_ON_LEVEL` menjadi `LOW`.
- Jika memakai passive buzzer, ubah `BUZZER_ACTIVE` menjadi `0` agar sketch memakai `tone()`.

## 3. Library Arduino

Gunakan Arduino IDE dengan board package ESP32 dari Espressif.

Library yang dipakai:

1. **ESP32** by Espressif Systems
2. **ArduinoJson** by Benoit Blanchon, versi 7.x
3. **LiquidCrystal_I2C** by Frank de Brabander

Board yang dipilih:

```text
DOIT ESP32 DEVKIT V1
```

Sketch ini sudah diuji kompilasi dengan:

- ESP32 core `3.3.11`
- ArduinoJson `7.4.3`
- LiquidCrystal_I2C `2.0.0`
- FQBN `esp32:esp32:esp32doit-devkit-v1`

## 4. Konfigurasi Sketch

Buka `esp32_iot_complete.ino`, lalu sesuaikan bagian berikut:

```cpp
const char* WIFI_SSID = "WIFI_KANTIN";
const char* WIFI_PASS = "PASSWORD_WIFI";

const char* SERVER_URL = "http://192.168.1.100:8000";
const char* IOT_API_TOKEN = "GANTI_DENGAN_IOT_API_TOKEN";
const char* KODE_MEJA = "LB-01";
```

- `WIFI_SSID` dan `WIFI_PASS`: ESP32 hanya mendukung WiFi **2,4 GHz**.
- `SERVER_URL`: gunakan IP LAN laptop/server, bukan `127.0.0.1` atau `localhost`.
- `IOT_API_TOKEN`: harus sama dengan `IOT_API_TOKEN` di `.env` Laravel.
- `KODE_MEJA`: harus sama dengan kode meja di menu Master Meja.

## 5. Konfigurasi Server Laravel

Tambahkan token di `.env`:

```env
IOT_API_TOKEN=token-rahasia-panjang-anda
```

Contoh membuat token:

```bash
openssl rand -hex 24
```

Setelah mengubah `.env`:

```bash
php artisan config:clear
php artisan optimize:clear
```

Pastikan meja sudah dibuat di **Admin > Master Meja**, misalnya `LB-01`.

Jalankan server agar bisa diakses dari ESP32:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Cari IP LAN laptop/server:

```bash
ip addr
```

atau di Windows:

```powershell
ipconfig
```

Contoh IP: `192.168.1.100`. Pastikan ESP32 dan laptop berada di jaringan WiFi yang sama, dan firewall mengizinkan port `8000`.

Uji API dari laptop:

```bash
curl -H "X-IoT-Token: token-rahasia-panjang-anda" \
  http://127.0.0.1:8000/api/iot/meja/LB-01
```

Respons yang benar berisi `success`, `data.kode_sesi`, `data.total_bayar`, dan `data.pesanan`.

## 6. Upload ke ESP32

1. Hubungkan ESP32 ke laptop memakai kabel USB data.
2. Buka Arduino IDE.
3. Pasang board package ESP32 dan ketiga library di atas.
4. Buat sketch baru bernama `esp32_iot_complete`, lalu salin isi file `esp32_iot_complete.ino` ke sketch tersebut. (Arduino IDE meminta nama folder sketch sama dengan nama file.)
5. Pilih board **DOIT ESP32 DEVKIT V1**.
6. Pilih port COM/ttyUSB yang muncul.
7. Klik Upload.
8. Buka Serial Monitor dengan baud rate **115200**.

Jika upload gagal, tahan tombol **BOOT** di ESP32, tekan **EN** sebentar, lepas **EN**, lalu lepas **BOOT** dan upload lagi. Jika port tidak muncul, pasang driver USB CH340/CP210x.

## 7. Cara Pakai

Saat boot:

1. LCD menampilkan `Saung Situ Awi`.
2. ESP32 menyambung ke WiFi.
3. ESP32 mengambil konfigurasi dari `/api/iot/status-meja`.
4. ESP32 mengambil status meja dari `/api/iot/meja/LB-01`.
5. LCD menampilkan status pesanan, total bayar, atau kode sesi.

Kontrol:

- **Tap singkat**: berganti layar `Status Pesanan` -> `Total Bayar` -> `Kode Verif Saung`.
- **Tahan sekitar 1,5 detik**: mengirim panggilan pelayan.
- **Buzzer**: berbunyi saat tap, saat panggilan berhasil, dan saat gagal.
- **LED**: menyala saat panggilan berhasil.

Data diperbarui otomatis:

- Status meja dan kode sesi: setiap 10 detik.
- Konfigurasi pesan LCD: setiap 60 detik.

Kode sesi yang tampil adalah kode aktif yang juga dipakai pelanggan untuk membuka e-menu. Jika sesi meja diganti oleh kasir, kode di LCD akan berubah otomatis pada pembaruan berikutnya.

## 8. Troubleshooting

| Gejala | Pemeriksaan |
|---|---|
| LCD blank | Cek VCC/GND, alamat I2C `0x27`/`0x3F`, potensiometer kontras, dan level shifter jika memakai 5 V |
| Sensor tidak merespons | Cek VCC 3,3 V, OUT ke GPIO 27, dan coba ubah `TOUCH_ACTIVE_HIGH` |
| Sensor kebalik | Ubah `TOUCH_ACTIVE_HIGH` antara `1` dan `0` |
| Buzzer tidak bunyi | Cek polaritas, `BUZZER_ACTIVE`, dan `BUZZER_ON_LEVEL` |
| Buzzer bunyi terus | Balik `BUZZER_ON_LEVEL` |
| WiFi gagal | Pastikan SSID benar, password benar, dan WiFi 2,4 GHz |
| ESP32 tidak bisa connect ke server | Jangan pakai `localhost`; gunakan IP LAN dan jalankan server dengan `--host=0.0.0.0` |
| HTTP 401 | `IOT_API_TOKEN` di sketch berbeda dengan `.env` |
| HTTP 503 | `IOT_API_TOKEN` di server masih kosong |
| HTTP 404 | `KODE_MEJA` tidak ada di Master Meja |
| HTTP 403 saat panggil | Kode sesi lama; tunggu refresh 10 detik atau buka ulang sesi meja |
| HTTP 429 | Anti-spam pelayan masih aktif; tunggu sesuai cooldown di admin |
| Upload gagal | Coba mode BOOT/EN dan cek driver USB |

## 9. Endpoint yang Dipakai

Semua endpoint memerlukan header:

```text
X-IoT-Token: <IOT_API_TOKEN>
```

- `GET /api/iot/status-meja` — konfigurasi LCD dan daftar meja.
- `GET /api/iot/meja/{kode_meja}` — status sesi, pesanan, total bayar.
- `POST /api/iot/panggil-pelayan` — body:

```json
{
  "kode_meja": "LB-01",
  "kode_sesi": "1234"
}
```

Token tidak boleh ditaruh di URL.

Untuk HTTPS, sketch memakai `setInsecure()` agar sertifikat self-signed bisa dipakai saat testing. Untuk production, gunakan sertifikat valid dan ganti `setInsecure()` dengan root CA yang sesuai.
