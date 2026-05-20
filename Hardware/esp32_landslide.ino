#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <Adafruit_MPU6050.h>
#include <Adafruit_Sensor.h>

// ================= KONFIGURASI =================
const char* ssid = "NAMA_WIFI_LU";
const char* password = "PASSWORD_WIFI_LU";

// Broker MQTT (Samakan dengan .env di Raspi lu)
const char* mqtt_server = "broker.hivemq.com";
const char* mqtt_topic_pub = "sensor/landslide/data";
const char* mqtt_topic_sub = "sensor/landslide/actuator";

// Pin Hardware
const int MOISTURE_PIN = 34; // Pin Analog untuk Sensor Kelembaban Tanah
const int SIRINE_PIN = 18;   // Pin Digital untuk Buzzer / Sirine Peringatan

// Objek Global
WiFiClient espClient;
PubSubClient client(espClient);
Adafruit_MPU6050 mpu;

unsigned long lastMsgTime = 0;
// ===============================================

// Fungsi untuk konek ke WiFi
void setup_wifi() {
  delay(10);
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nWiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
}

// Fungsi Callback: Dijalankan otomatis JIKA AI ngirim perintah dari Raspi
void callback(char* topic, byte* payload, unsigned int length) {
  Serial.print("Pesan masuk [");
  Serial.print(topic);
  Serial.print("]: ");
  
  // Ubah payload byte menjadi String JSON
  String messageTemp;
  for (int i = 0; i < length; i++) {
    messageTemp += (char)payload[i];
  }
  Serial.println(messageTemp);

  // Parsing JSON perintah dari AI (Misal: {"sirine":"ON", "pesan":"Evakuasi!"})
  StaticJsonDocument<200> doc;
  DeserializationError error = deserializeJson(doc, messageTemp);
  
  if (!error) {
    String perintahSirine = doc["sirine"];
    if (perintahSirine == "ON") {
      digitalWrite(SIRINE_PIN, HIGH); // Nyalakan Sirine
      Serial.println("⚠️ AI MEMERINTAHKAN SIRINE MENYALA! ⚠️");
    } else if (perintahSirine == "OFF") {
      digitalWrite(SIRINE_PIN, LOW);  // Matikan Sirine
      Serial.println("✅ AI menyatakan Aman. Sirine Mati.");
    }
  }
}

// Fungsi Reconnect MQTT kalau putus
void reconnect() {
  while (!client.connected()) {
    Serial.print("Attempting MQTT connection...");
    // Bikin ID unik untuk ESP32 ini
    String clientId = "ESP32_Landslide_";
    clientId += String(random(0, 1000));
    
    if (client.connect(clientId.c_str())) {
      Serial.println("connected");
      // Subscribe ke topik AI Actuator
      client.subscribe(mqtt_topic_sub);
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" try again in 5 seconds");
      delay(5000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  
  // Setup Pin
  pinMode(SIRINE_PIN, OUTPUT);
  digitalWrite(SIRINE_PIN, LOW);
  
  setup_wifi();
  client.setServer(mqtt_server, 1883);
  client.setCallback(callback); // Daftarkan fungsi pendengar perintah AI

  // Setup Sensor MPU6050
  if (!mpu.begin()) {
    Serial.println("Gagal menemukan sensor MPU6050!");
    while (1) { delay(10); } // Stop program jika sensor tidak ketemu
  }
  Serial.println("MPU6050 Found!");
  
  // Set sensitivitas sensor (bisa disesuaikan nanti)
  mpu.setAccelerometerRange(MPU6050_RANGE_8_G);
  mpu.setGyroRange(MPU6050_RANGE_500_DEG);
  mpu.setFilterBandwidth(MPU6050_BAND_21_HZ);
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop(); // Wajib ada agar MQTT bisa dengerin perintah masuk

  // Kirim data sensor setiap 2 detik sekali
  unsigned long now = millis();
  if (now - lastMsgTime > 2000) {
    lastMsgTime = now;

    // 1. Baca Sensor Kemiringan (MPU6050)
    sensors_event_t a, g, temp;
    mpu.getEvent(&a, &g, &temp);

    // 2. Baca Sensor Kelembaban Tanah (Analog)
    // Nilai analog ESP32 (0-4095). Harus di-map ke persen (0-100%)
    // *Nilai batas kering dan basah perlu kalibrasi ulang saat hardware asli dipasang
    int rawMoisture = analogRead(MOISTURE_PIN);
    float moisturePercent = map(rawMoisture, 4095, 0, 0, 100); 
    
    // Pastikan persentase tidak minus atau lebih dari 100
    if(moisturePercent < 0) moisturePercent = 0;
    if(moisturePercent > 100) moisturePercent = 100;

    // 3. Susun data JSON persis seperti simulasi python kita
    StaticJsonDocument<256> doc;
    doc["device_id"] = "ESP32_ASLI_01";
    
    JsonObject accel = doc.createNestedObject("accel");
    accel["x"] = a.acceleration.x;
    accel["y"] = a.acceleration.y;
    accel["z"] = a.acceleration.z;

    JsonObject gyro = doc.createNestedObject("gyro");
    gyro["x"] = g.gyro.x;
    gyro["y"] = g.gyro.y;
    gyro["z"] = g.gyro.z;

    doc["soil_moisture"] = moisturePercent;

    // 4. Ubah JSON jadi String lalu Publish ke MQTT
    char jsonBuffer[256];
    serializeJson(doc, jsonBuffer);
    
    Serial.print("Publish Data: ");
    Serial.println(jsonBuffer);
    
    client.publish(mqtt_topic_pub, jsonBuffer);
  }
}
