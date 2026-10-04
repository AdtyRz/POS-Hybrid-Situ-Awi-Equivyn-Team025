# Product Requirement Document (PRD)

**Nama Proyek:** Situ Awi Hybrid POS, Self-Service System & IoT Table Node

**Versi:** 1.0.0

**Tanggal Update:** 14 September 2026

**Status:** Approved / In-Development

**Pengembang (Tim Equivyn):** Dzakwan Fadlurohman Hisyam (Frontend/UI) & Aditya Riqi (Backend/DB/IoT)

**Pembimbing  & Pemilik:** Nurdiansyah Permana, S.T. (Guru Pembimbing) & Rodiansyah Ariwibowo (Owner Resto)

## 1. Executive Summary & Problem Statement

### 1.1 Latar Belakang & Masalah

Resto & Saung Lesehan Situ Awi memiliki 11 saung/meja yang tersebar di area indoor dan outdoor. Proses pencatatan pesanan manual menggunakan nota kertas menyebabkan beberapa kendala operasional utama:

- Risiko kesalahan pencatatan dan pesanan tertukar akibat tulisan tangan tidak terbaca di dapur/bar.

- Pelayan lambat merespons pemanggilan dari meja saung yang lokasinya jauh dari meja kasir/dapur.

- Antrean panjang di meja kasir saat waktu sibuk (peak hours).

### 1.2 Solusi & Tujuan Proyek

Membangun platform **Situ Awi Hybrid POS  & Self-Service System** berbasis Web (Laravel & PostgreSQL) yang terintegrasi secara real-time dengan:

- **Self-Service QR Ordering:** Pelanggan melakukan pemesanan dan pembayaran mandiri via e-Menu.

- **Multi-KDS (Kitchen Display System):** Pemisahan antrean makanan (Dapur) dan minuman (Bar) berbasis WebSocket.

- **IoT Table Node (ESP32):** Panggil pelayan mandiri via Touch Sensor (TTP223) dan pemantauan status pesanan di layar LCD saung via MQTT.

## 2. Target User & Matriks Pengguna (6 Roles)

Sistem mendukung autentikasi 6 Role menggunakan **Laravel Auth  & Custom Middleware Guard**:

| Role | Perangkat Utama | Tugas & Akses Utama |
| --- | --- | --- |
| **Pengguna (Tamu)** | Smartphone (Browser) | Scan QR, lihat e-Menu, Lihat Bon, Lanjut Pembayaran Checkout (Midtrans QRIS / Cash), tekan sensor panggil pelayan. |
| **Kasir** | Desktop / POS PC | Validasi pesanan tunai, pantau notifikasi panggil pelayan, cetak struk thermal. |
| **Pelayan** | Mobile POS (Tablet/HP) | Terima alert panggilan saung, ubah status pesanan menjadi "DELIVERED". |
| **Koki** | KDS Dapur (Tablet/Monitor) | Memantau & memproses antrean makanan (PROCESSING -> READY). |
| **Barista** | KDS Bar (Tablet/Monitor) | Memantau & memproses antrean minuman (PROCESSING -> READY). |
| **Admin / Owner** | PC / Laptop | Pengelolaan Master Data Menu, Stok, User, Kategori, serta Laporan Penjualan. |

## 3. Tech Stack & Arsitektur Sistem

### 3.1 Stack Teknologi Terkonfirmasi

```text
+-----------------------------------------------------------------------+
|                       FRONTEND & INTERFACE LAYER                      |
|           Laravel Blade Templating + Tailwind CSS + Axios             |
+-----------------------------------------------------------------------+
                                    |
                    +---------------+---------------+
                    | (WebSocket / Laravel Reverb)  | (REST API / Axios)
                    v                               v
+-----------------------------------------------------------------------+
|                        BACKEND & SERVER LAYER                         |
|           Laravel Framework (PHP 8.5.5) + Eloquent ORM                |
|           Laravel MQTT Client Service (Subscriber)                    |
+-----------------------------------------------------------------------+
        |                                   |                    |
        | (SQL Query)                       | (REST API Call)    | (MQTT Broker)
        v                                   v                    v
+---------------+                   +---------------+    +--------------+
| DATA STORAGE  |                   | PAYMENTS      |    | MQTT BROKER  |
| PostgreSQL DB |                   | Midtrans      |    | Mosquitto /  |
|               |                   | Sandbox QRIS  |    | EMQX         |
+---------------+                   +---------------+    +--------------+
                                                                 ^
                                                                 | (Wi-Fi MQTT)
                                                         +--------------+
                                                         | HARDWARE IOT |
                                                         | ESP32 Node   |
                                                         +--------------+
```

