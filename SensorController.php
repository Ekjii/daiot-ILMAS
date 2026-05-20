<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SensorController extends Controller
{
    public function index()
    {
        // Mengambil 20 data terbaru dari tabel sensor_logs milik Taya
        $logs = DB::table('sensor_logs')
                  ->orderBy('timestamp', 'desc')
                  ->limit(20)
                  ->get()
                  ->reverse(); // Dibalik supaya urutan grafik jalan dari kiri ke kanan

        // Ambil data paling terakhir untuk info card status
        $currentStatus = DB::table('sensor_logs')->orderBy('timestamp', 'desc')->first();

        return view('dashboard', compact('logs', 'currentStatus'));
    }
}
