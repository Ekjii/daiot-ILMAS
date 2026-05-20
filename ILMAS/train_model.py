import os
import csv
import random
import pandas as pd
from datetime import datetime, timedelta
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import classification_report, accuracy_score
import joblib

CSV_FILE = "synthetic_landslide_data.csv"
HEADER = ["timestamp", "accel_x", "accel_y", "accel_z", "gyro_x", "gyro_y", "gyro_z", "soil_moisture", "label"]
SAMPLES_PER_CLASS = 2500

def buat_data_dummy_otomatis():
    """Fungsi untuk membuat data jika file CSV belum ada"""
    print("[*] File CSV tidak ditemukan. Membuat data dummy otomatis...")
    rows = []
    start_time = datetime.now()
    
    # 1. DATA AMAN (Label 0)
    for i in range(SAMPLES_PER_CLASS):
        timestamp = (start_time + timedelta(seconds=i)).strftime("%Y-%m-%d %H:%M:%S")
        accel_x = random.uniform(-0.1, 0.1)
        accel_y = random.uniform(-0.1, 0.1)
        accel_z = 9.81 + random.uniform(-0.1, 0.1)
        gyro_x = random.uniform(-0.5, 0.5)
        gyro_y = random.uniform(-0.5, 0.5)
        gyro_z = random.uniform(-0.5, 0.5)
        soil_moisture = random.uniform(20.0, 60.0)
        rows.append([timestamp, accel_x, accel_y, accel_z, gyro_x, gyro_y, gyro_z, soil_moisture, 0])

    # 2. DATA LONGSOR (Label 1)
    time_offset = SAMPLES_PER_CLASS
    for i in range(SAMPLES_PER_CLASS):
        timestamp = (start_time + timedelta(seconds=time_offset + i)).strftime("%Y-%m-%d %H:%M:%S")
        accel_x = random.uniform(2.0, 5.0)
        accel_y = random.uniform(2.0, 5.0)
        accel_z = 7.0 + random.uniform(-1.0, 1.0)
        gyro_x = random.uniform(15.0, 50.0)
        gyro_y = random.uniform(15.0, 50.0)
        gyro_z = random.uniform(10.0, 30.0)
        soil_moisture = random.uniform(85.0, 100.0)
        rows.append([timestamp, accel_x, accel_y, accel_z, gyro_x, gyro_y, gyro_z, soil_moisture, 1])

    with open(CSV_FILE, mode='w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(HEADER)
        writer.writerows(rows)
    print(f"[✓] Berhasil membuat {len(rows)} baris data di '{CSV_FILE}'\n")

def main():
    # Cek dulu, kalau CSV belum ada, kita buatin otomatis
    if not os.path.exists(CSV_FILE):
        buat_data_dummy_otomatis()
        
    # --- PROSES TRAINING MODEL AI ---
    print("[*] Membaca dataset untuk training...")
    df = pd.read_csv(CSV_FILE)

    # Pisahkan Fitur dan Label
    X = df.drop(columns=["timestamp", "label"])
    y = df["label"]

    # Bagi Data (80% Train, 20% Test)
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

    # Training Model
    print("[*] Memulai proses training AI dengan Random Forest...")
    model = RandomForestClassifier(n_estimators=100, random_state=42)
    model.fit(X_train, y_train)
    print("[✓] Training selesai!")

    # Evaluasi
    y_pred = model.predict(X_test)
    accuracy = accuracy_score(y_test, y_pred)
    print(f"\n[SENSIVITAS AI] Akurasi Model: {accuracy * 100:.2f}%")
    print("\nLaporan Klasifikasi:")
    print(classification_report(y_test, y_pred))

    # Simpan Otak AI (.pkl)
    MODEL_NAME = "landslide_model.pkl"
    joblib.dump(model, MODEL_NAME)
    print(f"[✓] File otak AI berhasil disimpan: '{MODEL_NAME}'")

if __name__ == "__main__":
    main()
