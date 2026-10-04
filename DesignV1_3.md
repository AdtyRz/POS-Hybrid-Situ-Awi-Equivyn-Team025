# Design System & UI/UX Specification
## Situ Awi Hybrid POS, Self-Service System & IoT Table Node

| | |
|---|---|
| **Versi Dokumen** | 1.3 — Turunan dari `PRD.md`, direvisi untuk melengkapi cakupan 5 kelompok tampilan (Pelanggan, Kasir, Dapur/Bar, Pelayan, Admin/Owner) + spesifikasi tampilan adaptif desktop (1366px) & seluler (360–414px) |
| **Referensi Motion/Interaksi** | sanalabs.com (scroll-reveal, micro-interaction, premium minimal) |
| **Referensi Warna** | Logo "Saung Situ Awi" (hijau hutan + emas + daun bambu) |
| **Referensi Layout/Tata Letak** | Mockup aplikasi food-ordering yang dikirim user (card-based, bottom nav) — **hanya sebagai gambaran struktur**, bukan untuk ditiru identitasnya |
| **Prinsip Desain** | *Warm Forest Hospitality* — kehangatan saung tradisional dibungkus interaksi digital yang modern, halus, dan cepat |

---

## 1. Filosofi Desain

Situ Awi bukan aplikasi fast-food generik — ia adalah pengalaman bersantap lesehan di tepi situ, ditemani rimbunan bambu. Maka bahasa visualnya menggabungkan:

1. **Ketenangan alam** (hijau hutan, tekstur daun, ruang napas/whitespace lega) — dikontraskan dengan
2. **Kehangatan & kemewahan lokal** (emas/gold sebagai aksen premium, terinspirasi garis outline neon keemasan pada logo), dan
3. **Kecepatan & kejernihan interaksi digital** ala produk SaaS modern (transisi halus, hierarki tipografi tegas, feedback mikro instan) — diambil dari nuansa sanalabs.com: banyak *whitespace*, animasi *reveal* yang tenang bukan heboh, dan hover state yang terasa "hidup" tanpa berlebihan.

Setiap layar dirancang agar **tamu awam maupun staf dapur yang jarang pegang gadget** tetap bisa menavigasi dalam hitungan detik — besar, jelas, dan reflektif terhadap konteks fisik (outdoor, cahaya matahari, tangan basah/berminyak di dapur).

---

## 2. Design Tokens — Warna (diambil dari Logo)

Palet diekstrak langsung dari logo *Saung Situ Awi* (hijau hutan sebagai dasar, emas sebagai aksen signature, hijau daun bambu sebagai penyeimbang alami).

### 2.1 Primary — Forest Green (identitas utama)
| Token | Hex | Penggunaan |
|---|---|---|
| `--forest-900` | `#052E1B` | Teks di atas latar terang, header gelap KDS |
| `--forest-800` | `#06381F` | Navbar gelap, background hero mobile |
| `--forest-700` | `#0A4A28` | Hover state tombol primer |
| `--forest-600` | `#0B4D2B` | **Brand primary** — tombol utama, ikon aktif, item sidebar admin aktif |
| `--forest-400` | `#12703F` | Badge status "Diproses", ilustrasi sekunder |
| `--forest-100` | `#D9EBE0` | Background chip/tag netral, kartu inaktif |

### 2.2 Accent — Signature Gold (dari outline & cangkir logo)
| Token | Hex | Penggunaan |
|---|---|---|
| `--gold-600` | `#C9982F` | Teks tombol gold di atas latar terang, border aktif |
| `--gold-500` | `#E0B24E` | **Aksen utama** — CTA sekunder, harga, highlight status "Siap" |
| `--gold-300` | `#F3E27A` | Glow/neon accent (meniru efek neon tulisan logo), badge promo |
| `--gold-50` | `#FBF3DD` | Background kartu premium/highlight |

