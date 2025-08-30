<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\Service;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class FavoritesController extends Controller
{
    public function toggle(Request $request, Service $service)
    {
        $user = $request->user();

        if ($user->favoriteServices()->where('service_id', $service->id)->exists()) {
            $user->favoriteServices()->detach($service->id);
            return response()->json(['favorited' => false]);
        } else {
            $user->favoriteServices()->attach($service->id);
            return response()->json(['favorited' => true]);
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $favorites = $user->favoriteServices()
            ->latest('favorites.created_at')
            ->get();

        $simplified = $favorites->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'category' => $service->category,
                'location' => $service->location,
                'images' => $service->images,
                'description' => $service->description,
                'is_favorite' => true,
            ];
        });

        return response()->json($simplified);
    }

    public function isFavorite(Request $request, Service $service)
    {
        $user = $request->user();
        $isFavorite = $user->favoriteServices()->where('service_id', $service->id)->exists();

        return response()->json(['favorited' => $isFavorite]);
    }
}
