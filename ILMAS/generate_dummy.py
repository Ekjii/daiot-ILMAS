import csv
import random
import numpy as np
from datetime import datetime, timedelta

CSV_FILE = "synthetic_landslide_data.csv"
HEADER = ["timestamp", "accel_x", "accel_y", "accel_z", "gyro_x", "gyro_y", "gyro_z", "soil_moisture", "label"]

# Tentukan berapa banyak data yang mau dibuat (misal: total 5000 baris)
SAMPLES_PER_CLASS = 2500

def generate_data():
    rows = []
    start_time = datetime.now()
    
    # 1. GENERATE KONDISI AMAN (Label 0) - 2500 Data
    print("[*] Membuat data simulasi kondisi AMAN...")
    for i in range(SAMPLES_PER_CLASS):
        timestamp = (start_time + timedelta(seconds=i)).strftime("%Y-%m-%d %H:%M:%S")
        
        # Accelerometer: Diam di tempat, variasi getaran mikro karena angin/kendaraan lewat
        accel_x = random.uniform(-0.1, 0.1)
        accel_y = random.uniform(-0.1, 0.1)
        accel_z = 9.81 + random.uniform(-0.1, 0.1) # Gravitasi bumi normal
        
        # Gyroscope: Hampir tidak ada rotasi
        gyro_x = random.uniform(-0.5, 0.5)
        gyro_y = random.uniform(-0.5, 0.5)
        gyro_z = random.uniform(-0.5, 0.5)
        
        # Kelembaban: Normal cenderung kering sampai lembab biasa (20% - 60%)
        soil_moisture = random.uniform(20.0, 60.0)
        
        label = 0
        rows.append([timestamp, accel_x, accel_y, accel_z, gyro_x, gyro_y, gyro_z, soil_moisture, label])

    # 2. GENERATE KONDISI LONGSOR (Label 1) - 2500 Data
    print("[*] Membuat data simulasi kondisi LONGSOR...")
    time_offset = SAMPLES_PER_CLASS
    for i in range(SAMPLES_PER_CLASS):
        timestamp = (start_time + timedelta(seconds=time_offset + i)).strftime("%Y-%m-%d %H:%M:%S")
        
        # Accelerometer: Getaran hebat (gempa/pergeseran) & perubahan sudut gravitasi karena miring
        accel_x = random.uniform(2.0, 5.0)  # Lonjakan gaya X
        accel_y = random.uniform(2.0, 5.0)  # Lonjakan gaya Y
        accel_z = 7.0 + random.uniform(-1.0, 1.0) # Nilai Z turun karena sensor tidak lagi tegak lurus
        
        # Gyroscope: Rotasi cepat karena sensor terjatuh/terguling
        gyro_x = random.uniform(15.0, 50.0)
        gyro_y = random.uniform(15.0, 50.0)
        gyro_z = random.uniform(10.0, 30.0)
        
        # Kelembaban: Sangat tinggi (85% - 100%), pemicu utama longsor karena tanah jenuh air hujan
        soil_moisture = random.uniform(85.0, 100.0)
        
        label = 1
        rows.append([timestamp, accel_x, accel_y, accel_z, gyro_x, gyro_y, gyro_z, soil_moisture, label])

    # Tulis semua data ke file CSV
    with open(CSV_FILE, mode='w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(HEADER)
        writer.writerows(rows)
        
    print(f"[✓] Berhasil membuat {len(rows)} baris data dummy di file '{CSV_FILE}'!")

if __name__ == "__main__":
    generate_data()
