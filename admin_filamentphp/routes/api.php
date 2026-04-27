<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Auth\AuthController;
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
    Route::get('products/{product}', [ProductController::class, 'show']);
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
        Route::get('user', fn(Request $request) => $request->user());

        Route::apiResource('orders', OrderController::class);
    });
});
