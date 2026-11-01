<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ManagementController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderItemController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return redirect()->route(auth()->user()->isAdmin() ? 'dashboard' : 'profile.edit');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard')
        ->middleware('can:manage-catering');

    Route::prefix('admin')->name('admin.')->middleware('can:manage-catering')->group(function () {
        Route::get('/', [ManagementController::class, 'index'])->name('index');
        Route::resource('cities', CityController::class)->except('show');
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('payment-methods', PaymentMethodController::class)->except('show');
        Route::resource('couriers', CourierController::class)->except('show');
        Route::resource('customers', CustomerController::class)->except('show');
        Route::resource('menus', MenuController::class)->except('show');
        Route::resource('orders', OrderController::class)->except('show');
        Route::resource('order-items', OrderItemController::class)->except('show');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
