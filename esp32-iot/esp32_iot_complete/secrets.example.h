// Contoh konfigurasi ESP32.
// Salin file ini menjadi "secrets.h" lalu isi nilai yang sebenarnya.
// File secrets.h tidak ikut ter-commit karena sudah masuk .gitignore.
#pragma once

const char* WIFI_SSID = "NAMA_WIFI_2_4GHZ";
const char* WIFI_PASS = "PASSWORD_WIFI";

// IP laptop/server di jaringan yang sama dengan ESP32, tanpa slash akhir.
// Contoh lokal: http://192.168.1.100:8000
// Produksi    : https://domain-anda.com
const char* SERVER_URL = "http://192.168.1.100:8000";

// Samakan dengan IOT_API_TOKEN di .env Laravel.
const char* IOT_API_TOKEN = "GANTI_DENGAN_IOT_API_TOKEN";

// Boleh kode meja (mis. "LB-01") ATAU ID Device ESP32 (mis. "ESP32-LB01").
// Jika memakai ID Device, mengganti kode meja di admin tidak perlu upload ulang.
const char* KODE_MEJA = "LB-01";
