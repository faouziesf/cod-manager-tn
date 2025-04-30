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

class AdminDashboardController extends Controller
{
    /**
     * Afficher le tableau de bord
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $adminId = Auth::guard('admin')->id();
        
        // Statistiques générales
        $stats = [
            'total_products' => Product::where('admin_id', $adminId)->count(),
            'active_products' => Product::where('admin_id', $adminId)->where('active', true)->count(),
            'total_orders' => Order::where('admin_id', $adminId)->count(),
            'new_orders' => Order::where('admin_id', $adminId)->where('status', 'new')->count(),
            'confirmed_orders' => Order::where('admin_id', $adminId)->where('status', 'confirmed')->count(),
            'delivered_orders' => Order::where('admin_id', $adminId)->where('status', 'delivered')->count(),
            'total_users' => User::where('admin_id', $adminId)->count(),
            'active_users' => User::where('admin_id', $adminId)->where('active', true)->count(),
        ];
        
        // Produits les plus populaires
        $topProducts = DB::table('order_products')
            ->join('products', 'order_products.product_id', '=', 'products.id')
            ->join('orders', 'order_products.order_id', '=', 'orders.id')
            ->select('products.id', 'products.name', DB::raw('SUM(order_products.quantity) as total_quantity'))
            ->where('products.admin_id', $adminId)
            ->whereIn('orders.status', ['confirmed', 'shipped', 'delivered'])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();
        
        // Commandes récentes
        $recentOrders = Order::where('admin_id', $adminId)
            ->orderBy('created_at', 'desc')
            ->with('products')
            ->limit(10)
            ->get();
        
        // Statistiques sur les 30 derniers jours
        $last30Days = Carbon::now()->subDays(30)->startOfDay();
        
        $dailyOrders = DB::table('orders')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('admin_id', $adminId)
            ->where('created_at', '>=', $last30Days)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        
        $dailyConfirmed = DB::table('orders')
            ->select(DB::raw('DATE(updated_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('admin_id', $adminId)
            ->where('status', 'confirmed')
            ->where('updated_at', '>=', $last30Days)
            ->groupBy(DB::raw('DATE(updated_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        
        $dailyRevenue = DB::table('orders')
            ->select(DB::raw('DATE(updated_at) as date'), DB::raw('SUM(IFNULL(confirmed_price, total_price)) as revenue'))
            ->where('admin_id', $adminId)
            ->whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->where('updated_at', '>=', $last30Days)
            ->groupBy(DB::raw('DATE(updated_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('revenue', 'date')
            ->toArray();
        
        // Préparer les données pour le graphique
        $chartData = [];
        $startDate = Carbon::now()->subDays(30);
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $chartData[$date] = [
                'date' => $date,
                'orders' => $dailyOrders[$date] ?? 0,
                'confirmed' => $dailyConfirmed[$date] ?? 0,
                'revenue' => $dailyRevenue[$date] ?? 0
            ];
        }
        
        // Utilisateurs les plus performants ce mois-ci
        $topUsers = DB::table('orders')
            ->join('users', 'orders.assigned_to', '=', 'users.id')
            ->select('users.id', 'users.name', DB::raw('COUNT(CASE WHEN orders.status IN (\'confirmed\', \'shipped\', \'delivered\') THEN 1 ELSE NULL END) as confirmed_count'))
            ->where('orders.admin_id', $adminId)
            ->where('orders.updated_at', '>=', $last30Days)
            ->groupBy('users.id', 'users.name')
            ->orderBy('confirmed_count', 'desc')
            ->limit(5)
            ->get();
        
        return view('admin.dashboard', compact('stats', 'topProducts', 'recentOrders', 'chartData', 'topUsers'));
    }
}