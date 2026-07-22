<?php

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\BootstrapController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoResetController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OutletContextController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Services\MenuPermissionMap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', function (): RedirectResponse {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(MenuPermissionMap::defaultRouteNameForUser(auth()->user()));
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'resolve.outlet'])->group(function (): void {
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/analisis-bisnis', [PageController::class, 'analytics'])->name('analytics');
    Route::get('/pos', [PageController::class, 'pos'])->name('pos');
    Route::get('/inventori', [PageController::class, 'inventory'])->name('inventory');
    Route::get('/laporan', [PageController::class, 'reports'])->name('reports');
    Route::get('/biaya-operasional', [PageController::class, 'expenses'])->name('expenses');
    Route::get('/kategori', [PageController::class, 'categories'])->name('categories');
    Route::get('/crm', [PageController::class, 'crm'])->name('crm');
    Route::get('/pengaturan', [PageController::class, 'settings'])->name('settings');
    Route::get('/cabang', [PageController::class, 'outlets'])->name('outlets');

    Route::prefix('api')->name('api.')->group(function (): void {
        Route::get('/bootstrap', BootstrapController::class)->name('bootstrap');
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::put('/context/outlet', [OutletContextController::class, 'update'])->name('context.outlet');
        Route::apiResource('outlets', OutletController::class)->except('show');

        Route::apiResource('categories', CategoryController::class)->except('show');
        Route::apiResource('products', ProductController::class)->except('show');
        Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
        Route::apiResource('customers', CustomerController::class)->except('show');
        Route::apiResource('expenses', ExpenseController::class)->except('show');
        Route::apiResource('users', UserController::class)->except('show');

        Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
        Route::apiResource('transactions', TransactionController::class)->only(['index', 'store', 'show']);

        Route::get('/settings', [SettingController::class, 'show'])->name('settings.show');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::put('/roles/{role}/menus', [RolePermissionController::class, 'update'])->name('roles.menus.update');
        Route::post('/maintenance/reset-demo', DemoResetController::class)->name('maintenance.reset-demo');
    });
});
