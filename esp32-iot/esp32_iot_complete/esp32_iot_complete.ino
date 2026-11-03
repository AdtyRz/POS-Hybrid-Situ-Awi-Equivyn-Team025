/*
 * ESP32 IoT Client - Saung Situ Awi (versi ringan)
 * Board  : ESP32 DOIT DevKit V1 (30 pin)
 * LCD    : 16x2 I2C (PCF8574, alamat 0x27 atau 0x3F)
 * Touch  : TTP223 (output aktif HIGH)
 * Buzzer : active buzzer (bisa diganti passive)
 *
 * Tap singkat  : ganti layar Status -> Total -> Kode Sesi
 * Tahan 1.5 s  : panggil pelayan
 *
 * Catatan versi ringan:
 * - Hanya memanggil /api/iot/meja/{KODE_MEJA}. Respons endpoint itu sudah
 *   membawa konfigurasi LCD/buzzer, jadi tidak perlu mengunduh semua meja.
 * - JSON dibaca langsung dari stream HTTP, tanpa menyalin seluruh respons
 *   ke String.
 * - HTTPS tidak dikompilasi selama PAKAI_HTTPS = 0 (lebih kecil & ringan).
 * - Buzzer dan pesan LCD tidak memblokir loop, jadi touch tetap responsif.
 *
 * Agar bisa diakses ESP32, jalankan Laravel dengan host 0.0.0.0:
 *   php artisan serve --host=0.0.0.0 --port=8000
 * Jangan memakai 127.0.0.1 pada SERVER_URL karena itu loopback laptop.
 * Jika SERVER_URL memakai https://, ubah PAKAI_HTTPS menjadi 1.
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// ======================= KONFIGURASI =======================
// WiFi, SERVER_URL, token, dan KODE_MEJA disimpan di secrets.h agar
// kredensial tidak ikut ter-commit. Salin secrets.example.h bila belum ada.
#if __has_include("secrets.h")
#include "secrets.h"
#else
#include "secrets.example.h"
#endif

// 0 = HTTP lokal (paling ringan). 1 = HTTPS untuk produksi.
#define PAKAI_HTTPS 0

#if PAKAI_HTTPS
#include <WiFiClientSecure.h>
#endif

// ======================= PIN =======================
const int PIN_SDA = 21;
const int PIN_SCL = 22;
const int PIN_TOUCH = 27;
const int PIN_BUZZER = 25;
const int PIN_LED = 2;

// TTP223 umumnya aktif HIGH. Ubah ke 0 bila sensor Anda aktif LOW.
#define TOUCH_ACTIVE_HIGH 1

// 1 = active buzzer, 0 = passive buzzer.
#define BUZZER_ACTIVE 1
// Ubah ke LOW bila buzzer justru berbunyi saat sinyal LOW.
#define BUZZER_ON_LEVEL HIGH
#define BUZZER_FREQ 2000

// Alamat I2C LCD: 0x27 atau 0x3F.
const uint8_t LCD_ADDR = 0x27;
LiquidCrystal_I2C lcd(LCD_ADDR, 16, 2);

// ======================= TIMING =======================
const unsigned long STATUS_INTERVAL_MS = 10000;
const unsigned long RETRY_INTERVAL_MS = 30000;
const unsigned long WIFI_RETRY_MS = 15000;
const unsigned long HTTP_TIMEOUT_MS = 3000;
const unsigned long HTTP_CONNECT_TIMEOUT_MS = 1500;
const unsigned long LONG_PRESS_MS = 1500;
const unsigned long TAP_MIN_MS = 50;

// ======================= STATE =======================
String kodeSesi = "----";
String statusRingkas = "Belum ada pesan";
String lcdMessage = "Panggil Pelayan";
int buzzerDuration = 3;
float totalBayar = 0;
int jumlahPesanan = 0;

const int SCREEN_STATUS = 0;
const int SCREEN_TOTAL = 1;
const int SCREEN_KODE = 2;
const int SCREEN_COUNT = 3;
int currentScreen = SCREEN_STATUS;

bool touchActive = false;
bool longPressDone = false;
unsigned long pressStart = 0;
unsigned long lastStatusCheck = 0;
unsigned long lastWifiAttempt = 0;
unsigned long intervalPolling = STATUS_INTERVAL_MS;

// Umpan balik non-blocking.
bool buzzerAktif = false;
unsigned long buzzerSampai = 0;
bool ledAktif = false;
unsigned long ledSampai = 0;

// Pesan sementara di LCD (tidak menimpa layar utama selamanya).
bool pesanSementara = false;
String pesanBaris1 = "";
String pesanBaris2 = "";
unsigned long pesanSampai = 0;

// Isi LCD terakhir agar tidak flicker saat polling.
String lcdBaris1 = "";
String lcdBaris2 = "";

// ======================= HTTP CLIENT =======================
WiFiClient plainClient;
#if PAKAI_HTTPS
WiFiClientSecure secureClient;
#endif

// ======================= PROTOTYPES =======================
void connectWiFi();
void jagaWiFi();
bool fetchMejaStatus();
void panggilPelayan();
void handleTouch();
bool readTouch();
void renderLayar();
void tampilkanPesan(const String& line1, const String& line2, unsigned long durationMs);
void beep(uint16_t frequency, uint16_t durationMs);
void beepMulai(uint16_t frequency, unsigned long durationMs);
void updateBuzzer();
void buzzerOff();
void successFeedback();
void errorFeedback();
bool siapkanHttp(HTTPClient& http, const String& url);
void tulisLcd(const String& line1, const String& line2, bool paksa = false);
String rapikanBaris(const String& teks);
String labelStatus(const String& status);
String formatRupiah(float nominal);

// ======================= SETUP =======================
void setup() {
  Serial.begin(115200);
  delay(200);
  Serial.println();
  Serial.println("=== ESP32 IoT Saung Situ Awi ===");

  pinMode(PIN_LED, OUTPUT);
  digitalWrite(PIN_LED, LOW);

  pinMode(PIN_BUZZER, OUTPUT);
  buzzerOff();

  pinMode(PIN_TOUCH, TOUCH_ACTIVE_HIGH ? INPUT : INPUT_PULLUP);

  Wire.begin(PIN_SDA, PIN_SCL);
  lcd.begin(16, 2);
  lcd.backlight();
  lcd.clear();
  tulisLcd("Saung Situ Awi", "Menyiapkan...", true);
  delay(500);

  connectWiFi();

  if (WiFi.status() == WL_CONNECTED) {
    fetchMejaStatus();
    lastStatusCheck = millis();
    intervalPolling = STATUS_INTERVAL_MS;
  }

  Serial.println("Ready. Tap = ganti layar, tahan 1.5s = panggil pelayan.");
  beep(BUZZER_FREQ, 80);
}

// ======================= LOOP =======================
void loop() {
  updateBuzzer();

  if (pesanSementara && (long)(millis() - pesanSampai) >= 0) {
    pesanSementara = false;
    renderLayar();
  }

  handleTouch();
  jagaWiFi();

  if (WiFi.status() == WL_CONNECTED && millis() - lastStatusCheck >= intervalPolling) {
    lastStatusCheck = millis();
    intervalPolling = fetchMejaStatus() ? STATUS_INTERVAL_MS : RETRY_INTERVAL_MS;
  }

  delay(5);
}

// ======================= TOUCH =======================
bool readTouch() {
#if TOUCH_ACTIVE_HIGH
  return digitalRead(PIN_TOUCH) == HIGH;
#else
  return digitalRead(PIN_TOUCH) == LOW;
#endif
}

void handleTouch() {
  bool touched = readTouch();
  unsigned long now = millis();

  if (touched && !touchActive) {
    touchActive = true;
    longPressDone = false;
    pressStart = now;
  } else if (touched && touchActive) {
    if (!longPressDone && now - pressStart >= LONG_PRESS_MS) {
      longPressDone = true;
      Serial.println("[TOUCH] Tahan -> panggil pelayan");
      panggilPelayan();
    }
  } else if (!touched && touchActive) {
    unsigned long duration = now - pressStart;
    touchActive = false;

    if (!longPressDone && duration >= TAP_MIN_MS) {
      currentScreen = (currentScreen + 1) % SCREEN_COUNT;
      Serial.printf("[TOUCH] Tap %lu ms -> layar %d\n", duration, currentScreen + 1);
      beep(BUZZER_FREQ, 40);
      renderLayar();
    }
  }
}

// ======================= WIFI =======================
void connectWiFi() {
  if (WiFi.status() == WL_CONNECTED) {
    return;
  }

  Serial.printf("[WiFi] Menghubungkan ke %s", WIFI_SSID);
  tulisLcd("Menyambung WiFi", "");

  WiFi.mode(WIFI_STA);
  WiFi.setSleep(false);
  WiFi.setAutoReconnect(true);
  WiFi.persistent(false);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  unsigned long start = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - start < 15000) {
    delay(250);
    Serial.print(".");
  }
  Serial.println();

  lastWifiAttempt = millis();

  if (WiFi.status() == WL_CONNECTED) {
    Serial.printf("[WiFi] OK, IP=%s, RSSI=%d dBm\n",
                  WiFi.localIP().toString().c_str(), WiFi.RSSI());
    tulisLcd("WiFi OK", WiFi.localIP().toString());
    delay(800);
  } else {
    Serial.println("[WiFi] Gagal. Cek SSID/password dan pastikan WiFi 2.4 GHz.");
    tulisLcd("WiFi gagal", "Cek SSID/Pass");
  }
}

// Reconnect tanpa memblokir loop (beda dengan connectWiFi saat boot).
void jagaWiFi() {
  if (WiFi.status() == WL_CONNECTED) {
    return;
  }

  unsigned long now = millis();
  if (now - lastWifiAttempt < WIFI_RETRY_MS) {
    return;
  }

  lastWifiAttempt = now;
  Serial.println("[WiFi] Menyambung ulang...");
  tulisLcd("WiFi terputus", "Menyambung...");
  WiFi.begin(WIFI_SSID, WIFI_PASS);
}

// ======================= HTTP =======================
bool siapkanHttp(HTTPClient& http, const String& url) {
  bool started = false;

#if PAKAI_HTTPS
  if (url.startsWith("https://")) {
    secureClient.setInsecure();
    started = http.begin(secureClient, url);
  } else {
    started = http.begin(plainClient, url);
  }
#else
  started = http.begin(plainClient, url);
#endif

  if (! started) {
    return false;
  }

  http.setTimeout(HTTP_TIMEOUT_MS);
  http.setConnectTimeout(HTTP_CONNECT_TIMEOUT_MS);
  http.useHTTP10(true);
  http.setReuse(false);

  http.addHeader("Accept", "application/json");
  http.addHeader("User-Agent", "ESP32-IoT/2.0");
  http.addHeader("X-IoT-Token", IOT_API_TOKEN);

  return true;
}

// ======================= DATA MEJA =======================
bool fetchMejaStatus() {
  if (WiFi.status() != WL_CONNECTED) {
    return false;
  }

  HTTPClient http;
  String url = String(SERVER_URL) + "/api/iot/meja/" + KODE_MEJA;

  if (! siapkanHttp(http, url)) {
    Serial.println("[HTTP] begin gagal");
    return false;
  }

  int httpCode = http.GET();
  if (httpCode != 200) {
    Serial.printf("[HTTP] GET meja gagal: %d\n", httpCode);
    http.end();
    return false;
  }

  // Baca langsung dari stream: tidak perlu String response besar.
  JsonDocument doc;
  DeserializationError error = deserializeJson(doc, http.getStream());
  http.end();

  if (error) {
    Serial.printf("[JSON] Gagal parse: %s\n", error.c_str());
    return false;
  }

  if (! doc["success"].as<bool>()) {
    Serial.println("[API] success=false");
    return false;
  }

  JsonObject data = doc["data"].as<JsonObject>();
  JsonObject konfigurasi = doc["konfigurasi"].as<JsonObject>();

  const char* sesi = data["kode_sesi"] | "----";
  kodeSesi = String(sesi);
  if (kodeSesi.length() == 0) {
    kodeSesi = "----";
  }

  const char* pesan = konfigurasi["lcd_message"] | "Panggil Pelayan";
  lcdMessage = String(pesan);
  if (lcdMessage.length() == 0) {
    lcdMessage = "Panggil Pelayan";
  }
  if (lcdMessage.length() > 16) {
    lcdMessage = lcdMessage.substring(0, 16);
  }

  buzzerDuration = konfigurasi["buzzer_duration"] | 3;
  if (buzzerDuration < 1) {
    buzzerDuration = 1;
  }
  if (buzzerDuration > 10) {
    buzzerDuration = 10;
  }

  totalBayar = data["total_bayar"].as<float>();
  jumlahPesanan = data["jumlah_pesanan"].as<int>();

  if (jumlahPesanan <= 0) {
    statusRingkas = "Belum ada pesan";
  } else {
    const char* status = data["pesanan"][0]["status_pesanan"] | "-";
    String label = labelStatus(String(status));
    if (jumlahPesanan > 1) {
      statusRingkas = label + " (" + String(jumlahPesanan) + ")";
    } else {
      statusRingkas = label;
    }
  }

  renderLayar();
  Serial.printf("[MEJA] %s | sesi=%s | pesanan=%d | total=%.0f\n",
                KODE_MEJA, kodeSesi.c_str(), jumlahPesanan, totalBayar);
  return true;
}

void panggilPelayan() {
  if (WiFi.status() != WL_CONNECTED) {
    errorFeedback();
    tampilkanPesan("WiFi terputus", "Cek jaringan", 1500);
    return;
  }

  // Biasanya kode sudah segar dari polling. Kalau belum ada, ambil sekali.
  if (kodeSesi == "----" || kodeSesi.length() == 0) {
    fetchMejaStatus();
  }

  if (kodeSesi == "----" || kodeSesi.length() == 0) {
    errorFeedback();
    tampilkanPesan("Kode sesi", "Belum tersedia", 1500);
    return;
  }

  char body[96];
  snprintf(body, sizeof(body),
           "{\"kode_meja\":\"%s\",\"kode_sesi\":\"%s\"}",
           KODE_MEJA, kodeSesi.c_str());

  HTTPClient http;
  String url = String(SERVER_URL) + "/api/iot/panggil-pelayan";

  if (! siapkanHttp(http, url)) {
    errorFeedback();
    tampilkanPesan("Panggilan gagal", "Cek jaringan", 2000);
    return;
  }

  http.addHeader("Content-Type", "application/json");
  int httpCode = http.POST((uint8_t*)body, strlen(body));
  http.end();

  Serial.printf("[HTTP] POST panggil: %d\n", httpCode);

  if (httpCode == 200) {
    successFeedback();
    tampilkanPesan(lcdMessage, "Terkirim", 1500);
  } else if (httpCode == 403) {
    errorFeedback();
    tampilkanPesan("Sesi berubah", "Coba lagi", 1500);
    // Paksa polling berikutnya mengambil kode sesi terbaru.
    lastStatusCheck = 0;
  } else if (httpCode == 429) {
    errorFeedback();
    tampilkanPesan("Masih jeda", "Coba lagi", 1500);
  } else {
    errorFeedback();
    tampilkanPesan("Panggilan gagal", "Cek token/WiFi", 2000);
  }
}

// ======================= TAMPILAN LCD =======================
String labelStatus(const String& status) {
  if (status == "menunggu") return "Menunggu";
  if (status == "menunggu_verifikasi") return "Verifikasi";
  if (status == "menunggu_pembayaran") return "B. Bayar";
  if (status == "diproses") return "Diproses";
  if (status == "siap") return "Siap";
  if (status == "diantar") return "Diantar";
  if (status == "selesai") return "Selesai";
  if (status == "dibatalkan") return "Dibatalkan";
  return status;
}

String formatRupiah(float nominal) {
  long value = (long)(nominal + 0.5);
  String number = String(value);
  String result = "";
  int length = number.length();

  for (int i = 0; i < length; i++) {
    if (i > 0 && (length - i) % 3 == 0) {
      result += '.';
    }
    result += number[i];
  }

  return "Rp " + result;
}

void renderLayar() {
  if (pesanSementara) {
    return;
  }

  String baris1;
  String baris2;

  if (currentScreen == SCREEN_STATUS) {
    baris1 = "Status Pesanan";
    baris2 = statusRingkas;
  } else if (currentScreen == SCREEN_TOTAL) {
    baris1 = "Total Bayar";
    baris2 = formatRupiah(totalBayar);
  } else {
    baris1 = "Kode Verif Saung";
    baris2 = kodeSesi;
  }

  tulisLcd(baris1, baris2);
  Serial.printf("[LCD] %s | %s\n", baris1.c_str(), baris2.c_str());
}

String rapikanBaris(const String& teks) {
  String hasil = teks.substring(0, 16);
  while (hasil.length() < 16) {
    hasil += ' ';
  }
  return hasil;
}

// Tulis hanya baris yang berubah supaya LCD tidak flicker.
void tulisLcd(const String& line1, const String& line2, bool paksa) {
  String baris1 = rapikanBaris(line1);
  String baris2 = rapikanBaris(line2);

  if (paksa || baris1 != lcdBaris1) {
    lcd.setCursor(0, 0);
    lcd.print(baris1);
    lcdBaris1 = baris1;
  }

  if (paksa || baris2 != lcdBaris2) {
    lcd.setCursor(0, 1);
    lcd.print(baris2);
    lcdBaris2 = baris2;
  }
}

void tampilkanPesan(const String& line1, const String& line2, unsigned long durationMs) {
  pesanBaris1 = line1;
  pesanBaris2 = line2;
  pesanSampai = millis() + durationMs;
  pesanSementara = true;
  tulisLcd(pesanBaris1, pesanBaris2, true);
}

// ======================= BUZZER & LED =======================
void beep(uint16_t frequency, uint16_t durationMs) {
#if BUZZER_ACTIVE
  (void)frequency;
  digitalWrite(PIN_BUZZER, BUZZER_ON_LEVEL);
  delay(durationMs);
  digitalWrite(PIN_BUZZER, !BUZZER_ON_LEVEL);
#else
  tone(PIN_BUZZER, frequency, durationMs);
  delay(durationMs);
  noTone(PIN_BUZZER);
#endif
}

// Nyalakan buzzer tanpa memblokir loop; dimatikan oleh updateBuzzer().
void beepMulai(uint16_t frequency, unsigned long durationMs) {
#if BUZZER_ACTIVE
  (void)frequency;
  digitalWrite(PIN_BUZZER, BUZZER_ON_LEVEL);
#else
  tone(PIN_BUZZER, frequency);
#endif
  buzzerAktif = true;
  buzzerSampai = millis() + durationMs;
}

void updateBuzzer() {
  unsigned long now = millis();

  if (buzzerAktif && (long)(now - buzzerSampai) >= 0) {
    buzzerOff();
    buzzerAktif = false;
  }

  if (ledAktif && (long)(now - ledSampai) >= 0) {
    digitalWrite(PIN_LED, LOW);
    ledAktif = false;
  }
}

void buzzerOff() {
#if BUZZER_ACTIVE
  digitalWrite(PIN_BUZZER, !BUZZER_ON_LEVEL);
#else
  noTone(PIN_BUZZER);
#endif
}

void successFeedback() {
  unsigned long durasi = (unsigned long)buzzerDuration * 1000UL;
  digitalWrite(PIN_LED, HIGH);
  ledAktif = true;
  ledSampai = millis() + durasi;
  beepMulai(BUZZER_FREQ, durasi);
}

void errorFeedback() {
  for (int i = 0; i < 3; i++) {
    beep(500, 120);
    delay(80);
  }
}
