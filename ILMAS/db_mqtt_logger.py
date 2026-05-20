import os
import json
import psycopg2
import paho.mqtt.client as mqtt
from dotenv import load_dotenv

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
load_dotenv(os.path.join(BASE_DIR, "../.env"))

MQTT_BROKER = os.getenv("MQTT_BROKER", "broker.hivemq.com")
MQTT_SUB_TOPIC = "sensor/landslide/data"

DB_HOST = os.getenv("DB_HOST", "127.0.0.1")
DB_PORT = os.getenv("DB_PORT", "5432")
DB_NAME = os.getenv("DB_NAME", "ilmas_db")
DB_USER = os.getenv("DB_USER", "postgres")
DB_PASS = os.getenv("DB_PASS", "rahasia")

def connect_db():
    """Koneksi ke PostgreSQL"""
    try:
        conn = psycopg2.connect(
            host=DB_HOST, port=DB_PORT, dbname=DB_NAME, user=DB_USER, password=DB_PASS
        )
        conn.autocommit = True
        return conn
    except Exception as e:
        print(f"[!] Gagal koneksi DB: {e}")
        return None

db_conn = connect_db()

def on_connect(client, userdata, flags, rc):
    print(f"[✓] Database Logger aktif di Cloud HiveMQ! (Code: {rc})")
    client.subscribe(MQTT_SUB_TOPIC)

def on_message(client, userdata, msg):
    if not db_conn:
        return
        
    try:
        data = json.loads(msg.payload.decode('utf-8'))
        
        cursor = db_conn.cursor()
        query = """
            INSERT INTO sensor_logs 
            (device_id, accel_x, accel_y, accel_z, gyro_x, gyro_y, gyro_z, soil_moisture, status_ai)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        # Status AI sementara kita default 0 (karena logger ini tidak memprediksi)
        # Nanti datanya bisa digabung (JOIN) atau dikembangkan lebih jauh
        values = (
            data.get("device_id", "UNKNOWN"),
            data["accel"]["x"], data["accel"]["y"], data["accel"]["z"],
            data["gyro"]["x"], data["gyro"]["y"], data["gyro"]["z"],
            data["soil_moisture"], 0
        )
        
        cursor.execute(query, values)
        cursor.close()
        print(f"[DB] Inserted 1 row from {data.get('device_id')}")
        
    except Exception as e:
        print(f"[DB ERROR] {e}")

def main():
    if not db_conn:
        print("[!] Gagal menjalankan logger karena Database mati.")
        return
        
    client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION1)
    client.on_connect = on_connect
    client.on_message = on_message
    client.connect(MQTT_BROKER, 1883, 60)
    client.loop_forever()

if __name__ == "__main__":
    main()
