<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LanguageLineController;
use App\Http\Controllers\Admin\MailLogController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\InventoryMovementController;
use App\Http\Controllers\Admin\InventoryRecordController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ShippingMethodController;
use App\Http\Controllers\Admin\ShippingZoneController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TaxClassController;
use App\Http\Controllers\Admin\TaxRateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WebhookController;
use App\Http\Controllers\Admin\WebhookLogController;
use App\Http\Controllers\Admin\UpsellProductController;
use App\Http\Controllers\Admin\CrossSellProductController;
use App\Http\Controllers\Admin\ComboProductController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/login');
Route::redirect('/home', '/admin');

Route::get('/docs/webhook', function () {
    return view('docs.webhook');
})->name('docs.webhook');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('setLocale')->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
            Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
        });

        Route::middleware(['auth', 'admin.access'])->group(function () {
            Route::get('/', DashboardController::class)->name('dashboard');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

            $crud = function (string $uri, string $controller, string $name): void {
                Route::get($uri . '/export', [$controller, 'export'])->name($name . '.export');
                Route::post($uri . '/import', [$controller, 'import'])->name($name . '.import');
                Route::post($uri . '/reorder', [$controller, 'reorder'])->name($name . '.reorder');
                Route::resource($uri, $controller)->names($name);
            };

            $crud('users', UserController::class, 'users');
            $crud('roles', RoleController::class, 'roles');
            $crud('permissions', PermissionController::class, 'permissions');

            $crud('products', ProductController::class, 'products');
            $crud('brands', BrandController::class, 'brands');
            $crud('product-categories', ProductCategoryController::class, 'product-categories');
            $crud('tax-classes', TaxClassController::class, 'tax-classes');
            $crud('tax-rates', TaxRateController::class, 'tax-rates');
            $crud('shipping-zones', ShippingZoneController::class, 'shipping-zones');
            $crud('shipping-methods', ShippingMethodController::class, 'shipping-methods');
            $crud('inventories', InventoryController::class, 'inventories');
            Route::post('inventory-records/{inventory_record}/process', [InventoryRecordController::class, 'process'])->name('inventory-records.process');
            $crud('inventory-records', InventoryRecordController::class, 'inventory-records');
            $crud('inventory-movements', InventoryMovementController::class, 'inventory-movements');
            Route::middleware('marketing.module:upsell_enabled')->group(function () use ($crud) {
                $crud('upsell-products', UpsellProductController::class, 'upsell-products');
            });
            Route::middleware('marketing.module:cross_sell_enabled')->group(function () use ($crud) {
                $crud('cross-sell-products', CrossSellProductController::class, 'cross-sell-products');
            });
            Route::middleware('marketing.module:combo_enabled')->group(function () use ($crud) {
                $crud('combo-products', ComboProductController::class, 'combo-products');
            });
            Route::middleware('marketing.module:enable_coupons')->group(function () use ($crud) {
                $crud('coupons', \App\Http\Controllers\Admin\CouponController::class, 'coupons');
            });
            Route::middleware('marketing.module:loyalty_enabled')->group(function () use ($crud) {
                $crud('loyalty-points', \App\Http\Controllers\Admin\LoyaltyPointController::class, 'loyalty-points');
            });

            $crud('orders', OrderController::class, 'orders');
            Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
            Route::post('orders/{order}/payments', [OrderController::class, 'storePayment'])->name('orders.payments.store');
            Route::post('orders/{order}/refunds', [OrderController::class, 'storeRefund'])->name('orders.refunds.store');
            $crud('payments', PaymentController::class, 'payments');
            $crud('refunds', RefundController::class, 'refunds');

            $crud('posts', PostController::class, 'posts');
            $crud('post-categories', PostCategoryController::class, 'post-categories');
            $crud('comments', CommentController::class, 'comments');
            $crud('pages', PageController::class, 'pages');
            $crud('menus', MenuController::class, 'menus');
            $crud('menu-items', MenuItemController::class, 'menu-items');
            Route::post('language-lines/sync', [LanguageLineController::class, 'syncFromFiles'])->name('language-lines.sync');
            $crud('language-lines', LanguageLineController::class, 'language-lines');
            $crud('media', MediaController::class, 'media');

            Route::post('settings/update-group', [SettingController::class, 'updateGroup'])->name('settings.update-group');
            $crud('settings', SettingController::class, 'settings');
            Route::middleware('setting.check:WebhookSettings,enabled')->group(function () use ($crud) {
                $crud('webhooks', WebhookController::class, 'webhooks');
                Route::resource('webhook-logs', WebhookLogController::class)->only(['index', 'show'])->names('webhook-logs');
            });
            Route::resource('mail-logs', MailLogController::class)->only(['index', 'show'])->names('mail-logs');
        });
    });
});