### 2.3 Secondary — Bamboo Leaf Green
| Token | Hex | Penggunaan |
|---|---|---|
| `--leaf-600` | `#5C7A22` | Ikon kategori "Makanan", elemen dekoratif daun |
| `--leaf-400` | `#8BAA3F` | Status sukses ringan, progress bar |
| `--leaf-100` | `#EEF3DE` | Background section alternatif |

### 2.4 Neutral & Semantic
| Token | Hex | Penggunaan |
|---|---|---|
| `--cream-50` | `#FBF8F1` | Background utama app (pengganti putih polos — hangat) |
| `--cream-100` | `#F3EEDF` | Background kartu di atas cream-50 |
| `--charcoal-800` | `#2B2A26` | Teks body utama |
| `--charcoal-500` | `#6B6A63` | Teks sekunder/caption |
| `--danger-500` | `#C1442C` | Void/Batal, stok habis, error |
| `--warning-500` | `#E0B24E` (gold) | Status "Menunggu Pembayaran" |
| `--success-500` | `#2E8B57` | Status "Lunas"/"Siap Diantarkan" |

> **Catatan implementasi:** Semua warna disimpan sebagai CSS custom properties di :root. Layar KDS Dapur/Bar menggunakan basis tema terang (--cream-50) dengan kombinasi kartu --forest-600 dan aksen --gold-500 agar selaras dengan palet utama sistem.

---

## 3. Tipografi

| Peran | Font | Fallback Stack | Catatan |
|---|---|---|---|
| Display/Brand (judul hero, nama saung) | **"Playfair Display"** atau gaya script elegan seperti pada logo | `Georgia, 'Times New Roman', serif` | Dipakai terbatas — logo & judul besar saja, meniru kesan tulisan neon-script pada logo |
| UI/Heading | **"Plus Jakarta Sans"** (600–700) | `-apple-system, 'Segoe UI', sans-serif` | Tegas, modern, mudah dibaca dari jarak (penting untuk KDS & LCD) |
| Body/UI Text | **"Plus Jakarta Sans"** (400–500) | sama seperti di atas | Konsisten lintas layar web |
| Angka/Harga | **"Plus Jakarta Sans"** (700, tabular-nums) | — | Agar kolom harga & subtotal rapi sejajar |

**Skala tipografi (mobile-first, rem):** `12 / 14 / 16 / 20 / 24 / 32 / 40` — heading besar hanya dipakai di hero e-Menu & dashboard admin; KDS memakai skala diperbesar (`20/28/40/56`) karena dibaca dari jarak 1–2 meter.

---

## 4. Animasi & Micro-interaction (Referensi sanalabs.com)

Gaya sanalabs.com dicirikan oleh: transisi yang *tenang dan presisi* (bukan bouncy/playful), elemen muncul bertahap saat discroll, hover state yang subtil namun terasa premium, serta navigasi yang tetap ringan meski elemen bergerak. Prinsip ini diadaptasi sebagai berikut:

### 4.1 Prinsip Umum
- **Easing standar:** `cubic-bezier(0.22, 1, 0.36, 1)` ("ease-out-expo" lembut) untuk hampir semua transisi — memberi kesan halus, bukan mekanis.
- **Durasi:** 200–280ms untuk micro-interaction (tombol, hover), 400–600ms untuk transisi antar-state/halaman, 800ms+ hanya untuk reveal konten panjang.
- **Prinsip "calm motion":** tidak ada elemen yang memantul (no bounce/elastic easing) kecuali indikator notifikasi panggilan pelayan (satu-satunya elemen yang boleh terasa "mendesak").

### 4.2 Katalog Animasi per Konteks

