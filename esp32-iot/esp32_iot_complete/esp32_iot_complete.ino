

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>




#if __has_include("secrets.h")
#include "secrets.h"
#else
#include "secrets.example.h"
#endif


#define PAKAI_HTTPS 0

#if PAKAI_HTTPS
#include <WiFiClientSecure.h>
#endif


const int PIN_SDA = 21;
const int PIN_SCL = 22;
const int PIN_TOUCH = 27;
const int PIN_BUZZER = 25;
const int PIN_LED = 2;




#define TOUCH_AUTO_LEVEL 1
#define TOUCH_ACTIVE_HIGH 1


#define BUZZER_ACTIVE 1

#define BUZZER_ON_LEVEL HIGH
#define BUZZER_FREQ 2000


const uint8_t LCD_ADDR = 0x27;
LiquidCrystal_I2C lcd(LCD_ADDR, 16, 2);


const unsigned long STATUS_INTERVAL_MS = 10000;
const unsigned long RETRY_INTERVAL_MS = 15000;
const unsigned long WIFI_RETRY_MS = 15000;


const unsigned long OFFLINE_RESTART_MS = 180000;


const uint8_t WIFI_RADIO_RESET_EVERY = 4;

const unsigned long HTTP_TIMEOUT_MS = 5000;
const unsigned long HTTP_CONNECT_TIMEOUT_MS = 2500;
const unsigned long LONG_PRESS_MS = 3000;
const unsigned long TAP_MIN_MS = 50;


String kodeSesi = "----";


String kodeMejaTampil = KODE_MEJA;
String statusRingkas = "Belum ada pesan";
String lcdMessage = "Panggil Pelayan";
int buzzerDuration = 3;
float totalBayar = 0;
int jumlahPesanan = 0;
int jumlahSelesai = 0;
bool adaPesanan = false;





String faseLcd = "standby";
String metodePembayaran = "";


const int SCREEN_STATUS = 0;
const int SCREEN_TOTAL = 1;
const int SCREEN_KODE = 2;
const int SCREEN_COUNT = 3;
int currentScreen = SCREEN_STATUS;
const unsigned long ROTASI_LAYAR_MS = 20000;
unsigned long lastRotasiLayar = 0;


int touchIdleLevel = HIGH;

bool touchActive = false;
bool longPressDone = false;
unsigned long pressStart = 0;
unsigned long lastStatusCheck = 0;
unsigned long lastWifiAttempt = 0;
unsigned long terakhirSuksesMs = 0;
bool pernahSuksesPolling = false;
bool wifiTerhubungTerakhir = false;
uint8_t percobaanWifi = 0;
unsigned long intervalPolling = STATUS_INTERVAL_MS;


bool segeraPolling = true;


bool buzzerAktif = false;
unsigned long buzzerSampai = 0;
bool ledAktif = false;
unsigned long ledSampai = 0;


bool pesanSementara = false;
String pesanBaris1 = "";
String pesanBaris2 = "";
unsigned long pesanSampai = 0;


String lcdBaris1 = "";
String lcdBaris2 = "";



bool errorAktif = false;
String errorBaris1 = "";
String errorBaris2 = "";


WiFiClient plainClient;
#if PAKAI_HTTPS
WiFiClientSecure secureClient;
#endif


void connectWiFi();
void jagaWiFi();
bool fetchMejaStatus();
void panggilPelayan();
void handleTouch();
bool readTouch();
void renderLayar();
void tampilkanPesan(const String& line1, const String& line2, unsigned long durationMs);
void tampilkanKesalahan(const String& line1, const String& line2);
String urlApi(const String& path);
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


