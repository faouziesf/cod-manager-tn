<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $admin_id = Auth::user()->admin_id;
        $orders = Order::where('admin_id', $admin_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('manager.orders.index', compact('orders'));
    }

    public function dated()
    {
        $admin_id = Auth::user()->admin_id;
        $orders = Order::where('admin_id', $admin_id)
                       ->where('status', 'scheduled')
                       ->orderBy('delivery_date', 'asc')
                       ->paginate(10);
        
        return view('manager.orders.dated', [
            'orders' => $orders,
            'title' => 'Commandes datées'
        ]);
    }
    
    public function standard()
    {
        $admin_id = Auth::user()->admin_id;
        
        $order = Order::standard()
            ->where('admin_id', $admin_id)
            ->orderBy('attempt_count')
            ->orderBy('created_at', 'desc')
            ->first();
        
        $products = Product::where('admin_id', $admin_id)
            ->where('active', true)
            ->get();
        
        return view('manager.orders.standard', compact('order', 'products'));
    }
    
    public function scheduled()
    {
        $admin_id = Auth::user()->admin_id;
        
        $order = Order::scheduled()
            ->where('admin_id', $admin_id)
            ->orderBy('attempt_count')
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
        
        return view('manager.orders.scheduled', compact('order', 'reminder', 'products'));
    }
    
    public function old()
    {
        $admin_id = Auth::user()->admin_id;
        
        $order = Order::old()
            ->where('admin_id', $admin_id)
            ->orderBy('last_attempt_at')
            ->first();
        
        $products = Product::where('admin_id', $admin_id)
            ->where('active', true)
            ->get();
        
        return view('manager.orders.old', compact('order', 'products'));
    }
    
    public function needsVerification()
    {
        $admin_id = Auth::user()->admin_id;
        
        $orders = Order::where('admin_id', $admin_id)
            ->needsVerification()
            ->with('products')
            ->paginate(20);
        
        return view('manager.orders.needs-verification', compact('orders'));
    }
    
    public function search(Request $request)
    {
        $admin_id = Auth::user()->admin_id;
        
        // Récupérer les employés pour le formulaire de recherche
        $employees = User::where('admin_id', $admin_id)
                        ->where('role', 'employee')
                        ->get();
        
        $query = Order::where('admin_id', $admin_id);
        
        // Appliquer les filtres de recherche
        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'like', '%' . $request->input('customer_name') . '%');
        }
        
        if ($request->filled('customer_phone')) {
            $phone = $request->input('customer_phone');
            $query->where(function ($q) use ($phone) {
                $q->where('customer_phone1', 'like', '%' . $phone . '%')
                  ->orWhere('customer_phone2', 'like', '%' . $phone . '%');
            });
        }
        
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->input('city') . '%');
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('user_id')) {
            $query->where('assigned_to', $request->input('user_id'));
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(25);
        
        return view('manager.orders.search', compact('orders', 'employees'));
    }
    
    public function create()
    {
        $admin_id = Auth::user()->admin_id;
        
        $products = Product::where('admin_id', $admin_id)
            ->where('active', true)
            ->get();
        
        $employees = User::where('admin_id', $admin_id)
            ->where('role', 'employee')
            ->get();
            
        $regions = tunisianRegions();
        
        return view('manager.orders.create', compact('products', 'employees', 'regions'));
    }
    
    public function store(Request $request)
    {
        $admin_id = Auth::user()->admin_id;
        
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
            foreach ($validated['products'] as $productId => $productData) {
                $product = Product::find($productId);
                
                if ($product && $product->admin_id == $admin_id) {
                    $order->products()->attach($productId, [
                        'quantity' => $productData['quantity'],
                        'confirmed_price' => $product->price,
                    ]);
                }
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
            
            return redirect()->route('manager.orders.show', $order)->with('success', 'Commande créée avec succès!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création de la commande: ' . $e->getMessage())->withInput();
        }
    }
    
    public function show(Order $order)
    {
        // Vérifier que l'utilisateur a accès à cette commande
        $admin_id = Auth::user()->admin_id;
        
        if ($order->admin_id != $admin_id) {
            return redirect()->route('manager.dashboard')->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        $order->load('products', 'histories.user');
        
        $products = Product::where('admin_id', $admin_id)
            ->where('active', true)
            ->get();
        
        $regions = tunisianRegions();
        
        return view('manager.orders.show', compact('order', 'products', 'regions'));
    }
    
    public function edit(Order $order)
    {
        // Vérifier que l'utilisateur a accès à cette commande
        $admin_id = Auth::user()->admin_id;
        
        if ($order->admin_id != $admin_id) {
            return redirect()->route('manager.dashboard')->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        $order->load('products');
        
        $products = Product::where('admin_id', $admin_id)
            ->where('active', true)
            ->get();
            
        $employees = User::where('admin_id', $admin_id)
            ->where('role', 'employee')
            ->get();
        
        $regions = tunisianRegions();
        
        return view('manager.orders.edit', compact('order', 'products', 'employees', 'regions'));
    }
    
    public function update(Request $request, Order $order)
    {
        // Vérifier que l'utilisateur a accès à cette commande
        $admin_id = Auth::user()->admin_id;
        
        if ($order->admin_id != $admin_id) {
            return redirect()->route('manager.dashboard')->with('error', 'Vous n\'avez pas accès à cette commande.');
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
            'assigned_to' => 'nullable|exists:users,id',
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
                'assigned_to' => $validated['assigned_to'] ?? $order->assigned_to,
            ]);
            
            // Mettre à jour les produits
            $order->products()->detach();
            foreach ($validated['products'] as $productId => $productData) {
                $product = Product::find($productId);
                
                if ($product && $product->admin_id == $admin_id) {
                    $order->products()->attach($productId, [
                        'quantity' => $productData['quantity'],
                        'confirmed_price' => $product->price,
                    ]);
                }
            }
            
            $order->addHistory('update', 'Commande mise à jour par manager');
            
            DB::commit();
            
            return redirect()->route('manager.orders.show', $order)->with('success', 'Commande mise à jour avec succès!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la mise à jour de la commande: ' . $e->getMessage())->withInput();
        }
    }
    
    public function process(Request $request, Order $order)
    {
        // Vérifier que l'utilisateur a accès à cette commande
        $admin_id = Auth::user()->admin_id;
        
        if ($order->admin_id != $admin_id) {
            return redirect()->route('manager.dashboard')->with('error', 'Vous n\'avez pas accès à cette commande.');
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
    
    public function assign(Request $request, Order $order)
    {
        // Vérifier que l'utilisateur a accès à cette commande
        $admin_id = Auth::user()->admin_id;
        
        if ($order->admin_id != $admin_id) {
            return redirect()->route('manager.dashboard')->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        
        // Vérifier que l'utilisateur sélectionné appartient au même admin
        $user = User::find($validated['user_id']);
        if (!$user || $user->admin_id != $admin_id) {
            return back()->with('error', 'Utilisateur invalide.')->withInput();
        }
        
        $order->update(['assigned_to' => $validated['user_id']]);
        $order->addHistory('assign', 'Commande assignée à ' . $user->name);
        
        return back()->with('success', 'Commande assignée avec succès!');
    }
    
    public function destroy(Order $order)
    {
        // Vérifier que l'utilisateur a accès à cette commande
        $admin_id = Auth::user()->admin_id;
        
        if ($order->admin_id != $admin_id) {
            return redirect()->route('manager.dashboard')->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        // Vérifier si la commande peut être supprimée
        if ($order->status == 'confirmed') {
            return back()->with('error', 'Impossible de supprimer une commande confirmée.');
        }
        
        try {
            $order->products()->detach();
            $order->histories()->delete();
            $order->delete();
            
            return redirect()->route('manager.orders.index')->with('success', 'Commande supprimée avec succès!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression de la commande: ' . $e->getMessage());
        }
    }
    
    public function import()
    {
        return view('manager.orders.import');
    }
    
    public function importCsv(Request $request)
    {
        $admin_id = Auth::user()->admin_id;
        
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'has_header' => 'nullable',
        ]);
        
        // Logique d'importation CSV
        try {
            $file = $request->file('csv_file');
            $hasHeader = $request->has('has_header');
            $path = $file->getRealPath();
            $data = array_map('str_getcsv', file($path));
            
            // Supprimer l'en-tête si nécessaire
            if ($hasHeader) {
                array_shift($data);
            }
            
            $successCount = 0;
            
            DB::beginTransaction();
            
            foreach ($data as $row) {
                // Assurez-vous qu'il y a suffisamment de colonnes
                if (count($row) < 6) continue;
                
                // Créer la commande
                $order = Order::create([
                    'admin_id' => $admin_id,
                    'user_id' => Auth::id(),
                    'customer_name' => $row[0] ?? '',
                    'customer_phone1' => $row[1] ?? '',
                    'customer_phone2' => $row[2] ?? '',
                    'delivery_address' => $row[3] ?? '',
                    'region' => $row[4] ?? '',
                    'city' => $row[5] ?? '',
                    'total_price' => $row[8] ?? 0,
                    'status' => 'new',
                    'max_attempts' => getSetting('standard_max_attempts', 9),
                    'max_daily_attempts' => getSetting('standard_max_daily_attempts', 3),
                ]);
                
                // Traiter les produits
                $productNames = explode(',', $row[6] ?? '');
                $quantities = explode(',', $row[7] ?? '');
                
                for ($i = 0; $i < count($productNames); $i++) {
                    $productName = trim($productNames[$i]);
                    $quantity = isset($quantities[$i]) ? (int)trim($quantities[$i]) : 1;
                    
                    if (empty($productName)) continue;
                    
                    // Trouver ou créer le produit
                    $product = Product::firstOrCreate(
                        ['admin_id' => $admin_id, 'name' => $productName],
                        ['price' => 0, 'stock' => 1000000, 'active' => true]
                    );
                    
                    // Attacher le produit à la commande
                    $order->products()->attach($product->id, [
                        'quantity' => $quantity,
                        'confirmed_price' => $product->price,
                    ]);
                }
                
                $order->addHistory('import', 'Commande importée par CSV (manager)');
                $successCount++;
            }
            
            DB::commit();
            
            return redirect()->route('manager.orders.search')->with('success', $successCount . ' commandes importées avec succès!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'importation: ' . $e->getMessage())->withInput();
        }
    }
}