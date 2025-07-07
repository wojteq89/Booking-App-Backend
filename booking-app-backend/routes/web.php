<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

  Route::get('/reset-password/{token}', function (string $token, Request $request) {
      return view('auth.reset-password', [
          'token' => $token,
          'errors' => session('errors') ?? new \Illuminate\Support\ViewErrorBag, // dodaj, aby $errors było dostępne
      ]);
  })->name('password.reset');

Route::get('/email/verify/{id}', function ($id, Request $request) {
    if (! $request->hasValidSignature()) {
        abort(401, 'Link jest nieprawidłowy lub wygasł.');
    }

    $user = User::findOrFail($id);

    if (! $user->is_email_verified) {
        $user->is_email_verified = true;
        $user->save();
    }

    return view('emails.email_verified');
})->name('verification.verify');