| Konteks | Animasi | Trigger |
|---|---|---|
| **Landing e-Menu (scan QR)** | Logo & nama saung *fade + slide-up* 24px bertahap (staggered 80ms per elemen), mirip reveal section di sanalabs.com | Saat halaman dimuat |
| **Scroll kategori menu** | Card menu *fade-in* + *scale 0.96 → 1* saat masuk viewport (IntersectionObserver), staggered per baris | Scroll |
| **Hover/tap kartu menu** | Elevasi bayangan naik halus (`box-shadow` transisi 200ms) + gambar `scale(1.03)` di dalam frame terpotong (overflow hidden) | Hover (desktop) / tap-hold (mobile) |
| **Tombol "Tambah ke Pesanan"** | Ripple gold tipis dari titik tekan + ikon keranjang di header "bounce" ringan 1x sebagai konfirmasi | Tap |
| **Badge "Stok Habis"** | Fade-in dengan sedikit desaturasi gambar produk (grayscale 40%) | Saat data stok = 0 |
| **Floating Cart Button (mengambang, bukan bottom-nav)** | Badge jumlah item *pop* (`scale 0→1.15→1`) setiap kali item ditambahkan, tombol sedikit "berdenyut" 1x untuk menarik perhatian | Item ditambahkan ke keranjang |
| **Transisi antar-halaman single-page (Menu → Keranjang → Checkout → Pembayaran)** | *Cross-fade* + *slide* 24px, durasi 400ms, konten lama fade-out lebih cepat dari fade-in konten baru (overlap 100ms) — pola khas transisi Sana; setiap perpindahan terasa seperti membalik "lembar" baru, bukan berpindah tab | Tap "Checkout" / "Lanjut ke Pembayaran" / tombol back |
| **KDS — pesanan baru masuk** | Kartu order *slide-in dari kanan* ke ujung baris (bukan masuk ke kolom tertentu), dengan highlight border gold berdenyut lembut (`pulse` opacity 0.6↔1, 2 siklus) lalu diam | Event broadcast diterima (Reverb) |
| **KDS — ubah status (tap kartu)** | Badge status di dalam kartu *cross-fade* ke warna & label berikutnya (Menunggu→Diproses→Siap Diantar), kartu tetap di posisinya di baris (tidak berpindah kolom), disertai checkmark hijau muncul dengan `scale 0 → 1` (elastic ringan, 300ms) saat mencapai "Siap" | Tap kartu / tombol status |
| **KDS — kartu diambil pelayan ("Diantarkan")** | Kartu *fade + collapse width* lalu hilang dari baris, kartu di sebelah kanannya bergeser mengisi celah (`shift` halus 250ms) | Pelayan tap "Tandai Diantarkan" di Mobile POS |
| **Notifikasi Panggil Pelayan (Kasir/Mobile POS)** | Toast slide-down dari atas + ikon lonceng bergetar (`shake` halus 3x) + warna latar gold berdenyut 2 detik lalu meredup ke netral — satu-satunya elemen "urgent" dalam sistem | Touch Sensor TTP223 fisik di meja disentuh → sinyal MQTT event `call_waiter` (bukan dari aplikasi tamu) |
| **LCD Meja (ESP32, non-web)** | Teks status *fade cross* antar state (Waiting → Diproses → Siap) via kontrol backlight/kontras bertahap (bukan animasi CSS — disimulasikan di firmware dengan delay increment brightness), tetap konsisten secara *perasaan* dengan transisi web | Update MQTT topic |
| **Dashboard Admin — grafik omzet** | Bar/line chart *draw-in* dari 0 ke nilai aktual (ease-out, 700ms) saat pertama render, angka counter naik bertahap ("count-up") | Saat dasbor dibuka |
| **Sticky header saat scroll** | Header menyusut tinggi (`padding` 20px→12px) + background dari transparan menjadi `--cream-50` dengan `backdrop-filter: blur(8px)`, mirip nav sanalabs.com yang mengecil & blur saat discroll | Scroll > 40px |

---

## 5. Referensi Layout & Tata Letak (dari Gambar 2 — diadaptasi kreatif)

