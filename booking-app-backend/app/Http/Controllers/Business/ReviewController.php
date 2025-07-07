<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * Store a newly created review.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'user_id' => 'required|exists:users,id',
            'client_name' => 'required|string|max:255',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $existingReview = Review::where('service_id', $validated['service_id'])
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'Użytkownik już dodał opinię dla tej usługi.'
            ], 422);
        }

        $review = Review::create($validated);

        return response()->json(['review' => $review], 201);
    }


    /**
     * Update the specified review.
     */
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $validated = $request->validate([
            'client_name' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'rating' => 'sometimes|required|integer|min:1|max:5',
        ]);

        $review->update($validated);

        return response()->json(['review' => $review], 200);
    }

    /**
     * Remove the specified review.
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return response()->json(['message' => 'Review deleted'], 200);
    }

    /**
     * Get all reviews for a specific service.
     */
    public function getServiceReviews($id)
    {
        $reviews = \App\Models\Review::where('service_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        $count = $reviews->count();
        $average = round($reviews->avg('rating'), 2);
        $ratingsCount = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];

        return response()->json([
            'reviews' => $reviews,
            'total' => $count,
            'average_rating' => $average,
            'ratings_breakdown' => $ratingsCount,
        ]);
    }
}
