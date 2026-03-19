<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ── Health check ──────────────────────────────────────────────────────────
Route::get('/health', fn () => response()->json(['status' => 'ok', 'time' => now()]));

// ── Tenant registration (public) ──────────────────────────────────────────
Route::post('/tenants/register', [TenantController::class, 'register']);
Route::get('/tenants', [TenantController::class, 'index']);

// ── Auth routes (require X-Tenant-Domain header) ──────────────────────────
Route::middleware(['tenant'])->group(function () {

    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login',    [AuthController::class, 'login']);

    // ── Authenticated routes ───────────────────────────────────────────────
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me',     [AuthController::class, 'me']);

        // ── Products (read for all authenticated) ──────────────────────────
        Route::get('/products',        [ProductController::class, 'index']);
        Route::get('/products/{id}',   [ProductController::class, 'show']);
        Route::get('/products/search', [ProductController::class, 'search']);

        // ── Products (write for admin only) ────────────────────────────────
        Route::middleware(['role:admin'])->group(function () {
            Route::post('/products',          [ProductController::class, 'store']);
            Route::put('/products/{id}',      [ProductController::class, 'update']);
            Route::delete('/products/{id}',   [ProductController::class, 'destroy']);
            Route::post('/products/{id}/image', [ProductController::class, 'uploadImage']);
        });

        // ── Cart ───────────────────────────────────────────────────────────
        Route::get('/cart',                     [CartController::class, 'index']);
        Route::post('/cart/items',              [CartController::class, 'addItem']);
        Route::put('/cart/items/{itemId}',      [CartController::class, 'updateItem']);
        Route::delete('/cart/items/{itemId}',   [CartController::class, 'removeItem']);
        Route::delete('/cart',                  [CartController::class, 'clear']);

        // ── Orders ─────────────────────────────────────────────────────────
        Route::post('/orders',         [OrderController::class, 'checkout']);
        Route::get('/orders',          [OrderController::class, 'index']);
        Route::get('/orders/{id}',     [OrderController::class, 'show']);

        Route::middleware(['role:admin'])->group(function () {
            Route::get('/admin/orders',           [OrderController::class, 'adminIndex']);
            Route::put('/admin/orders/{id}',      [OrderController::class, 'updateStatus']);
            Route::get('/admin/dashboard',        [TenantController::class, 'dashboard']);
        });
    });
});
