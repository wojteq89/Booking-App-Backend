<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TokenController extends Controller
{
    public function checkTokenExpiry(Request $request)
    {
        $user = $request->user();

        foreach ($user->tokens as $token) {
            if ($token->expires_at && Carbon::parse($token->expires_at)->isPast()) {
                $token->delete();
            }
        }

        return response()->json(['message' => 'Expired tokens removed'], 200);
    }
}
