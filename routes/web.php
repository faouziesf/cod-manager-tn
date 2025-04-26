<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SuperAdminLoginController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\UserLoginController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\SuperAdmin\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Routes pour le SuperAdmin
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    // Routes d'authentification accessibles aux invités
    Route::middleware('guest:admin')->group(function() {
        Route::get('/login', [SuperAdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [SuperAdminLoginController::class, 'login']);
    });
    
    // Route de déconnexion
    Route::post('/logout', [SuperAdminLoginController::class, 'logout'])->name('logout');
    
    // Routes protégées pour SuperAdmin
    Route::middleware(['superadmin'])->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        
        // Gestion des administrateurs
        Route::get('/admins', [AdminController::class, 'index'])->name('admins.index');
        Route::get('/admins/create', [AdminController::class, 'create'])->name('admins.create');
        Route::post('/admins', [AdminController::class, 'store'])->name('admins.store');
        Route::get('/admins/{admin}', [AdminController::class, 'show'])->name('admins.show');
        Route::get('/admins/{admin}/edit', [AdminController::class, 'edit'])->name('admins.edit');
        Route::put('/admins/{admin}', [AdminController::class, 'update'])->name('admins.update');
        Route::delete('/admins/{admin}', [AdminController::class, 'destroy'])->name('admins.destroy');
    });
});

// Routes d'authentification pour Admin
Route::prefix('admin')->name('admin.')->group(function () {
    // Routes d'authentification accessibles aux invités
    Route::middleware('guest:admin')->group(function() {
        Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'login']);
    });
    
    // Route de déconnexion
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    
    // Routes protégées pour Admin
    Route::middleware(['admin'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
        
        Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/standard', [\App\Http\Controllers\Admin\OrderController::class, 'standard'])->name('orders.standard');
        Route::get('/orders/dated', [\App\Http\Controllers\Admin\OrderController::class, 'dated'])->name('orders.dated');
        Route::get('/orders/old', [\App\Http\Controllers\Admin\OrderController::class, 'old'])->name('orders.old');
        Route::get('/orders/search', [\App\Http\Controllers\Admin\OrderController::class, 'search'])->name('orders.search');
        Route::get('/orders/create', [\App\Http\Controllers\Admin\OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/edit', [\App\Http\Controllers\Admin\OrderController::class, 'edit'])->name('orders.edit');
        Route::put('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'update'])->name('orders.update');
        Route::delete('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'destroy'])->name('orders.destroy');
    });
});

// Routes d'authentification pour User (Manager/Employee)
Route::middleware('guest')->group(function() {
    Route::get('/login', [UserLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UserLoginController::class, 'login']);
});
Route::post('/logout', [UserLoginController::class, 'logout'])->name('logout');

// Routes pour Manager
Route::prefix('manager')->name('manager.')->middleware(['manager'])->group(function () {
    Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
    
    // Gestion des commandes pour le manager
    // Route::get('/orders/all', [Manager\OrderController::class, 'all'])->name('orders.all');
    // Route::get('/orders/standard', [Manager\OrderController::class, 'standard'])->name('orders.standard');
    // Route::get('/orders/dated', [Manager\OrderController::class, 'dated'])->name('orders.dated');
    // Route::get('/orders/old', [Manager\OrderController::class, 'old'])->name('orders.old');
    // Route::get('/orders/search', [Manager\OrderController::class, 'search'])->name('orders.search');
});

// Routes pour Employee
Route::prefix('employee')->name('employee.')->middleware(['employee'])->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    
    // Gestion des commandes pour l'employé
    // Route::get('/orders/assigned', [Employee\OrderController::class, 'assigned'])->name('orders.assigned');
    // Route::get('/orders/standard', [Employee\OrderController::class, 'standard'])->name('orders.standard');
    // Route::get('/orders/dated', [Employee\OrderController::class, 'dated'])->name('orders.dated');
    // Route::get('/orders/old', [Employee\OrderController::class, 'old'])->name('orders.old');
});