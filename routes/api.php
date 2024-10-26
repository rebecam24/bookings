<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PlaceController;
use App\Http\Controllers\API\BookingController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function() {
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::post('places', [PlaceController::class, 'store']); 
    Route::put('places/{id}', [PlaceController::class, 'update']); 
    Route::delete('places/{id}', [PlaceController::class, 'destroy']); 
});

Route::middleware(['auth:api', 'role:user|admin'])->group(function () {
    Route::get('places', [PlaceController::class, 'index']); 
    Route::get('places/{id}', [PlaceController::class, 'show']);
    Route::apiResource('bookings', BookingController::class);
    Route::get('/filter-places', [PlaceController::class, 'filter']);
    Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::get('/places/{place_id}/booked-schedule', [BookingController::class, 'getBookedSchedule']);
});