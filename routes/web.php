<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

$registerAppRoutes = function () {
    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('/index.php', [PageController::class, 'home']);
    Route::get('/restaurant', [PageController::class, 'restaurant'])->name('restaurant');
    Route::get('/restaurant.php', [PageController::class, 'restaurant']);
    Route::get('/stats', [PageController::class, 'stats'])->name('stats');
    Route::get('/stats.php', [PageController::class, 'stats']);
    Route::get('/search', [PageController::class, 'search'])->name('search');
    Route::get('/search.php', [PageController::class, 'search']);

    // API互換ルート（web経由の /foodlog/api/... リクエスト対応）
    Route::prefix('api')->group(function () {
        Route::any('/restaurants.php', function (Request $request, RestaurantController $controller) {
            $id = $request->query('id');
            $method = $request->method();
            if ($method === 'GET') {
                return $id ? $controller->show((int)$id) : $controller->index($request);
            } elseif ($method === 'POST') {
                return $id ? $controller->update($request, (int)$id) : $controller->store($request);
            } elseif ($method === 'DELETE') {
                return $controller->destroy((int)$id);
            }
            return response()->json(['error' => 'Method not allowed'], 405);
        });

        Route::any('/reviews.php', function (Request $request, ReviewController $controller) {
            $id = $request->query('id');
            $method = $request->method();
            if ($method === 'GET') {
                return $controller->index($request);
            } elseif ($method === 'POST') {
                return $id ? $controller->update($request, (int)$id) : $controller->store($request);
            } elseif ($method === 'DELETE') {
                return $controller->destroy((int)$id);
            }
            return response()->json(['error' => 'Method not allowed'], 405);
        });

        Route::get('/restaurants', [RestaurantController::class, 'index']);
        Route::get('/restaurants/{id}', [RestaurantController::class, 'show']);
        Route::post('/restaurants', [RestaurantController::class, 'store']);
        Route::post('/restaurants/{id}', [RestaurantController::class, 'update']);
        Route::delete('/restaurants/{id}', [RestaurantController::class, 'destroy']);

        Route::get('/reviews', [ReviewController::class, 'index']);
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::post('/reviews/{id}', [ReviewController::class, 'update']);
        Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    });
};

// ルート直下（php artisan serve 等で http://localhost:8000/ にアクセス時）
$registerAppRoutes();

// foodlog プレフィックス付き（http://localhost/foodlog/public/ にアクセス時）
Route::prefix('foodlog')->group($registerAppRoutes);
Route::prefix('foodlog/public')->group($registerAppRoutes);
