import csv
import time
from datetime import datetime
import os

# 1. Tentukan nama file dataset lu
CSV_FILE = "dataset_landslide.csv"

# 2. Definisikan kolom data (Header)
# Kita tambahkan kolom 'label' untuk kebutuhan training AI nanti
HEADER = ["timestamp", "accel_x", "accel_y", "accel_z", "gyro_x", "gyro_y", "gyro_z", "soil_moisture", "label"]

def read_sensor_data():
    """
    Fungsi ini tempat lu naro kode pembacaan sensor asli.
    Untuk sekarang, gua kasi nilai dummy/placeholder buat testing.
    """
    # TODO: Ganti dengan library asli (misal: mpu6050-raspberrypi)
    accel_x = 0.01
    accel_y = -0.02
    accel_z = 9.81  # Kondisi diam, gravitasi bumi ke arah Z
    
    gyro_x = 0.0
    gyro_y = 0.0
    gyro_z = 0.0
    
    soil_moisture = 45.5  # Contoh nilai kelembaban dalam persen
    
    return accel_x, accel_y, accel_z, gyro_x, gyro_y, gyro_z, soil_moisture

def main():
    # Cek apakah file sudah ada, biar nggak nulis header dua kali
    file_exists = os.path.isfile(CSV_FILE)
    
    print(f"[*] Memulai logging data ke {CSV_FILE}...")
    print("[*] Tekan Ctrl+C untuk menghentikan program.")
    
    # Tentukan label kondisi saat ini untuk training AI
    # 0 = Kondisi Aman / Normal
    # 1 = Kondisi Simulasi Longsor / Pergeseran ekstrem
    CURRENT_LABEL = 0
    
    try:
        with open(CSV_FILE, mode='a', newline='', encoding='utf-8') as f:
            writer = csv.writer(f)
            
            # Tulis header jika filenya baru dibuat
            if not file_exists:
                writer.writerow(HEADER)
                f.flush() # Paksa tulis ke disk saat itu juga
                
            while True:
                # Ambil waktu sekarang
                now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                
                # Baca data dari sensor
                ax, ay, az, gx, gy, gz, moisture = read_sensor_data()
                
                # Gabungkan semua data ke dalam satu baris
                row = [now, ax, ay, az, gx, gy, gz, moisture, CURRENT_LABEL]
                
                # Tulis ke CSV
                writer.writerow(row)
                f.flush()  # Memastikan data langsung tersimpan, aman kalau Raspi tiba-tiba mati
                
                print(f"[{now}] Data tersimpan! Label: {CURRENT_LABEL}")
                
                # Interval pengambilan data (misal: setiap 1 detik)
                time.sleep(1.0)
                
    except KeyboardInterrupt:
        print("\n[!] Logging dihentikan oleh user. File tersimpan dengan aman.")

if __name__ == "__main__":
    main()
