<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderProduct;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $adminId = Auth::guard('admin')->id();
        $query = Order::where('admin_id', $adminId);
        
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
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Pour les filtres du formulaire
        $statuses = [
            'new' => 'Nouvelle',
            'processing' => 'En traitement',
            'confirmed' => 'Confirmée',
            'shipped' => 'Expédiée',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
            'returned' => 'Retournée'
        ];
        
        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $adminId = Auth::guard('admin')->id();
        $products = Product::where('admin_id', $adminId)->where('active', true)->get();
        $employees = User::where('admin_id', $adminId)->where('active', true)->get();
        
        return view('admin.orders.create', compact('products', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone1' => 'required|string|max:20',
            'customer_phone2' => 'nullable|string|max:20',
            'delivery_address' => 'required|string',
            'region' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'scheduled_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'total_price' => 'nullable|numeric|min:0',
            'external_id' => 'nullable|string|max:100',
            'external_source' => 'nullable|string|max:100',
        ]);
        
        $adminId = Auth::guard('admin')->id();
        
        // Vérifier que les produits appartiennent à l'admin
        foreach ($request->products as $productData) {
            $product = Product::findOrFail($productData['id']);
            if ($product->admin_id !== $adminId) {
                return redirect()->back()
                    ->withErrors(['products' => 'Un des produits sélectionnés n\'appartient pas à cet administrateur'])
                    ->withInput();
            }
        }
        
        // Récupérer les paramètres de tentatives
        $maxAttempts = Setting::where('key', 'standard_max_attempts')->value('value') ?? 9;
        $maxDailyAttempts = Setting::where('key', 'standard_max_daily_attempts')->value('value') ?? 3;
        
        // Si la commande est programmée, utiliser les paramètres de commandes programmées
        if ($request->filled('scheduled_date')) {
            $maxAttempts = Setting::where('key', 'scheduled_max_attempts')->value('value') ?? 5;
            $maxDailyAttempts = Setting::where('key', 'scheduled_max_daily_attempts')->value('value') ?? 2;
        }
        
        // Créer la commande
        $order = new Order();
        $order->admin_id = $adminId;
        $order->customer_name = $validated['customer_name'];
        $order->customer_phone1 = $validated['customer_phone1'];
        $order->customer_phone2 = $validated['customer_phone2'] ?? null;
        $order->delivery_address = $validated['delivery_address'];
        $order->region = $validated['region'];
        $order->city = $validated['city'];
        $order->status = 'new';
        $order->max_attempts = $maxAttempts;
        $order->max_daily_attempts = $maxDailyAttempts;
        $order->scheduled_date = $validated['scheduled_date'] ?? null;
        $order->assigned_to = $validated['assigned_to'] ?? null;
        $order->external_id = $validated['external_id'] ?? null;
        $order->external_source = $validated['external_source'] ?? null;
        
        // Calculer le prix total si ce n'est pas fourni
        if (empty($validated['total_price'])) {
            $totalPrice = 0;
            foreach ($request->products as $productData) {
                $product = Product::find($productData['id']);
                $totalPrice += $product->price * $productData['quantity'];
            }
            $order->total_price = $totalPrice;
        } else {
            $order->total_price = $validated['total_price'];
        }
        
        $order->save();
        
        // Ajouter les produits à la commande
        foreach ($request->products as $productData) {
            $product = Product::find($productData['id']);
            OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $productData['quantity'],
                'confirmed_price' => $product->price * $productData['quantity']
            ]);
        }
        
        // Créer l'historique
        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::guard('admin')->id(),
            'action' => 'Création de la commande',
            'note' => 'Commande créée par ' . Auth::guard('admin')->user()->name
        ]);
        
        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Commande créée avec succès');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        // Vérifier que la commande appartient à l'admin connecté
        if ($order->admin_id !== Auth::guard('admin')->id() && !Auth::guard('admin')->user()->is_super_admin) {
            abort(403, 'Accès non autorisé à cette commande');
        }
        
        $order->load(['products', 'histories', 'assignedTo']);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        // Vérifier que la commande appartient à l'admin connecté
        if ($order->admin_id !== Auth::guard('admin')->id() && !Auth::guard('admin')->user()->is_super_admin) {
            abort(403, 'Accès non autorisé à cette commande');
        }
        
        $adminId = Auth::guard('admin')->id();
        $products = Product::where('admin_id', $adminId)->where('active', true)->get();
        $employees = User::where('admin_id', $adminId)->where('active', true)->get();
        $orderProducts = $order->products;
        
        $statuses = [
            'new' => 'Nouvelle',
            'processing' => 'En traitement',
            'confirmed' => 'Confirmée',
            'shipped' => 'Expédiée',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
            'returned' => 'Retournée'
        ];
        
        return view('admin.orders.edit', compact('order', 'products', 'employees', 'orderProducts', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        // Vérifier que la commande appartient à l'admin connecté
        if ($order->admin_id !== Auth::guard('admin')->id() && !Auth::guard('admin')->user()->is_super_admin) {
            return redirect()->back()->withErrors(['error' => 'Vous n\'êtes pas autorisé à modifier cette commande']);
        }
        
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone1' => 'required|string|max:20',
            'customer_phone2' => 'nullable|string|max:20',
            'delivery_address' => 'required|string',
            'region' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'status' => 'required|string|in:new,processing,confirmed,shipped,delivered,cancelled,returned',
            'callback_date' => 'nullable|date',
            'scheduled_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'total_price' => 'required|numeric|min:0',
            'confirmed_price' => 'nullable|numeric|min:0',
            'external_id' => 'nullable|string|max:100',
            'external_source' => 'nullable|string|max:100',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.confirmed_price' => 'nullable|numeric|min:0',
        ]);
        
        // Vérifier les produits
        $adminId = Auth::guard('admin')->id();
        foreach ($request->products as $productData) {
            $product = Product::findOrFail($productData['id']);
            if ($product->admin_id !== $adminId && !Auth::guard('admin')->user()->is_super_admin) {
                return redirect()->back()
                    ->withErrors(['products' => 'Un des produits sélectionnés n\'appartient pas à cet administrateur'])
                    ->withInput();
            }
        }
        
        // Stocker le statut original pour l'historique
        $originalStatus = $order->status;
        
        // Mettre à jour les champs
        $order->customer_name = $validated['customer_name'];
        $order->customer_phone1 = $validated['customer_phone1'];
        $order->customer_phone2 = $validated['customer_phone2'] ?? null;
        $order->delivery_address = $validated['delivery_address'];
        $order->region = $validated['region'];
        $order->city = $validated['city'];
        $order->status = $validated['status'];
        $order->callback_date = $validated['callback_date'] ?? null;
        $order->scheduled_date = $validated['scheduled_date'] ?? null;
        $order->assigned_to = $validated['assigned_to'] ?? null;
        $order->total_price = $validated['total_price'];
        $order->confirmed_price = $validated['confirmed_price'] ?? null;
        $order->external_id = $validated['external_id'] ?? null;
        $order->external_source = $validated['external_source'] ?? null;
        
        // Si le statut a changé, enregistrer dans l'historique
        if ($order->status !== $originalStatus) {
            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => Auth::guard('admin')->id(),
                'action' => 'Changement de statut',
                'note' => "Statut modifié de '{$originalStatus}' à '{$order->status}'"
            ]);
        }
        
        $order->save();
        
        // Mettre à jour les produits de la commande
        $order->products()->detach(); // Supprimer les anciennes relations
        
        foreach ($request->products as $productData) {
            $order->products()->attach($productData['id'], [
                'quantity' => $productData['quantity'],
                'confirmed_price' => $productData['confirmed_price'] ?? null
            ]);
        }
        
        // Créer une entrée d'historique pour la mise à jour
        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::guard('admin')->id(),
            'action' => 'Mise à jour de la commande',
            'note' => 'Commande mise à jour par ' . Auth::guard('admin')->user()->name
        ]);
        
        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Commande mise à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        // Vérifier que la commande appartient à l'admin connecté
        if ($order->admin_id !== Auth::guard('admin')->id() && !Auth::guard('admin')->user()->is_super_admin) {
            abort(403, 'Accès non autorisé à cette commande');
        }
        
        // Supprimer les produits liés à la commande (OrderProduct)
        $order->products()->detach();
        
        // Supprimer les historiques liés à la commande
        $order->histories()->delete();
        
        // Supprimer la commande
        $order->delete();
        
        return redirect()->route('admin.orders.index')
            ->with('success', 'Commande supprimée avec succès');
    }
    
    /**
     * Mettre à jour le statut d'une commande
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Vérifier que la commande appartient à l'admin connecté
        if ($order->admin_id !== Auth::guard('admin')->id() && !Auth::guard('admin')->user()->is_super_admin) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }
        
        $validated = $request->validate([
            'status' => 'required|string|in:new,processing,confirmed,shipped,delivered,cancelled,returned',
            'note' => 'nullable|string'
        ]);
        
        $originalStatus = $order->status;
        $order->status = $validated['status'];
        $order->save();
        
        // Créer une entrée d'historique
        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::guard('admin')->id(),
            'action' => 'Changement de statut',
            'note' => "Statut modifié de '{$originalStatus}' à '{$order->status}'" . 
                    ($validated['note'] ? " - Note: {$validated['note']}" : "")
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès',
            'new_status' => $order->status
        ]);
    }
    
    /**
     * Assigner une commande à un utilisateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function assignOrder(Request $request, Order $order)
    {
        // Vérifier que la commande appartient à l'admin connecté
        if ($order->admin_id !== Auth::guard('admin')->id() && !Auth::guard('admin')->user()->is_super_admin) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'note' => 'nullable|string'
        ]);
        
        $user = User::findOrFail($validated['user_id']);
        
        // Vérifier que l'utilisateur appartient à cet admin
        if ($user->admin_id !== Auth::guard('admin')->id() && !Auth::guard('admin')->user()->is_super_admin) {
            return response()->json(['error' => 'Cet utilisateur n\'est pas sous votre administration'], 403);
        }
        
        $previousUser = $order->assignedTo ? $order->assignedTo->name : 'Personne';
        
        $order->assigned_to = $validated['user_id'];
        $order->save();
        
        // Créer une entrée d'historique
        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::guard('admin')->id(),
            'action' => 'Assignation',
            'note' => "Commande assignée de '{$previousUser}' à '{$user->name}'" . 
                    ($validated['note'] ? " - Note: {$validated['note']}" : "")
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Commande assignée avec succès',
            'assigned_to' => $user->name
        ]);
    }
    
    /**
     * Réinitialiser les compteurs de tentatives d'une commande
     * 
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function resetAttempts(Order $order)
    {
        // Vérifier que la commande appartient à l'admin connecté
        if ($order->admin_id !== Auth::guard('admin')->id() && !Auth::guard('admin')->user()->is_super_admin) {
            return redirect()->back()->withErrors(['error' => 'Vous n\'êtes pas autorisé à modifier cette commande']);
        }
        
        $order->attempt_count = 0;
        $order->daily_attempt_count = 0;
        $order->last_attempt_at = null;
        $order->next_attempt_at = null;
        $order->save();
        
        // Créer une entrée d'historique
        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::guard('admin')->id(),
            'action' => 'Réinitialisation des tentatives',
            'note' => 'Compteurs de tentatives réinitialisés par ' . Auth::guard('admin')->user()->name
        ]);
        
        return redirect()->back()->with('success', 'Compteurs de tentatives réinitialisés avec succès');
    }
    



    public function import()
    {
        // Récupérer les paramètres d'importation
        $wooCommerceSettings = [
            'api_url' => Setting::where('key', 'woocommerce_api_url')->value('value') ?? '',
            'api_key' => Setting::where('key', 'woocommerce_api_key')->value('value') ?? '',
            'api_secret' => Setting::where('key', 'woocommerce_api_secret')->value('value') ?? '',
            'status_to_import' => Setting::where('key', 'woocommerce_status_to_import')->value('value') ?? 'processing',
        ];
        
        $googleSheetId = Setting::where('key', 'google_sheet_id')->value('value') ?? '';
        
        // Récupérer le nombre de commandes importées récemment
        $recentImports = Order::where('admin_id', Auth::guard('admin')->id())
            ->whereNotNull('external_id')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->count();
            
        // Récupérer les 10 dernières commandes importées
        $lastImportedOrders = Order::where('admin_id', Auth::guard('admin')->id())
            ->whereNotNull('external_id')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('admin.orders.import', compact(
            'wooCommerceSettings',
            'googleSheetId',
            'recentImports',
            'lastImportedOrders'
        ));
    }
    
    /**
     * Importer des commandes depuis un fichier CSV
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);
        
        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        // Ouvrir le fichier
        $handle = fopen($path, 'r');
        
        // Lire l'en-tête
        $header = fgetcsv($handle, 1000, ',');
        
        // Préparer les colonnes requises
        $requiredColumns = ['customer_name', 'customer_phone1', 'delivery_address', 'region', 'city'];
        $columnIndexes = [];
        
        // Vérifier si l'en-tête contient les colonnes requises
        foreach ($requiredColumns as $column) {
            $index = array_search($column, $header);
            if ($index === false) {
                return redirect()->back()->withErrors(['csv_file' => "Le fichier CSV manque de la colonne requise: {$column}"]);
            }
            $columnIndexes[$column] = $index;
        }
        
        // Colonnes facultatives
        $optionalColumns = ['customer_phone2', 'total_price', 'external_id', 'external_source', 'product_name', 'product_quantity', 'product_price'];
        foreach ($optionalColumns as $column) {
            $index = array_search($column, $header);
            $columnIndexes[$column] = $index !== false ? $index : null;
        }
        
        $adminId = Auth::guard('admin')->id();
        $importCount = 0;
        $errorCount = 0;
        $errors = [];
        
        // Récupérer les paramètres de tentatives
        $maxAttempts = Setting::where('key', 'standard_max_attempts')->value('value') ?? 9;
        $maxDailyAttempts = Setting::where('key', 'standard_max_daily_attempts')->value('value') ?? 3;
        
        DB::beginTransaction();
        
        try {
            // Parcourir les lignes du CSV
            $lineNumber = 1;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $lineNumber++;
                
                // Vérifier si la ligne a assez de colonnes
                if (count($data) < count($header)) {
                    $errors[] = "Ligne {$lineNumber}: Pas assez de colonnes.";
                    $errorCount++;
                    continue;
                }
                
                // Créer une nouvelle commande
                $order = new Order();
                $order->admin_id = $adminId;
                $order->customer_name = $data[$columnIndexes['customer_name']];
                $order->customer_phone1 = $data[$columnIndexes['customer_phone1']];
                $order->customer_phone2 = $columnIndexes['customer_phone2'] !== null ? $data[$columnIndexes['customer_phone2']] : null;
                $order->delivery_address = $data[$columnIndexes['delivery_address']];
                $order->region = $data[$columnIndexes['region']];
                $order->city = $data[$columnIndexes['city']];
                $order->status = 'new';
                $order->max_attempts = $maxAttempts;
                $order->max_daily_attempts = $maxDailyAttempts;
                
                // Colonnes facultatives
                if ($columnIndexes['total_price'] !== null) {
                    $order->total_price = $data[$columnIndexes['total_price']];
                }
                
                if ($columnIndexes['external_id'] !== null) {
                    $order->external_id = $data[$columnIndexes['external_id']];
                }
                
                if ($columnIndexes['external_source'] !== null) {
                    $order->external_source = $data[$columnIndexes['external_source']];
                }
                
                $order->save();
                
                // Ajouter les produits si disponibles
                if ($columnIndexes['product_name'] !== null && $columnIndexes['product_quantity'] !== null) {
                    $productName = $data[$columnIndexes['product_name']];
                    $productQuantity = $data[$columnIndexes['product_quantity']];
                    $productPrice = $columnIndexes['product_price'] !== null ? $data[$columnIndexes['product_price']] : 0;
                    
                    if (!empty($productName) && !empty($productQuantity)) {
                        // Chercher le produit ou en créer un nouveau
                        $product = Product::firstOrCreate(
                            ['name' => $productName, 'admin_id' => $adminId],
                            [
                                'price' => $productPrice,
                                'stock' => 0,
                                'active' => true
                            ]
                        );
                        
                        // Ajouter le produit à la commande
                        OrderProduct::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'quantity' => $productQuantity,
                            'confirmed_price' => $productPrice * $productQuantity
                        ]);
                    }
                }
                
                // Créer l'historique
                OrderHistory::create([
                    'order_id' => $order->id,
                    'user_id' => $adminId,
                    'action' => 'Importation CSV',
                    'note' => 'Commande importée depuis CSV'
                ]);
                
                $importCount++;
            }
            
            fclose($handle);
            DB::commit();
            
            return redirect()->route('admin.orders.index')
                ->with('success', "Importation réussie: {$importCount} commandes importées, {$errorCount} erreurs.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['csv_file' => 'Erreur lors de l\'importation: ' . $e->getMessage()]);
        }
    }
}