- **Backend:** Laravel Framework (PHP 8.5.5)

- **Frontend:** Laravel Blade Templating Engine + Tailwind CSS

- **Database:** PostgreSQL Database

- **Real-time Engine:** Laravel Reverb (WebSockets for Web UI) & Eclipse Mosquitto / EMQX (MQTT Broker for IoT Devices)

- **Payment Gateway:** Midtrans Sandbox (Dynamic QRIS)

- **IoT Hardware (Table Node):** ESP32 DOIT DevKit V1, Sensor Sentuh TTP223, Active Buzzer (3 detik), LCD 16x2 I2C.

## 4. Master Data & Layout Resto

### 4.1 Master Saung / Meja (11 Meja)

- **Lesehan Bawah (5 Meja):** LB-01, LB-02, LB-03, LB-04, LB-05

- **Lesehan Atas (2 Meja):** LA-01, LA-02

- **Kursi Atas (4 Meja):** KA-01, KA-02, KA-03, KA-04

### 4.2 Kategori Menu (Seed Data)

- **Dapur (31 Menu Makanan  & Camilan):** Paket Liwet, Nasi Rempah Ayam, Baso Aci, Pepes, Pisang Keju, Cheese Roll, dll.

- **Bar (13 Menu Coffee  & Drinks):** Vietnam Drip, Moka Pot, V60, Kopi Tubruk, Arabika Ice Coffee.

## 5. Alur Kerja Utama (User Flow & System Logic)

### 5.1 Self-Service QR Order & Payment Flow

**Ketentuan Utama Pemesanan:** Seluruh pemesanan menu (makanan & minuman) wajib dan murni dilakukan secara mandiri oleh pelanggan melalui pemindaian QR Code di saung (table_id). Sistem tidak melayani pembuatan/input pesanan langsung oleh kasir (no direct ordering) maupun transaksi Takeaway.

- Pelanggan memindai QR Code di saung (contoh: LB-01).

- System membuka e-Menu dan mengunci nomor meja table=LB-01.

- Pelanggan menambah menu ke keranjang, Lihat Bon, jika sudah sesuai, tekan Lanjut Pembayaran

- **Metode Pembayaran:**
  - **Opsi A (Midtrans QRIS):** Sistem menggenerate Dynamic QRIS. Setelah dibayar, Webhook memperbarui status menjadi PAID secara otomatis.
  - **Opsi B (Tunai di Kasir):** Pesanan masuk dengan status UNPAID / WAITING_CASH. Kasir menerima uang tunai dan mengklik **Konfirmasi Lunas**.

- **Broadcasting Event:**
  - Backend menyiarkan item ke KDS Dapur / KDS Bar via WebSocket.
  - Backend mengirim pesan MQTT ke saung resto/saung/LB-01/status -> Teks LCD berubah menjadi **"Status: Diproses"**.

### 5.2 IoT Waiter Call Flow (Sensor Sentuh)

- Pelanggan menyentuh sensor TTP223 di saung.

- **Respons Hardware Lokal:** Active Buzzer menyala 3 detik, LCD menampilkan "Memanggil Pelayan..." selama 3 detik.

- Perangkat ESP32 mem-publish MQTT ke topik resto/saung/LB-01/call.

- Backend Laravel menerima MQTT, mencatat log ke DB (call_waiter_logs), lalu menyiarkan notifikasi via WebSocket ke Kasir & Mobile POS Pelayan (suara alarm & pop-up modal).

- **Anti-Spam Logic:** Sensor TTP223 masuk ke masa *cooldown* 5 detik setelah buzzer mati.

### 5.3 KDS Workflow & Status Order Synchronizer

```text
[PAID] ---> KDS Dapur/Bar Klik 'Proses' ---> Teks LCD IoT: "Status: Diproses"
        ---> KDS Dapur/Bar Klik 'Selesai' ---> Teks LCD IoT: "Status: Siap"
        ---> Pelayan Antar & Klik 'Deliver'---> Order Complete & Cetak Struk Thermal
```

## 6. Functional & Non-Functional Requirements

### 6.1 Functional Requirements (FR) Matrix

