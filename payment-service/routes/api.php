<?php

use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/payments', [PaymentController::class, 'index']);
Route::post('/payments', [PaymentController::class, 'store']);
Route::get('/payments/{id}', [PaymentController::class, 'show']);
Route::put('/payments/{id}', [PaymentController::class, 'update']);
Route::delete('/payments/{id}', [PaymentController::class, 'destroy']);

Route::post('/payments/{id}/pay', [PaymentController::class, 'pay']);
Route::post('/payments/{id}/cancel', [PaymentController::class, 'cancel']);
Route::post('/payments/{id}/refund', [PaymentController::class, 'refund']);
Route::post('/payments/{id}/process', [PaymentController::class, 'processAsync']);

Route::get('/bookings/{bookingId}/payments', [PaymentController::class, 'bookingPayments']);