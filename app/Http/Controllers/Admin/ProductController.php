<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $adminId = Auth::guard('admin')->id();
        $products = Product::where('admin_id', $adminId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.products.create');
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
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'active' => 'nullable|boolean',
            'sku' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'dimensions' => 'nullable|string',
            'attributes' => 'nullable|string',
            'external_id' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
        ]);

        $product = new Product();
        $product->admin_id = Auth::guard('admin')->id();
        $product->name = $validated['name'];
        $product->price = $validated['price'];
        $product->stock = $validated['stock'];
        $product->active = $request->has('active') ? true : false;
        $product->sku = $validated['sku'] ?? null;
        $product->category = $validated['category'] ?? null;
        $product->description = $validated['description'] ?? null;
        $product->dimensions = $validated['dimensions'] ?? null;
        $product->attributes = $validated['attributes'] ?? null;
        $product->external_id = $validated['external_id'] ?? null;

        if ($request->hasFile('image')) {
            try {
                // Stocker l'image dans le dossier storage/app/public/products
                $imagePath = $request->file('image')->store('products', 'public');
                $product->image_path = $imagePath;
            } catch (\Exception $e) {
                // Gérer l'erreur de chargement d'image
                return redirect()->back()
                    ->withErrors(['image' => 'Erreur lors du chargement de l\'image: ' . $e->getMessage()])
                    ->withInput();
            }
        }

        $product->save();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit créé avec succès.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        // Vérifier que le produit appartient à l'admin connecté
        if ($product->admin_id !== Auth::guard('admin')->id() && !Auth::guard('admin')->user()->is_super_admin) {
            abort(403, 'Accès non autorisé à ce produit');
        }
        
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        // Vérifier que le produit appartient à l'admin connecté
        if ($product->admin_id !== Auth::guard('admin')->id() && !Auth::guard('admin')->user()->is_super_admin) {
            abort(403, 'Accès non autorisé à ce produit');
        }
        
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        // Vérifier que le produit appartient à l'admin connecté
        if ($product->admin_id !== Auth::guard('admin')->id() && !Auth::guard('admin')->user()->is_super_admin) {
            abort(403, 'Accès non autorisé à ce produit');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'active' => 'nullable|boolean',
            'sku' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'dimensions' => 'nullable|string',
            'attributes' => 'nullable|string',
            'external_id' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
        ]);

        $product->name = $validated['name'];
        $product->price = $validated['price'];
        $product->stock = $validated['stock'];
        $product->active = $request->has('active') ? true : false;
        $product->sku = $validated['sku'] ?? null;
        $product->category = $validated['category'] ?? null;
        $product->description = $validated['description'] ?? null;
        $product->dimensions = $validated['dimensions'] ?? null;
        $product->attributes = $validated['attributes'] ?? null;
        $product->external_id = $validated['external_id'] ?? null;

        if ($request->hasFile('image')) {
            try {
                // Supprimer l'ancienne image si elle existe
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                
                // Stocker la nouvelle image
                $imagePath = $request->file('image')->store('products', 'public');
                $product->image_path = $imagePath;
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withErrors(['image' => 'Erreur lors du chargement de l\'image: ' . $e->getMessage()])
                    ->withInput();
            }
        }

        $product->save();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        // Vérifier que le produit appartient à l'admin connecté
        if ($product->admin_id !== Auth::guard('admin')->id() && !Auth::guard('admin')->user()->is_super_admin) {
            abort(403, 'Accès non autorisé à ce produit');
        }
        
        // Supprimer l'image associée si elle existe
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit supprimé avec succès.');
    }
}