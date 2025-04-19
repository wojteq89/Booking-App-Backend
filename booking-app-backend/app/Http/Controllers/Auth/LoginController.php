<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Tworzenie tokena
        $token = $user->createToken('auth_token')->plainTextToken;

        // Ustalenie daty wygaśnięcia tokena
        $expiresAt = Carbon::now()->addMinutes(60);

        // Zapisanie daty wygaśnięcia tylko dla tego tokena
        $user->tokens()->where('id', $user->tokens()->latest()->first()->id)->update(['expires_at' => $expiresAt]);

        return response()->json([
            'token' => $token,
        ]);
    }
}