| ID | Deskripsi Fitur | Role Terkait | Prioritas |
| --- | --- | --- | --- |
| **FR-001** | Autentikasi 6 Role menggunakan Laravel Middleware Guard. | All Roles | High |
| **FR-002** | CRUD Master Data Menu, Kategori (Dapur/Bar), Stok Auto-Cut, dan Harga. | Admin | High |
| **FR-003** | QR Code Scanning & Self-Service Checkout System. | Pengguna | High |
| **FR-004** | Multi-KDS Engine real-time via WebSocket Broadcast. | Koki, Barista | High |
| **FR-005** | Integrasi Payments Midtrans QRIS & POS Validasi Kasir Tunai. | Kasir | High |
| **FR-006** | IoT Waiter Call System via Touch Sensor & MQTT Broker. | Pelayan, Kasir | High |
| **FR-007** | LCD Display Status Synchronizer (Diproses -> Siap). | Hardware IoT | Medium |
| **FR-008** | Dasbor Laporan Penjualan & Ekspor Data (PDF/Excel). | Admin / Owner | Medium |

### 6.2 Non-Functional Requirements (NFR)

- **Performance:** Waktu respon loading e-Menu < 2 detik pada jaringan mobile 4G.

- **Real-time Latency:** Latensi sinyal WebSocket & MQTT < 1 detik.

- **Security:** Hashing Password dengan Bcrypt, Proteksi CSRF, Sanitasi Input SQL Injection via Eloquent ORM.

- **Resiliency:** Auto-reconnect Wi-Fi & MQTT pada ESP32 jika jaringan terputus.

## 7. Scope & Boundary Management

| IN-SCOPE (Fitur Wajib Completed) | OUT-OF-SCOPE (Tidak Dikerjakan) | Justifikasi Technical |
| --- | --- | --- |
| Autentikasi 6 Role Laravel Auth. | Mode Production Payment Gateway. | Menggunakan Midtrans Sandbox untuk pengujian tanpa transaksi uang asli. |
| Self-Service e-Menu Blade & Checkout. | Reservasi Saung Jarak Jauh. | Fokus pada On-Site Ordering di saung resto. |
| Multi-KDS Dapur & Bar via WebSockets. | Native Mobile App (APK/IPA). | Fokus Web Responsive PWA yang ringan diakses browser. |
| Integrasi ESP32 IoT Node (MQTT & Sensor). | Modul Akuntansi Lanjutan (Jurnal/Depresiasi). | Membatasi kompleksitas sesuai cakupan POS Kuliner. |
| Integrasi Midtrans Sandbox (QRIS) & Cash. | Pelacakan titik GPS posisi pelayan. | Tidak mendesak untuk operasional saung lesehan. |

## 8. Matrix RACI & Manajemen Risiko

### 8.1 Matriks RACI Tim Proyek

| Kode Artefak / Tasks | Dzakwan Fadlurohman H. (Frontend) | Aditya Riqi (Backend/DB/IoT) | Nurdiansyah Permana, S.T. (Pembimbing) |
| --- | --- | --- | --- |
| **S0:** Analisis & Dokumen PRD | R / A | R | C / I |
| **S1:** UI/UX Wireframe & Blade Views | R / A | C | I |
| **S1:** ERD DB PostgreSQL & Skema IoT ESP32 | C | R / A | C |
| **S2:** API Development & MQTT Setup | C | R / A | I |
| **S2:** Integrasi Midtrans & WebSockets Reverb | R | R / A | I |
| **S3:** Testing End-to-End & Deployment | R | R / A | A |

*(Keterangan: R = Responsible, A = Accountable, C = Consulted, I = Informed)*

### 8.2 Risk Register & Mitigasi Utama

| Ref ID | Potensi Masalah | Impact | Rencana Mitigasi (Pencegahan) | Contingency Plan (Bila Terjadi) |
| --- | --- | --- | --- | --- |
| **R-001** | Conflict Merge Git saat development. | High | Menerapkan aturan fitur terpisah (feature/frontend & feature/backend). | Manual merge resolution bersama tim. |
| **R-002** | ESP32 terputus dari Wi-Fi Resto. | Medium | Fitur Auto-Reconnect Wi-Fi & MQTT Auto-Rebind di firmware. | Tampilkan "Offline" di LCD tanpa menyebabkan Web POS crash. |
| **R-004** | Jaringan internet mati saat Demo Day. | High | Menyiapkan Localhost Environment (Laragon + Mosquitto Local). | Switch penuh ke jaringan Wi-Fi Portable/Lokal. |