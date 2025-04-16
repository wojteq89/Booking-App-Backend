<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TokenController extends Controller
{
    /**
     * Sprawdzenie, czy tokeny użytkownika wygasły i usunięcie tych, które wygasły.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkTokenExpiry(Request $request)
    {
        $user = $request->user();

        // Iterujemy po wszystkich tokenach użytkownika
        foreach ($user->tokens as $token) {
            // Sprawdzamy, czy token ma ustawioną datę wygaśnięcia i czy jest już po dacie wygaśnięcia
            if ($token->expires_at && Carbon::parse($token->expires_at)->isPast()) {
                // Usuwamy token, który wygasł
                $token->delete();
            }
        }

        return response()->json(['message' => 'Expired tokens removed'], 200);
    }
}
