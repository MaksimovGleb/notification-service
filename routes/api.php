<?php

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/notifications/bulk', [NotificationController::class, 'sendBulk']);
Route::get('/notifications/history/{recipient}', [NotificationController::class, 'getHistory']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
