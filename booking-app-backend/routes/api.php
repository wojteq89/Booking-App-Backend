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
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\User\FavoritesController;
use App\Http\Controllers\CategoryController;

// Public endpoints ---------------------
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);

// Private endpoints ---------------------
Route::middleware('auth:sanctum')->group(function () {

  // User ---------------------
  Route::get('/user', fn(Request $request) => $request->user());
  Route::get('/check-token-expiry', [TokenController::class, 'checkTokenExpiry']);

  // Business ---------------------
  Route::get('/my-business', [BusinessController::class, 'myService']);
  Route::get('/businesses-all', [BusinessController::class, 'index']);
  Route::get('/business/{id}', [BusinessController::class, 'show']);
  Route::post('/business-add', [BusinessController::class, 'store']);
  Route::post('/business-update/{id}', [BusinessController::class, 'update']);
  Route::delete('/business-delete/{id}', [BusinessController::class, 'destroy']);

  // Categories ---------------------
  Route::get('/categories', [CategoryController::class, 'index']);


  // Services ---------------------
  Route::get('/service-items/{service_id}', [ServiceItemController::class, 'index']); // Zmieniona trasa
  Route::post('/service-item-add', [ServiceItemController::class, 'store']);
  Route::put('/service-item-update/{id}', [ServiceItemController::class, 'update']);
  Route::delete('/service-item-delete/{id}', [ServiceItemController::class, 'destroy']);

  // Reviews ---------------------
  Route::get('/service-reviews/{id}', [ReviewController::class, 'getServiceReviews']);
  Route::post('/reviews-add', [ReviewController::class, 'store']);
  Route::put('/reviews-update/{id}', [ReviewController::class, 'update']);
  Route::delete('/reviews-delete/{id}', [ReviewController::class, 'destroy']);

  // Favorites ---------------------
  Route::get('/favorites', [FavoritesController::class, 'index']);
  Route::post('/favorites/{service}/toggle', [FavoritesController::class, 'toggle']);
  Route::get('/favorites/{service}/is-favorite', [FavoritesController::class, 'isFavorite']);

});
