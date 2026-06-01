<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\StoreController;
use App\Http\Controllers\Client\ProductController;
use App\Http\Controllers\Client\ChannelController;
use App\Http\Controllers\Client\ListingController;
use App\Http\Controllers\Client\CampaignController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ChannelSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'));

// WooCommerce posts credentials here server-to-server — no session, no auth middleware
Route::match(['get', 'post'], '/channels/woocommerce/callback', [ChannelController::class, 'woocommerceCallback'])
    ->name('channels.woocommerce.callback');

// ── JSON API routes (consumed by Vue components) ───────────────────────────
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/stores', [StoreController::class, 'apiIndex'])->name('stores.index');
    Route::get('/stores/all', [StoreController::class, 'apiAll'])->name('stores.all');
    Route::get('/products', [ProductController::class, 'apiIndex'])->name('products.index');
    Route::post('/products/upload-image',  [ProductController::class, 'uploadImage'])->name('api.products.upload-image');
    Route::post('/products/analyze-image', [ProductController::class, 'analyzeImage'])->name('api.products.analyze-image');
    Route::get('/channels', [ChannelController::class, 'apiIndex'])->name('channels.index');
    Route::get('/campaigns', [CampaignController::class, 'apiIndex'])->name('campaigns.index');
    Route::get('/listings', [ListingController::class, 'apiIndex'])->name('listings.index');
});

// ── Client routes ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Stores
    Route::resource('stores', StoreController::class);
    Route::post('stores/{store}/sync', [StoreController::class, 'sync'])->name('stores.sync');

    // Products
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::resource('products', ProductController::class)->only(['index', 'show', 'create', 'store', 'edit', 'update', 'destroy']);

    // Channel integrations — dedicated OAuth callbacks first (before resource routes)
    Route::get('channels/tiktok_shop/callback', [ChannelController::class, 'tiktokShopCallback'])
        ->name('channels.tiktok_shop.callback');
    Route::match(['get', 'post'], 'channels/{channel}/callback', [ChannelController::class, 'callback'])
        ->name('channels.callback');
    Route::get('channels/{channel}/connect', [ChannelController::class, 'connect'])->name('channels.connect');
    Route::resource('channels', ChannelController::class);

    // Marketplace listings
    Route::resource('listings', ListingController::class)->only(['index', 'store', 'destroy']);
    Route::post('listings/{listing}/push', [ListingController::class, 'push'])->name('listings.push');
    Route::post('listings/bulk-push',   [ListingController::class, 'bulkPush'])->name('listings.bulk-push');
    Route::post('listings/bulk-delete', [ListingController::class, 'bulkDelete'])->name('listings.bulk-delete');

    // Ad campaigns
    Route::resource('campaigns', CampaignController::class);
    Route::post('campaigns/{campaign}/generate-content', [CampaignController::class, 'generateContent'])->name('campaigns.generate-content');
    Route::post('campaigns/{campaign}/push', [CampaignController::class, 'push'])->name('campaigns.push');
});

// ── Admin routes ───────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
    Route::get('channel-settings', [ChannelSettingsController::class, 'index'])->name('channel-settings.index');
    Route::post('channel-settings/{type}/toggle', [ChannelSettingsController::class, 'toggle'])->name('channel-settings.toggle');
});

require __DIR__.'/auth.php';
