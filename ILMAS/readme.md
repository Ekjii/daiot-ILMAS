# ILMAS: Sistem Pendeteksi Dini Tanah Longsor Menggunakan Multisensor Berbasis Algoritma LSTM-SOS

## 1. Nama Kelompok dan Tugas
* **Taya** - Hardware Engineer & Cloud Infra (Sensor + ESP32, Setup VM, Database PostgreSQL & Metabase)
* **Devi** - AI Engineer & Middleware (ML Training AI (LSTM-SOS), n8n, MQTT Architecture)
* **Xyz Frizy Firstyaji** - Backend Engineer & Integrasi (Data API BMKG, Telegram Alert System)
* **Sayyid** - 3D Designer & Hardware Provisioning (Desain Casing, Maket, Kebutuhan Hardware)

## 2. Deskripsi
**ILMAS (Intelligent Landslide Mitigation & Alert System)** adalah sistem peringatan dini tanah longsor berbasis Internet of Things (IoT) dan Artificial Intelligence (AI). Sistem ini menggunakan pembacaan multisensor (kemiringan lahan dan kelembaban tanah) yang diolah menggunakan algoritma *Long Short Term Memory - Symbiotic Organism Search (LSTM-SOS)*. 

Sistem ini diperkuat dengan integrasi data curah hujan secara *real-time* dari API BMKG sebagai variabel pendukung keputusan. Jika sistem mendeteksi potensi bahaya longsor, notifikasi peringatan SOS akan dikirimkan secara otomatis melalui Telegram, dan perintah aktuasi darurat dikirim kembali ke perangkat lapangan.

## 3. Flow System
1. **Edge Device (ESP32):** Membaca data multisensor dan mempublikasikan payload JSON ke broker MQTT.
2. **Middleware & AI (MQTT & n8n/Python):** Menjembatani aliran data secara *real-time*, menarik data API Curah Hujan BMKG, dan memproses prediksi dengan model LSTM-SOS.
3. **Cloud & Storage (VM & PostgreSQL):** Mengingesti dan menyimpan seluruh log sensor dan hasil prediksi AI ke dalam tabel *time-series*.
4. **Automated Alert:** Jika status berubah menjadi "Bahaya", sistem otomatis menembak *bot* Telegram untuk mengirim *alert* darurat kepada tim terkait.
5. **Dashboard (Metabase & Web Frontend):** Memvisualisasikan data sensor, status cuaca BMKG, dan status AI secara *live* dari database.

## 4. Foto Alat
*(TBA - Insert foto rangkaian ESP32, Sensor, Casing, dan Maket di sini setelah selesai dirakit)*

## 5. Tech Stack yang Digunakan
* **Hardware:** ESP32, MPU6050, Soil Moisture Sensor, Buzzer/Relay
* **AI & Middleware:** Algoritma LSTM-SOS, Python, n8n, Paho-MQTT
* **Integrasi External:** API Cuaca Digital BMKG, Telegram Bot API
* **Database & Visualisasi:** VM (Virtual Machine), PostgreSQL, Metabase
* **Frontend:** HTML, Vanilla JS/Vue, Chart.js

## 6. Video Demo
*(TBA - Insert link Google Drive video presentasi maket & demo alat di sini)*