void setup() {
  Serial.begin(115200);
  delay(200);
  Serial.println();
  Serial.println("=== ESP32 IoT Saung Situ Awi ===");
  Serial.printf("[CFG] Build  : %s %s\n", __DATE__, __TIME__);
  Serial.printf("[CFG] Chip   : %s rev%d, heap %u byte\n",
                ESP.getChipModel(), ESP.getChipRevision(), ESP.getFreeHeap());
  Serial.printf("[CFG] WiFi   : %s\n", WIFI_SSID);
  Serial.printf("[CFG] Server : %s\n", SERVER_URL);
  Serial.printf("[CFG] Meja   : %s\n", KODE_MEJA);
  Serial.printf("[CFG] Token  : %u karakter\n", (unsigned)strlen(IOT_API_TOKEN));

  String serverCek = String(SERVER_URL);
  if (serverCek.indexOf("127.0.0.1") >= 0 || serverCek.indexOf("localhost") >= 0) {
    Serial.println("[CFG] PERINGATAN: SERVER_URL memakai loopback (127.0.0.1/localhost).");
    Serial.println("[CFG] ESP32 tidak bisa mengakses loopback laptop. Pakai IP LAN, mis. 192.168.160.171.");
  }
  if (serverCek.endsWith("/")) {
    Serial.println("[CFG] PERINGATAN: SERVER_URL diakhiri '/'. Slash akan diabaikan otomatis.");
  }

  pinMode(PIN_LED, OUTPUT);
  digitalWrite(PIN_LED, LOW);

  pinMode(PIN_BUZZER, OUTPUT);
  buzzerOff();

#if TOUCH_AUTO_LEVEL

  pinMode(PIN_TOUCH, INPUT);
  delay(200);
  touchIdleLevel = digitalRead(PIN_TOUCH);
  Serial.printf("[TOUCH] Level idle=%s (auto)\n", touchIdleLevel == HIGH ? "HIGH" : "LOW");
#elif TOUCH_ACTIVE_HIGH
  pinMode(PIN_TOUCH, INPUT);
#else
  pinMode(PIN_TOUCH, INPUT_PULLUP);
#endif

  Wire.begin(PIN_SDA, PIN_SCL);
  lcd.begin(16, 2);
  lcd.backlight();
  lcd.clear();
  tulisLcd("Saung Situ Awi", "Menyiapkan...", true);
  delay(500);

  connectWiFi();

  if (WiFi.status() == WL_CONNECTED) {
    tulisLcd("Cek server...", "Mohon tunggu", true);
    fetchMejaStatus();
    lastStatusCheck = millis();
    intervalPolling = STATUS_INTERVAL_MS;
    segeraPolling = false;
  }

  Serial.println("Ready. Tap = ganti layar, tahan 3s = panggil pelayan. Reconnect otomatis aktif.");
  beep(BUZZER_FREQ, 80);
}


void loop() {
  updateBuzzer();

  if (pesanSementara && (long)(millis() - pesanSampai) >= 0) {
    pesanSementara = false;
    renderLayar();
  }

  handleTouch();
  jagaWiFi();




  if (pernahSuksesPolling && millis() - terakhirSuksesMs >= OFFLINE_RESTART_MS) {
    Serial.println("[WATCHDOG] Server tidak terjangkau terlalu lama. Restart otomatis...");
    delay(200);
    ESP.restart();
  }



  if (!pesanSementara && !errorAktif && faseLcd == "lunas"
      && millis() - lastRotasiLayar >= ROTASI_LAYAR_MS) {
    lastRotasiLayar = millis();
    currentScreen = (currentScreen + 1) % SCREEN_COUNT;
    renderLayar();
  }

  if (WiFi.status() == WL_CONNECTED && (segeraPolling || millis() - lastStatusCheck >= intervalPolling)) {
    segeraPolling = false;
    lastStatusCheck = millis();
    intervalPolling = fetchMejaStatus() ? STATUS_INTERVAL_MS : RETRY_INTERVAL_MS;
  }

  delay(5);
}


bool readTouch() {
#if TOUCH_AUTO_LEVEL

  return digitalRead(PIN_TOUCH) != touchIdleLevel;
#elif TOUCH_ACTIVE_HIGH
  return digitalRead(PIN_TOUCH) == HIGH;
#else
  return digitalRead(PIN_TOUCH) == LOW;
#endif
}

