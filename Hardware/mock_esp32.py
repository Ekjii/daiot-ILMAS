import time
import json
import random
import threading
import paho.mqtt.client as mqtt

MQTT_BROKER = "broker.hivemq.com"
MQTT_PUB_TOPIC = "sensor/landslide/data"       # Kirim data ke Raspi
MQTT_SUB_TOPIC = "sensor/landslide/actuator"   # Dengerin perintah dari Raspi

def on_connect(client, userdata, flags, rc):
    print(f"[✓] Emulator ESP32 terhubung ke broker! (Code: {rc})")
    client.subscribe(MQTT_SUB_TOPIC)
    print(f"[*] ESP32 sukses subscribe ke topik sirine: {MQTT_SUB_TOPIC}\n")

def on_message(client, userdata, msg):
    """Fungsi mendengarkan respon balik dari AI di Raspi"""
    try:
        perintah = json.loads(msg.payload.decode('utf-8'))
        if perintah["sirine"] == "ON":
            print(f"🤖 [ESP32 HARDWARE RESPONSE] 🔊 BEEP! BEEP! BEEP! Sirine Fisik Menyala! ({perintah['pesan']})")
        else:
            print(f"🤖 [ESP32 HARDWARE RESPONSE] 🔇 Sirine Mati (Kondisi Aman).")
    except Exception as e:
        pass

def send_loop(client):
    """Loop untuk mengirim data sensor setiap 1 detik"""
    counter = 0
    while True:
        if (counter // 5) % 2 == 0:
            data = {
                "device_id": "ESP32_MOCK",
                "accel": {"x": random.uniform(-0.05, 0.05), "y": random.uniform(-0.05, 0.05), "z": random.uniform(9.7, 9.9)},
                "gyro": {"x": random.uniform(-0.2, 0.2), "y": random.uniform(-0.2, 0.2), "z": random.uniform(-0.2, 0.2)},
                "soil_moisture": random.uniform(30.0, 45.0)
            }
        else:
            data = {
                "device_id": "ESP32_MOCK",
                "accel": {"x": random.uniform(3.0, 4.5), "y": random.uniform(2.5, 4.0), "z": random.uniform(6.5, 7.5)},
                "gyro": {"x": random.uniform(20.0, 40.0), "y": random.uniform(20.0, 40.0), "z": random.uniform(15.0, 25.0)},
                "soil_moisture": random.uniform(88.0, 98.0)
            }
        client.publish(MQTT_PUB_TOPIC, json.dumps(data))
        counter += 1
        time.sleep(1)

def main():
    client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION1)
    client.on_connect = on_connect
    client.on_message = on_message
    client.connect(MQTT_BROKER, 1883, 60)
    
    # Jalankan loop pengiriman data di thread terpisah agar tidak memblokir fungsi dengerin sirine
    t = threading.Thread(target=send_loop, args=(client,), daemon=True)
    t.start()
    
    client.loop_forever()

if __name__ == "__main__":
    main()
