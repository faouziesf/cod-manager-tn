<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function standard()
    {
        $query = Order::where('status', 'new')
            ->where(function($q) {
                $q->where('next_attempt_at', '<=', now())
                   ->orWhereNull('next_attempt_at');
            })
            ->where(function($q) {
                $q->where('daily_attempt_count', '<', DB::raw('max_daily_attempts'))
                   ->orWhereDate('last_attempt_at', '<', now()->toDateString())
                   ->orWhereNull('last_attempt_at');
            });
            
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('manager')) {
            $query->where(function($q) {
                $q->where('assigned_to', auth()->id())
                  ->orWhereNull('assigned_to');
            });
        }
        
        $order = $query->orderBy('attempt_count')
                       ->orderBy('created_at', 'desc')
                       ->first();
        
        // Si pas de commande et l'utilisateur n'est ni admin ni manager
        $needsMoreOrders = false;
        if (!$order && !auth()->user()->hasRole('admin') && !auth()->user()->hasRole('manager')) {
            $needsMoreOrders = true;
        }
        
        $products = Product::where('is_active', true)->get();
        $regions = tunisianRegions();
        
        return view('orders.standard', compact('order', 'needsMoreOrders', 'products', 'regions'));
    }
    
    public function scheduled()
    {
        $query = Order::where('status', 'scheduled')
            ->whereDate('scheduled_date', '<=', now())
            ->where(function($q) {
                $q->where('next_attempt_at', '<=', now())
                   ->orWhereNull('next_attempt_at');
            })
            ->where(function($q) {
                $q->where('daily_attempt_count', '<', DB::raw('max_daily_attempts'))
                   ->orWhereDate('last_attempt_at', '<', now()->toDateString())
                   ->orWhereNull('last_attempt_at');
            });
            
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('manager')) {
            $query->where(function($q) {
                $q->where('assigned_to', auth()->id())
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
        
        $products = Product::where('is_active', true)->get();
        $regions = tunisianRegions();
        
        return view('orders.scheduled', compact('order', 'reminder', 'products', 'regions'));
    }
    
    public function old()
    {
        $query = Order::where('status', 'old')
            ->where(function($q) {
                $q->where('next_attempt_at', '<=', now())
                   ->orWhereNull('next_attempt_at');
            });
            
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('manager')) {
            $query->where(function($q) {
                $q->where('assigned_to', auth()->id())
                  ->orWhereNull('assigned_to');
            });
        }
        
        $order = $query->orderBy('last_attempt_at')
                       ->first();
        
        $products = Product::where('is_active', true)->get();
        $regions = tunisianRegions();
        
        return view('orders.old', compact('order', 'products', 'regions'));
    }
    
    public function needsVerification()
    {
        // Vérifier si l'utilisateur a les permissions
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('manager')) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        $orders = Order::whereHas('products', function($q) {
                $q->where('stock', '<=', 0);
            })
            ->with('products')
            ->paginate(20);
        
        return view('orders.needs-verification', compact('orders'));
    }
    
    public function search(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        
        $query = Order::query();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone1', 'like', "%{$search}%")
                  ->orWhere('phone2', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $orders = $query->orderBy('id', 'desc')->paginate(25);
        
        return view('orders.search', compact('orders', 'search', 'status'));
    }
    
    public function create()
    {
        $products = Product::where('is_active', true)->get();
        $regions = tunisianRegions();
        
        return view('orders.create', compact('products', 'regions'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'phone1' => 'required|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'address' => 'nullable|string',
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
                'customer_name' => $validated['customer_name'],
                'phone1' => $validated['phone1'],
                'phone2' => $validated['phone2'] ?? null,
                'address' => $validated['address'] ?? null,
                'region' => $validated['region'] ?? null,
                'city' => $validated['city'] ?? null,
                'total_price' => $validated['total_price'],
                'status' => $validated['status'],
                'max_attempts' => getSetting('standard_max_attempts', 9),
                'max_daily_attempts' => getSetting('standard_max_daily_attempts', 3),
                'assigned_to' => auth()->id(),
            ]);
            
            // Ajouter les produits
            foreach ($validated['products'] as $productData) {
                $product = Product::find($productData['id']);
                
                // Vérifier si le produit existe, sinon le créer automatiquement
                if (!$product) {
                    $product = Product::create([
                        'name' => 'Nouveau produit ' . $productData['id'],
                        'price' => 0,
                        'stock' => 1000000, // 1M par défaut
                        'is_active' => true,
                    ]);
                }
                
                $order->products()->attach($product->id, [
                    'quantity' => $productData['quantity'],
                    'price' => $product->price,
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
        $order->load('products', 'histories.user');
        $products = Product::where('is_active', true)->get();
        $regions = tunisianRegions();
        
        return view('orders.show', compact('order', 'products', 'regions'));
    }
    
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone1' => 'required|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'address' => 'required|string',
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
                'phone1' => $validated['phone1'],
                'phone2' => $validated['phone2'] ?? null,
                'address' => $validated['address'],
                'region' => $validated['region'],
                'city' => $validated['city'],
                'total_price' => $validated['total_price'],
            ]);
            
            // Mettre à jour les produits
            $order->products()->detach();
            foreach ($validated['products'] as $productData) {
                $order->products()->attach($productData['id'], [
                    'quantity' => $productData['quantity'],
                    'price' => Product::find($productData['id'])->price,
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
        $validated = $request->validate([
            'action' => 'required|in:confirm,cancel,no_answer,schedule',
            'note' => 'nullable|string',
            'confirmed_price' => 'nullable|required_if:action,confirm|numeric|min:0',
            'scheduled_date' => 'nullable|required_if:action,schedule|date|after:today',
        ]);
        
        switch ($validated['action']) {
            case 'confirm':
                // Vérifier que tous les champs obligatoires sont remplis
                if (empty($order->customer_name) || empty($order->address) || empty($order->region) || empty($order->city)) {
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
        $user = auth()->user();
        
        // Trouver les 10 plus anciennes commandes non assignées
        $unassignedOrders = Order::where('status', 'new')
            ->whereNull('assigned_to')
            ->orderBy('created_at')
            ->limit(10)
            ->get();
            
        foreach ($unassignedOrders as $order) {
            $order->update(['assigned_to' => $user->id]);
        }
        
        return redirect()->route('orders.standard')
            ->with('success', 'Vous avez reçu ' . count($unassignedOrders) . ' nouvelles commandes!');
    }
    
    public function import()
    {
        // Vérifier si l'utilisateur a les permissions
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('manager')) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        return view('orders.import');
    }
    
    public function importCsv(Request $request)
    {
        // Vérifier si l'utilisateur a les permissions
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('manager')) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);
        
        // Logique d'importation CSV à implémenter
        
        return redirect()->route('orders.search')->with('success', 'Commandes importées avec succès!');
    }
}