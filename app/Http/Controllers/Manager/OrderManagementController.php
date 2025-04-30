<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderManagementController extends Controller
{
    /**
     * Afficher la liste des commandes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Order::where('admin_id', $user->admin_id);
        
        // Si c'est un employé (et non un manager), ne montrer que les commandes assignées
        if ($user->role === 'employee') {
            $query->where('assigned_to', $user->id);
        }
        
        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone1', 'like', "%{$search}%")
                  ->orWhere('delivery_address', 'like', "%{$search}%")
                  ->orWhere('external_id', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('sort_by')) {
            $sortField = $request->sort_by;
            $sortDirection = $request->sort_direction ?? 'asc';
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Ajouter un filtre pour les commandes qui peuvent être tentées aujourd'hui
        if ($request->has('can_attempt_today')) {
            $query->where(function($q) {
                $q->where('attempt_count', '<', \DB::raw('max_attempts'))
                  ->where(function($sq) {
                      $sq->where('daily_attempt_count', '<', \DB::raw('max_daily_attempts'))
                        ->orWhereNull('last_attempt_at')
                        ->orWhere('last_attempt_at', '<', \DB::raw('DATE(NOW())'));
                  })
                  ->where(function($sq) {
                      $sq->whereNull('next_attempt_at')
                        ->orWhere('next_attempt_at', '<=', now());
                  })
                  ->where(function($sq) {
                      $sq->whereNull('scheduled_date')
                        ->orWhere('scheduled_date', '<=', now());
                  });
            });
        }
        
        $orders = $query->paginate(15);
        
        // Pour les filtres du formulaire
        $statuses = Order::getStatusLabels();
        
        return view('manager.orders.index', compact('orders', 'statuses'));
    }
    
    /**
     * Afficher les détails d'une commande.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur a le droit de voir cette commande
        if ($order->admin_id !== $user->admin_id) {
            abort(403, 'Vous n\'êtes pas autorisé à voir cette commande.');
        }
        
        // Si c'est un employé, vérifier qu'il est bien assigné
        if ($user->role === 'employee' && $order->assigned_to !== $user->id) {
            abort(403, 'Cette commande ne vous est pas assignée.');
        }
        
        $order->load(['products', 'histories.user', 'assignedTo']);
        $canAttemptToday = $order->canBeAttemptedToday();
        
        return view('manager.orders.show', compact('order', 'canAttemptToday'));
    }
    
    /**
     * Enregistrer une tentative pour une commande.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function recordAttempt(Request $request, Order $order)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur a le droit de modifier cette commande
        if ($order->admin_id !== $user->admin_id) {
            return redirect()->back()->withErrors(['error' => 'Vous n\'êtes pas autorisé à modifier cette commande.']);
        }
        
        // Si c'est un employé, vérifier qu'il est bien assigné
        if ($user->role === 'employee' && $order->assigned_to !== $user->id) {
            return redirect()->back()->withErrors(['error' => 'Cette commande ne vous est pas assignée.']);
        }
        
        // Vérifier que la commande peut être tentée aujourd'hui
        if (!$order->canBeAttemptedToday()) {
            return redirect()->back()->withErrors(['error' => 'Cette commande ne peut pas être tentée actuellement.']);
        }
        
        $validated = $request->validate([
            'result' => 'required|string|in:no_answer,not_available,callback,confirmed,cancelled,returned',
            'note' => 'nullable|string',
            'callback_date' => 'nullable|required_if:result,callback|date|after:today'
        ]);
        
        // Mettre à jour la date de rappel si nécessaire
        if ($validated['result'] === 'callback' && isset($validated['callback_date'])) {
            $order->callback_date = $validated['callback_date'];
            $order->save();
        }
        
        // Enregistrer la tentative
        $success = $order->recordAttempt($validated['result'], $validated['note'], $user->id);
        
        if ($success) {
            return redirect()->route('manager.orders.show', $order)
                ->with('success', 'Tentative enregistrée avec succès.');
        } else {
            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de l\'enregistrement de la tentative.']);
        }
    }
    
    /**
     * Confirmer une commande.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function confirmOrder(Request $request, Order $order)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur a le droit de modifier cette commande
        if ($order->admin_id !== $user->admin_id) {
            return redirect()->back()->withErrors(['error' => 'Vous n\'êtes pas autorisé à modifier cette commande.']);
        }
        
        // Si c'est un employé, vérifier qu'il est bien assigné
        if ($user->role === 'employee' && $order->assigned_to !== $user->id) {
            return redirect()->back()->withErrors(['error' => 'Cette commande ne vous est pas assignée.']);
        }
        
        $validated = $request->validate([
            'confirmed_price' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);
        
        // Confirmer la commande
        $success = $order->confirm($validated['confirmed_price'], $validated['note'], $user->id);
        
        if ($success) {
            return redirect()->route('manager.orders.show', $order)
                ->with('success', 'Commande confirmée avec succès.');
        } else {
            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la confirmation de la commande.']);
        }
    }
}