void handleTouch() {


  if (!errorAktif && faseLcd != "lunas") {
    touchActive = false;
    return;
  }

  bool touched = readTouch();
  unsigned long now = millis();

  if (touched && !touchActive) {
    touchActive = true;
    longPressDone = false;
    pressStart = now;
    Serial.printf("[TOUCH] Tekan (pin=%d)\n", digitalRead(PIN_TOUCH));
  } else if (touched && touchActive) {
    if (!longPressDone && now - pressStart >= LONG_PRESS_MS) {
      longPressDone = true;
      if (faseLcd == "lunas") {
        Serial.println("[TOUCH] Tahan -> panggil pelayan");
        panggilPelayan();
      } else {
        Serial.println("[TOUCH] Tahan diabaikan (belum lunas)");
      }
    }
  } else if (!touched && touchActive) {
    unsigned long duration = now - pressStart;
    touchActive = false;
    Serial.printf("[TOUCH] Lepas %lu ms%s\n", duration,
                  duration < TAP_MIN_MS ? " (terlalu cepat)" : "");

    if (!longPressDone && duration >= TAP_MIN_MS) {
      beep(BUZZER_FREQ, 40);



      if (errorAktif) {
        Serial.println("[TOUCH] Tap -> coba server lagi");
        tulisLcd("Mencoba server", "Mohon tunggu", true);
        segeraPolling = true;
        intervalPolling = STATUS_INTERVAL_MS;
        return;
      }

      currentScreen = (currentScreen + 1) % SCREEN_COUNT;
      lastRotasiLayar = millis();
      Serial.printf("[TOUCH] Tap %lu ms -> layar %d\n", duration, currentScreen + 1);
      renderLayar();
    }
  }
}


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
    Serial.printf("[WiFi] OK, IP=%s, GW=%s, mask=%s, kanal=%d, RSSI=%d dBm\n",
                  WiFi.localIP().toString().c_str(),
                  WiFi.gatewayIP().toString().c_str(),
                  WiFi.subnetMask().toString().c_str(),
                  WiFi.channel(), WiFi.RSSI());
    Serial.printf("[WiFi] MAC=%s\n", WiFi.macAddress().c_str());
    wifiTerhubungTerakhir = true;
    percobaanWifi = 0;
    tulisLcd("WiFi OK", WiFi.localIP().toString());
    delay(500);
  } else {
    Serial.println("[WiFi] Gagal. Cek SSID/password dan pastikan WiFi 2.4 GHz.");
    wifiTerhubungTerakhir = false;
    tulisLcd("WiFi gagal", "Cek SSID/Pass");
  }
}





void jagaWiFi() {
  if (WiFi.status() == WL_CONNECTED) {
    if (!wifiTerhubungTerakhir) {
      Serial.printf("[WiFi] Tersambung kembali. IP=%s\n", WiFi.localIP().toString().c_str());
      wifiTerhubungTerakhir = true;
      percobaanWifi = 0;
      segeraPolling = true;
    }
    return;
  }

  if (wifiTerhubungTerakhir) {
    Serial.println("[WiFi] Terputus. Menyambung ulang otomatis (tanpa sentuh)...");
    wifiTerhubungTerakhir = false;
  }

  unsigned long now = millis();
  if (now - lastWifiAttempt < WIFI_RETRY_MS) {
    return;
  }

  lastWifiAttempt = now;
  percobaanWifi++;
  segeraPolling = true;



  if (percobaanWifi % WIFI_RADIO_RESET_EVERY == 0) {
    Serial.printf("[WiFi] Reset radio (percobaan %u)...\n", percobaanWifi);
    WiFi.disconnect(true, false);
    WiFi.mode(WIFI_STA);
    WiFi.setSleep(false);
    WiFi.setAutoReconnect(true);
  }

  Serial.printf("[WiFi] Sambung ulang otomatis #%u...\n", percobaanWifi);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
}



String urlApi(const String& path) {
  String base = String(SERVER_URL);
  while (base.endsWith("/")) {
    base.remove(base.length() - 1);
  }
  return base + path;
}

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


