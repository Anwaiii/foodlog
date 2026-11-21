<?php

namespace App\Http\Controllers;

use App\Models\ReviewLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewLikeController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'review_id' => 'required|integer|exists:reviews,id',
        ]);

        $memberId = Auth::id();
        $reviewId = (int) $validated['review_id'];

        $existing = ReviewLike::where('member_id', $memberId)
            ->where('review_id', $reviewId)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            ReviewLike::create([
                'member_id' => $memberId,
                'review_id' => $reviewId,
            ]);
            $liked = true;
        }

        $count = ReviewLike::where('review_id', $reviewId)->count();

        return response()->json([
            'review_id'   => $reviewId,
            'liked'       => $liked,
            'likes_count' => $count,
        ]);
    }
}
