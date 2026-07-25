<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Agent\AgentDashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ComboController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CrossSellController;
use App\Http\Controllers\Api\LoyaltyController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PostCategoryController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductInventoryController;
use App\Http\Controllers\Api\StorefrontSettingsController;
use App\Http\Controllers\Api\UpsellController;
use App\Http\Middleware\CheckTokenExpiration;
use App\Http\Middleware\HandleIdempotency;
use App\Http\Middleware\VerifyAgentToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

    // Storefront API Group
    Route::prefix('storefront')->name('storefront.')->group(function () {

        // Settings
        Route::get('settings', [StorefrontSettingsController::class, 'index']);

        // Menus
        Route::prefix('menus')->group(function () {
            Route::get('/', [MenuController::class, 'index']);
            Route::get('{handle}', [MenuController::class, 'show']);
        });

        // Cart API
        Route::prefix('cart')->group(function () {
            Route::post('/', [CartController::class, 'index']);
            Route::delete('/', [CartController::class, 'clear']);
            Route::post('sync', [CartController::class, 'sync']);
            Route::post('items', [CartController::class, 'addItem']);
            Route::put('items/{itemId}', [CartController::class, 'updateItem']);
            Route::delete('items/{itemId}', [CartController::class, 'removeItem']);
            Route::post('suggestions', [CartController::class, 'suggestions']);
            Route::post('shipping-methods', [CartController::class, 'shippingMethods']);
        });

        // Coupon API
        Route::middleware('marketing.module:enable_coupons')->prefix('coupons')->group(function () {
            Route::post('apply', [CouponController::class, 'apply']);
        });

        // Auth (Public)
        Route::prefix('auth')->group(function () {
            Route::post('login', [AuthController::class, 'login']);
            Route::post('refresh-token', [AuthController::class, 'refreshToken']);
        });

        // Address Metadata (Countries/States)
        Route::prefix('addresses')->group(function () {
            Route::get('countries', [AddressController::class, 'countries']);
            Route::get('countries/{countryCode}/states', [AddressController::class, 'states']);
            Route::get('countries/{countryCode}/states/{stateId}/regions', [AddressController::class, 'regions']);
            Route::get('countries/{countryCode}/states/{stateId}/regions/{regionId}/sub-regions', [AddressController::class, 'subRegions']);
        });

        // Posts
        Route::prefix('posts')->group(function () {
            Route::get('/', [PostController::class, 'index']);
            Route::get('{post}', [PostController::class, 'show']);
        });
        Route::prefix('post-categories')->group(function () {
            Route::get('/', [PostCategoryController::class, 'index']);
            Route::get('{post_category}/posts', [PostCategoryController::class, 'posts']);
        });

        // Products
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::get('by-slug/{slug}', [ProductController::class, 'showBySlug']);
            Route::get('{productId}/inventory', [ProductInventoryController::class, 'index']);

            Route::middleware('marketing.module:upsell_enabled')->get('{productId}/upsells', [UpsellController::class, 'index']);
            Route::middleware('marketing.module:cross_sell_enabled')->get('{productId}/cross-sells', [CrossSellController::class, 'index']);

            Route::get('{product}', [ProductController::class, 'show']);
        });

        Route::get('product-categories', [ProductCategoryController::class, 'index']);
        Route::get('brands', [BrandController::class, 'index']);

        // Combos
        Route::middleware('marketing.module:combo_enabled')->prefix('combos')->group(function () {
            Route::get('/', [ComboController::class, 'index']);
            Route::get('{slug}', [ComboController::class, 'show']);
        });

        // Pages
        Route::prefix('pages')->group(function () {
            Route::get('/', [PageController::class, 'index']);
            Route::get('{page}', [PageController::class, 'show']);
        });

        // Authenticated Routes
        Route::middleware([
            'auth:sanctum',
            CheckTokenExpiration::class,
            HandleIdempotency::class,
        ])->group(function () {
            // Auth (Protected)
            Route::prefix('auth')->group(function () {
                Route::post('logout', [AuthController::class, 'logout']);
                Route::get('profile', fn (Request $request) => $request->user()->load(['defaultShippingAddress', 'defaultBillingAddress']));
                Route::put('profile', [AuthController::class, 'updateProfile']);
            });

            // User Addresses
            Route::prefix('user-addresses')->group(function () {
                Route::get('/', [AddressController::class, 'index']);
                Route::post('/', [AddressController::class, 'store']);
                Route::put('{address}', [AddressController::class, 'update']);
                Route::delete('{address}', [AddressController::class, 'destroy']);
            });

            // Loyalty
            Route::middleware('marketing.module:loyalty_enabled')->prefix('loyalty')->group(function () {
                Route::get('points', [LoyaltyController::class, 'getPoints']);
                Route::get('history', [LoyaltyController::class, 'getHistory']);
            });

            // Orders
            Route::apiResource('orders', OrderController::class)->middleware('throttle:checkout');
        });

        // Agent API
        Route::middleware([VerifyAgentToken::class])->prefix('agents')->name('agents.')->group(function () {
            Route::get('health', [AgentDashboardController::class, 'health'])->name('health');
            Route::get('metrics', [AgentDashboardController::class, 'metrics'])->name('metrics');
        });
    });
});
