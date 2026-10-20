<?php

use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::middleware('auth')->group(function () {

    // Landing page after login (also redirects staff / customers to their page)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Customer price + aisle check (from the earlier phase)
    Route::get('/lookup', [LookupController::class, 'index'])->name('lookup.index');
    Route::post('/lookup', [LookupController::class, 'check'])->name('lookup.check');

    // Breeze profile pages
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin + Staff: Scan / POS
    Route::middleware('role:admin,staff')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/lookup', [PosController::class, 'lookup'])->name('pos.lookup');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    });

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::resource('products', ProductController::class)->except(['show']);

        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/{product}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/export', [SaleController::class, 'export'])->name('sales.export');
        Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/settings/barcode', [SettingController::class, 'barcode'])->name('settings.barcode');
        Route::put('/settings/barcode', [SettingController::class, 'barcodeUpdate'])->name('settings.barcode.update');

        // React CRUD page + JSON API (GET, POST, PUT, DELETE)
        Route::get('/admin/products-react', function () {
            return view('admin.products-react');
        })->name('admin.productsReact');

        Route::prefix('admin/api')->group(function () {
            Route::get('/products', [ProductApiController::class, 'index']);
            Route::post('/products', [ProductApiController::class, 'store']);
            Route::put('/products/{product}', [ProductApiController::class, 'update']);
            Route::delete('/products/{product}', [ProductApiController::class, 'destroy']);
        });
    });
});

require __DIR__.'/auth.php';