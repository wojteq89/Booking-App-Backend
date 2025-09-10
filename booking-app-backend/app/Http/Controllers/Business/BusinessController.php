<?php

namespace App\Http\Controllers\Business;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->has('category') && $request->category !== '') {
            $query->where('category', $request->category);
        }

        if ($request->has('search') && $request->search !== '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        $perPage = 10;
        $businesses = $query->paginate($perPage);

        return response()->json($businesses);
    }

    public function nearbyServices(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->city) {
            return response()->json([
                'data' => [],
                'message' => 'Brak miasta dla użytkownika'
            ]);
        }

        $query = Service::query();

        if ($request->has('category') && $request->category !== '') {
            $query->where('category', $request->category);
        }

        if ($request->has('search') && $request->search !== '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        $query->where('location', 'like', '%' . $user->city . '%');

        $perPage = 10;
        $services = $query->paginate($perPage);

        return response()->json($services);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'location' => 'required|string',
            'description' => 'nullable|string',
            'opening_hours' => 'nullable|string',
            'images.*' => 'nullable|image|max:2048',
            'facebook_url' => 'nullable|string|url',
            'instagram_url' => 'nullable|string|url',
            'youtube_url' => 'nullable|string|url',
            'website_url' => 'nullable|string|url',
        ]);

        $folderName = Str::slug($request->name) . '-' . time();

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store("business/{$folderName}", 'public');
                $imagePaths[] = Storage::url($path);
            }
        }

        $service = new Service();
        $service->name = $request->name;
        $service->category_id = $request->category_id;
        $service->location = $request->location;
        $service->description = $request->description;
        $service->opening_hours = $request->opening_hours;
        $service->images = json_encode($imagePaths);
        $service->facebook_url = $request->facebook_url;
        $service->instagram_url = $request->instagram_url;
        $service->youtube_url = $request->youtube_url;
        $service->website_url = $request->website_url;
        $service->user_id = Auth::id();
        $service->save();

        $user = Auth::user();
        $user->role = 'owner';
        $user->save();

        return response()->json(['message' => 'Business added', 'business' => $service], 201);
    }

    public function show(string $id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Business not found'], 404);
        }

        return response()->json(['business' => $service]);
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        if ($service->user_id !== Auth::id()) {
            return response()->json(['message' => 'No permission'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'location' => 'required|string',
            'description' => 'nullable|string',
            'opening_hours' => 'nullable|string',
            'images.*' => 'nullable|image|max:2048',
            'existing_images' => 'nullable|string',
            'facebook_url' => 'nullable|string|url',
            'instagram_url' => 'nullable|string|url',
            'youtube_url' => 'nullable|string|url',
            'website_url' => 'nullable|string|url',
        ]);

        $service->name = $request->name;
        $service->category_id = $request->category_id;
        $service->location = $request->location;
        $service->description = $request->description;
        $service->opening_hours = $request->opening_hours;
        $service->facebook_url = $request->facebook_url;
        $service->instagram_url = $request->instagram_url;
        $service->youtube_url = $request->youtube_url;
        $service->website_url = $request->website_url;

        $allImages = [];
        if ($request->existing_images) {
            $existing = json_decode($request->existing_images, true);
            if (is_array($existing)) {
                $allImages = $existing;
            }
        }

        if ($request->hasFile('images')) {
            $folderName = Str::slug($request->name) . '-' . time();
            foreach ($request->file('images') as $image) {
                $path = $image->store("business/{$folderName}", 'public');
                $allImages[] = Storage::url($path);
            }
        }

        $service->images = json_encode($allImages);

        $service->save();

        return response()->json(['message' => 'Business updated', 'business' => $service]);
    }

    public function destroy(string $id)
    {
        $service = Service::findOrFail($id);

        if ($service->user_id !== Auth::id()) {
            return response()->json(['message' => 'No permission'], 403);
        }

        $service->delete();

        $user = Auth::user();
        $hasOtherServices = Service::where('user_id', $user->id)->exists();

        if (!$hasOtherServices) {
            $user->role = 'user';
            $user->save();
        }

        return response()->json(['message' => 'Business deleted']);
    }

    public function myService()
    {
        $user = Auth::user();

        if ($user->role !== 'owner') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $service = Service::where('user_id', $user->id)->first();

        if (!$service) {
            return response()->json(['message' => 'No business found for this user'], 404);
        }

        return response()->json(['business' => $service]);
    }
}
