/*
 * ESP32 IoT Client - Saung Situ Awi
 * Board  : ESP32 DOIT DevKit V1 (30 pin)
 * LCD    : 16x2 I2C (PCF8574, alamat 0x27 atau 0x3F)
 * Touch  : TTP223 (output aktif HIGH)
 * Buzzer : active buzzer (bisa diganti passive)
 *
 * Tap singkat  : ganti layar Status -> Total -> Kode Sesi
 * Tahan 1.5 s  : panggil pelayan
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// ======================= KONFIGURASI =======================
const char* WIFI_SSID = "WIFI_KANTIN";
const char* WIFI_PASS = "PASSWORD_WIFI";

// Local     : http://192.168.1.100:8000  (IP laptop/server, tanpa slash akhir)
// Production: https://domain-anda.com
const char* SERVER_URL = "http://192.168.1.100:8000";

// Samakan dengan IOT_API_TOKEN di .env Laravel.
const char* IOT_API_TOKEN = "GANTI_DENGAN_IOT_API_TOKEN";
const char* KODE_MEJA = "LB-01";

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
const unsigned long CONFIG_INTERVAL_MS = 60000;
const unsigned long WIFI_RETRY_MS = 10000;
const unsigned long LONG_PRESS_MS = 1500;
const unsigned long TAP_MIN_MS = 50;

// ======================= STATE =======================
String kodeSesi = "----";
String statusRingkas = "Belum ada pesan";
String lcdMessage = "Panggil Pelayan";
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
unsigned long lastConfigCheck = 0;
unsigned long lastWifiAttempt = 0;

// ======================= PROTOTYPES =======================
void connectWiFi();
void fetchKonfigurasi();
bool fetchMejaStatus();
void panggilPelayan();
void handleTouch();
bool readTouch();
void renderLayar();
void showMessage(const String& line1, const String& line2, unsigned long durationMs);
void beep(uint16_t frequency, uint16_t durationMs);
void buzzerOff();
void successFeedback();
void errorFeedback();
int httpRequest(const String& method, const String& url, const String& body, String& response);
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
  lcd.setCursor(0, 0);
  lcd.print("Saung Situ Awi");
  lcd.setCursor(0, 1);
  lcd.print("Menyiapkan...");
  delay(500);

  connectWiFi();

  if (WiFi.status() == WL_CONNECTED) {
    fetchKonfigurasi();
    fetchMejaStatus();
    lastConfigCheck = millis();
    lastStatusCheck = millis();
  }

  Serial.println("Ready. Tap = ganti layar, tahan 1.5s = panggil pelayan.");
  beep(BUZZER_FREQ, 80);
}

// ======================= LOOP =======================
void loop() {
  handleTouch();

  if (WiFi.status() == WL_CONNECTED) {
    if (lastConfigCheck == 0 || millis() - lastConfigCheck >= CONFIG_INTERVAL_MS) {
      lastConfigCheck = millis();
      fetchKonfigurasi();
    }

    if (lastStatusCheck == 0 || millis() - lastStatusCheck >= STATUS_INTERVAL_MS) {
      lastStatusCheck = millis();
      fetchMejaStatus();
    }
  } else if (millis() - lastWifiAttempt >= WIFI_RETRY_MS) {
    connectWiFi();
  }

  delay(10);
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
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Menyambung WiFi");

  WiFi.mode(WIFI_STA);
  WiFi.setSleep(false);
  WiFi.disconnect(true);
  delay(100);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  unsigned long start = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - start < 20000) {
    delay(250);
    Serial.print(".");
  }
  Serial.println();

  if (WiFi.status() == WL_CONNECTED) {
    lastStatusCheck = 0;
    Serial.printf("[WiFi] OK, IP=%s, RSSI=%d dBm\n",
                  WiFi.localIP().toString().c_str(), WiFi.RSSI());
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi OK");
    lcd.setCursor(0, 1);
    lcd.print(WiFi.localIP().toString());
    delay(800);
  } else {
    Serial.println("[WiFi] Gagal. Cek SSID/password dan pastikan WiFi 2.4 GHz.");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi gagal");
    lcd.setCursor(0, 1);
    lcd.print("Cek SSID/Pass");
  }

  lastWifiAttempt = millis();
}

// ======================= HTTP =======================
int httpRequest(const String& method, const String& url, const String& body, String& response) {
  HTTPClient http;
  WiFiClient plainClient;
  WiFiClientSecure secureClient;

  http.setTimeout(8000);
  http.setReuse(false);

  bool started = false;
  if (url.startsWith("https://")) {
    secureClient.setInsecure();
    started = http.begin(secureClient, url);
  } else {
    started = http.begin(plainClient, url);
  }

  if (!started) {
    response = "HTTP begin failed";
    return -1;
  }

  http.addHeader("Accept", "application/json");
  http.addHeader("User-Agent", "ESP32-IoT/1.0");
  http.addHeader("X-IoT-Token", IOT_API_TOKEN);

  int httpCode;
  if (method == "POST") {
    http.addHeader("Content-Type", "application/json");
    httpCode = http.POST(body);
  } else {
    httpCode = http.GET();
  }

  if (httpCode > 0) {
    response = http.getString();
  } else {
    response = http.errorToString(httpCode);
  }

  http.end();
  return httpCode;
}

// ======================= KONFIGURASI & DATA MEJA =======================
void fetchKonfigurasi() {
  if (WiFi.status() != WL_CONNECTED) {
    return;
  }

  String url = String(SERVER_URL) + "/api/iot/status-meja";
  String response;
  int httpCode = httpRequest("GET", url, "", response);

  if (httpCode != 200) {
    Serial.printf("[HTTP] GET konfigurasi gagal: %d %s\n", httpCode, response.c_str());
    return;
  }

  JsonDocument doc;
  DeserializationError error = deserializeJson(doc, response);
  if (error) {
    Serial.printf("[JSON] Gagal parse konfigurasi: %s\n", error.c_str());
    return;
  }

  JsonObject konfigurasi = doc["konfigurasi"].as<JsonObject>();
  const char* pesan = konfigurasi["lcd_message"] | "Panggil Pelayan";
  lcdMessage = String(pesan);
  if (lcdMessage.length() == 0) {
    lcdMessage = "Panggil Pelayan";
  }
  if (lcdMessage.length() > 16) {
    lcdMessage = lcdMessage.substring(0, 16);
  }

  JsonArray data = doc["data"].as<JsonArray>();
  for (JsonVariant item : data) {
    JsonObject meja = item.as<JsonObject>();
    if (String(meja["kode_meja"] | "") == KODE_MEJA) {
      const char* sesi = meja["kode_sesi"] | "----";
      kodeSesi = String(sesi);
      if (kodeSesi.length() == 0) {
        kodeSesi = "----";
      }
      break;
    }
  }

  Serial.printf("[KONFIG] Pesan LCD: %s\n", lcdMessage.c_str());
}

bool fetchMejaStatus() {
  if (WiFi.status() != WL_CONNECTED) {
    return false;
  }

  String url = String(SERVER_URL) + "/api/iot/meja/" + KODE_MEJA;
  String response;
  int httpCode = httpRequest("GET", url, "", response);

  if (httpCode != 200) {
    Serial.printf("[HTTP] GET meja gagal: %d %s\n", httpCode, response.c_str());
    return false;
  }

  JsonDocument doc;
  DeserializationError error = deserializeJson(doc, response);
  if (error) {
    Serial.printf("[JSON] Gagal parse: %s\n", error.c_str());
    return false;
  }

  if (!doc["success"].as<bool>()) {
    Serial.println("[API] success=false");
    return false;
  }

  JsonObject data = doc["data"].as<JsonObject>();
  const char* sesi = data["kode_sesi"] | "----";
  kodeSesi = String(sesi);
  if (kodeSesi.length() == 0) {
    kodeSesi = "----";
  }

  totalBayar = data["total_bayar"].as<float>();
  jumlahPesanan = data["jumlah_pesanan"].as<int>();

  JsonArray pesanan = data["pesanan"].as<JsonArray>();
  if (pesanan.size() == 0) {
    statusRingkas = "Belum ada pesan";
  } else {
    const char* status = pesanan[0]["status_pesanan"] | "-";
    String label = labelStatus(String(status));
    if (pesanan.size() > 1) {
      statusRingkas = label + " (" + String(pesanan.size()) + ")";
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
    showMessage("WiFi terputus", "Cek jaringan", 1500);
    return;
  }

  if (!fetchMejaStatus()) {
    errorFeedback();
    showMessage("Gagal cek sesi", "Coba lagi", 1500);
    return;
  }

  if (kodeSesi == "----" || kodeSesi.length() == 0) {
    errorFeedback();
    showMessage("Kode sesi", "Belum tersedia", 1500);
    return;
  }

  String url = String(SERVER_URL) + "/api/iot/panggil-pelayan";
  JsonDocument doc;
  doc["kode_meja"] = KODE_MEJA;
  doc["kode_sesi"] = kodeSesi;

  String body;
  serializeJson(doc, body);

  String response;
  int httpCode = httpRequest("POST", url, body, response);
  Serial.printf("[HTTP] POST panggil: %d %s\n", httpCode, response.c_str());

  bool sukses = false;
  if (httpCode == 200) {
    JsonDocument resp;
    DeserializationError error = deserializeJson(resp, response);
    if (!error) {
      sukses = resp["success"].as<bool>();
    }
  }

  if (sukses) {
    successFeedback();
    showMessage(lcdMessage, "Terkirim", 1500);
  } else if (httpCode == 429) {
    errorFeedback();
    showMessage("Masih jeda", "Coba lagi", 1500);
  } else {
    errorFeedback();
    showMessage("Panggilan gagal", "Cek token/WiFi", 2000);
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

  baris1 = baris1.substring(0, 16);
  baris2 = baris2.substring(0, 16);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(baris1);
  lcd.setCursor(0, 1);
  lcd.print(baris2);

  Serial.printf("[LCD] %s | %s\n", baris1.c_str(), baris2.c_str());
}

void showMessage(const String& line1, const String& line2, unsigned long durationMs) {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(line1.substring(0, 16));
  lcd.setCursor(0, 1);
  lcd.print(line2.substring(0, 16));
  delay(durationMs);
  renderLayar();
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

void buzzerOff() {
#if BUZZER_ACTIVE
  digitalWrite(PIN_BUZZER, !BUZZER_ON_LEVEL);
#else
  noTone(PIN_BUZZER);
#endif
}

void successFeedback() {
  digitalWrite(PIN_LED, HIGH);
  beep(BUZZER_FREQ, 100);
  delay(80);
  beep(BUZZER_FREQ + 300, 100);
  digitalWrite(PIN_LED, LOW);
}

void errorFeedback() {
  for (int i = 0; i < 3; i++) {
    beep(500, 120);
    delay(80);
  }
}
