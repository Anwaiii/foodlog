<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RestaurantController extends Controller
{
    private string $uploadDir;
    private string $uploadPath = 'assets/images/';

    public function __construct()
    {
        $this->uploadDir = public_path('assets/images/');
    }

    public function index(Request $request): JsonResponse
    {
        $restaurants = Restaurant::query()
            ->withCount('reviews')
            ->withMax('reviews as latest_date', 'date')
            ->orderBy('created_at', 'ASC')
            ->get()
            ->map(function ($r) {
                // 旧コード互換の review_count (エイリアス)
                $data = $r->toArray();
                $data['review_count'] = $r->reviews_count;
                return $data;
            });

        return response()->json($restaurants);
    }

    public function show(int $id): JsonResponse
    {
        $restaurant = Restaurant::query()
            ->withCount('reviews')
            ->withMax('reviews as latest_date', 'date')
            ->where('id', $id)
            ->first();

        if (!$restaurant) {
            return response()->json(['error' => 'Restaurant not found'], 404);
        }

        $data = $restaurant->toArray();
        $data['review_count'] = $restaurant->reviews_count;

        return response()->json($data);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:50',
            'category'    => 'required|string|max:200',
            'description' => 'nullable|string|max:200',
            'address'     => 'nullable|string|max:100',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $imagePath = $this->handleUpload($request);

        $restaurant = Restaurant::create([
            'name'        => $validated['name'],
            'category'    => $validated['category'],
            'description' => $validated['description'] ?? '',
            'address'     => $validated['address'] ?? null,
            'image'       => $imagePath,
        ]);

        return $this->show($restaurant->id);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $restaurant = Restaurant::find($id);
        if (!$restaurant) {
            return response()->json(['error' => 'Restaurant not found'], 404);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:50',
            'category'    => 'required|string|max:200',
            'description' => 'nullable|string|max:200',
            'address'     => 'nullable|string|max:100',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $updateData = [
            'name'        => $validated['name'],
            'category'    => $validated['category'],
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

        $file = $request->file('image');
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'restaurant_' . time() . '_' . rand(1000, 9999) . '.' . $ext;

        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }

        $file->move($this->uploadDir, $filename);
        return $this->uploadPath . $filename;
    }
}
