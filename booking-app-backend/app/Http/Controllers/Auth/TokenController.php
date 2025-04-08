<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TokenController extends Controller
{
    /**
     * Sprawdzenie, czy token użytkownika wygasł.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkTokenExpiry(Request $request)
    {
        $user = $request->user();
        
        if ($user->currentAccessToken()->expires_at && Carbon::parse($user->currentAccessToken()->expires_at)->isPast()) {
            $user->currentAccessToken()->delete();
            
            return response()->json(['message' => 'Token expired'], 401);
        }

        return response()->json(['message' => 'Token valid'], 200);
    }
}
