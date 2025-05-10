<?php
namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceItem;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class ServiceItemController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $items = ServiceItem::whereHas('service', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        return response()->json(['services' => $items]);

    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'duration' => 'nullable|integer',
        ]);

        $service = Service::findOrFail($request->service_id);

        if ($service->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item = ServiceItem::create($request->all());

        return response()->json($item, 201);
    }

    public function update(Request $request, $id)
    {
        $item = ServiceItem::with('service')->findOrFail($id);

        if ($item->service->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item->update($request->only(['name', 'description', 'price', 'duration']));

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = ServiceItem::with('service')->findOrFail($id);

        if ($item->service->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted']);
    }
}
