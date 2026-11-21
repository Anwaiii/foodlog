<?php

use App\Http\Controllers\AdminMemberController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;
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
    Route::redirect('/search', '/foodlog/');
    Route::redirect('/search.php', '/foodlog/');

    // 認証・会員画面
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/logout', [AuthController::class, 'showLogout']);

    // パスワードリセット
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:5,1');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:5,1');

    Route::get('/users/{id}', [MemberController::class, 'publicProfile'])->whereNumber('id')->name('users.show');

    Route::middleware('auth')->group(function () {
        Route::get('/member', [MemberController::class, 'show']);
        Route::get('/member/password', [MemberController::class, 'editPassword']);
        Route::post('/member/password', [MemberController::class, 'updatePassword']);

        // 管理者専用：会員管理
        Route::get('/admin/members', [AdminMemberController::class, 'index'])->name('admin.members');
        Route::post('/admin/members/{id}/toggle-status', [AdminMemberController::class, 'toggleStatus'])->whereNumber('id');
    });

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

        Route::post('/favorites.php', [FavoriteController::class, 'toggle']);
        Route::post('/favorites', [FavoriteController::class, 'toggle']);
        Route::post('/review-likes.php', [ReviewLikeController::class, 'toggle']);
        Route::post('/review-likes', [ReviewLikeController::class, 'toggle']);
    });
};

// ルート直下（php artisan serve 等で http://localhost:8000/ にアクセス時）
$registerAppRoutes();

// foodlog プレフィックス付き（http://localhost/foodlog/public/ にアクセス時）
Route::prefix('foodlog')->group($registerAppRoutes);
Route::prefix('foodlog/public')->group($registerAppRoutes);
