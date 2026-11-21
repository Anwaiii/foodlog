<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'restaurant_id' => 'required|integer|exists:restaurants,id',
        ]);

        $memberId = Auth::id();
        $restaurantId = (int) $validated['restaurant_id'];

        $existing = Favorite::where('member_id', $memberId)
            ->where('restaurant_id', $restaurantId)
            ->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
        } else {
            Favorite::create([
                'member_id'     => $memberId,
                'restaurant_id' => $restaurantId,
            ]);
            $favorited = true;
        }

        $count = Favorite::where('restaurant_id', $restaurantId)->count();

        return response()->json([
            'restaurant_id'   => $restaurantId,
            'favorited'       => $favorited,
            'favorites_count' => $count,
        ]);
    }
}
