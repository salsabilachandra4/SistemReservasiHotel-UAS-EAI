<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;

Route::post('/rooms/{id}/reserve', [RoomController::class, 'reserve']);
Route::post('/rooms/{id}/release', [RoomController::class, 'release']);
Route::get('/rooms/available', [RoomController::class, 'available']);
Route::get('/rooms/{id}/bookings', [RoomController::class, 'bookings']);

Route::get('/rooms', [RoomController::class, 'index']);
Route::post('/rooms', [RoomController::class, 'store']);
Route::get('/rooms/{id}', [RoomController::class, 'show']);
Route::put('/rooms/{id}', [RoomController::class, 'update']);
Route::delete('/rooms/{id}', [RoomController::class, 'destroy']);
