<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques de base
        $totalAdmins = Admin::where('is_super_admin', false)->count();
        $activeAdmins = Admin::where('is_super_admin', false)->where('active', true)->count();
        $inactiveAdmins = $totalAdmins - $activeAdmins;
        $newAdmins = Admin::where('is_super_admin', false)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        
        // Statistiques globales
        $totalUsers = User::count();
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        
        // Données pour le graphique - 7 jours
        $chartDates7 = [];
        $chartAdmins7 = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartDates7[] = now()->subDays($i)->format('d/m');
            
            $count = Admin::where('is_super_admin', false)
                ->whereDate('created_at', $date)
                ->count();
                
            $chartAdmins7[] = $count;
        }
        
        // Données pour le graphique - 30 jours
        $chartDates30 = [];
        $chartAdmins30 = [];
        
        for ($i = 0; $i < 30; $i += 3) {
            $startDate = now()->subDays(29 - $i);
            $endDate = $i < 27 ? now()->subDays(27 - $i) : now();
            
            $chartDates30[] = $startDate->format('d/m') . '-' . $endDate->format('d/m');
            
            $count = Admin::where('is_super_admin', false)
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->count();
                
            $chartAdmins30[] = $count;
        }
        
        // Données pour le graphique - 90 jours
        $chartDates90 = [];
        $chartAdmins90 = [];
        
        for ($i = 0; $i < 90; $i += 10) {
            $startDate = now()->subDays(89 - $i);
            $endDate = $i < 80 ? now()->subDays(80 - $i) : now();
            
            $chartDates90[] = $startDate->format('d/m') . '-' . $endDate->format('d/m');
            
            $count = Admin::where('is_super_admin', false)
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->count();
                
            $chartAdmins90[] = $count;
        }
        
        // Liste des admins avec statistiques
        $admins = Admin::where('is_super_admin', false)
            ->withCount(['users', 'orders', 'products'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('superadmin.dashboard', compact(
            'totalAdmins', 'activeAdmins', 'inactiveAdmins', 'newAdmins',
            'totalUsers', 'totalOrders', 'totalProducts',
            'chartDates7', 'chartAdmins7',
            'chartDates30', 'chartAdmins30',
            'chartDates90', 'chartAdmins90',
            'admins'
        ));
    }
}