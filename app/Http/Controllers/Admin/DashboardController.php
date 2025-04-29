<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        
        // Statistiques des commandes
        $totalOrders = $admin->orders()->count();
        $newOrders = $admin->orders()->where('status', 'new')->count();
        $confirmedOrders = $admin->orders()->where('status', 'confirmed')->count();
        // Utiliser uniquement 'scheduled' pour la logique interne
        $scheduledOrders = $admin->orders()->where('status', 'scheduled')->count();
        // Variable datedOrders supprimée car redondante avec scheduledOrders
        $recallOrders = $admin->orders()->where('status', 'recall')->count();
        $cancelledOrders = $admin->orders()->where('status', 'cancelled')->count();
        
        // Statistiques des utilisateurs
        $totalUsers = $admin->users()->count();
        $managers = $admin->users()->where('role', 'manager')->count();
        $employees = $admin->users()->where('role', 'employee')->count();
        
        // Statistiques des produits
        $totalProducts = $admin->products()->count();
        
        // Commandes par jour (7 derniers jours)
        $chartDates = [];
        $chartNewOrders = [];
        $chartConfirmedOrders = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartDates[] = $date->format('d/m');
            
            $chartNewOrders[] = $admin->orders()
                ->whereDate('created_at', $date)
                ->count();
                
            $chartConfirmedOrders[] = $admin->orders()
                ->where('status', 'confirmed')
                ->whereDate('updated_at', $date)
                ->count();
        }
        
        // Produits populaires
        $popularProducts = Product::where('admin_id', $admin->id)
            ->withCount(['orders as orders_count' => function($query) use ($admin) {
                $query->where('orders.admin_id', $admin->id)
                      ->where('orders.status', 'confirmed');
            }])
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get();
            
        // Commandes récentes
        $recentOrders = $admin->orders()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Taux de confirmation
        $confirmationRateValue = $totalOrders > 0 ? ($confirmedOrders / $totalOrders) * 100 : 0;
        $confirmationRate = number_format($confirmationRateValue, 1) . '%';
        
        // Commandes du jour
        $todayOrders = $admin->orders()
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        // Commandes à vérifier
        $ordersToVerify = $admin->orders()
            ->whereHas('products', function($query) {
                $query->where('stock', '<=', 0);
            })
            ->count();
        
        return view('admin.dashboard', compact(
            'totalOrders', 'newOrders', 'confirmedOrders', 'scheduledOrders', 
            'recallOrders', 'cancelledOrders', 'totalUsers', 'managers', 'employees',
            'totalProducts', 'chartDates', 'chartNewOrders', 'chartConfirmedOrders',
            'popularProducts', 'recentOrders', 'confirmationRate', 'confirmationRateValue',
            'todayOrders', 'ordersToVerify'
        ));
    }
}