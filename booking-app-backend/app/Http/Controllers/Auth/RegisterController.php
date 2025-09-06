<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string',
            'second_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*()\-_=+{};:,<.>]/',
            ],
            'role' => 'nullable|string|in:user,owner,admin',
            'city' => 'required|string',
            'phone_number' => 'required|string',
        ]);

        $role = $validatedData['role'] ?? 'user';

        $user = new User([
            'first_name' => $validatedData['first_name'],
            'second_name' => $validatedData['second_name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'role' => $role,
            'city' => $validatedData['city'],
            'phone_number' => $validatedData['phone_number'],
        ]);

        $user->save();
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['id' => $user->id]
        );

        $user->notify(new VerifyEmailNotification($url));

        return response()->json(['message' => 'User registered. Verification email sent.']);
    }
}
