-- 1. Buat tabel utama untuk menampung log sensor dan hasil prediksi AI
CREATE TABLE sensor_logs (
    id SERIAL PRIMARY KEY,
    timestamp TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    device_id VARCHAR(50) NOT NULL,
    
    -- Data Accelerometer (MPU6050)
    accel_x REAL NOT NULL,
    accel_y REAL NOT NULL,
    accel_z REAL NOT NULL,
    
    -- Data Gyroscope (MPU6050)
    gyro_x REAL NOT NULL,
    gyro_y REAL NOT NULL,
    gyro_z REAL NOT NULL,
    
    -- Data Kelembaban Tanah
    soil_moisture REAL NOT NULL,
    
    -- Hasil Prediksi AI (0 = Aman, 1 = Bahaya Longsor)
    status_ai INT NOT NULL
);

-- 2. Buat Index pada kolom timestamp
-- Ini WAJIB hukumnya untuk data IoT agar dashboard Metabase lu tidak lemot saat datanya sudah jutaan baris
CREATE INDEX idx_sensor_logs_timestamp ON sensor_logs (timestamp DESC);

-- 3. Buat Index pada kolom status_ai
-- Berguna untuk filtering cepat di Metabase jika ingin melihat riwayat kejadian longsor saja
CREATE INDEX idx_sensor_logs_status ON sensor_logs (status_ai);
