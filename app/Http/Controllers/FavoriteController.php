<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(): View
    {
        $memberId = Auth::id();

        $favIds = Favorite::where('member_id', $memberId)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->pluck('restaurant_id')
            ->all();

        $restaurants = collect();
        if (!empty($favIds)) {
            $orderMap = array_flip($favIds);
            $restaurants = Restaurant::query()
                ->withCount('reviews')
                ->withAvg('reviews as avg_rating', 'rating')
                ->whereIn('id', $favIds)
                ->get()
                ->sortBy(fn ($r) => $orderMap[$r->id] ?? PHP_INT_MAX)
                ->values()
                ->map(function ($r) {
                    return [
                        'id'           => $r->id,
                        'name'         => $r->name,
                        'category'     => $r->category,
                        'description'  => $r->description,
                        'address'      => $r->address,
                        'image'        => $r->image,
                        'review_count' => $r->reviews_count,
                        'avg_rating'   => $r->avg_rating ? round((float) $r->avg_rating, 1) : 0,
                    ];
                });
        }

        return view('favorites.index', [
            'favoritesPayload' => $restaurants,
        ]);
    }

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

        return response()->json([
            'restaurant_id' => $restaurantId,
            'favorited'     => $favorited,
        ]);
    }
}
