<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{
    /**
     * Affiche la liste des produits
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $products = Product::where('admin_id', Auth::id())
                        ->orderBy('name')
                        ->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Affiche le formulaire de création d'un produit
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = Product::where('admin_id', Auth::id())
                        ->whereNotNull('category')
                        ->distinct()
                        ->pluck('category')
                        ->toArray();

        return view('admin.products.create', compact('categories'));
    }

     /**
     * Enregistre un nouveau produit
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Débogage: Afficher toutes les données reçues
        \Log::info('Données produit reçues :', $request->all());
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048', // Changé pour accepter l'upload d'image
            'active' => 'boolean',
            'category' => 'nullable|string|max:100',
            'dimensions.weight' => 'nullable|numeric|min:0',
            'dimensions.length' => 'nullable|numeric|min:0',
            'dimensions.width' => 'nullable|numeric|min:0',
            'dimensions.height' => 'nullable|numeric|min:0',
            'attributes.keys.*' => 'nullable|string|max:100',
            'attributes.values.*' => 'nullable|string|max:255',
        ]);
        
        // Débogage: Afficher les données validées
        \Log::info('Données validées :', $validated);
    
        try {
            DB::beginTransaction();
            
            // Préparation des dimensions
            $dimensions = null;
            if ($request->has('dimensions')) {
                $dimensions = [
                    'weight' => $request->input('dimensions.weight'),
                    'dimensions' => [
                        'length' => $request->input('dimensions.length'),
                        'width' => $request->input('dimensions.width'),
                        'height' => $request->input('dimensions.height')
                    ]
                ];
            }
            
            // Préparation des attributs
            $attributes = [];
            $attributeKeys = $request->input('attributes.keys', []);
            $attributeValues = $request->input('attributes.values', []);
            
            foreach ($attributeKeys as $index => $key) {
                if (!empty($key) && isset($attributeValues[$index])) {
                    $attributes[$key] = $attributeValues[$index];
                }
            }
            
            // Gestion du stock (infini par défaut si non défini)
            $stock = $request->has('stock') ? $request->stock : 999999;
            
            // Traitement de l'image
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('products', 'public');
                \Log::info('Image téléchargée :', ['path' => $imagePath]);
            }
            
            // Création du produit
            $product = new Product();
            $product->admin_id = Auth::id();
            $product->name = $validated['name'];
            $product->description = $validated['description'] ?? null;
            $product->sku = $validated['sku'] ?? null;
            $product->price = $validated['price'];
            $product->stock = $stock;
            $product->image_path = $imagePath;
            $product->active = $request->has('active');
            $product->category = $validated['category'] ?? null;
            $product->dimensions = $dimensions;
            $product->attributes = !empty($attributes) ? $attributes : null;
            
            $saved = $product->save();
            \Log::info('Produit sauvegardé :', ['success' => $saved, 'product_id' => $product->id]);
            
            DB::commit();
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Produit créé avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la création du produit: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()
                ->with('error', 'Erreur lors de la création du produit: ' . $e->getMessage());
        }
    }

    /**
     * Affiche les détails d'un produit
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\View\View
     */
    public function show(Product $product)
    {
        // Vérifier que l'utilisateur a accès à ce produit
        if ($product->admin_id != Auth::id()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Vous n\'avez pas accès à ce produit');
        }

        return view('admin.products.show', compact('product'));
    }

    /**
     * Affiche le formulaire d'édition d'un produit
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\View\View
     */
    public function edit(Product $product)
    {
        // Vérifier que l'utilisateur a accès à ce produit
        if ($product->admin_id != Auth::id()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Vous n\'avez pas accès à ce produit');
        }
        
        $categories = Product::where('admin_id', Auth::id())
                        ->whereNotNull('category')
                        ->distinct()
                        ->pluck('category')
                        ->toArray();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Met à jour un produit
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Product $product)
    {
        // Vérifier que l'utilisateur a accès à ce produit
        if ($product->admin_id != Auth::id()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Vous n\'avez pas accès à ce produit');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048', // Changé pour accepter l'upload d'image
            'active' => 'boolean',
            'category' => 'nullable|string|max:100',
            'dimensions.weight' => 'nullable|numeric|min:0',
            'dimensions.length' => 'nullable|numeric|min:0',
            'dimensions.width' => 'nullable|numeric|min:0',
            'dimensions.height' => 'nullable|numeric|min:0',
            'attributes.keys.*' => 'nullable|string|max:100',
            'attributes.values.*' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            
            // Préparation des dimensions
            $dimensions = null;
            if ($request->has('dimensions')) {
                $dimensions = [
                    'weight' => $request->input('dimensions.weight'),
                    'dimensions' => [
                        'length' => $request->input('dimensions.length'),
                        'width' => $request->input('dimensions.width'),
                        'height' => $request->input('dimensions.height')
                    ]
                ];
            }
            
            // Préparation des attributs
            $attributes = [];
            $attributeKeys = $request->input('attributes.keys', []);
            $attributeValues = $request->input('attributes.values', []);
            
            foreach ($attributeKeys as $index => $key) {
                if (!empty($key) && isset($attributeValues[$index])) {
                    $attributes[$key] = $attributeValues[$index];
                }
            }
            
            // Gestion du stock (infini par défaut si non défini ou vide)
            $stock = $request->has('stock') && $request->stock !== null ? $request->stock : 999999;
            
            // Traitement de l'image
            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image si elle existe
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                
                $image = $request->file('image');
                $imagePath = $image->store('products', 'public');
                $product->image_path = $imagePath;
            }
            
            // Mise à jour du produit
            $product->name = $validated['name'];
            $product->description = $validated['description'] ?? null;
            $product->sku = $validated['sku'] ?? null;
            $product->price = $validated['price'];
            $product->stock = $stock;
            $product->active = $request->has('active');
            $product->category = $validated['category'] ?? null;
            $product->dimensions = $dimensions;
            $product->attributes = !empty($attributes) ? $attributes : null;
            
            $product->save();
            
            DB::commit();
            
            return redirect()->route('admin.products.show', $product)
                ->with('success', 'Produit mis à jour avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Erreur lors de la mise à jour du produit: ' . $e->getMessage());
        }
    }

    /**
     * Supprime un produit
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Product $product)
    {
        // Vérifier que l'utilisateur a accès à ce produit
        if ($product->admin_id != Auth::id()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Vous n\'avez pas accès à ce produit');
        }
        
        try {
            // Vérifier si le produit est utilisé dans des commandes
            $usedInOrders = $product->orders()->count() > 0;
            
            if ($usedInOrders) {
                return back()->with('error', 'Ce produit ne peut pas être supprimé car il est utilisé dans des commandes');
            }
            
            // Supprimer l'image si elle existe
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            
            $product->delete();
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Produit supprimé avec succès');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression du produit: ' . $e->getMessage());
        }
    }

    /**
     * Met à jour le stock d'un produit
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStock(Request $request, Product $product)
    {
        // Vérifier que l'utilisateur a accès à ce produit
        if ($product->admin_id != Auth::id()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Vous n\'avez pas accès à ce produit');
        }
        
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'operation' => 'required|in:add,subtract',
        ]);
        
        // Si le produit a un stock infini, expliquer que ce n'est pas nécessaire
        if ($product->is_infinite_stock) {
            return back()->with('info', 'Ce produit a un stock infini. Il n\'est pas nécessaire de mettre à jour le stock.');
        }
        
        $result = $product->updateStock($validated['quantity'], $validated['operation']);
        
        if ($result) {
            return back()->with('success', 'Stock mis à jour avec succès');
        } else {
            return back()->with('error', 'Erreur lors de la mise à jour du stock. Vérifiez que le stock est suffisant.');
        }
    }
}