bool fetchMejaStatus() {
  if (WiFi.status() != WL_CONNECTED) {

    Serial.println("[HTTP] Dilewati: WiFi belum terhubung");
    return false;
  }

  String url = urlApi(String("/api/iot/meja/") + KODE_MEJA);
  Serial.printf("[HTTP] GET %s\n", url.c_str());

  HTTPClient http;
  if (! siapkanHttp(http, url)) {
    Serial.println("[HTTP] begin gagal: URL tidak valid?");
    tampilkanKesalahan("HTTP init gagal", "Cek SERVER_URL");
    return false;
  }

  unsigned long mulai = millis();
  int httpCode = http.GET();
  unsigned long durasi = millis() - mulai;

  if (httpCode != 200) {
    Serial.printf("[HTTP] GAGAL kode=%d durasi=%lu ms\n", httpCode, durasi);

    if (httpCode > 0) {
      String body = http.getString();
      body.trim();
      if (body.length() > 240) {
        body = body.substring(0, 240);
      }
      Serial.printf("[HTTP] Body: %s\n", body.c_str());
    }
    http.end();

    if (httpCode <= 0) {
      if (httpCode == HTTPC_ERROR_CONNECTION_REFUSED) {
        Serial.println("[HTTP] Koneksi ditolak. Pastikan server listen 0.0.0.0 dan port tidak diblokir firewall.");
      } else if (httpCode == HTTPC_ERROR_READ_TIMEOUT) {
        Serial.println("[HTTP] Server tidak menjawab. Bisa juga diblokir firewall.");
      } else if (httpCode == HTTPC_ERROR_NOT_CONNECTED || httpCode == HTTPC_ERROR_CONNECTION_LOST) {
        Serial.println("[HTTP] Koneksi terputus.");
      } else {
        Serial.printf("[HTTP] Koneksi gagal (kode %d).\n", httpCode);
      }


    } else if (httpCode == 401 || httpCode == 403) {
      Serial.println("[HTTP] Token IoT ditolak. Samakan dengan IOT_API_TOKEN di .env Laravel.");
      tampilkanKesalahan("Token ditolak", "Cek API token");
    } else if (httpCode == 404) {
      Serial.printf("[HTTP] Meja '%s' tidak ditemukan di database.\n", KODE_MEJA);
      tampilkanKesalahan("Meja tak ada", "Cek KODE_MEJA");
    } else if (httpCode >= 300 && httpCode < 400) {
      Serial.println("[HTTP] Server mengalihkan request. Cek APP_URL/URL server.");
      tampilkanKesalahan("Dialihkan", "Cek APP_URL");
    } else if (httpCode == 429) {
      Serial.println("[HTTP] Rate limit. Tunggu sebentar.");

    } else if (httpCode >= 500) {
      Serial.println("[HTTP] Error server. Cek storage/logs/laravel.log.");

    } else {
      Serial.printf("[HTTP] HTTP %d. Cek server.\n", httpCode);
      tampilkanKesalahan(String("HTTP ") + httpCode, "Cek server");
    }
    return false;
  }



  String payload = http.getString();
  http.end();

  Serial.printf("[HTTP] OK %u byte, %lu ms\n", (unsigned)payload.length(), durasi);

  JsonDocument doc;
  DeserializationError error = deserializeJson(doc, payload);
  if (error) {
    Serial.printf("[JSON] Gagal parse: %s\n", error.c_str());
    String cuplikan = payload;
    cuplikan.replace("\n", " ");
    cuplikan.replace("\r", " ");
    if (cuplikan.length() > 200) {
      cuplikan = cuplikan.substring(0, 200);
    }
    Serial.printf("[JSON] Body: %s\n", cuplikan.c_str());
    tampilkanKesalahan("Respons aneh", "Bukan JSON");
    return false;
  }

  if (! doc["success"].as<bool>()) {
    const char* pesan = doc["message"] | "(tanpa pesan)";
    Serial.printf("[API] success=false: %s\n", pesan);
    tampilkanKesalahan("API menolak", "Cek token/meja");
    return false;
  }

  JsonObject data = doc["data"].as<JsonObject>();
  if (data.isNull()) {
    Serial.println("[API] Field data kosong.");
    tampilkanKesalahan("Data kosong", "Cek server");
    return false;
  }

  JsonObject konfigurasi = doc["konfigurasi"].as<JsonObject>();

  const char* mejaApi = data["kode_meja"] | "";
  if (strlen(mejaApi) > 0) {
    kodeMejaTampil = String(mejaApi);
  }

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
  jumlahSelesai = data["jumlah_selesai"].as<int>();
  adaPesanan = data["ada_pesanan"] | false;

  const char* faseApi = data["fase"] | "standby";
  const char* metodeApi = data["metode_pembayaran"] | "";
  String faseBaru = String(faseApi);
  String metodeBaru = String(metodeApi);

  if (faseBaru != "standby" && faseBaru != "pembayaran" && faseBaru != "lunas") {
    faseBaru = "standby";
  }

  bool faseBerubah = faseBaru != faseLcd;
  faseLcd = faseBaru;
  metodePembayaran = metodeBaru;

  if (faseBerubah) {
    currentScreen = SCREEN_STATUS;
    lastRotasiLayar = millis();
    touchActive = false;
    Serial.printf("[FASE] %s (metode=%s)\n",
                  faseLcd.c_str(),
                  metodePembayaran.length() > 0 ? metodePembayaran.c_str() : "-");
  }

  if (jumlahPesanan <= 0) {


    if (adaPesanan && jumlahSelesai > 0) {
      statusRingkas = jumlahSelesai > 1
        ? "Selesai (" + String(jumlahSelesai) + ")"
        : "Selesai";
    } else {
      statusRingkas = "Belum ada pesan";
    }
  } else {
    const char* status = data["pesanan"][0]["status_pesanan"] | "-";
    String label = labelStatus(String(status));
    if (jumlahPesanan > 1) {
      statusRingkas = label + " (" + String(jumlahPesanan) + ")";
    } else {
      statusRingkas = label;
    }
  }

  errorAktif = false;
  terakhirSuksesMs = millis();
  pernahSuksesPolling = true;
  percobaanWifi = 0;
  wifiTerhubungTerakhir = true;
  renderLayar();
  Serial.printf("[MEJA] %s | sesi=%s | pesanan=%d | total=%.0f | heap=%u\n",
                KODE_MEJA, kodeSesi.c_str(), jumlahPesanan, totalBayar,
                ESP.getFreeHeap());
  return true;
}

