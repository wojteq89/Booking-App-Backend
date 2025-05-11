<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TokenController;
use App\Http\Controllers\Business\BusinessController;
use App\Http\Controllers\Business\ServiceItemController;
use App\Http\Controllers\Business\ReviewController;

// Public endpoints ---------------------
  Route::post('/login', [LoginController::class, 'login']);
  Route::post('/register', [RegisterController::class, 'register']);

// Private endpoints ---------------------
  Route::middleware('auth:sanctum')->group(function () {

  // User ---------------------
    Route::get('/user', fn(Request $request) => $request->user());
    Route::get('/check-token-expiry', [TokenController::class, 'checkTokenExpiry']);

  // Business ---------------------
    Route::get('/my-business', [BusinessController::class, 'myService']);
    Route::get('/business/{id}', [BusinessController::class, 'show']);
    Route::post('/business-add', [BusinessController::class, 'store']);
    Route::delete('/business-delete/{id}', [BusinessController::class, 'destroy']);

  // Services ---------------------
    Route::get('/service-items', [ServiceItemController::class, 'index']);
    Route::post('/service-item-add', [ServiceItemController::class, 'store']);
    Route::put('/service-item-update/{id}', [ServiceItemController::class, 'update']);
    Route::delete('/service-item-delete/{id}', [ServiceItemController::class, 'destroy']);
  
  // Reviews ---------------------
    Route::get('/service-reviews/{id}', [ReviewController::class, 'getServiceReviews']);
    Route::post('/reviews-add', [ReviewController::class, 'store']);
    Route::put('/reviews-update/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews-delete/{id}', [ReviewController::class, 'destroy']);

});
