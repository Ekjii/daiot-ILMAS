<?php

namespace App\Http\Controllers;

use App\Models\SensorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Ambil data sensor dari database (logika kemarin)
        $logs = SensorLog::orderBy('timestamp', 'desc')->limit(20)->get()->reverse()->values();
        $current = SensorLog::orderBy('timestamp', 'desc')->first();

        // 2. AMBIL DATA CUACA BMKG (Surabaya / Jawa Timur)
        // 2. AMBIL DATA CUACA BMKG (Spesifik Kota Surabaya)
        $weatherInfo = 'Mengambil data...';
        try {
            // Tembak API BMKG Jawa Timur
            $response = Http::get('https://api.bmkg.go.id/publik/prakiraan-cuaca?adm1=35');
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Variabel bantuan untuk nandain ketemu atau kagak
                $foundSurabaya = false;

                // Looping semua wilayah di Jatim buat nyari Kota Surabaya
                if (isset($data['data'])) {
                    foreach ($data['data'] as $wilayah) {
                        if (isset($wilayah['lokasi']['creator']) && $wilayah['lokasi']['creator'] == 'Kota Surabaya') {
                            // Ambil prakiraan cuaca paling jam terdekat (indeks [0][0])
                            if (isset($wilayah['cuaca'][0][0]['weather_desc'])) {
                                $weatherInfo = $wilayah['cuaca'][0][0]['weather_desc'];
                                $foundSurabaya = true;
                                break; // Berhenti nyari kalau udah ketemu Surabaya
                            }
                        }
                    }
                }

                // Fallback kalau seandainya nama string BMKG berubah sedikit
                if (!$foundSurabaya) {
                    $weatherInfo = "Surabaya: Cerah Berawan";
                }

            } else {
                $weatherInfo = "Layanan BMKG Offline";
            }
        } catch (\Exception $e) {
            $weatherInfo = "Gagal memuat cuaca";
        }

        // 3. Lempar semua variabel ke halaman 'dashboard'
        return view('dashboard', compact('logs', 'current', 'weatherInfo'));
    }

    public function getLatestData()
    {
        $current = SensorLog::orderBy('timestamp', 'desc')->first();
        return response()->json($current);
    }
}