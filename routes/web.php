<?php

use Illuminate\Support\Facades\Route;

// Contrôleurs d'authentification
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SuperAdminLoginController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\UserLoginController;

// Contrôleurs SuperAdmin
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\AdminController;

// Contrôleurs Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\SettingController;

// Contrôleurs Manager
use App\Http\Controllers\Manager\ManagerAuthController;
use App\Http\Controllers\Manager\ManagerDashboardController;
use App\Http\Controllers\Manager\OrderManagementController;
use App\Http\Controllers\Manager\OrderController as ManagerOrderController;
use App\Http\Controllers\Manager\ProductController as ManagerProductController;

// Contrôleurs Employee
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\OrderController as EmployeeOrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Route par défaut - redirection vers login admin
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Routes pour le SuperAdmin
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    // Routes d'authentification accessibles aux invités
    Route::middleware('guest:admin')->group(function() {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login']);
    });
    
    // Route de déconnexion
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Routes protégées pour SuperAdmin
    Route::middleware(['superadmin'])->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        
        // Gestion des administrateurs
        Route::resource('admins', AdminController::class);
        
        // Paramètres système globaux
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

// Routes admin
Route::prefix('admin')->name('admin.')->group(function () {
    // Routes publiques admin
    Route::middleware('guest:admin')->group(function() {
        Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login']);
    });
    
    // Routes protégées admin
    Route::middleware('admin')->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        
        // Gestion des utilisateurs
        Route::resource('users', UserController::class);
        
        // Gestion des produits
        Route::resource('products', ProductController::class);
        Route::patch('/products/{product}/stock', [ProductController::class, 'updateStock'])->name('products.update-stock');
        
        // Paramètres et intégrations
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('settings/reset', [SettingController::class, 'resetToDefaults'])->name('settings.reset');
        Route::post('settings/test-woocommerce', [SettingController::class, 'testWooCommerceConnection'])->name('settings.test-woocommerce');
        Route::post('settings/test-google-sheets', [SettingController::class, 'testGoogleSheetsConnection'])->name('settings.test-google-sheets');
        Route::post('settings/import-woocommerce', [SettingController::class, 'importWoocommerce'])->name('settings.import-woocommerce');
        Route::post('settings/import-google-sheet', [SettingController::class, 'importGoogleSheet'])->name('settings.import-google-sheet');
        
        // Import de WooCommerce
        Route::get('import/woocommerce', [ImportController::class, 'showWooCommerceForm'])->name('import.woocommerce.form');
        Route::post('import/woocommerce', [ImportController::class, 'importFromWooCommerce'])->name('import.woocommerce');
        
        // Export des données
        Route::get('export/orders', [OrderController::class, 'exportOrders'])->name('export.orders');
        Route::get('export/products', [ProductController::class, 'exportProducts'])->name('export.products');
        
        // Gestion des commandes - IMPORTANT : Routes spécifiques d'abord
        Route::get('orders/import', [OrderController::class, 'import'])->name('orders.import');
        Route::post('orders/import-csv', [OrderController::class, 'importCsv'])->name('orders.import-csv');
        Route::get('orders/standard', [OrderController::class, 'standard'])->name('orders.standard');
        Route::get('orders/scheduled', [OrderController::class, 'scheduled'])->name('orders.scheduled');
        Route::get('orders/old', [OrderController::class, 'old'])->name('orders.old');
        Route::get('orders/needs-verification', [OrderController::class, 'needsVerification'])->name('orders.needs-verification');
        Route::get('orders/search', [OrderController::class, 'search'])->name('orders.search');
        
        // Actions spécifiques sur les commandes
        Route::post('orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::post('orders/{order}/assign', [OrderController::class, 'assignOrder'])->name('orders.assign');
        Route::post('orders/{order}/reset-attempts', [OrderController::class, 'resetAttempts'])->name('orders.reset-attempts');
        Route::post('orders/{order}/process', [OrderController::class, 'process'])->name('orders.process');
        
        // Routes de base pour les commandes (CRUD)
        Route::resource('orders', OrderController::class);
        
        // Routes super admin uniquement
        Route::middleware('super_admin')->group(function () {
            // Gestion des admins
            Route::resource('admins', \App\Http\Controllers\Admin\AdminController::class);
            
            // Statistiques avancées
            Route::get('analytics', [AdminDashboardController::class, 'analytics'])->name('analytics');
            
            // Outils de maintenance
            Route::get('maintenance', [AdminDashboardController::class, 'maintenance'])->name('maintenance');
            Route::post('maintenance/optimize', [AdminDashboardController::class, 'optimize'])->name('maintenance.optimize');
            Route::post('maintenance/clear-cache', [AdminDashboardController::class, 'clearCache'])->name('maintenance.clear-cache');
        });
    });
});

