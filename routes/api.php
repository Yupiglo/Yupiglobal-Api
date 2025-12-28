<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AdminStatsController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\SubcategoryController;
use App\Http\Controllers\Api\V1\SendEmailController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WishlistController;

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json(['status' => 200, 'responseMsg' => 'OK']);
    });

    Route::prefix('auth')->group(function () {
        Route::post('/signin', [AuthController::class, 'signin']);
        Route::post('/loginWithYupi', [AuthController::class, 'loginWithYupi']);
        Route::post('/validateSession', [AuthController::class, 'validateSession'])->middleware('auth:sanctum');
    });

    Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });

    Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
        Route::get('/stats', [AdminStatsController::class, 'index']);
    });

    Route::get('/settings', [SettingsController::class, 'index']);
    Route::get('/settings/general', [SettingsController::class, 'general']);

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store'])->middleware('auth:sanctum');

        Route::get('/special', [ProductController::class, 'special']);
        Route::get('/filter', [ProductController::class, 'filter']);
        Route::get('/category/{category}', [ProductController::class, 'byCategory']);

        Route::get('/{id}', [ProductController::class, 'show']);
        Route::put('/{id}', [ProductController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->middleware('auth:sanctum');
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store'])->middleware('auth:sanctum');
        Route::put('/{id}', [CategoryController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->middleware('auth:sanctum');

        Route::prefix('{categoryId}/subcategories')->group(function () {
            Route::get('/', [SubcategoryController::class, 'index']);
            Route::post('/', [SubcategoryController::class, 'store'])->middleware('auth:sanctum');
            Route::put('/{id}', [SubcategoryController::class, 'update'])->middleware('auth:sanctum');
            Route::delete('/{id}', [SubcategoryController::class, 'destroy'])->middleware('auth:sanctum');
        });
    });

    Route::prefix('subcategories')->group(function () {
        Route::get('/', [SubcategoryController::class, 'index']);
        Route::post('/', [SubcategoryController::class, 'store'])->middleware('auth:sanctum');
        Route::put('/{id}', [SubcategoryController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/{id}', [SubcategoryController::class, 'destroy'])->middleware('auth:sanctum');
    });

    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index']);
        Route::post('/', [BrandController::class, 'store'])->middleware('auth:sanctum');
        Route::put('/{id}', [BrandController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/{id}', [BrandController::class, 'destroy'])->middleware('auth:sanctum');
    });

    Route::prefix('banner')->group(function () {
        Route::get('/', [BannerController::class, 'index']);
        Route::post('/', [BannerController::class, 'store'])->middleware('auth:sanctum');
        Route::put('/{id}', [BannerController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/{id}', [BannerController::class, 'destroy'])->middleware('auth:sanctum');
    });

    Route::prefix('coupons')->group(function () {
        Route::get('/', [CouponController::class, 'index']);
        Route::post('/', [CouponController::class, 'store'])->middleware('auth:sanctum');
        Route::get('/{id}', [CouponController::class, 'show']);
        Route::put('/{id}', [CouponController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/{id}', [CouponController::class, 'destroy'])->middleware('auth:sanctum');
    });

    Route::prefix('carts')->group(function () {
        Route::get('/', [CartController::class, 'show'])->middleware('auth:sanctum');
        Route::post('/', [CartController::class, 'store'])->middleware('auth:sanctum');
        Route::post('/apply-coupon', [CartController::class, 'applyCoupon'])->middleware('auth:sanctum');
        Route::delete('/{id}', [CartController::class, 'destroy'])->middleware('auth:sanctum');
        Route::put('/{id}', [CartController::class, 'update'])->middleware('auth:sanctum');
    });

    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'show'])->middleware('auth:sanctum');
        Route::get('/all', [OrderController::class, 'index']);
        Route::post('/checkOut/{id}', [OrderController::class, 'checkOut'])->middleware('auth:sanctum');
        Route::post('/user-cart', [OrderController::class, 'storeFromUserCart'])->middleware('auth:sanctum');
        Route::post('/{id}', [OrderController::class, 'store'])->middleware('auth:sanctum');
    });

    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->middleware('auth:sanctum');
        Route::patch('/', [WishlistController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/', [WishlistController::class, 'destroy'])->middleware('auth:sanctum');
    });

    Route::prefix('review')->group(function () {
        Route::get('/', [ReviewController::class, 'index']);
        Route::post('/', [ReviewController::class, 'store'])->middleware('auth:sanctum');
        Route::get('/{id}', [ReviewController::class, 'show']);
        Route::put('/{id}', [ReviewController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/{id}', [ReviewController::class, 'destroy'])->middleware('auth:sanctum');
    });

    Route::prefix('address')->group(function () {
        Route::get('/', [AddressController::class, 'index'])->middleware('auth:sanctum');
        Route::patch('/', [AddressController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/', [AddressController::class, 'destroy'])->middleware('auth:sanctum');
    });

    Route::prefix('blogs')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::post('/', [PostController::class, 'store'])->middleware('auth:sanctum');
        Route::get('/{id}', [PostController::class, 'show']);
        Route::put('/{id}', [PostController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/{id}', [PostController::class, 'destroy'])->middleware('auth:sanctum');
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/getalluser', [UserController::class, 'getAllUsersSql']);

        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::patch('/{id}', [UserController::class, 'changePassword']);
    });

    Route::prefix('sendemail')->group(function () {
        Route::post('/', [SendEmailController::class, 'store']);
    });
});
