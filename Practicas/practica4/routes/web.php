<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
    Route::resource('stock-entries', \App\Http\Controllers\Admin\StockEntryController::class)->only([
        'index', 'show', 'destroy',
    ]);
    Route::resource('dispatch-orders', \App\Http\Controllers\Admin\DispatchOrderController::class)->only([
        'index', 'show', 'destroy',
    ]);
    Route::resource('deliveries', \App\Http\Controllers\Admin\DeliveryController::class);
});

Route::middleware(['auth', 'role:almacenista'])->prefix('almacenista')->name('almacenista.')->group(function () {
    Route::resource('movements', \App\Http\Controllers\Almacenista\MovementController::class)->only([
        'index', 'create', 'store', 'show',
    ]);
    Route::resource('stock-entries', \App\Http\Controllers\Almacenista\StockEntryController::class)->only([
        'index', 'create', 'store', 'show',
    ]);
    Route::resource('dispatch-orders', \App\Http\Controllers\Almacenista\DispatchOrderController::class)->only([
        'index', 'create', 'store', 'show',
    ]);
});

Route::middleware(['auth', 'role:admin,supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('dispatch-orders', [\App\Http\Controllers\Supervisor\DispatchOrderController::class, 'index'])->name('dispatch-orders.index');
    Route::get('dispatch-orders/{dispatchOrder}', [\App\Http\Controllers\Supervisor\DispatchOrderController::class, 'show'])->name('dispatch-orders.show');
    Route::patch('dispatch-orders/{dispatchOrder}/approve', [\App\Http\Controllers\Supervisor\DispatchOrderController::class, 'approve'])->name('dispatch-orders.approve');
    Route::patch('dispatch-orders/{dispatchOrder}/reject', [\App\Http\Controllers\Supervisor\DispatchOrderController::class, 'reject'])->name('dispatch-orders.reject');
    Route::get('deliveries', [\App\Http\Controllers\Supervisor\DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('deliveries/{delivery}', [\App\Http\Controllers\Supervisor\DeliveryController::class, 'show'])->name('deliveries.show');
});

Route::middleware(['auth', 'role:admin,repartidor'])->prefix('repartidor')->name('repartidor.')->group(function () {
    Route::get('deliveries', [\App\Http\Controllers\Repartidor\DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('deliveries/{delivery}', [\App\Http\Controllers\Repartidor\DeliveryController::class, 'show'])->name('deliveries.show');
    Route::patch('deliveries/{delivery}/update-status', [\App\Http\Controllers\Repartidor\DeliveryController::class, 'updateStatus'])->name('deliveries.update-status');
});

require __DIR__.'/auth.php';
