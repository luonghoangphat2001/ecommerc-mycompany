<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PostCategoryController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Middleware\CheckTokenExpiration;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

Route::middleware(['api'])->prefix('v1')->group(function () {
    Route::get('storefront-settings', [\App\Http\Controllers\Api\StorefrontSettingsController::class, 'index']);
    Route::get('menus', [\App\Http\Controllers\Api\MenuController::class, 'index']);
    Route::get('menus/{handle}', [\App\Http\Controllers\Api\MenuController::class, 'show']);
    
    // Cart API
    Route::post('cart', [\App\Http\Controllers\Api\CartController::class, 'index']);
    Route::post('cart/sync', [\App\Http\Controllers\Api\CartController::class, 'sync']);
    Route::post('cart/items', [\App\Http\Controllers\Api\CartController::class, 'addItem']);
    Route::put('cart/items/{itemId}', [\App\Http\Controllers\Api\CartController::class, 'updateItem']);
    Route::delete('cart/items/{itemId}', [\App\Http\Controllers\Api\CartController::class, 'removeItem']);
    Route::delete('cart', [\App\Http\Controllers\Api\CartController::class, 'clear']);
    Route::post('cart/suggestions', [\App\Http\Controllers\Api\CartController::class, 'suggestions']);
    Route::post('cart/shipping-methods', [\App\Http\Controllers\Api\CartController::class, 'shippingMethods']);
    
    // Coupon API
    Route::post('coupons/apply', [\App\Http\Controllers\Api\CouponController::class, 'apply']);
    Route::post('login', [AuthController::class, 'login']);

    // Address API
    Route::get('countries', [AddressController::class, 'countries']);
    Route::get('countries/{countryCode}/states', [AddressController::class, 'states']);
    Route::get('countries/{countryCode}/states/{stateId}/regions', [AddressController::class, 'regions']);
    Route::get('countries/{countryCode}/states/{stateId}/regions/{regionId}/sub-regions', [AddressController::class, 'subRegions']);

    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{post}', [PostController::class, 'show']);
    Route::get('post-categories', [PostCategoryController::class, 'index']);
    Route::get('post-categories/{post_category}/posts', [PostCategoryController::class, 'posts']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/by-slug/{slug}', [ProductController::class, 'showBySlug']);
    // Specific routes must come BEFORE the generic {product} route
    Route::get('products/{productId}/inventory', [\App\Http\Controllers\Api\ProductInventoryController::class, 'index']);
    Route::get('products/{productId}/upsells', [\App\Http\Controllers\Api\UpsellController::class, 'index']);
    Route::get('products/{productId}/cross-sells', [\App\Http\Controllers\Api\CrossSellController::class, 'index']);
    Route::get('products/{product}', [ProductController::class, 'show']);

    // Combos
    Route::get('combos', [\App\Http\Controllers\Api\ComboController::class, 'index']);
    Route::get('combos/{slug}', [\App\Http\Controllers\Api\ComboController::class, 'show']);
    Route::get('product-categories', [ProductCategoryController::class, 'index']);
    Route::get('brands', [BrandController::class, 'index']);
    Route::get('pages', [PageController::class, 'index']);
    Route::get('pages/{page}', [PageController::class, 'show']);

    Route::middleware([
        'auth:sanctum',
        \App\Http\Middleware\CheckTokenExpiration::class,
        \App\Http\Middleware\HandleIdempotency::class
    ])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', fn(Request $request) => $request->user()->load(['defaultShippingAddress', 'defaultBillingAddress']));
        Route::put('user', [AuthController::class, 'updateProfile']);
        Route::put('user/profile', [AuthController::class, 'updateProfile']);
        
        Route::get('user/addresses', [AddressController::class, 'index']);
        Route::post('user/addresses', [AddressController::class, 'store']);
        Route::put('user/addresses/{address}', [AddressController::class, 'update']);
        Route::delete('user/addresses/{address}', [AddressController::class, 'destroy']);

        // Loyalty
        Route::get('user/loyalty/points', [\App\Http\Controllers\Api\LoyaltyController::class, 'getPoints']);
        Route::get('user/loyalty/history', [\App\Http\Controllers\Api\LoyaltyController::class, 'getHistory']);

        Route::apiResource('orders', OrderController::class)->middleware('throttle:checkout');
    });
});
