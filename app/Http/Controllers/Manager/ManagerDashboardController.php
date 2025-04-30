<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerDashboardController extends Controller
{
    /**
     * Afficher le tableau de bord
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $adminId = $user->admin_id;
        
        // Pour les employés, on filtre sur les commandes qui leur sont assignées
        $orderQuery = Order::where('admin_id', $adminId);
        if ($user->role === 'employee') {
            $orderQuery->where('assigned_to', $user->id);
        }
        
        // Statistiques générales
        $stats = [
            'total_orders' => (clone $orderQuery)->count(),
            'new_orders' => (clone $orderQuery)->where('status', 'new')->count(),
            'processing_orders' => (clone $orderQuery)->where('status', 'processing')->count(),
            'confirmed_orders' => (clone $orderQuery)->where('status', 'confirmed')->count(),
            'delivered_orders' => (clone $orderQuery)->where('status', 'delivered')->count(),
            'cancelled_orders' => (clone $orderQuery)->where('status', 'cancelled')->count(),
            'returned_orders' => (clone $orderQuery)->where('status', 'returned')->count(),
        ];
        
        // Commandes qui peuvent être tentées aujourd'hui
        $attemptsQuery = Order::where('admin_id', $adminId)
            ->whereNotIn('status', ['delivered', 'cancelled', 'returned'])
            ->where('attempt_count', '<', DB::raw('max_attempts'));
            
        if ($user->role === 'employee') {
            $attemptsQuery->where('assigned_to', $user->id);
        }
        
        $ordersToAttempt = $attemptsQuery->where(function($q) {
                $q->where('daily_attempt_count', '<', DB::raw('max_daily_attempts'))
                  ->orWhereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<', DB::raw('DATE(NOW())'));
            })
            ->where(function($q) {
                $q->whereNull('next_attempt_at')
                  ->orWhere('next_attempt_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('scheduled_date')
                  ->orWhere('scheduled_date', '<=', now());
            })
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();
        
        // Commandes récentes
        $recentOrdersQuery = Order::where('admin_id', $adminId);
        if ($user->role === 'employee') {
            $recentOrdersQuery->where('assigned_to', $user->id);
        }
        $recentOrders = $recentOrdersQuery->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Statistiques sur les 30 derniers jours
        $last30Days = Carbon::now()->subDays(30)->startOfDay();
        
        $dailyStats = DB::table('orders')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('admin_id', $adminId)
            ->where('created_at', '>=', $last30Days);
            
        if ($user->role === 'employee') {
            $dailyStats->where('assigned_to', $user->id);
        }
        
        $dailyStats = $dailyStats->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        
        $dailyConfirmed = DB::table('orders')
            ->select(DB::raw('DATE(updated_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('admin_id', $adminId)
            ->where('status', 'confirmed')
            ->where('updated_at', '>=', $last30Days);
            
        if ($user->role === 'employee') {
            $dailyConfirmed->where('assigned_to', $user->id);
        }
        
        $dailyConfirmed = $dailyConfirmed->groupBy(DB::raw('DATE(updated_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        
        // Préparer les données pour le graphique
        $chartData = [];
        $startDate = Carbon::now()->subDays(30);
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $chartData[$date] = [
                'date' => $date,
                'new' => $dailyStats[$date] ?? 0,
                'confirmed' => $dailyConfirmed[$date] ?? 0
            ];
        }
        
        return view('manager.dashboard', compact('stats', 'ordersToAttempt', 'recentOrders', 'chartData'));
    }

    /**
     * Afficher le profil de l'utilisateur
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        $user = Auth::user();
        return view('manager.profile', compact('user'));
    }

    /**
     * Mettre à jour le profil de l'utilisateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->route('manager.profile')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Mettre à jour le mot de passe de l'utilisateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = bcrypt($validated['password']);
        $user->save();

        return redirect()->route('manager.profile')
            ->with('success', 'Mot de passe mis à jour avec succès.');
    }

    /**
     * Afficher le rapport journalier
     *
     * @return \Illuminate\View\View
     */
    public function dailyReport()
    {
        $today = Carbon::today();
        $user = Auth::user();
        $adminId = $user->admin_id;
        
        // Vérifier que l'utilisateur est un manager
        if ($user->role !== 'manager') {
            abort(403, 'Accès non autorisé');
        }
        
        // Récupérer les statistiques du jour
        $todayStats = [
            'new_orders' => Order::where('admin_id', $adminId)
                ->whereDate('created_at', $today)
                ->count(),
            'confirmed_orders' => Order::where('admin_id', $adminId)
                ->where('status', 'confirmed')
                ->whereDate('updated_at', $today)
                ->count(),
            'delivered_orders' => Order::where('admin_id', $adminId)
                ->where('status', 'delivered')
                ->whereDate('updated_at', $today)
                ->count(),
            'cancelled_orders' => Order::where('admin_id', $adminId)
                ->where('status', 'cancelled')
                ->whereDate('updated_at', $today)
                ->count(),
            'returned_orders' => Order::where('admin_id', $adminId)
                ->where('status', 'returned')
                ->whereDate('updated_at', $today)
                ->count(),
            'total_revenue' => Order::where('admin_id', $adminId)
                ->whereIn('status', ['confirmed', 'delivered'])
                ->whereDate('updated_at', $today)
                ->sum('confirmed_price'),
        ];
        
        // Récupérer les commandes du jour
        $todayOrders = Order::where('admin_id', $adminId)
            ->whereDate('created_at', $today)
            ->with(['products', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('manager.reports.daily', compact('todayStats', 'todayOrders', 'today'));
    }

    /**
     * Afficher le rapport hebdomadaire
     *
     * @return \Illuminate\View\View
     */
    public function weeklyReport()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $user = Auth::user();
        $adminId = $user->admin_id;
        
        // Vérifier que l'utilisateur est un manager
        if ($user->role !== 'manager') {
            abort(403, 'Accès non autorisé');
        }
        
        // Récupérer les statistiques de la semaine
        $weeklyStats = [
            'new_orders' => Order::where('admin_id', $adminId)
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->count(),
            'confirmed_orders' => Order::where('admin_id', $adminId)
                ->where('status', 'confirmed')
                ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
                ->count(),
            'delivered_orders' => Order::where('admin_id', $adminId)
                ->where('status', 'delivered')
                ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
                ->count(),
            'cancelled_orders' => Order::where('admin_id', $adminId)
                ->where('status', 'cancelled')
                ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
                ->count(),
            'returned_orders' => Order::where('admin_id', $adminId)
                ->where('status', 'returned')
                ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
                ->count(),
            'total_revenue' => Order::where('admin_id', $adminId)
                ->whereIn('status', ['confirmed', 'delivered'])
                ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
                ->sum('confirmed_price'),
        ];
        
        // Récupérer les statistiques par jour de la semaine
        $dailyStats = DB::table('orders')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('admin_id', $adminId)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();
            
        return view('manager.reports.weekly', compact('weeklyStats', 'dailyStats', 'startOfWeek', 'endOfWeek'));
    }

    /**
     * Afficher le rapport mensuel
     *
     * @return \Illuminate\View\View
     */
    public function monthlyReport()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $user = Auth::user();
        $adminId = $user->admin_id;
        
        // Vérifier que l'utilisateur est un manager
        if ($user->role !== 'manager') {
            abort(403, 'Accès non autorisé');
        }
        
        // Récupérer les statistiques du mois
        $monthlyStats = [
            'new_orders' => Order::where('admin_id', $adminId)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count(),
            'confirmed_orders' => Order::where('admin_id', $adminId)
                ->where('status', 'confirmed')
                ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                ->count(),
            'delivered_orders' => Order::where('admin_id', $adminId)
                ->where('status', 'delivered')
                ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                ->count(),
            'cancelled_orders' => Order::where('admin_id', $adminId)
                ->where('status', 'cancelled')
                ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                ->count(),
            'returned_orders' => Order::where('admin_id', $adminId)
                ->where('status', 'returned')
                ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                ->count(),
            'total_revenue' => Order::where('admin_id', $adminId)
                ->whereIn('status', ['confirmed', 'delivered'])
                ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                ->sum('confirmed_price'),
        ];
        
        // Récupérer les statistiques par jour du mois
        $dailyStats = DB::table('orders')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('admin_id', $adminId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();
            
        return view('manager.reports.monthly', compact('monthlyStats', 'dailyStats', 'startOfMonth', 'endOfMonth'));
    }

    /**
     * Afficher le rapport par employé
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function employeeReport($userId)
    {
        $manager = Auth::user();
        $adminId = $manager->admin_id;
        
        // Vérifier que l'utilisateur est un manager
        if ($manager->role !== 'manager') {
            abort(403, 'Accès non autorisé');
        }
        
        // Récupérer l'employé
        $employee = \App\Models\User::where('id', $userId)
            ->where('admin_id', $adminId)
            ->where('role', 'employee')
            ->firstOrFail();
        
        // Récupérer les statistiques de l'employé
        $employeeStats = [
            'total_assigned' => Order::where('admin_id', $adminId)
                ->where('assigned_to', $employee->id)
                ->count(),
            'confirmed_orders' => Order::where('admin_id', $adminId)
                ->where('assigned_to', $employee->id)
                ->where('status', 'confirmed')
                ->count(),
            'delivered_orders' => Order::where('admin_id', $adminId)
                ->where('assigned_to', $employee->id)
                ->where('status', 'delivered')
                ->count(),
            'cancelled_orders' => Order::where('admin_id', $adminId)
                ->where('assigned_to', $employee->id)
                ->where('status', 'cancelled')
                ->count(),
            'returned_orders' => Order::where('admin_id', $adminId)
                ->where('assigned_to', $employee->id)
                ->where('status', 'returned')
                ->count(),
            'total_revenue' => Order::where('admin_id', $adminId)
                ->where('assigned_to', $employee->id)
                ->whereIn('status', ['confirmed', 'delivered'])
                ->sum('confirmed_price'),
            'confirmation_rate' => Order::where('admin_id', $adminId)
                ->where('assigned_to', $employee->id)
                ->whereIn('status', ['confirmed', 'delivered', 'cancelled', 'returned'])
                ->count() > 0 
                    ? (Order::where('admin_id', $adminId)
                        ->where('assigned_to', $employee->id)
                        ->whereIn('status', ['confirmed', 'delivered'])
                        ->count() / 
                      Order::where('admin_id', $adminId)
                        ->where('assigned_to', $employee->id)
                        ->whereIn('status', ['confirmed', 'delivered', 'cancelled', 'returned'])
                        ->count()) * 100
                    : 0,
        ];
        
        // Récupérer les 20 dernières commandes traitées par l'employé
        $recentOrders = Order::where('admin_id', $adminId)
            ->where('assigned_to', $employee->id)
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();
            
        return view('manager.reports.employee', compact('employee', 'employeeStats', 'recentOrders'));
    }
}