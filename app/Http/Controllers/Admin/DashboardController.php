<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        
        // Statistiques des commandes
        $totalOrders = $admin->orders()->count();
        $newOrders = $admin->orders()->where('status', 'new')->count();
        $confirmedOrders = $admin->orders()->where('status', 'confirmed')->count();
        $datedOrders = $admin->orders()->where('status', 'dated')->count();
        $recallOrders = $admin->orders()->where('status', 'recall')->count();
        $canceledOrders = $admin->orders()->where('status', 'canceled')->count();
        
        // Statistiques des utilisateurs
        $totalUsers = $admin->users()->count();
        $managers = $admin->users()->where('role', 'manager')->count();
        $employees = $admin->users()->where('role', 'employee')->count();
        
        // Statistiques des produits
        $totalProducts = $admin->products()->count();
        
        // Commandes par jour (7 derniers jours)
        $dailyOrders = $admin->orders()
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return view('admin.dashboard', compact(
            'totalOrders', 'newOrders', 'confirmedOrders', 'datedOrders', 'recallOrders', 'canceledOrders',
            'totalUsers', 'managers', 'employees',
            'totalProducts', 'dailyOrders'
        ));
    }
}