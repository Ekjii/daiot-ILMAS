import os
import csv
import random
import pandas as pd
from datetime import datetime, timedelta
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import classification_report, accuracy_score
import joblib

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
CSV_FILE = os.path.join(BASE_DIR, "synthetic_landslide_data.csv")
# Pastikan model di-save langsung ke folder AI Engine
MODEL_PATH = os.path.join(BASE_DIR, "../AI Engine/landslide_model.pkl")

HEADER = ["timestamp", "accel_x", "accel_y", "accel_z", "gyro_x", "gyro_y", "gyro_z", "soil_moisture", "label"]
SAMPLES_PER_CLASS = 2500

# NOTE UNTUK DEVI:
# Saat ini kita masih pakai Random Forest untuk prototype agar sistem jalan.
# Nanti framework LSTM-SOS bisa lu kembangkan menggantikan logic Classifier di bawah ini.

def buat_data_dummy_otomatis():
    # ... (ISI FUNGSI INI SAMA PERSIS SEPERTI SEBELUMNYA, TIDAK DIUBAH) ...
    # Gua potong biar gak kepanjangan, lu pakai yang asli lu aja.
    pass

def main():
    if not os.path.exists(CSV_FILE):
        print("[!] Dataset belum ada. Generate pakai generate_dummy.py dulu.")
        return
        
    print("[*] Membaca dataset untuk training...")
    df = pd.read_csv(CSV_FILE)

    X = df.drop(columns=["timestamp", "label"])
    y = df["label"]

    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

    print("[*] Memulai proses training AI (Placeholder untuk LSTM-SOS)...")
    model = RandomForestClassifier(n_estimators=100, random_state=42)
    model.fit(X_train, y_train)
    print("[✓] Training selesai!")

    y_pred = model.predict(X_test)
    accuracy = accuracy_score(y_test, y_pred)
    print(f"\n[SENSIVITAS AI] Akurasi Model: {accuracy * 100:.2f}%")
    
    # Pastikan folder AI Engine ada sebelum nge-save model
    os.makedirs(os.path.dirname(MODEL_PATH), exist_ok=True)
    joblib.dump(model, MODEL_PATH)
    print(f"[✓] File otak AI berhasil diupdate dan disimpan ke: '{MODEL_PATH}'")

if __name__ == "__main__":
    main()
