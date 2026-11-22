<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * 統計ダッシュボード用の全集計データを 1 回のリクエスト（SQL 集計）で返却する
     */
    public function index(): JsonResponse
    {
        $restaurantCount = Restaurant::count();

        $reviewStats = Review::query()
            ->selectRaw('COUNT(*) as total_reviews, AVG(CASE WHEN rating > 0 THEN rating END) as avg_rating, MAX(date) as latest_date')
            ->first();

        $totalReviews = (int) ($reviewStats->total_reviews ?? 0);
        $avgRating    = $reviewStats->avg_rating !== null ? round((float) $reviewStats->avg_rating, 1) : null;
        $latestDate   = $reviewStats->latest_date ?: null;

        // 直近6か月の月別レビュー数
        $now = Carbon::now()->startOfMonth();
        $startDate = $now->copy()->subMonths(5)->format('Y-m-d');

        $monthlyMap = Review::query()
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as ym, COUNT(*) as cnt")
            ->where('date', '>=', $startDate)
            ->groupBy('ym')
            ->pluck('cnt', 'ym')
            ->all();

        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i);
            $key = $d->format('Y-m');
            $monthly[] = [
                'key'   => $key,
                'year'  => (int) $d->format('Y'),
                'month' => (int) $d->format('n'),
                'count' => (int) ($monthlyMap[$key] ?? 0),
            ];
        }

        // カテゴリ別店舗数
        $categories = Restaurant::query()
            ->selectRaw("COALESCE(NULLIF(category, ''), 'Other') as category, COUNT(*) as count")
            ->groupBy('category')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category,
                'count'    => (int) $row->count,
            ])
            ->values();

        // 高評価レストラン TOP 5（評価ありのみ・平均評価降順、同点時レビュー数降順）
        $ranking = Restaurant::query()
            ->select('restaurants.id', 'restaurants.name', 'restaurants.image')
            ->join('reviews', 'reviews.restaurant_id', '=', 'restaurants.id')
            ->groupBy('restaurants.id', 'restaurants.name', 'restaurants.image')
            ->selectRaw('COUNT(reviews.id) as review_count, AVG(CASE WHEN reviews.rating > 0 THEN reviews.rating END) as avg_rating, SUM(CASE WHEN reviews.rating > 0 THEN 1 ELSE 0 END) as rated_count')
            ->havingRaw('SUM(CASE WHEN reviews.rating > 0 THEN 1 ELSE 0 END) > 0')
            ->orderByDesc('avg_rating')
            ->orderByDesc('review_count')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'id'           => $r->id,
                'name'         => $r->name,
                'image'        => $r->image,
                'avg_rating'   => round((float) $r->avg_rating, 1),
                'review_count' => (int) $r->review_count,
            ])
            ->values();

        return response()->json([
            'summary' => [
                'restaurant_count' => $restaurantCount,
                'review_count'     => $totalReviews,
                'avg_rating'       => $avgRating,
                'latest_date'      => $latestDate,
            ],
            'monthly'    => $monthly,
            'categories' => $categories,
            'ranking'    => $ranking,
        ]);
    }
}
