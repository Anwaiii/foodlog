<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    private function ensureAdmin(): void
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, '管理者権限が必要です。');
        }
    }

    public function index(Request $request): View
    {
        $this->ensureAdmin();

        $keyword = trim((string) $request->query('q', ''));
        $rating  = (string) $request->query('rating', 'all');

        $query = Review::with(['restaurant:id,name', 'member:id,nickname,member_code,avatar'])
            ->withCount('likes')
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($keyword !== '') {
            $like = '%' . $keyword . '%';
            $query->where(function ($q) use ($like) {
                $q->where('order_details', 'like', $like)
                  ->orWhere('impression', 'like', $like)
                  ->orWhereHas('restaurant', fn ($rq) => $rq->where('name', 'like', $like))
                  ->orWhereHas('member', fn ($mq) => $mq->where('nickname', 'like', $like)->orWhere('member_code', 'like', $like));
            });
        }

        if (in_array($rating, ['1', '2', '3', '4', '5'], true)) {
            $query->where('rating', (int) $rating);
        }

        $reviews = $query->paginate(20)->withQueryString();
        $totalAll = Review::count();

        return view('admin.reviews', [
            'reviews'  => $reviews,
            'totalAll' => $totalAll,
            'keyword'  => $keyword,
            'rating'   => $rating,
        ]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->ensureAdmin();

        $review = Review::findOrFail($id);
        $review->delete();

        return back()->with('status', __('レビューを削除しました。'));
    }
}