Gambar kedua (mockup pemesanan makanan) digunakan **murni sebagai referensi struktur informasi & pola interaksi**, bukan identitas visual (identitas tetap dari logo Situ Awi). Pola yang diserap dan diadaptasi:

- Header sapaan personal + ikon notifikasi/keranjang di kanan atas → di Situ Awi diganti dengan **sapaan berbasis nomor saung** ("Halo, Tamu Saung 02! 🌿") karena tidak ada login tamu.
- Search bar + filter → dipertahankan untuk pencarian menu, dengan filter kategori Dapur/Bar.
- Hero banner carousel besar → diganti **kartu status saung interaktif** (menampilkan status IoT: "Meja Anda Terhubung ✅") menggantikan promo produk, karena ini lebih relevan di awal alur self-service.
- Strip ikon kategori horizontal (scrollable) → dipertahankan, namun ikon diberi ilustrasi custom bertema kuliner Sunda/lesehan (bukan generik).
- Kartu "Popular Combos" horizontal-scroll dengan badge (Bestseller/Promo) → diadaptasi jadi **"Menu Favorit Saung"**, dengan badge dinamis dari data penjualan real (bukan statis).
- Tombol "+" bulat pada kartu produk → dipertahankan sebagai *quick-add*, versi lebih besar untuk kemudahan sentuh di outdoor.
- Bottom navigation bar dengan tombol tengah menonjol (FAB-style) → **tidak dipakai**. Sisi tamu dirancang sebagai **single-page flow** tanpa navbar/tab sama sekali: Menu (satu halaman scroll panjang) → tap "Checkout" → halaman Keranjang & Ringkasan Pesanan (single page) → tap "Lanjut ke Pembayaran" → halaman Pembayaran (single page, QRIS/Cash). Tidak ada tab "Home/Orders/Profile" karena tamu tidak login dan hanya punya satu tujuan: pesan lalu bayar. Sebagai gantinya, hanya **satu tombol mengambang** (*floating action button*) menemani tamu di sepanjang halaman menu: **Keranjang** (kanan bawah). Tidak ada tombol "Panggil Pelayan" di aplikasi — pemanggilan pelayan murni fisik, dilakukan lewat Touch Sensor TTP223 pada modul IoT Table Node yang terpasang di meja/saung (lihat FR-006).
- Kartu promo banner di bawah kategori → diganti **status pesanan real-time tamu saat ini** (mis. progress bar "Pesanan Diproses 🍳") — memindahkan fungsi "jualan" menjadi fungsi "informasi transparan", lebih sesuai konteks self-service on-site.

Pendekatan ini sengaja **tidak meniru 1:1** — layout dipakai sebagai kerangka pola UX yang sudah terbukti familiar (card grid, carousel, quick-add), lalu diisi ulang dengan konten & prioritas fungsional khas alur Situ Awi (QR-based, tanpa akun, terhubung IoT, single-page). Navbar/sidebar sesungguhnya **hanya ada di Dashboard Admin** — semua layar operasional lain (e-Menu, KDS, POS Kasir, Mobile Pelayan) sengaja dibuat tanpa navigasi bertingkat agar fokus satu tugas per layar.

---

## 6. Komponen UI Inti

