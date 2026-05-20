import json
import joblib
import requests
import time
import os  # <-- WAJIB TAMBAHIN INI
from dotenv import load_dotenv  # <-- WAJIB TAMBAHIN INI
import paho.mqtt.client as mqtt

# Jalankan fungsi load_dotenv() di awal biar Python ngebaca file .env lu
load_dotenv()

# 1. Load Otak AI
MODEL_PATH = "landslide_model.pkl"
print(f"[*] Memuat model AI dari {MODEL_PATH}...")
model = joblib.load(MODEL_PATH)

# 2. Ambil konfigurasi dari file .env (BIARKAN SEPERTI INI)
MQTT_BROKER = os.getenv("MQTT_BROKER")
MQTT_SUB_TOPIC = "sensor/landslide/data"
MQTT_PUB_TOPIC = "sensor/landslide/actuator"

# 3. Ambil Token & ID Telegram dari file .env (BIARKAN SEPERTI INI)
TELEGRAM_TOKEN = os.getenv("TELEGRAM_TOKEN")
TELEGRAM_CHAT_ID = os.getenv("TELEGRAM_CHAT_ID")

# Variabel kontrol jeda alert
last_alert_time = 0

# ... (sisa kode on_message dan main ke bawah sama persis seperti kemarin)

def kirim_alert_telegram(moisture, status_text):
    """Fungsi untuk mengirimkan notifikasi SOS ke Telegram"""
    pesan = (
        f"🚨 <b>[PERINGATAN DINI: TANAH LONGSOR]</b> 🚨\n\n"
        f"Sistem EWS mendeteksi adanya pergeseran tanah ekstrem!\n"
        f"🔹 <b>Status AI:</b> {status_text}\n"
        f"🔹 <b>Kelembaban Tanah:</b> {moisture:.1f}%\n\n"
        f"⚠️ <i>Mohon segera lakukan pengecekan lokasi atau evakuasi mandiri!</i>"
    )
    
    url = f"https://api.telegram.org/bot{TELEGRAM_TOKEN}/sendMessage"
    payload = {
        "chat_id": TELEGRAM_CHAT_ID,
        "text": pesan,
        "parse_mode": "HTML"
    }
    
    try:
        response = requests.post(url, json=payload)
        if response.status_code == 200:
            print("[✓] Notifikasi bahaya berhasil dikirim ke Telegram!")
        else:
            print(f"[❌] Gagal kirim Telegram: {response.text}")
    except Exception as e:
        print(f"[ERROR TELEGRAM] {e}")

def cek_cuaca_bmkg():
    """Mengambil data peringatan dini cuaca dari API XML BMKG"""
    url = "https://data.bmkg.go.id/DataMKG/MEWS/DigitalForecast/digitalforecast_jawatimur.xml"
    try:
        response = requests.get(url, timeout=5)
        if response.status_code == 200:
            return "Koneksi BMKG Aman & Stabil"
    except Exception as e:
        return f"Gagal load data BMKG: {e}"

def on_connect(client, userdata, flags, rc):
    print(f"[✓] AI Subscriber aktif di Cloud HiveMQ! (Code: {rc})")
    status_bmkg = cek_cuaca_bmkg()
    print(f"[*] Status API BMKG: {status_bmkg}")
    
    client.subscribe(MQTT_SUB_TOPIC)
    print(f"[*] Menunggu data di: {MQTT_SUB_TOPIC}\n")

def on_message(client, userdata, msg):
    global last_alert_time
    try:
        payload = json.loads(msg.payload.decode('utf-8'))
        
        # Susun susunan fitur input AI
        fitur_input = [[
            payload["accel"]["x"], payload["accel"]["y"], payload["accel"]["z"],
            payload["gyro"]["x"], payload["gyro"]["y"], payload["gyro"]["z"],
            payload["soil_moisture"]
        ]]
        
        # Jalankan prediksi otak AI
        prediksi = model.predict(fitur_input)[0]
        
        if prediksi == 1:
            status = "🚨 [STATUS: BAHAYA LONGSOR!!]"
            # Perintah balik ke ESP32
            client.publish(MQTT_PUB_TOPIC, json.dumps({"sirine": "ON", "pesan": "Evakuasi!"}))
            
            # Eksekusi kirim telegram dengan proteksi cooldown 10 detik
            current_time = time.time()
            if current_time - last_alert_time > 10:
                kirim_alert_telegram(payload["soil_moisture"], "BAHAYA LONGSOR")
                last_alert_time = current_time
        else:
            status = "✅ [STATUS: AMAN]"
            client.publish(MQTT_PUB_TOPIC, json.dumps({"sirine": "OFF", "pesan": "Stabil"}))
            
        print(f"[{payload['device_id']}] Moisture: {payload['soil_moisture']:.1f}% | {status}")
        
    except Exception as e:
        print(f"[ERROR] Gagal memproses data: {e}")

def main():
    client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION1)
    client.on_connect = on_connect
    client.on_message = on_message
    client.connect(MQTT_BROKER, 1883, 60)
    client.loop_forever()

if __name__ == "__main__":
    main()
