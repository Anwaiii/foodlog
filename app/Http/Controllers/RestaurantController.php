<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Restaurant;
use App\Models\Review;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RestaurantController extends Controller
{
    private string $uploadDir;
    private string $uploadPath = 'assets/images/';

    public function __construct()
    {
        $this->uploadDir = public_path('assets/images/');
    }

    private function requireAdmin(): ?JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        return null;
    }

    public function index(Request $request): JsonResponse
    {
        $userFavIds = Auth::check()
            ? Favorite::where('member_id', Auth::id())->pluck('restaurant_id')->flip()->all()
            : [];

        // トップ画面の横断キーワード検索用テキストを 1 クエリで集約（N+1 HTTP リクエスト解消）
        $reviewTextMap = [];
        Review::query()
            ->select('restaurant_id', 'order_details', 'impression')
            ->get()
            ->each(function ($rv) use (&$reviewTextMap) {
                $rid = (int) $rv->restaurant_id;
                $chunk = trim(($rv->order_details ?? '') . ' ' . ($rv->impression ?? ''));
                if ($chunk !== '') {
                    $reviewTextMap[$rid][] = $chunk;
                }
            });

        $restaurants = Restaurant::query()
            ->withCount(['reviews', 'favorites'])
            ->withAvg('reviews as avg_rating', 'rating')
            ->withMax('reviews as latest_date', 'date')
            ->orderBy('created_at', 'ASC')
            ->get()
            ->map(function ($r) use ($userFavIds, $reviewTextMap) {
                $data = $r->toArray();
                $data['review_count']       = $r->reviews_count;
                $data['avg_rating']         = $r->avg_rating ? round((float)$r->avg_rating, 1) : 0;
                $data['favorites_count']    = $r->favorites_count;
                $data['is_favorited']       = isset($userFavIds[$r->id]);
                $data['review_search_text'] = isset($reviewTextMap[$r->id])
                    ? implode(' ', $reviewTextMap[$r->id])
                    : '';
                return $data;
            });

        return response()->json($restaurants);
    }

    public function show(int $id): JsonResponse
    {
        $restaurant = Restaurant::query()
            ->withCount(['reviews', 'favorites'])
            ->withAvg('reviews as avg_rating', 'rating')
            ->withMax('reviews as latest_date', 'date')
            ->where('id', $id)
            ->first();

        if (!$restaurant) {
            return response()->json(['error' => 'Restaurant not found'], 404);
        }

        $data = $restaurant->toArray();
        $data['review_count']    = $restaurant->reviews_count;
        $data['avg_rating']      = $restaurant->avg_rating ? round((float)$restaurant->avg_rating, 1) : 0;
        $data['favorites_count'] = $restaurant->favorites_count;
        $data['is_favorited']    = Auth::check()
            ? Favorite::where('member_id', Auth::id())->where('restaurant_id', $restaurant->id)->exists()
            : false;

        return response()->json($data);
    }

    public function store(Request $request): JsonResponse
    {
        if ($err = $this->requireAdmin()) return $err;

        $validated = $request->validate([
            'name'        => 'required|string|max:50',
            'category'    => 'required|string|max:30',
            'area'        => 'nullable|string|max:50',
            'price_range' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:200',
            'address'     => 'nullable|string|max:100',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $imagePath = $this->handleUpload($request);

        $restaurant = Restaurant::create([
            'name'        => $validated['name'],
            'category'    => $validated['category'],
            'area'        => ($validated['area'] ?? '') !== '' ? trim($validated['area']) : null,
            'price_range' => ($validated['price_range'] ?? '') !== '' ? trim($validated['price_range']) : null,
            'description' => $validated['description'] ?? '',
            'address'     => $validated['address'] ?? null,
            'image'       => $imagePath,
        ]);

        return $this->show($restaurant->id);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if ($err = $this->requireAdmin()) return $err;

        $restaurant = Restaurant::find($id);
        if (!$restaurant) {
            return response()->json(['error' => 'Restaurant not found'], 404);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:50',
            'category'    => 'required|string|max:30',
            'area'        => 'nullable|string|max:50',
            'price_range' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:200',
            'address'     => 'nullable|string|max:100',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $updateData = [
            'name'        => $validated['name'],
            'category'    => $validated['category'],
            'area'        => ($validated['area'] ?? '') !== '' ? trim($validated['area']) : null,
            'price_range' => ($validated['price_range'] ?? '') !== '' ? trim($validated['price_range']) : null,
            'description' => $validated['description'] ?? '',
            'address'     => $validated['address'] ?? null,
        ];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $this->handleUpload($request);
            if ($imagePath) {
                $updateData['image'] = $imagePath;
            }
        }

        $restaurant->update($updateData);

        return $this->show($restaurant->id);
    }

    public function destroy(int $id): JsonResponse
    {
        if ($err = $this->requireAdmin()) return $err;

        $restaurant = Restaurant::find($id);
        if (!$restaurant) {
            return response()->json(['error' => 'Restaurant not found'], 404);
        }

        $deleted = $restaurant->delete();
        return response()->json(['deleted' => $deleted ? $id : null]);
    }

    private function handleUpload(Request $request): ?string
    {
        if (!$request->hasFile('image') || !$request->file('image')->isValid()) {
            return null;
        }

        return ImageOptimizer::saveOptimized(
            $request->file('image'),
            $this->uploadDir,
            $this->uploadPath,
            'restaurant',
            1200,
            false,
            82
        );
    }
}
