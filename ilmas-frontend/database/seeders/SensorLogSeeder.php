<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SensorLog;

class SensorLogSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚨 Mesin Generator Data Mentah ILMAS Dimulai! (Jeda: 0.5 Detik)');

        while (true) {
            // Generasi angka acak sensor
            $moisture = rand(200, 850) / 10; 
            $accelX = (rand(-200, 200) / 100); 
            $accelY = (rand(-200, 200) / 100);
            $accelZ = 9.81 + (rand(-100, 100) / 100); 
            
            // Set default 0, status aslinya bakal dihitung & di-update langsung oleh script Python .pkl
            $statusAi = 0;

            SensorLog::create([
                'device_id' => 'ESP32_GENERATOR_XYZ',
                'accel_x' => $accelX,
                'accel_y' => $accelY,
                'accel_z' => $accelZ,
                'gyro_x' => rand(-50, 50) / 100,
                'gyro_y' => rand(-50, 50) / 100,
                'gyro_z' => rand(-50, 50) / 100,
                'soil_moisture' => $moisture,
                'status_ai' => $statusAi,
                'timestamp' => now()->setTimezone('Asia/Jakarta')->toDateTimeString()
            ]);

            $this->command->info('Data mentah masuk DB -> Menunggu kalkulasi Otak AI Python...');
            
            // Jeda waktu 0.5 detik menggunakan mikrodetik
            usleep(500000); 
        }
    }
}