<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Agent\AgentDashboardController;
use App\Http\Controllers\Api\AttributeController;
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
            Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
            Route::post('register', [AuthController::class, 'register']);
            Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
            Route::post('reset-password', [AuthController::class, 'resetPassword']);
            Route::post('refresh-token', [AuthController::class, 'refreshToken']);
        });

        // Public Order Tracking
        Route::get('orders/track/{number}', [OrderController::class, 'track']);

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
            Route::get('featured', [PostController::class, 'featured']);
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
            Route::get('{productId}/recommendations', [ProductController::class, 'recommendations']);
            Route::get('{productId}/related', [ProductController::class, 'related']);

            Route::middleware('marketing.module:upsell_enabled')->get('{productId}/upsells', [UpsellController::class, 'index']);
            Route::middleware('marketing.module:cross_sell_enabled')->get('{productId}/cross-sells', [CrossSellController::class, 'index']);

            Route::get('{product}', [ProductController::class, 'show']);
        });

        Route::prefix('product-categories')->group(function () {
            Route::get('/', [ProductCategoryController::class, 'index']);
            Route::get('{category}/products', [ProductCategoryController::class, 'products']);
            Route::get('{productCategory}', [ProductCategoryController::class, 'show']);
        });
        Route::get('brands', [BrandController::class, 'index']);
        Route::get('brands/{brand}', [BrandController::class, 'show']);

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

        // Global Attributes
        Route::prefix('attributes')->group(function () {
            Route::get('/', [AttributeController::class, 'index']);
            Route::post('generate-variants', [AttributeController::class, 'generateVariants']);
            Route::get('{id}', [AttributeController::class, 'show']);
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
                Route::get('profile', [AuthController::class, 'profile']);
                Route::put('profile', [AuthController::class, 'updateProfile']);
                Route::post('profile', [AuthController::class, 'updateProfile']);
                Route::post('change-password', [AuthController::class, 'changePassword']);
            });

            // User Profile & Addresses Aliases
            Route::get('user/profile', [AuthController::class, 'profile']);
            Route::put('user/profile', [AuthController::class, 'updateProfile']);
            Route::post('user/profile', [AuthController::class, 'updateProfile']);

            Route::prefix('user-addresses')->group(function () {
                Route::get('/', [AddressController::class, 'index']);
                Route::post('/', [AddressController::class, 'store']);
                Route::put('{address}', [AddressController::class, 'update']);
                Route::delete('{address}', [AddressController::class, 'destroy']);
            });

            Route::prefix('user/addresses')->group(function () {
                Route::get('/', [AddressController::class, 'index']);
                Route::post('/', [AddressController::class, 'store']);
                Route::put('{address}', [AddressController::class, 'update']);
                Route::delete('{address}', [AddressController::class, 'destroy']);
            });

            // Global Attributes (Authenticated Write)
            Route::prefix('attributes')->group(function () {
                Route::post('/', [AttributeController::class, 'store']);
                Route::put('{id}', [AttributeController::class, 'update']);
                Route::delete('{id}', [AttributeController::class, 'destroy']);
                Route::post('{id}/values', [AttributeController::class, 'addValues']);
            });

            // Loyalty
            Route::prefix('loyalty')->group(function () {
                Route::get('points', [LoyaltyController::class, 'getPoints']);
                Route::get('balance', [LoyaltyController::class, 'getPoints']);
                Route::get('history', [LoyaltyController::class, 'getHistory']);
            });

            // Coupons (Protected)
            Route::get('coupons', [CouponController::class, 'index']);
            Route::get('coupons/my-usages', [CouponController::class, 'myUsages']);

            // Orders
            Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
            Route::apiResource('orders', OrderController::class)->middleware('throttle:checkout');
        });

        // Agent API
        Route::middleware([VerifyAgentToken::class])->prefix('agents')->name('agents.')->group(function () {
            Route::get('health', [AgentDashboardController::class, 'health'])->name('health');
            Route::get('metrics', [AgentDashboardController::class, 'metrics'])->name('metrics');
            Route::get('products', [\App\Http\Controllers\Api\Agent\AgentProductController::class, 'index'])->name('products.index');
            Route::get('products/{product}', [\App\Http\Controllers\Api\Agent\AgentProductController::class, 'show'])->name('products.show');
            Route::get('products/{product}/tax', [\App\Http\Controllers\Api\Agent\AgentProductController::class, 'tax'])->name('products.tax');

            // Agent Shipping Endpoints
            Route::prefix('shipping')->name('shipping.')->group(function () {
                Route::get('zones', [\App\Http\Controllers\Api\Agent\AgentShippingController::class, 'zones'])->name('zones');
                Route::get('destinations', [\App\Http\Controllers\Api\Agent\AgentShippingController::class, 'destinations'])->name('destinations');
                Route::post('estimate', [\App\Http\Controllers\Api\Agent\AgentShippingController::class, 'estimate'])->name('estimate');
                Route::get('track/{trackingNumber}', [\App\Http\Controllers\Api\Agent\AgentShippingController::class, 'track'])->name('track');
                Route::get('policies', [\App\Http\Controllers\Api\Agent\AgentShippingController::class, 'policies'])->name('policies');
            });
        });
    });
});