void panggilPelayan() {
  if (WiFi.status() != WL_CONNECTED) {
    errorFeedback();
    tampilkanKesalahan("WiFi terputus", "Cek jaringan");
    return;
  }


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
           kodeMejaTampil.c_str(), kodeSesi.c_str());

  HTTPClient http;
  String url = urlApi("/api/iot/panggil-pelayan");
  Serial.printf("[HTTP] POST %s\n", url.c_str());

  if (! siapkanHttp(http, url)) {
    errorFeedback();
    tampilkanKesalahan("HTTP init gagal", "Cek SERVER_URL");
    return;
  }

  http.addHeader("Content-Type", "application/json");
  unsigned long mulai = millis();
  int httpCode = http.POST((uint8_t*)body, strlen(body));
  unsigned long durasi = millis() - mulai;

  String respons = http.getString();
  http.end();
  respons.trim();
  if (respons.length() > 200) {
    respons = respons.substring(0, 200);
  }

  Serial.printf("[HTTP] POST panggil: kode=%d durasi=%lu ms body=%s\n",
                httpCode, durasi, respons.c_str());

  if (httpCode == 200) {
    errorAktif = false;
    successFeedback();
    tampilkanPesan(lcdMessage, "Terkirim", 1500);
  } else if (httpCode == 401) {
    errorFeedback();
    tampilkanKesalahan("Token ditolak", "Cek API token");
  } else if (httpCode == 403) {
    errorFeedback();
    tampilkanPesan("Sesi berubah", "Coba lagi", 1500);

    segeraPolling = true;
  } else if (httpCode == 404) {
    errorFeedback();
    tampilkanKesalahan("Meja tak ada", "Cek KODE_MEJA");
  } else if (httpCode == 429) {
    errorFeedback();
    tampilkanPesan("Masih jeda", "Coba lagi", 1500);
  } else if (httpCode <= 0) {
    errorFeedback();
    tampilkanKesalahan("Server offline", "Cek IP/firewall");
  } else if (httpCode >= 500) {
    errorFeedback();
    tampilkanKesalahan("Server error", "Cek log Laravel");
  } else {
    errorFeedback();
    tampilkanKesalahan(String("HTTP ") + httpCode, "Cek server");
  }
}


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

  if (errorAktif) {
    tulisLcd(errorBaris1, errorBaris2);
    return;
  }

  String baris1;
  String baris2;

  if (faseLcd == "standby") {


    baris1 = String("Meja : ") + kodeMejaTampil;
    baris2 = String("Kode : ") + kodeSesi;
  } else if (faseLcd == "pembayaran") {
    if (metodePembayaran == "qris") {
      baris1 = "Pembayaran TF";
      baris2 = formatRupiah(totalBayar);
    } else if (metodePembayaran == "tunai") {
      baris1 = "Pembayaran";
      baris2 = "di Kasir";
    } else {
      baris1 = "Pembayaran";
      baris2 = "TF / Kasir";
    }
  } else if (currentScreen == SCREEN_STATUS) {
    baris1 = "Status Pesanan";
    baris2 = statusRingkas;
  } else if (currentScreen == SCREEN_TOTAL) {
    baris1 = "Total Bayar";
    baris2 = formatRupiah(totalBayar);
  } else {


    baris1 = String("Meja : ") + kodeMejaTampil;
    baris2 = String("Kode : ") + kodeSesi;
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



void tampilkanKesalahan(const String& line1, const String& line2) {
  errorBaris1 = line1;
  errorBaris2 = line2;
  errorAktif = true;
  pesanSementara = false;
  tulisLcd(errorBaris1, errorBaris2, true);
  Serial.printf("[LCD] ERROR: %s | %s\n", line1.c_str(), line2.c_str());
}


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
