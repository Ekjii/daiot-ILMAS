<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatbotController;

// Bikin jalur /api/sensor-now yang manggil fungsi 'getLatestData' di Controller
Route::get('/sensor-now', [DashboardController::class, 'getLatestData']);
Route::post('/chatbot', [ChatbotController::class, 'ask']);