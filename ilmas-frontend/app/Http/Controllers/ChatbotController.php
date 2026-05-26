<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\SensorLog;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $userMessage = $request->input('message');
        
        // 1. Ambil data sensor paling terakhir dari database lokal
        $current = SensorLog::orderBy('timestamp', 'desc')->first();
        
        // Antisipasi kalau seandainya DB kosong saat pertama kali run
        $moisture = $current ? $current->soil_moisture : 0;
        $accelX = $current ? $current->accel_x : 0;
        
        // Sesuaikan penamaan status tanpa emoji agar sinkron dengan UI SCADA
        $statusText = ($current && $current->status_ai == 1) ? "CRITICAL_WARNING" : "SYSTEM_NORMAL";

        // 2. Racik "System Prompt" - Transformasi menjadi Sistem Pelapor Tanpa Basa-basi
        $systemPrompt = "Anda adalah Core Engine EWS ILMAS. " .
                        "Telemetri: Moisture {$moisture}%, Accel X {$accelX} m/s², Status: {$statusText}. " .
                        "ATURAN MUTLAK: " .
                        "1. DILARANG KERAS menggunakan salam pembuka, penutup, atau basa-basi (seperti 'Sistem mendeteksi', 'Halo', atau 'Silakan'). " .
                        "2. JAWAB SANGAT SINGKAT DAN TEGAS. Maksimal 1 sampai 2 kalimat saja. " .
                        "3. Gunakan nada militer/mesin: faktual, presisi, dingin, dan langsung ke inti (to the point). " .
                        "4. Jangan mengulang data telemetri kecuali ditanya secara spesifik. " .
                        "Jika ada kueri cuaca, jawab: 'Validasi cuaca makro via panel BMKG di navigasi atas.'";

        try {
            // 3. Tembak API Google Gemini
            $apiKey = "AIzaSyBHK7UHG9w1l0IC5i6oRKXAH5Q_-HtE7RU"; 
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key={$apiKey}";
            
            $response = Http::post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $systemPrompt . "\n\nKueri Operator: " . $userMessage]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $aiReply = $response->json()['candidates'][0]['content']['parts'][0]['text'];
                return response()->json(['reply' => $aiReply]);
            } else {
                // Tampilkan error ala terminal console
                $errorAsli = $response->body();
                return response()->json(['reply' => 'System Exception: API response invalid. Detail: ' . $errorAsli]);
            }
            
        } catch (\Exception $e) {
            // Tangkap error koneksi ala log sistem
            return response()->json(['reply' => 'Connection Timeout: Gagal menjalin koneksi ke server AI pusat. Status: ' . $e->getMessage()]);
        }
    }
}