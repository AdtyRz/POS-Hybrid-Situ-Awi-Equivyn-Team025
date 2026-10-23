/*
 * ESP32 IoT Client untuk Saung Situ Awi
 * Compatible: Local Development & Production
 * 
 * Hardware: ESP32 DevKit V1
 * Libraries: WiFi, HTTPClient, ArduinoJson, Preferences
 * 
 * Pin Mapping:
 * - TOUCH_PIN:  GPIO 4  (sensor sentuh: tap = ganti layar, tahan = panggil pelayan)
 * - LED_PIN:    GPIO 2  (built-in LED)
 * - BUZZER_PIN: GPIO 5  (optional buzzer)
 * - SDA:        GPIO 21 (LCD I2C)
 * - SCL:        GPIO 22 (LCD I2C)
 *
 * Fitur sensor sentuh:
 * - Tap singkat -> ganti tampilan LCD bergiliran:
 *                  Status Pesanan -> Total Bayar -> Kode Verif Saung
 * - Tahan ~1.5s -> panggil pelayan
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Preferences.h>
#include <Wire.h>

// Sensor touch dipakai untuk ganti layar & panggil pelayan.
// Comment baris #define USE_LCD kalau tidak pakai LCD I2C.
#define USE_LCD
#ifdef USE_LCD
#include <LiquidCrystal_I2C.h>
#endif

// ============================================================
// KONFIGURASI - GANTI SESUAI ENVIRONMENT
// ============================================================

// --- PILIH MODE ---
// #define MODE_LOCAL      // Development: http://192.168.x.x:8000
#define MODE_PRODUCTION   // Production: https://api.situawi.com

// --- WIFI (akan disimpan ke NVS setelah setup pertama) ---
const char* DEFAULT_SSID = "WIFI_KANTIN";
const char* DEFAULT_PASS = "PASSWORD_WIFI";

// --- SERVER URL ---
#ifdef MODE_LOCAL
  const char* BASE_URL = "http://192.168.1.100:8000";  // Ganti IP laptop/server
#else
  const char* BASE_URL = "https://api.situawi.com";    // Domain production
#endif

const char* ENDPOINT_PANGGIL = "/api/iot/panggil-pelayan";
const char* ENDPOINT_MEJA    = "/api/iot/meja/";  // + kode_meja / id_device

// --- HARDWARE PINS (JANGAN GUNAKAN GPIO 0,1,3,6-11) ---
const int TOUCH_PIN    = 4;   // sensor sentuh (GPIO 4 - aman)
const int LED_PIN      = 2;   // Built-in LED
const int BUZZER_PIN   = 5;   // Buzzer aktif LOW
const int SDA_PIN      = 21;  // LCD I2C SDA
const int SCL_PIN      = 22;  // LCD I2C SCL

// Polaritas sensor sentuh.
// 1 = aktif LOW (butuh INPUT_PULLUP, seperti tombol biasa)
// 0 = aktif HIGH (umum pada modul TTP223) -> ganti ke 0 kalau sensor tidak terdeteksi
#define TOUCH_ACTIVE_LOW 1

// --- DEVICE IDENTIFIER ---
const char* KODE_MEJA_DEFAULT = "LB-01";  // Ganti per device

// ============================================================
// GLOBAL OBJECTS
// ============================================================
Preferences prefs;
String ssid, password, kodeMeja;
bool useHttps = false;

// LCD (16x2 I2C)
#ifdef USE_LCD
LiquidCrystal_I2C lcd(0x27, 16, 2);
#endif

// --- Sensor sentuh: bedakan tap singkat vs tahan ---
unsigned long pressStart = 0;
bool touchActive = false;
bool longPressDone = false;
const unsigned long LONG_PRESS_MS = 1500;  // tahan >= ini -> panggil pelayan
const unsigned long TAP_MIN_MS = 40;       // abaikan noise kontak

// --- Layar LCD yang bergiliran ---
const int SCREEN_STATUS = 0;
const int SCREEN_TOTAL  = 1;
const int SCREEN_KODE   = 2;
const int SCREEN_COUNT  = 3;
int currentScreen = SCREEN_STATUS;

// --- Data meja (diisi dari API) ---
String kodeSesi = "----";
String statusRingkas = "Belum ada pesan";
float totalBayar = 0;
int jumlahPesanan = 0;

// Timing
unsigned long lastStatusCheck = 0;
const unsigned long STATUS_INTERVAL_MS = 30000;

// WiFi reconnect
unsigned long lastReconnect = 0;
const unsigned long RECONNECT_INTERVAL_MS = 10000;
int wifiFailCount = 0;
const int MAX_WIFI_FAILS = 5;

// ============================================================
// SETUP
// ============================================================
void setup() {
  Serial.begin(115200);
  delay(100);
  Serial.println("\n=== ESP32 IoT Saung Situ Awi ===");
  Serial.printf("Mode: %s\n", 
    #ifdef MODE_LOCAL "LOCAL" #else "PRODUCTION" #endif
  );

  // Pin setup
#if TOUCH_ACTIVE_LOW
  pinMode(TOUCH_PIN, INPUT_PULLUP);
#else
  pinMode(TOUCH_PIN, INPUT);
#endif
  pinMode(LED_PIN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(LED_PIN, LOW);
  digitalWrite(BUZZER_PIN, HIGH);  // Buzzer aktif LOW

  // LCD init (opsional)
#ifdef USE_LCD
  Wire.begin(SDA_PIN, SCL_PIN);
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0, 0);
  lcd.print("Saung Situ Awi");
  lcd.setCursor(0, 1);
  lcd.print("Menyiapkan...");
#endif

  // Load config dari NVS
  loadConfig();
  
  // Connect WiFi
  connectWiFi();
  
  // Start watchdog task
  xTaskCreatePinnedToCore(watchdogTask, "watchdog", 4096, NULL, 1, NULL, 0);
  
  // Tampilkan layar awal + ambil data meja pertama kali
  renderLayar();
  fetchMejaStatus();

  Serial.println("Ready!");
  blinkLED(3, 100);
}

// ============================================================
// MAIN LOOP
// ============================================================
void loop() {
  // 1. Sensor sentuh: tap = ganti layar, tahan = panggil pelayan
  handleTouch();
  
  // 2. Periodic status check untuk LCD
  if (millis() - lastStatusCheck > STATUS_INTERVAL_MS) {
    lastStatusCheck = millis();
    fetchMejaStatus();
  }
  
  // 3. WiFi watchdog
  if (WiFi.status() != WL_CONNECTED) {
    handleWiFiReconnect();
  }
  
  // Feed watchdog
  esp_task_wdt_reset();
  
  // Small yield untuk stability
  vTaskDelay(pdMS_TO_TICKS(10));
}

// ============================================================
// TOUCH HANDLER (tap = ganti layar, tahan = panggil pelayan)
// ============================================================
void handleTouch() {
#if TOUCH_ACTIVE_LOW
  bool raw = (digitalRead(TOUCH_PIN) == LOW);   // true = sedang disentuh
#else
  bool raw = (digitalRead(TOUCH_PIN) == HIGH);  // true = sedang disentuh
#endif
  unsigned long now = millis();

  if (raw && !touchActive) {
    // Baru mulai disentuh
    touchActive = true;
    longPressDone = false;
    pressStart = now;
  } else if (raw && touchActive) {
    // Sedang ditahan -> cek long press
    if (!longPressDone && (now - pressStart >= LONG_PRESS_MS)) {
      longPressDone = true;
      Serial.println("[TOUCH] Tahan -> panggilPelayan()");
      panggilPelayan();
    }
  } else if (!raw && touchActive) {
    // Baru dilepas
    unsigned long dur = now - pressStart;
    touchActive = false;
    if (!longPressDone && dur >= TAP_MIN_MS) {
      Serial.printf("[TOUCH] Tap %lums -> ganti layar\n", dur);
      gantiLayar();
    }
  }
}

// Pindah layar: Status Pesanan -> Total Bayar -> Kode Verif Saung -> (ulang)
void gantiLayar() {
  currentScreen = (currentScreen + 1) % SCREEN_COUNT;
  tone(BUZZER_PIN, 1500, 60);  // feedback singkat
  renderLayar();
}

void panggilPelayan() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[ERROR] WiFi disconnected");
    errorFeedback(1);
    return;
  }

  WiFiClientSecure client;
  client.setInsecure();  // DEV: skip cert verify. PROD: client.setCACert(rootCA)
  
  HTTPClient http;
  http.setTimeout(8000);
  http.setReuse(true);
  http.setFollowRedirects(HTTPC_FORCE_FOLLOW_REDIRECTS);
  
  String url = String(BASE_URL) + ENDPOINT_PANGGIL;
  if (!http.begin(client, url)) {
    Serial.println("[HTTP] Begin failed");
    errorFeedback(1);
    return;
  }
  
  http.addHeader("Content-Type", "application/json");
  http.addHeader("User-Agent", "ESP32-IoT/1.0");
  http.addHeader("Accept", "application/json");
  
  // Build JSON dengan ArduinoJson (hindah String concat)
  JsonDocument doc;
  doc["kode_meja"] = kodeMeja;
  
  String payload;
  serializeJson(doc, payload);
  
  Serial.printf("[HTTP] POST %s\n", url.c_str());
  Serial.printf("[HTTP] Payload: %s\n", payload.c_str());
  
  int httpCode = http.POST(payload);
  
  if (httpCode > 0) {
    String response = http.getString();
    Serial.printf("[HTTP] %d: %s\n", httpCode, response.c_str());
    
    // Parse response
    JsonDocument respDoc;
    DeserializationError err = deserializeJson(respDoc, response);
    
    if (!err && respDoc["success"].as<bool>()) {
      Serial.println("[OK] Panggilan terkirim");
      successFeedback();
    } else {
      String msg = respDoc["message"] | "Unknown error";
      Serial.printf("[ERROR] %s\n", msg.c_str());
      errorFeedback(httpCode == 404 ? 2 : 1);
    }
  } else {
    Serial.printf("[HTTP] Error: %s\n", http.errorToString(httpCode).c_str());
    errorFeedback(1);
  }
  http.end();
}

// Ambil data meja ini: kode sesi, status pesanan, total bayar.
void fetchMejaStatus() {
  if (WiFi.status() != WL_CONNECTED) return;
  
  WiFiClientSecure client;
  client.setInsecure();
  
  HTTPClient http;
  http.setTimeout(5000);
  
  String url = String(BASE_URL) + ENDPOINT_MEJA + kodeMeja;
  if (!http.begin(client, url)) return;
  
  http.addHeader("Accept", "application/json");
  
  int httpCode = http.GET();
  if (httpCode == 200) {
    String response = http.getString();
    JsonDocument doc;
    DeserializationError err = deserializeJson(doc, response);
    
    if (!err && doc["success"].as<bool>()) {
      JsonObject data = doc["data"].as<JsonObject>();

      kodeSesi = String((const char*)(data["kode_sesi"] | "----"));
      totalBayar = data["total_bayar"] | 0.0f;
      jumlahPesanan = data["jumlah_pesanan"] | 0;

      // Bangun ringkasan status untuk layar "Status Pesanan"
      JsonArray pesanan = data["pesanan"].as<JsonArray>();
      if (pesanan.size() == 0) {
        statusRingkas = "Belum ada pesan";
      } else {
        JsonObject terakhir = pesanan[0].as<JsonObject>();  // API urut terbaru dulu
        String label = labelStatus(String((const char*)(terakhir["status_pesanan"] | "-")));
        statusRingkas = (pesanan.size() > 1)
          ? (label + " (" + String(pesanan.size()) + ")")
          : label;
      }

      renderLayar();
      Serial.printf("[MEJA] %s | kode=%s | pesanan=%d | total=%.0f\n",
        kodeMeja.c_str(), kodeSesi.c_str(), jumlahPesanan, totalBayar);
    }
  } else if (httpCode > 0) {
    Serial.printf("[HTTP] GET meja gagal: %d\n", httpCode);
  }
  http.end();
}

// ============================================================
// WIFI MANAGEMENT
// ============================================================
void loadConfig() {
  prefs.begin("iot-config", false);
  
  ssid = prefs.getString("ssid", DEFAULT_SSID);
  password = prefs.getString("pass", DEFAULT_PASS);
  kodeMeja = prefs.getString("kode_meja", KODE_MEJA_DEFAULT);
  useHttps = prefs.getBool("https", false);
  
  Serial.printf("Config loaded: SSID=%s, Meja=%s, HTTPS=%s\n", 
    ssid.c_str(), kodeMeja.c_str(), useHttps ? "true" : "false");
}

void saveConfig(String newSsid, String newPass, String newMeja, bool https) {
  prefs.putString("ssid", newSsid);
  prefs.putString("pass", newPass);
  prefs.putString("kode_meja", newMeja);
  prefs.putBool("https", https);
  ssid = newSsid; password = newPass; kodeMeja = newMeja; useHttps = https;
  Serial.println("Config saved to NVS");
}

void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.setSleep(false);
  WiFi.setTxPower(WIFI_POWER_19_5dBm);  // Max power
  
  // Set hostname untuk DHCP
  String hostname = "esp32-" + kodeMeja;
  hostname.replace("-", "");
  WiFi.setHostname(hostname.c_str());
  
  WiFi.begin(ssid.c_str(), password.c_str());
  
  Serial.printf("Connecting to %s", ssid.c_str());
  unsigned long start = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - start < 20000) {
    delay(200);
    Serial.print(".");
  }
  Serial.println();
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.printf("[WiFi] Connected! IP: %s, RSSI: %d dBm\n", 
      WiFi.localIP().toString().c_str(), WiFi.RSSI());
    wifiFailCount = 0;
    blinkLED(3, 100);
  } else {
    Serial.println("[WiFi] Failed!");
    wifiFailCount++;
    if (wifiFailCount >= MAX_WIFI_FAILS) {
      Serial.println("[WiFi] Max fails reached -> restart");
      ESP.restart();
    }
  }
}

void handleWiFiReconnect() {
  if (millis() - lastReconnect > RECONNECT_INTERVAL_MS) {
    lastReconnect = millis();
    Serial.println("[WiFi] Reconnecting...");
    WiFi.disconnect(true);
    delay(500);
    connectWiFi();
  }
}

// ============================================================
// FEEDBACK
// ============================================================
void successFeedback() {
  digitalWrite(LED_PIN, HIGH);
  tone(BUZZER_PIN, 1000, 200);
  delay(200);
  digitalWrite(LED_PIN, LOW);
}

void errorFeedback(int beeps) {
  for (int i = 0; i < beeps; i++) {
    tone(BUZZER_PIN, 500, 150);
    delay(200);
  }
}

void blinkLED(int times, int delayMs) {
  for (int i = 0; i < times; i++) {
    digitalWrite(LED_PIN, HIGH);
    delay(delayMs);
    digitalWrite(LED_PIN, LOW);
    delay(delayMs);
  }
}

// ============================================================
// LCD: 3 LAYAR BERGILIRAN
// ============================================================
String labelStatus(String status) {
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

// Format angka jadi "Rp 55.000"
String formatRupiah(float nominal) {
  long v = (long)(nominal + 0.5);
  String s = String(v);
  String out = "";
  int n = s.length();
  for (int i = 0; i < n; i++) {
    if (i > 0 && (n - i) % 3 == 0) out += ".";
    out += s[i];
  }
  return "Rp " + out;
}

void renderLayar() {
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

  Serial.printf("[LCD] Layar %d/%d: %s | %s\n",
    currentScreen + 1, SCREEN_COUNT, baris1.c_str(), baris2.c_str());

#ifdef USE_LCD
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(baris1.substring(0, 16));
  lcd.setCursor(0, 1);
  lcd.print(baris2.substring(0, 16));
#endif
}

// ============================================================
// WATCHDOG TASK (FreeRTOS)
// ============================================================
void watchdogTask(void* pvParameters) {
  for (;;) {
    esp_task_wdt_reset();
    vTaskDelay(pdMS_TO_TICKS(5000));
  }
}
