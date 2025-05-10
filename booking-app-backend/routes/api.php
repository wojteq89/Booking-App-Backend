<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TokenController;
use App\Http\Controllers\Business\BusinessController;

// Public endpoints ---------------------
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

// Private endpoints ---------------------
Route::middleware('auth:sanctum')->group(function () {

  // User ---------------------
    Route::get('/user', fn(Request $request) => $request->user());
    Route::get('/check-token-expiry', [TokenController::class, 'checkTokenExpiry']);

  // Services ---------------------
    Route::post('/business-add', [BusinessController::class, 'store']);
    Route::delete('/business-delete/{id}', [BusinessController::class, 'destroy']);
    Route::get('/my-business', [BusinessController::class, 'myService']);
    Route::get('/business/{id}', [BusinessController::class, 'show']);
});
