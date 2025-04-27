<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function standard()
    {
        $admin_id = Auth::user()->admin_id;
        
        $query = Order::standard()->where('admin_id', $admin_id);
        
        // Si l'utilisateur n'est pas manager, montrer seulement ses commandes
        if (Auth::user()->role !== 'manager') {
            $query->where(function($q) {
                $q->where('assigned_to', Auth::id())
                  ->orWhereNull('assigned_to');
            });
        }
        
        $order = $query->orderBy('attempt_count')
                       ->orderBy('created_at', 'desc')
                       ->first();
        
        // Si pas de commande et l'utilisateur n'est pas manager
        $needsMoreOrders = false;
        if (!$order && Auth::user()->role !== 'manager') {
            $needsMoreOrders = true;
        }
        
        $products = Product::where('admin_id', $admin_id)
                          ->where('active', true)
                          ->get();
        $regions = tunisianRegions();
        
        return view('orders.standard', compact('order', 'needsMoreOrders', 'products', 'regions'));
    }
    
    public function scheduled()
    {
        $admin_id = Auth::user()->admin_id;
        
        $query = Order::scheduled()->where('admin_id', $admin_id);
        
        // Si l'utilisateur n'est pas manager, montrer seulement ses commandes
        if (Auth::user()->role !== 'manager') {
            $query->where(function($q) {
                $q->where('assigned_to', Auth::id())
                  ->orWhereNull('assigned_to');
            });
        }
        
        $order = $query->orderBy('attempt_count')
                       ->orderBy('scheduled_date')
                       ->first();
        
        // Vérifier s'il y a des rendez-vous à afficher
        $reminder = null;
        if ($order && $order->attempt_count == 0) {
            $reminder = "Rappel : Vous avez un rendez-vous aujourd'hui avec le client " . $order->customer_name;
        }
        
        $products = Product::where('admin_id', $admin_id)
                          ->where('active', true)
                          ->get();
        $regions = tunisianRegions();
        
        return view('orders.scheduled', compact('order', 'reminder', 'products', 'regions'));
    }
    
    public function old()
    {
        $admin_id = Auth::user()->admin_id;
        
        $query = Order::old()->where('admin_id', $admin_id);
        
        // Si l'utilisateur n'est pas manager, montrer seulement ses commandes
        if (Auth::user()->role !== 'manager') {
            $query->where(function($q) {
                $q->where('assigned_to', Auth::id())
                  ->orWhereNull('assigned_to');
            });
        }
        
        $order = $query->orderBy('last_attempt_at')
                       ->first();
        
        $products = Product::where('admin_id', $admin_id)
                          ->where('active', true)
                          ->get();
        $regions = tunisianRegions();
        
        return view('orders.old', compact('order', 'products', 'regions'));
    }
    
    public function needsVerification()
    {
        // Vérifier si l'utilisateur est admin ou manager
        if (Auth::user()->role !== 'manager' && !Auth::guard('admin')->check()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        $admin_id = Auth::user()->admin_id ?? Auth::guard('admin')->id();
        
        $orders = Order::where('admin_id', $admin_id)
                       ->whereHas('products', function($q) {
                            $q->where('stock', '<=', 0);
                        })
                       ->with('products')
                       ->paginate(20);
        
        return view('orders.needs-verification', compact('orders'));
    }
    
    public function search(Request $request)
    {
        $admin_id = Auth::user()->admin_id ?? Auth::guard('admin')->id();
        
        $search = $request->input('search');
        $status = $request->input('status');
        
        $query = Order::where('admin_id', $admin_id);
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone1', 'like', "%{$search}%")
                  ->orWhere('customer_phone2', 'like', "%{$search}%")
                  ->orWhere('delivery_address', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        // Si l'utilisateur n'est pas admin ou manager, montrer seulement ses commandes
        if (Auth::user()->role !== 'manager' && !Auth::guard('admin')->check()) {
            $query->where('assigned_to', Auth::id());
        }
        
        $orders = $query->orderBy('id', 'desc')->paginate(25);
        
        return view('orders.search', compact('orders', 'search', 'status'));
    }
    
    public function create()
    {
        $admin_id = Auth::user()->admin_id ?? Auth::guard('admin')->id();
        
        $products = Product::where('admin_id', $admin_id)
                          ->where('active', true)
                          ->get();
        $regions = tunisianRegions();
        
        return view('orders.create', compact('products', 'regions'));
    }
    
    public function store(Request $request)
    {
        $admin_id = Auth::user()->admin_id ?? Auth::guard('admin')->id();
        
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone1' => 'required|string|max:20',
            'customer_phone2' => 'nullable|string|max:20',
            'delivery_address' => 'nullable|string',
            'region' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'status' => 'required|in:new,confirmed,cancelled,scheduled',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
            'confirmed_price' => 'nullable|numeric|min:0',
            'scheduled_date' => 'nullable|date|after:today',
            'note' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Créer la commande
            $order = Order::create([
                'admin_id' => $admin_id,
                'user_id' => Auth::id(),
                'customer_name' => $validated['customer_name'],
                'customer_phone1' => $validated['customer_phone1'],
                'customer_phone2' => $validated['customer_phone2'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
                'region' => $validated['region'] ?? null,
                'city' => $validated['city'] ?? null,
                'total_price' => $validated['total_price'],
                'status' => $validated['status'],
                'max_attempts' => getSetting('standard_max_attempts', 9),
                'max_daily_attempts' => getSetting('standard_max_daily_attempts', 3),
                'assigned_to' => Auth::id(),
            ]);
            
            // Ajouter les produits
            foreach ($validated['products'] as $productData) {
                $product = Product::find($productData['id']);
                
                // Vérifier si le produit existe, sinon le créer automatiquement
                if (!$product) {
                    $product = Product::create([
                        'admin_id' => $admin_id,
                        'name' => 'Nouveau produit ' . $productData['id'],
                        'price' => 0,
                        'stock' => 1000000, // 1M par défaut
                        'active' => true,
                    ]);
                }
                
                $order->products()->attach($product->id, [
                    'quantity' => $productData['quantity'],
                    'confirmed_price' => $product->price,
                ]);
            }
            
            // Gérer les différents statuts
            if ($validated['status'] === 'confirmed') {
                $order->confirm($validated['confirmed_price'] ?? null, $validated['note'] ?? null);
            } elseif ($validated['status'] === 'cancelled') {
                $order->cancel($validated['note'] ?? 'Commande annulée lors de la création');
            } elseif ($validated['status'] === 'scheduled') {
                $order->schedule($validated['scheduled_date'], $validated['note'] ?? null);
            } else {
                $order->addHistory('create', $validated['note'] ?? 'Nouvelle commande créée');
            }
            
            DB::commit();
            
            return redirect()->route('orders.show', $order)->with('success', 'Commande créée avec succès!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création de la commande: ' . $e->getMessage())->withInput();
        }
    }
    
    public function show(Order $order)
    {
        // Vérifier que l'utilisateur a accès à cette commande
        $admin_id = Auth::user()->admin_id ?? Auth::guard('admin')->id();
        
        if ($order->admin_id != $admin_id) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        $order->load('products', 'histories.user');
        
        $products = Product::where('admin_id', $admin_id)
                          ->where('active', true)
                          ->get();
        $regions = tunisianRegions();
        
        return view('orders.show', compact('order', 'products', 'regions'));
    }
    
    public function update(Request $request, Order $order)
    {
        // Vérifier que l'utilisateur a accès à cette commande
        $admin_id = Auth::user()->admin_id ?? Auth::guard('admin')->id();
        
        if ($order->admin_id != $admin_id) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone1' => 'required|string|max:20',
            'customer_phone2' => 'nullable|string|max:20',
            'delivery_address' => 'required|string',
            'region' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Mettre à jour les informations de base
            $order->update([
                'customer_name' => $validated['customer_name'],
                'customer_phone1' => $validated['customer_phone1'],
                'customer_phone2' => $validated['customer_phone2'] ?? null,
                'delivery_address' => $validated['delivery_address'],
                'region' => $validated['region'],
                'city' => $validated['city'],
                'total_price' => $validated['total_price'],
            ]);
            
            // Mettre à jour les produits
            $order->products()->detach();
            foreach ($validated['products'] as $productData) {
                $product = Product::find($productData['id']);
                $order->products()->attach($product->id, [
                    'quantity' => $productData['quantity'],
                    'confirmed_price' => $product->price,
                ]);
            }
            
            $order->addHistory('update', 'Commande mise à jour');
            
            DB::commit();
            
            return redirect()->route('orders.show', $order)->with('success', 'Commande mise à jour avec succès!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la mise à jour de la commande: ' . $e->getMessage())->withInput();
        }
    }
    
    public function process(Request $request, Order $order)
    {
        // Vérifier que l'utilisateur a accès à cette commande
        $admin_id = Auth::user()->admin_id ?? Auth::guard('admin')->id();
        
        if ($order->admin_id != $admin_id) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        $validated = $request->validate([
            'action' => 'required|in:confirm,cancel,no_answer,schedule',
            'note' => 'nullable|string',
            'confirmed_price' => 'nullable|required_if:action,confirm|numeric|min:0',
            'scheduled_date' => 'nullable|required_if:action,schedule|date|after:today',
        ]);
        
        switch ($validated['action']) {
            case 'confirm':
                // Vérifier que tous les champs obligatoires sont remplis
                if (empty($order->customer_name) || empty($order->delivery_address) || empty($order->region) || empty($order->city)) {
                    return back()->with('error', 'Tous les champs clients sont obligatoires pour confirmer une commande')->withInput();
                }
                
                $order->confirm($validated['confirmed_price'], $validated['note']);
                $message = 'Commande confirmée avec succès!';
                break;
                
            case 'cancel':
                if (empty($validated['note'])) {
                    return back()->with('error', 'La note est obligatoire pour annuler une commande')->withInput();
                }
                
                $order->cancel($validated['note']);
                $message = 'Commande annulée avec succès!';
                break;
                
            case 'no_answer':
                if (empty($validated['note'])) {
                    return back()->with('error', 'La note est obligatoire pour marquer une tentative sans réponse')->withInput();
                }
                
                $order->recordAttempt('no_answer', $validated['note']);
                $message = 'Tentative enregistrée avec succès!';
                break;
                
            case 'schedule':
                if (empty($validated['note']) || empty($validated['scheduled_date'])) {
                    return back()->with('error', 'La note et la date sont obligatoires pour programmer une commande')->withInput();
                }
                
                $order->schedule($validated['scheduled_date'], $validated['note']);
                $message = 'Commande programmée avec succès!';
                break;
        }
        
        return redirect()->back()->with('success', $message);
    }
    
    public function requestMoreOrders()
    {
        $admin_id = Auth::user()->admin_id;
        $user_id = Auth::id();
        
        // Trouver les 10 plus anciennes commandes non assignées
        $unassignedOrders = Order::where('admin_id', $admin_id)
            ->where('status', 'new')
            ->whereNull('assigned_to')
            ->orderBy('created_at')
            ->limit(10)
            ->get();
            
        foreach ($unassignedOrders as $order) {
            $order->update(['assigned_to' => $user_id]);
        }
        
        return redirect()->route('orders.standard')
            ->with('success', 'Vous avez reçu ' . count($unassignedOrders) . ' nouvelles commandes!');
    }
    
    public function import()
    {
        // Vérifier si l'utilisateur est admin ou manager
        if (Auth::user()->role !== 'manager' && !Auth::guard('admin')->check()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        return view('orders.import');
    }
    
    public function importCsv(Request $request)
    {
        // Vérifier si l'utilisateur est admin ou manager
        if (Auth::user()->role !== 'manager' && !Auth::guard('admin')->check()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        $admin_id = Auth::user()->admin_id ?? Auth::guard('admin')->id();
        
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);
        
        // Logique d'importation CSV à implémenter ici
        
        return redirect()->route('orders.search')->with('success', 'Commandes importées avec succès!');
    }
}