| Komponen | Radius | Shadow | Catatan |
|---|---|---|---|
| Kartu produk/menu | `16px` | `0 2px 8px rgba(6,77,43,0.08)` | Hover: shadow naik ke `0 8px 24px`, gambar zoom 1.03x |
| Tombol Primer | `12px` (pill untuk CTA utama: `999px`) | — | `--forest-600` bg, teks `--cream-50`, hover `--forest-700` |
| Tombol Sekunder (gold outline) | `12px` | — | Border `--gold-500` 1.5px, teks `--gold-600`, hover fill tipis `--gold-50` |
| Badge status | `999px` (pill) | — | Warna semantik (success/warning/danger), teks putih, huruf kecil bold |
| Floating Action Button (Keranjang) | `999px` (bulat) | `0 4px 14px rgba(6,77,43,0.25)` | Satu-satunya FAB di halaman Menu tamu — **bukan bottom-nav**. Tidak ada FAB "Panggil Pelayan"; pemanggilan pelayan murni via Touch Sensor fisik IoT |
| Sidebar Admin | — | `2px 0 12px rgba(0,0,0,0.05)` | `--forest-800` bg, satu-satunya elemen navigasi bertingkat di seluruh sistem |
| Toast notifikasi | `14px` | `0 4px 16px rgba(0,0,0,0.12)` | Slide-down dari atas, auto-dismiss 4s kecuali panggilan pelayan (perlu tap "Selesai") |

---

## 7. Aksesibilitas & Konteks Fisik

- **Kontras tinggi wajib** di semua layar outdoor (e-Menu, LCD IoT) — rasio minimal 4.5:1, diuji khusus di bawah simulasi cahaya terik (mode "outdoor legibility": tingkatkan saturasi `--forest-600` & `--gold-500` sedikit di breakpoint mobile).
- **Touch target minimal 44×44px** di semua tombol interaktif Kasir, Mobile POS, dan e-Menu (tangan basah/terburu-buru saat jam sibuk).
- **KDS menggunakan varian Light Mode** dengan latar `--cream-50`, kartu `--forest-600`, dan aksen `--gold-500` dengan kontras tinggi agar tetap jelas terbaca di area dapur & bar.

---

## 8. Adaptasi Layar — Desktop (1366px) & Seluler (360–414px)

Seluruh layar **berbasis web** (e-Menu tamu, POS Kasir, Mobile POS Pelayan, Dashboard Admin/Owner) wajib adaptif terhadap dua target viewport acuan. KDS Dapur/Bar dan LCD Meja (ESP32) dikecualikan dari klausul ini — keduanya memakai layar fisik tetap (monitor dapur & modul IoT), bukan browser yang di-resize.

### 8.1 Breakpoint Acuan

| Token | Lebar | Perangkat Representatif | Layar yang Berjalan di Sini |
|---|---|---|---|
| `--bp-mobile-min` | `360px` | Android kelas menengah (mis. Galaxy A-series) | e-Menu tamu, Mobile POS Pelayan |
| `--bp-mobile-max` | `414px` | iPhone Plus/Max, Android besar | e-Menu tamu, Mobile POS Pelayan |
| `--bp-tablet` | `768px` | Tablet Kasir portable (opsional, transisi) | POS Kasir (mode tablet), e-Menu (jika tamu buka di tablet) |
| `--bp-desktop` | `1366px` | Laptop/monitor Kasir & Admin standar | POS Kasir (fixed station), Dashboard Admin/Owner |

Strategi: **mobile-first fluid**, bukan sekadar dua breakpoint kaku. CSS ditulis untuk 360px sebagai dasar, memakai unit `rem`/`%`/`clamp()` agar tetap wajar mengisi lebar berapa pun *di antara* 360–414px maupun di atas 768px, lalu `@media (min-width: 1366px)` mengunci tata letak versi desktop penuh. Ini mencegah tampilan "pecah" di lebar-lebar perantara (mis. 390px, 393px, 480px) yang tidak persis 360 atau 414.

```css
:root {
  --container-mobile: 100%;
  --container-desktop: 1280px; /* max-width konten di dalam layar 1366px, sisakan margin */
  --gutter-mobile: 16px;
  --gutter-desktop: 32px;
}

/* Basis: 360px ke atas */
.container { width: 100%; padding-inline: var(--gutter-mobile); }

@media (min-width: 768px) {
  .container { padding-inline: 24px; }
}

@media (min-width: 1366px) {
  .container { max-width: var(--container-desktop); margin-inline: auto; padding-inline: var(--gutter-desktop); }
}
```

