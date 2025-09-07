<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\URL;

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

        if ($user && is_null($user->is_email_verified)) {
            $url = URL::temporarySignedRoute(
                'verification.verify',
                now()->addHours(24),
                ['id' => $user->id]
            );
            $user->notify(new VerifyEmailNotification($url));
            return response()->json([
                'message' => 'Email not verified. Verification email has been resent.',
                'status' => 'email_not_verified'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $expiresAt = Carbon::now()->addMinutes(60);
        $user->tokens()->where('id', $user->tokens()->latest()->first()->id)->update(['expires_at' => $expiresAt]);

        return response()->json([
            'token' => $token,
        ]);
    }
}
