<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

  Route::get('/reset-password/{token}', function (string $token, Request $request) {
      return view('auth.reset-password', [
          'token' => $token,
          'errors' => session('errors') ?? new \Illuminate\Support\ViewErrorBag, // dodaj, aby $errors było dostępne
      ]);
  })->name('password.reset');