### 8.2 Grid

| | Mobile (360–414px) | Desktop (≥1366px) |
|---|---|---|
| Kolom grid | 4 kolom | 12 kolom |
| Gutter | `16px` | `32px` |
| Margin luar | `16px` | `(100vw − 1280px)/2`, min `48px` |
| Kartu menu/produk per baris | 2 kolom (grid kaku) | 4–5 kolom |
| Kartu "Menu Favorit" (horizontal scroll) | tetap horizontal-scroll (snap) | diganti grid statis 5–6 kartu/baris tanpa scroll, karena lebar layar sudah cukup |

### 8.3 Adaptasi per Kelompok Layar

**e-Menu Tamu (single-page flow, §5)** — tetap **tanpa navbar/bottom-nav** di kedua ukuran, sesuai prinsip single-page.
- Mobile (360–414px): 1 kolom penuh untuk kartu status saung & progress pesanan; grid produk 2 kolom; FAB Keranjang tetap di kanan-bawah, ukuran `56×56px`.
- Desktop (≥1366px): konten dikunci ke `max-width: 1280px` di tengah layar (bukan melebar penuh — mencegah baris teks & kartu terlalu panjang dibaca); grid produk naik ke 4 kolom; kartu status saung & search/filter disusun sebagai **dua kolom sejajar** (status di kiri, search+filter di kanan) alih-alih ditumpuk vertikal seperti di mobile; FAB Keranjang tetap kanan-bawah namun berubah jadi **panel keranjang tertambat (docked)** di sisi kanan pada lebar ≥1366px — opsional, fallback tetap FAB jika waktu build terbatas.

**POS Kasir** — target utama desktop 1366px (perangkat kasir tetap), namun tetap harus dapat dibuka di tablet/mobile sebagai cadangan saat perangkat utama bermasalah.
- Desktop (≥1366px): layout dua panel — daftar menu/kategori di kiri (≈65% lebar), keranjang & ringkasan pembayaran tertambat di kanan (≈35% lebar, `position: sticky`), tombol Cetak Struk & metode bayar selalu terlihat tanpa scroll.
- Mobile (360–414px): dua panel di atas ditumpuk — daftar menu di atas (scrollable), ringkasan keranjang menjadi **bottom sheet** yang bisa ditarik naik (collapsed menampilkan total & tombol "Bayar", expand menampilkan rincian item).

**Mobile POS Pelayan** — dirancang **mobile-only** (360–414px), tidak perlu versi desktop karena secara fungsi selalu dipegang berjalan; jika dibuka di layar besar, cukup ditampilkan ter-*center* dengan `max-width: 480px` (tidak melebar penuh, tidak perlu tata letak desktop khusus).

**Dashboard Admin/Owner** — target utama desktop 1366px dengan sidebar navigasi (§5, §6).
- Desktop (≥1366px): sidebar tetap terbuka (`~240px` lebar), konten utama di kanan, grafik omzet & kartu ringkasan tersusun grid 3–4 kolom.
- Mobile (360–414px): sidebar disembunyikan secara default, diganti **hamburger menu** yang membuka sidebar sebagai *drawer* overlay dari kiri (slide-in, sama easing dengan §4.1); grid kartu ringkasan turun jadi 1 kolom, grafik omzet full-width dengan tinggi disesuaikan agar tetap terbaca tanpa horizontal-scroll.

### 8.4 Tipografi & Touch Target Adaptif

- Skala tipografi mobile (§3: `12/14/16/20/24/32/40`) dipakai apa adanya di 360–414px.
- Di ≥1366px, heading hero & judul dashboard boleh naik satu tingkat dari skala dasar (mis. `32px → 40px`, `40px → 48px`) karena jarak baca layar desktop lebih jauh dan ruang lebih lega; body text (`16px`) **tidak berubah** di kedua breakpoint agar konsistensi keterbacaan terjaga.
- Touch target minimal `44×44px` (§7) tetap berlaku di **kedua** ukuran layar — POS Kasir & Dashboard Admin tetap harus nyaman disentuh di layar desktop bertipe touchscreen (asumsi umum kasir menggunakan monitor sentuh), bukan hanya dioptimalkan untuk mouse.

