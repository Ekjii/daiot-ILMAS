<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorLog extends Model
{
    // KUNCI MATI: Paksa model ini menggunakan koneksi pgsql (PostgreSQL)
    protected $connection = 'pgsql';

    // Kasih tahu Laravel nama tabel asli buatan Taya
    protected $table = 'sensor_logs';
    
    // Matikan created_at & updated_at bawaan Laravel karena Taya pakai format sendiri
    public $timestamps = false; 
    
    protected $guarded = [];
}