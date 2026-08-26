<?php

use App\Http\Controllers\Admin\GreetSettingController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\EventController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('/greet', [GreetSettingController::class, 'index'])->name('greet');
Route::get('/orders', [OrderController::class, 'site'])->name('orders.site');


    Route::inertia('/dashboard', 'dashboard')->name('dashboard');

    Route::get('/admin/greet', [GreetSettingController::class, 'edit'])->name('admin.greet.edit');
    Route::put('/admin/greet', [GreetSettingController::class, 'update'])->name('admin.greet.update');

    Route::get('/admin/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/admin/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::post('/admin/orders', [OrderController::class, 'store'])->name('admin.orders.store');
    Route::put('/admin/orders/{order}', [OrderController::class, 'update'])->name('admin.orders.update');
    Route::delete('/admin/orders/{order}', [OrderController::class, 'destroy'])->name('admin.orders.destroy');
    
    //event
        Route::resource('/admin/events', EventController::class)->except('show');


require __DIR__ . '/settings.php';
