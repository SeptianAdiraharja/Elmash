<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClusteringController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RawLemonStockController;
use App\Http\Controllers\SalesTransactionController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Account Management / Profile
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Master Products CRUD & Export
    Route::get('/products/export/pdf', [ProductController::class, 'exportPdf'])->name('products.export.pdf');
    Route::get('/products/export/excel', [ProductController::class, 'exportExcel'])->name('products.export.excel');
    Route::resource('products', ProductController::class);

    // Master Categories CRUD
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    // Sales Transactions CRUD & Export
    Route::get('/transactions/export/pdf', [SalesTransactionController::class, 'exportPdf'])->name('transactions.export.pdf');
    Route::get('/transactions/export/excel', [SalesTransactionController::class, 'exportExcel'])->name('transactions.export.excel');
    Route::get('transactions/import', [SalesTransactionController::class, 'importForm'])->name('transactions.import');
    Route::post('transactions/import', [SalesTransactionController::class, 'importStore'])->name('transactions.import.store');
    Route::resource('transactions', SalesTransactionController::class);

    // K-Means Clustering Studio, History, Compare & Export
    Route::prefix('clustering')->name('clustering.')->group(function () {
        Route::get('/', [ClusteringController::class, 'index'])->name('index');
        Route::post('/run', [ClusteringController::class, 'index'])->name('run');
        Route::post('/save', [ClusteringController::class, 'save'])->name('save');
        Route::get('/history', [ClusteringController::class, 'history'])->name('history');
        Route::get('/compare', [ClusteringController::class, 'compare'])->name('compare');
        Route::get('/{clustering}', [ClusteringController::class, 'show'])->name('show');
        Route::delete('/{clustering}', [ClusteringController::class, 'destroy'])->name('destroy');
        Route::get('/{clustering}/export/pdf', [ClusteringController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/{clustering}/export/excel', [ClusteringController::class, 'exportExcel'])->name('export.excel');
    });

    // Raw Lemon Inventory Stock (Thesis Bab 1 Overstock & Stockout Tracker)
    Route::resource('lemon-stocks', RawLemonStockController::class)->only(['index', 'store', 'update', 'destroy']);
});