// Routes manager
Route::prefix('manager')->name('manager.')->group(function () {
    // Routes publiques manager
    Route::get('login', [ManagerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [ManagerAuthController::class, 'login']);
    
    // Routes protégées manager
    Route::middleware('manager')->group(function () {
        Route::get('dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [ManagerAuthController::class, 'logout'])->name('logout');
        
        // Gestion du profil
        Route::get('profile', [ManagerDashboardController::class, 'profile'])->name('profile');
        Route::put('profile', [ManagerDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::put('profile/password', [ManagerDashboardController::class, 'updatePassword'])->name('profile.password');
        
        // Gestion des commandes pour le OrderManagementController
        Route::get('orders', [OrderManagementController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderManagementController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/attempt', [OrderManagementController::class, 'recordAttempt'])->name('orders.attempt');
        Route::post('orders/{order}/confirm', [OrderManagementController::class, 'confirmOrder'])->name('orders.confirm');
        
        // Gestion des commandes pour le ManagerOrderController - Routes spécifiques d'abord
        Route::get('orders/import', [ManagerOrderController::class, 'import'])->name('orders.import');
        Route::post('orders/import-csv', [ManagerOrderController::class, 'importCsv'])->name('orders.import-csv');
        Route::get('orders/standard', [ManagerOrderController::class, 'standard'])->name('orders.standard');
        Route::get('orders/scheduled', [ManagerOrderController::class, 'scheduled'])->name('orders.scheduled');
        Route::get('orders/dated', [ManagerOrderController::class, 'dated'])->name('orders.dated');
        Route::get('orders/old', [ManagerOrderController::class, 'old'])->name('orders.old');
        Route::get('orders/needs-verification', [ManagerOrderController::class, 'needsVerification'])->name('orders.needs-verification');
        Route::get('orders/search', [ManagerOrderController::class, 'search'])->name('orders.search');
        Route::get('orders/create', [ManagerOrderController::class, 'create'])->name('orders.create');
        
        // Actions spécifiques sur les commandes (ManagerOrderController)
        Route::post('orders/{order}/process', [ManagerOrderController::class, 'process'])->name('orders.process');
        Route::post('orders/{order}/assign', [ManagerOrderController::class, 'assign'])->name('orders.assign');
        
        // Gestion des produits (lecture seule)
        Route::get('products', [ManagerProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [ManagerProductController::class, 'show'])->name('products.show');
        
        // Rapports (uniquement pour les managers)
        Route::middleware('manager_role')->group(function () {
            Route::get('reports/daily', [ManagerDashboardController::class, 'dailyReport'])->name('reports.daily');
            Route::get('reports/weekly', [ManagerDashboardController::class, 'weeklyReport'])->name('reports.weekly');
            Route::get('reports/monthly', [ManagerDashboardController::class, 'monthlyReport'])->name('reports.monthly');
            Route::get('reports/employee/{user}', [ManagerDashboardController::class, 'employeeReport'])->name('reports.employee');
        });
    });
});

// Routes employee
Route::prefix('employee')->name('employee.')->group(function () {
    // Routes d'authentification (utilise le même système que manager)
    Route::get('login', [ManagerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [ManagerAuthController::class, 'login']);
    
    // Routes protégées employee
    Route::middleware('employee')->group(function () {
        Route::get('dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [ManagerAuthController::class, 'logout'])->name('logout');
        
        // Gestion des commandes pour l'employé - Routes spécifiques d'abord
        Route::get('orders/standard', [EmployeeOrderController::class, 'standard'])->name('orders.standard');
        Route::get('orders/scheduled', [EmployeeOrderController::class, 'scheduled'])->name('orders.scheduled');
        Route::get('orders/old', [EmployeeOrderController::class, 'old'])->name('orders.old');
        Route::get('orders/search', [EmployeeOrderController::class, 'search'])->name('orders.search');
        Route::post('orders/request-more', [EmployeeOrderController::class, 'requestMoreOrders'])->name('orders.request-more');
        
        // Routes avec paramètres en dernier
        Route::get('orders', [EmployeeOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [EmployeeOrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/process', [EmployeeOrderController::class, 'process'])->name('orders.process');
        
        // Gestion du profil (même que manager)
        Route::get('profile', [ManagerDashboardController::class, 'profile'])->name('profile');
        Route::put('profile', [ManagerDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::put('profile/password', [ManagerDashboardController::class, 'updatePassword'])->name('profile.password');
    });
});

// Route pour l'authentification générale (si nécessaire)
Route::middleware('guest')->group(function() {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Fallback pour les routes inconnues
Route::fallback(function() {
    return redirect()->route('admin.login');
});