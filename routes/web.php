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
use App\Http\Controllers\SettingController;

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
        Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
    });
    
    // Route de déconnexion
    Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
    
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
        
        // Paramètres système globaux
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

// Routes d'authentification pour Admin
Route::prefix('admin')->name('admin.')->group(function () {
    // Routes d'authentification accessibles aux invités
    Route::middleware('guest:admin')->group(function() {
        Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
    });
    
    // Route de déconnexion
    Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
    
    // Routes protégées pour Admin
    Route::middleware(['admin'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        
        // Gestion des produits
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
        Route::patch('/products/{product}/stock', [\App\Http\Controllers\Admin\ProductController::class, 'updateStock'])->name('products.update-stock');
        
        // Gestion des commandes
        Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/standard', [\App\Http\Controllers\Admin\OrderController::class, 'standard'])->name('orders.standard');
        Route::get('/orders/scheduled', [\App\Http\Controllers\Admin\OrderController::class, 'scheduled'])->name('orders.scheduled');
        Route::get('/orders/old', [\App\Http\Controllers\Admin\OrderController::class, 'old'])->name('orders.old');
        Route::get('/orders/needs-verification', [\App\Http\Controllers\Admin\OrderController::class, 'needsVerification'])->name('orders.needs-verification');
        Route::get('/orders/search', [\App\Http\Controllers\Admin\OrderController::class, 'search'])->name('orders.search');
        Route::get('/orders/create', [\App\Http\Controllers\Admin\OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/edit', [\App\Http\Controllers\Admin\OrderController::class, 'edit'])->name('orders.edit');
        Route::put('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'update'])->name('orders.update');
        Route::post('/orders/{order}/process', [\App\Http\Controllers\Admin\OrderController::class, 'process'])->name('orders.process');
        Route::delete('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'destroy'])->name('orders.destroy');
        
        // Importation de commandes
        Route::get('/orders/import', [\App\Http\Controllers\Admin\OrderController::class, 'import'])->name('orders.import');
        Route::post('/orders/import-csv', [\App\Http\Controllers\Admin\OrderController::class, 'importCsv'])->name('orders.import-csv');
        
        // Intégrations externes
        Route::post('/settings/import-woocommerce', [SettingController::class, 'importFromWoocommerce'])->name('settings.import-woocommerce');
        Route::post('/settings/import-google-sheet', [SettingController::class, 'importFromGoogleSheet'])->name('settings.import-google-sheet');
        
        // Paramètres spécifiques à l'admin
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

// Routes d'authentification pour User (Manager/Employee)
Route::middleware('guest')->group(function() {
    Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
});

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Routes pour Manager
Route::prefix('manager')->name('manager.')->middleware(['manager'])->group(function () {
    Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
    
    // Gestion des commandes pour le manager
    Route::get('/orders', [\App\Http\Controllers\Manager\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/standard', [\App\Http\Controllers\Manager\OrderController::class, 'standard'])->name('orders.standard');
    Route::get('/orders/scheduled', [\App\Http\Controllers\Manager\OrderController::class, 'scheduled'])->name('orders.scheduled');
    Route::get('/orders/dated', [\App\Http\Controllers\Manager\OrderController::class, 'dated'])->name('orders.dated'); // Ajout de la route manquante
    Route::get('/orders/old', [\App\Http\Controllers\Manager\OrderController::class, 'old'])->name('orders.old');
    Route::get('/orders/needs-verification', [\App\Http\Controllers\Manager\OrderController::class, 'needsVerification'])->name('orders.needs-verification');
    Route::get('/orders/search', [\App\Http\Controllers\Manager\OrderController::class, 'search'])->name('orders.search');
    Route::get('/orders/create', [\App\Http\Controllers\Manager\OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [\App\Http\Controllers\Manager\OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [\App\Http\Controllers\Manager\OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/edit', [\App\Http\Controllers\Manager\OrderController::class, 'edit'])->name('orders.edit');
    Route::put('/orders/{order}', [\App\Http\Controllers\Manager\OrderController::class, 'update'])->name('orders.update');
    Route::post('/orders/{order}/process', [\App\Http\Controllers\Manager\OrderController::class, 'process'])->name('orders.process');
    Route::post('/orders/{order}/assign', [\App\Http\Controllers\Manager\OrderController::class, 'assign'])->name('orders.assign');
    Route::delete('/orders/{order}', [\App\Http\Controllers\Manager\OrderController::class, 'destroy'])->name('orders.destroy');
    
    // Importation de commandes
    Route::get('/orders/import', [\App\Http\Controllers\Manager\OrderController::class, 'import'])->name('orders.import');
    Route::post('/orders/import-csv', [\App\Http\Controllers\Manager\OrderController::class, 'importCsv'])->name('orders.import-csv');
    
    // Gestion des produits (lecture seule)
    Route::get('/products', [\App\Http\Controllers\Manager\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [\App\Http\Controllers\Manager\ProductController::class, 'show'])->name('products.show');
});

// Routes pour Employee
Route::prefix('employee')->name('employee.')->middleware(['employee'])->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    
    // Gestion des commandes pour l'employé
    Route::get('/orders', [\App\Http\Controllers\Employee\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/standard', [\App\Http\Controllers\Employee\OrderController::class, 'standard'])->name('orders.standard');
    Route::get('/orders/scheduled', [\App\Http\Controllers\Employee\OrderController::class, 'scheduled'])->name('orders.scheduled');
    Route::get('/orders/old', [\App\Http\Controllers\Employee\OrderController::class, 'old'])->name('orders.old');
    Route::get('/orders/search', [\App\Http\Controllers\Employee\OrderController::class, 'search'])->name('orders.search');
    Route::get('/orders/{order}', [\App\Http\Controllers\Employee\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/process', [\App\Http\Controllers\Employee\OrderController::class, 'process'])->name('orders.process');
    Route::post('/orders/request-more', [\App\Http\Controllers\Employee\OrderController::class, 'requestMoreOrders'])->name('orders.request-more');
});