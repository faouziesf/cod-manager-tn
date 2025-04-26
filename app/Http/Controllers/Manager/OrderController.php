<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Affiche toutes les commandes
     */
    public function index()
    {
        $user = Auth::user();
        $admin = $user->admin;
        $orders = $admin->orders()->orderBy('created_at', 'desc')->paginate(15);
        
        return view('manager.orders.index', compact('orders'));
    }

    /**
     * Affiche les commandes standards (à confirmer)
     */
    public function standard()
    {
        $user = Auth::user();
        $admin = $user->admin;
        $orders = $admin->orders()
            ->where('status', 'new')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('manager.orders.standard', compact('orders'));
    }

    /**
     * Affiche les commandes datées
     */
    public function dated()
    {
        $user = Auth::user();
        $admin = $user->admin;
        $orders = $admin->orders()
            ->where('status', 'dated')
            ->orderBy('callback_date')
            ->paginate(15);
        
        return view('manager.orders.dated', compact('orders'));
    }

    /**
     * Affiche les commandes anciennes (nombre max de tentatives atteint)
     */
    public function old()
    {
        $user = Auth::user();
        $admin = $user->admin;
        $orders = $admin->orders()
            ->whereRaw('current_attempts >= max_attempts')
            ->where('status', 'recall')
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
        
        return view('manager.orders.old', compact('orders'));
    }

    /**
     * Recherche de commandes
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $admin = $user->admin;
        $query = $admin->orders();
        
        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        }
        
        if ($request->filled('customer_phone')) {
            $query->where(function($q) use ($request) {
                $q->where('customer_phone1', 'like', '%' . $request->customer_phone . '%')
                  ->orWhere('customer_phone2', 'like', '%' . $request->customer_phone . '%');
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        $users = User::where('admin_id', $admin->id)->get();
        
        return view('manager.orders.search', compact('orders', 'users'));
    }

    /**
     * Formulaire de création de commande
     */
    public function create()
    {
        $user = Auth::user();
        $admin = $user->admin;
        $products = $admin->products()->get();
        $users = $admin->users()->where('role', 'employee')->get();
        
        return view('manager.orders.create', compact('products', 'users'));
    }

    /**
     * Enregistrement d'une nouvelle commande
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'nullable|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone1' => 'required|string|max:20',
            'customer_phone2' => 'nullable|string|max:20',
            'delivery_address' => 'required|string',
            'region' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $admin = $user->admin;
        
        // Vérifier que le produit appartient à cet admin
        $product = Product::findOrFail($request->product_id);
        if ($product->admin_id !== $admin->id) {
            return redirect()->route('manager.orders.create')
                ->with('error', 'Le produit sélectionné n\'est pas valide.');
        }
        
        // Vérifier que l'utilisateur appartient à cet admin si spécifié
        if ($request->filled('user_id')) {
            $assignedUser = User::findOrFail($request->user_id);
            if ($assignedUser->admin_id !== $admin->id) {
                return redirect()->route('manager.orders.create')
                    ->with('error', 'L\'utilisateur sélectionné n\'est pas valide.');
            }
        }
        
        $order = new Order();
        $order->admin_id = $admin->id;
        $order->product_id = $request->product_id;
        $order->user_id = $request->user_id;
        $order->customer_name = $request->customer_name;
        $order->customer_phone1 = $request->customer_phone1;
        $order->customer_phone2 = $request->customer_phone2;
        $order->delivery_address = $request->delivery_address;
        $order->region = $request->region;
        $order->city = $request->city;
        $order->quantity = $request->quantity;
        $order->status = 'new';
        $order->max_attempts = $request->max_attempts ?? 3;
        $order->current_attempts = 0;
        $order->save();
        
        // Créer une entrée dans l'historique
        $history = new OrderHistory();
        $history->order_id = $order->id;
        $history->user_id = Auth::id();
        $history->status = 'new';
        $history->private_note = 'Commande créée par le manager';
        $history->save();

        return redirect()->route('manager.orders.index')
            ->with('success', 'Commande créée avec succès.');
    }

    /**
     * Affiche les détails d'une commande
     */
    public function show(Order $order)
    {
        $user = Auth::user();
        $admin = $user->admin;
        
        // Vérifier que la commande appartient à cet admin
        if ($order->admin_id !== $admin->id) {
            return redirect()->route('manager.orders.index')
                ->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        $history = $order->history()->orderBy('created_at', 'desc')->get();
        $employees = User::where('admin_id', $admin->id)
            ->where('role', 'employee')
            ->where('active', true)
            ->get();
        
        return view('manager.orders.show', compact('order', 'history', 'employees'));
    }

    /**
     * Formulaire d'édition d'une commande
     */
    public function edit(Order $order)
    {
        $user = Auth::user();
        $admin = $user->admin;
        
        // Vérifier que la commande appartient à cet admin
        if ($order->admin_id !== $admin->id) {
            return redirect()->route('manager.orders.index')
                ->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        $products = $admin->products()->get();
        $employees = User::where('admin_id', $admin->id)
            ->where('role', 'employee')
            ->where('active', true)
            ->get();
        
        return view('manager.orders.edit', compact('order', 'products', 'employees'));
    }

    /**
     * Mise à jour d'une commande
     */
    public function update(Request $request, Order $order)
    {
        $user = Auth::user();
        $admin = $user->admin;
        
        // Vérifier que la commande appartient à cet admin
        if ($order->admin_id !== $admin->id) {
            return redirect()->route('manager.orders.index')
                ->with('error', 'Vous n\'avez pas accès à cette commande.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'nullable|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone1' => 'required|string|max:20',
            'customer_phone2' => 'nullable|string|max:20',
            'delivery_address' => 'required|string',
            'region' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|in:new,confirmed,dated,recall,canceled',
            'callback_date' => 'nullable|required_if:status,dated|date',
            'private_note' => 'nullable|string',
        ]);

        // Vérifier que le produit appartient à cet admin
        $product = Product::findOrFail($request->product_id);
        if ($product->admin_id !== $admin->id) {
            return redirect()->route('manager.orders.edit', $order)
                ->with('error', 'Le produit sélectionné n\'est pas valide.');
        }
        
        // Vérifier que l'utilisateur appartient à cet admin si spécifié
        if ($request->filled('user_id')) {
            $assignedUser = User::findOrFail($request->user_id);
            if ($assignedUser->admin_id !== $admin->id || $assignedUser->role !== 'employee') {
                return redirect()->route('manager.orders.edit', $order)
                    ->with('error', 'L\'utilisateur sélectionné n\'est pas valide ou n\'est pas un employé.');
            }
        }
        
        $oldStatus = $order->status;
        $newStatus = $request->status;
        
        // Mettre à jour la commande
        $order->product_id = $request->product_id;
        $order->user_id = $request->user_id;
        $order->customer_name = $request->customer_name;
        $order->customer_phone1 = $request->customer_phone1;
        $order->customer_phone2 = $request->customer_phone2;
        $order->delivery_address = $request->delivery_address;
        $order->region = $request->region;
        $order->city = $request->city;
        $order->quantity = $request->quantity;
        $order->status = $newStatus;
        
        // Traitement spécifique selon le statut
        if ($newStatus === 'dated') {
            $order->callback_date = $request->callback_date;
        } elseif ($newStatus === 'recall') {
            $order->current_attempts += 1;
        }
        
        $order->save();
        
        // Créer une entrée dans l'historique si le statut a changé ou si une note est fournie
        if ($oldStatus !== $newStatus || $request->filled('private_note')) {
            $history = new OrderHistory();
            $history->order_id = $order->id;
            $history->user_id = Auth::id();
            $history->status = $newStatus;
            $history->private_note = $request->private_note;
            $history->save();
        }

        return redirect()->route('manager.orders.show', $order)
            ->with('success', 'Commande mise à jour avec succès.');
    }

    /**
     * Assigner une commande à un employé
     */
    public function assign(Request $request, Order $order)
    {
        $user = Auth::user();
        $admin = $user->admin;
        
        // Vérifier que la commande appartient à cet admin
        if ($order->admin_id !== $admin->id) {
            return redirect()->route('manager.orders.index')
                ->with('error', 'Vous n\'avez pas accès à cette commande.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Vérifier que l'utilisateur est un employé de cet admin
        $employee = User::findOrFail($request->user_id);
        if ($employee->admin_id !== $admin->id || $employee->role !== 'employee') {
            return redirect()->route('manager.orders.show', $order)
                ->with('error', 'L\'employé sélectionné n\'est pas valide.');
        }

        $order->user_id = $request->user_id;
        $order->save();

        // Créer une entrée dans l'historique
        $history = new OrderHistory();
        $history->order_id = $order->id;
        $history->user_id = Auth::id();
        $history->status = $order->status;
        $history->private_note = "Commande assignée à l'employé " . $employee->name;
        $history->save();

        return redirect()->route('manager.orders.show', $order)
            ->with('success', 'Commande assignée avec succès.');
    }

    /**
     * Supprimer une commande 
     */
    public function destroy(Order $order)
    {
        $user = Auth::user();
        $admin = $user->admin;
        
        // Vérifier que la commande appartient à cet admin
        if ($order->admin_id !== $admin->id) {
            return redirect()->route('manager.orders.index')
                ->with('error', 'Vous n\'avez pas accès à cette commande.');
        }

        // Supprimer l'historique
        $order->history()->delete();
        // Supprimer la commande
        $order->delete();

        return redirect()->route('manager.orders.index')
            ->with('success', 'Commande supprimée avec succès.');
    }
}