### 8.5 Kaidah Uji

Setiap layar web wajib diperiksa minimal pada **tiga lebar acuan**: `360px`, `414px`, dan `1366px` (DevTools responsive mode atau perangkat asli), memastikan:
1. Tidak ada horizontal-scroll yang tidak disengaja pada elemen non-carousel.
2. Semua teks & tombol tetap terbaca/tersentuh tanpa overlap.
3. Transisi antar-breakpoint (mis. sidebar → hamburger) tidak "meloncat" — mengikuti prinsip *calm motion* §4.1 bila melibatkan animasi buka/tutup.

> **Catatan konfirmasi:** Rentang 360–414px mencakup mayoritas ponsel Android/iOS umum, dan 1366px mewakili resolusi laptop/monitor kasir paling umum di lapangan — namun perlu dikonfirmasi ke `PRD.md`/NFR asli apakah ada persyaratan dukungan tablet (768–1024px) atau layar ultra-wide admin yang belum tercakup eksplisit di sini.

---

## 9. Ringkasan Traceability ke PRD

| Elemen Desain | Terhubung ke |
|---|---|
| e-Menu layout, animasi kartu & status stok habis | FR-003, US-001, PB-003 |
| Catatan khusus per item (checkout → KDS → struk) | FR-003, FR-005 |
| Halaman Login Multi-Role (6 role) | FR-002 |
| KDS light-mode kanban terfilter per kategori & pulse animasi | FR-004, PB-004 |
| Toast notifikasi panggil pelayan (urgent motion) | FR-006, US-002, PB-006, PB-007 |
| Palet status pembayaran (warning/success/danger) | FR-005, FR-005A, FR-005B, PB-005 |
| Cetak & cetak-ulang struk thermal | FR-005 |
| Dashboard admin, count-up chart & filter periode | FR-008, PB-008 |
| Master data menu (upload gambar) & meja/saung (generate QR) | FR-001, FR-003 |
| Ekspor laporan PDF/Excel | FR-008 |
| Kontras tinggi & spesifikasi LCD 16x2 | FR-007, NFR-002 |
| Tampilan adaptif desktop 1366px & seluler 360–414px (e-Menu, POS Kasir, Mobile POS, Dashboard Admin) | NFR-003 *(nomor perlu dikonfirmasi ke `PRD.md`)* |

> **Catatan validasi:** Nomor FR di atas mengikuti pola penomoran `PRD.md` yang sudah dipakai di dokumen aslinya; beberapa baris baru (Login, Cetak Struk, Ekspor Laporan, Generate QR, Upload Gambar) dipetakan berdasarkan kedekatan fungsional karena requirement-nya eksplisit diminta user namun belum ada nomor FR spesifik di draf ini — mohon dicocokkan/dikoreksi terhadap `PRD.md` yang sebenarnya.

---

*Dokumen ini adalah spesifikasi desain turunan dari `PRD.md` dan siap dipakai sebagai acuan Sprint 1 (Perancangan UI e-Menu, KDS, & POS).*

---

## 10. Changelog — v1.0 → v1.1

Revisi ini melengkapi 5 poin yang belum tercakup di v1.0 (divalidasi terhadap daftar kebutuhan per role):

