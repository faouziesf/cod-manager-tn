<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $admin_id = Auth::user()->admin_id;
        
        // Statistiques des commandes
        $totalOrders = Order::where('admin_id', $admin_id)->count();
        $newOrders = Order::where('admin_id', $admin_id)->where('status', 'new')->count();
        $confirmedOrders = Order::where('admin_id', $admin_id)->where('status', 'confirmed')->count();
        $cancelledOrders = Order::where('admin_id', $admin_id)->where('status', 'cancelled')->count();
        $scheduledOrders = Order::where('admin_id', $admin_id)->where('status', 'scheduled')->count();
        $oldOrders = Order::where('admin_id', $admin_id)->where('status', 'old')->count();
        
        // Les commandes datées sont les mêmes que les commandes programmées (scheduled)
        $datedOrders = $scheduledOrders;
        
        // Taux de confirmation
        $confirmationRate = $totalOrders > 0 ? number_format(($confirmedOrders / $totalOrders) * 100, 1) : 0;
        $confirmationRateValue = $totalOrders > 0 ? ($confirmedOrders / $totalOrders) * 100 : 0;
        
        // Commandes du jour
        $todayOrders = Order::where('admin_id', $admin_id)
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        // Commandes à vérifier
        $ordersToVerify = Order::where('admin_id', $admin_id)
            ->whereHas('products', function($q) {
                $q->where('stock', '<=', 0);
            })
            ->count();
        
        // Données pour le graphique des 7 derniers jours
        $dates = [];
        $orderCounts = [];
        $confirmedCounts = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dates[] = $date->format('d/m');
            
            $orderCount = Order::where('admin_id', $admin_id)
                ->whereDate('created_at', $date)
                ->count();
            $orderCounts[] = $orderCount;
            
            $confirmedCount = Order::where('admin_id', $admin_id)
                ->where('status', 'confirmed')
                ->whereDate('updated_at', $date)
                ->count();
            $confirmedCounts[] = $confirmedCount;
        }
        
        // Commandes récentes
        $recentOrders = Order::where('admin_id', $admin_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Produits populaires
        $popularProducts = Product::where('admin_id', $admin_id)
            ->withCount(['orders as orders_count' => function($query) use ($admin_id) {
                $query->where('orders.admin_id', $admin_id)
                      ->where('orders.status', 'confirmed');
            }])
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get();
        
        // Performances des employés - VERSION CORRIGÉE
        $employeePerformance = User::where('admin_id', $admin_id)
            ->where('role', 'employee')
            ->get();
            
        // Calculer manuellement les statistiques pour chaque employé
        foreach ($employeePerformance as $employee) {
            $employee->total_orders = Order::where('admin_id', $admin_id)
                ->where('assigned_to', $employee->id)
                ->count();
                
            $employee->confirmed_orders = Order::where('admin_id', $admin_id)
                ->where('assigned_to', $employee->id)
                ->where('status', 'confirmed')
                ->count();
                
            // Calculer le taux de confirmation
            $employee->confirmation_rate = $employee->total_orders > 0 
                ? round(($employee->confirmed_orders / $employee->total_orders) * 100, 1) 
                : 0;
        }
        
        return view('manager.dashboard', compact(
            'totalOrders', 'newOrders', 'confirmedOrders', 'cancelledOrders',
            'scheduledOrders', 'datedOrders', 'oldOrders', 'confirmationRate', 
            'confirmationRateValue', 'todayOrders', 'ordersToVerify', 'dates', 
            'orderCounts', 'confirmedCounts', 'recentOrders', 'popularProducts', 
            'employeePerformance'
        ));
    }
}