<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TokenController;
use App\Http\Controllers\Services\ServiceController;

// Public endpoints ---------------------
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

// Private endpoints ---------------------
Route::middleware('auth:sanctum')->group(function () {

  // User ---------------------
    Route::get('/user', fn(Request $request) => $request->user());
    Route::get('/check-token-expiry', [TokenController::class, 'checkTokenExpiry']);

  // Services ---------------------
    Route::post('/services-add', [ServiceController::class, 'store']);
    Route::delete('/services-delete/{id}', [ServiceController::class, 'destroy']);
});
