<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    private string $uploadDir;
    private string $uploadPath = 'assets/images/';

    public function __construct()
    {
        $this->uploadDir = public_path('assets/images/');
    }

    public function index(Request $request): JsonResponse
    {
        $restaurantId = $request->query('restaurant_id');
        if (!$restaurantId) {
            return response()->json(['error' => 'restaurant_id required'], 400);
        }

        $reviews = Review::where('restaurant_id', $restaurantId)
            ->orderBy('date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json($reviews);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|integer|exists:restaurants,id',
            'date'          => 'required|date|before_or_equal:today',
            'order_details' => 'required|string|max:200',
            'impression'    => 'required|string|max:500',
            'rating'        => 'required|integer|min:1|max:5',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $imagePath = $this->handleUpload($request);

        $review = Review::create([
            'restaurant_id' => $validated['restaurant_id'],
            'date'          => $validated['date'],
            'order_details' => $validated['order_details'],
            'impression'    => $validated['impression'],
            'rating'        => $validated['rating'],
            'image'         => $imagePath,
        ]);

        return response()->json($review);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $review = Review::find($id);
        if (!$review) {
            return response()->json(['error' => 'Review not found'], 404);
        }

        $validated = $request->validate([
            'date'          => 'required|date|before_or_equal:today',
            'order_details' => 'required|string|max:200',
            'impression'    => 'required|string|max:500',
            'rating'        => 'required|integer|min:1|max:5',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $updateData = [
            'date'          => $validated['date'],
            'order_details' => $validated['order_details'],
            'impression'    => $validated['impression'],
            'rating'        => $validated['rating'],
        ];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $this->handleUpload($request);
            if ($imagePath) {
                $updateData['image'] = $imagePath;
            }
        }

        $review->update($updateData);

        return response()->json($review);
    }

    public function destroy(int $id): JsonResponse
    {
        $review = Review::find($id);
        if (!$review) {
            return response()->json(['error' => 'Review not found'], 404);
        }

        $deleted = $review->delete();
        return response()->json(['deleted' => $deleted ? $id : null]);
    }

    private function handleUpload(Request $request): ?string
    {
        if (!$request->hasFile('image') || !$request->file('image')->isValid()) {
            return null;
        }

        $file = $request->file('image');
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'review_' . time() . '_' . rand(1000, 9999) . '.' . $ext;

        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }

        $file->move($this->uploadDir, $filename);
        return $this->uploadPath . $filename;
    }
}
