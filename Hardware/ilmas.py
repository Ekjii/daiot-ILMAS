import json
import psycopg2
import paho.mqtt.client as mqtt

DB_HOST = "100.115.172.95"
DB_NAME = "db_longsor"
DB_USER = "user_longsor"
DB_PASS = "password_kuat"
DB_PORT = 5432

def on_message(client, userdata, msg):

    try:
        data = json.loads(msg.payload.decode())

        print("\n========== DATA DITERIMA ==========")
        print(data)


        conn = psycopg2.connect(
            host=DB_HOST,
            database=DB_NAME,
            user=DB_USER,
            password=DB_PASS,
            port=DB_PORT
        )

        cur = conn.cursor()


        query = """
        INSERT INTO sensor_data
        (moisture, moisture_percent, ax, ay, az, gx, gy, gz, pitch, roll)
        VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
        """

        cur.execute(
            query,
            (
                data.get("moisture"),
                data.get("moisture_percent"),
                data.get("ax"),
                data.get("ay"),
                data.get("az"),
                data.get("gx"),
                data.get("gy"),
                data.get("gz"),
                data.get("pitch"),
                data.get("roll")
            )
        )

        conn.commit()

        print("Data berhasil masuk sensor_data")

    
        cur.close()
        conn.close()

    except Exception as e:
        print("ERROR:", e)


client = mqtt.Client()
client.on_message = on_message

client.connect("localhost", 1883, 60)

client.subscribe("datasensor/data")

print("Menunggu data MQTT dari ESP32...")

client.loop_forever()