<?php

namespace App\Http\Controllers\Services;
use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'location' => 'required|string',
            'description' => 'nullable|string',
            'opening_hours' => 'nullable|string',
            'images' => 'nullable|array',
        ]);
    
        $service = new Service();
        $service->name = $request->name;
        $service->category = $request->category;
        $service->location = $request->location;
        $service->description = $request->description;
        $service->opening_hours = $request->opening_hours;
        $service->images = $request->images ? json_encode($request->images) : null;
        $service->user_id = Auth::id();
        $service->save();
    
        $user = Auth::user();
        $user->role = 'owner';
        $user->save();
    
        return response()->json(['message' => 'Usługa dodana', 'service' => $service], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $service = Service::findOrFail($id);
    
        if ($service->user_id !== Auth::id()) {
            return response()->json(['message' => 'Brak dostępu'], 403);
        }
    
        $service->delete();
    
        $user = Auth::user();
        $hasOtherServices = Service::where('user_id', $user->id)->exists();
    
        if (!$hasOtherServices) {
            $user->role = 'user';
            $user->save();
        }
    
        return response()->json(['message' => 'Usługa usunięta']);
    }    
}
