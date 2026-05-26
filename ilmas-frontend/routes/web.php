<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// Arahkan halaman utama ('/') langsung ke fungsi index di DashboardController
Route::get('/', [DashboardController::class, 'index']);