1. **e-Menu:** menambahkan visual state kartu "Stok Habis" (grayscale + badge + tombol disabled) pada Halaman 1; mengubah kolom catatan dari level-order menjadi **per-item** agar konsisten dengan contoh "pedas/tidak pedas".
2. **POS Kasir:** menambahkan tombol & spesifikasi **Cetak Struk / Reprint** ke thermal printer (sebelumnya tidak ada di v1.0).
3. **KDS Dapur/Bar:** mengoreksi tampilan v1.0 yang tampak sebagai satu layar gabungan kategori — dipertegas menjadi **dua instance terpisah** (Dapur = kategori Makanan saja, Bar = kategori Minuman saja), dengan mode gabungan sebagai opsi konfigurasi eksplisit untuk saung kecil.
4. **Admin/Owner:** menambahkan **Halaman Autentikasi/Login Multi-Role** (6 role) yang sebelumnya tidak ada sama sekali di v1.0; merinci sub-halaman Dashboard yang sebelumnya hanya berupa label sidebar — filter periode Harian/Mingguan/Bulanan pada grafik omzet, upload gambar menu, generate QR Code meja/saung, dan **ekspor laporan PDF/Excel**.
5. Traceability table (§9) diperluas agar setiap elemen baru di atas punya rujukan FR, dengan catatan bahwa penomoran FR baru perlu dicocokkan ke `PRD.md` asli.

**Item yang perlu konfirmasi user/PRD** (diasumsikan sementara agar desain tetap lengkap, ditandai di teks terkait):
- Pembagian persis 6 role staf — didokumentasikan sebagai asumsi: Owner, Admin, Kasir, Koki/Dapur, Barista/Bar, Pelayan.
- Nomor FR untuk elemen baru (Login, Cetak Struk, Ekspor Laporan, Generate QR, Upload Gambar) belum ada di draf sebelumnya.

## 11. Changelog — v1.1 → v1.2

1. **KDS Dapur/Bar:** layout diubah dari kanban 3-kolom (Menunggu/Diproses/Siap Diantar sebagai kolom terpisah) menjadi **satu baris kartu sejajar** yang di-scroll horizontal, dengan status ditandai lewat badge di dalam tiap kartu, bukan lewat posisi kolom. Tap kartu memajukan status satu langkah di tempat; kartu hilang dari baris begitu diambil pelayan. Animasi terkait di §4.2 disesuaikan.
2. **e-Menu — Pembayaran (Halaman 3):** menambahkan tampilan **QR code QRIS** langsung di halaman saat metode QRIS dipilih (bukan hanya radio button), lengkap dengan timer masa berlaku dan auto-polling status.

## 12. Changelog — v1.2 → v1.3

1. **Menambahkan §8 — Adaptasi Layar (Desktop 1366px & Seluler 360–414px)**, memenuhi ketentuan baru bahwa seluruh aplikasi web harus tampil adaptif di dua target viewport tersebut. Mencakup: breakpoint token, strategi *mobile-first fluid* dengan `clamp()`, aturan grid (4 kolom mobile / 12 kolom desktop), adaptasi tata letak per kelompok layar (e-Menu, POS Kasir, Mobile POS Pelayan, Dashboard Admin), skala tipografi & touch target lintas breakpoint, serta kaidah uji tiga-lebar (360px/414px/1366px).
2. KDS Dapur/Bar & LCD Meja (ESP32) dinyatakan eksplisit **di luar cakupan** klausul adaptif ini karena berjalan di layar fisik tetap, bukan browser yang di-resize.
3. Tabel Traceability (§9) diperbarui dengan satu baris baru mengaitkan §8 ke `NFR-003` (nomor sementara, perlu dikonfirmasi ke `PRD.md` asli — lihat catatan di §9).
4. Penomoran seluruh section digeser: Traceability §8→§9, Changelog v1.0→v1.1 §9→§10, Changelog v1.1→v1.2 §10→§11.

**Item yang perlu konfirmasi user/PRD:**
- Nomor NFR resmi untuk persyaratan tampilan adaptif (sementara diberi label `NFR-003`).
- Apakah dukungan tablet (768–1024px) juga menjadi persyaratan eksplisit, atau cukup dua target 1366px & 360–414px seperti yang diminta.
