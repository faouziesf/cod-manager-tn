<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord administrateur
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $adminId = Auth::guard('admin')->id();
        
        // Statistiques générales
        $stats = [
            'total_orders' => Order::where('admin_id', $adminId)->count(),
            'new_orders' => Order::where('admin_id', $adminId)
                ->where('status', 'new')
                ->count(),
            'total_products' => Product::where('admin_id', $adminId)->count(),
            'active_users' => User::where('admin_id', $adminId)
                ->where('active', true)
                ->count(),
        ];
        
        // Commandes récentes
        $recentOrders = Order::where('admin_id', $adminId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Produits les plus populaires (par quantité vendue)
        $topProducts = DB::table('products')
            ->join('order_products', 'products.id', '=', 'order_products.product_id')
            ->join('orders', 'order_products.order_id', '=', 'orders.id')
            ->select('products.name', DB::raw('SUM(order_products.quantity) as total_quantity'))
            ->where('products.admin_id', $adminId)
            ->whereIn('orders.status', ['confirmed', 'delivered'])
            ->groupBy('products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();
            
        // Données pour le graphique des 30 derniers jours
        $last30Days = Carbon::now()->subDays(30);
        
        $dailyOrders = DB::table('orders')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('admin_id', $adminId)
            ->where('created_at', '>=', $last30Days)
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
            
        $dailyRevenue = DB::table('orders')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as revenue'))
            ->where('admin_id', $adminId)
            ->where('created_at', '>=', $last30Days)
            ->groupBy('date')
            ->pluck('revenue', 'date')
            ->toArray();
            
        // Formater les données pour le graphique
        $chartData = [];
        $currentDate = Carbon::now()->subDays(29);
        
        for ($i = 0; $i < 30; $i++) {
            $date = $currentDate->copy()->addDays($i)->format('Y-m-d');
            $chartData[$date] = [
                'orders' => $dailyOrders[$date] ?? 0,
                'revenue' => $dailyRevenue[$date] ?? 0
            ];
        }
        
        return view('admin.dashboard', compact('stats', 'recentOrders', 'topProducts', 'chartData'));
    }
}