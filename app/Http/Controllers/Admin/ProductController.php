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
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $products = $admin->products()->get();
        
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $admin = Auth::guard('admin')->user();
        
        $product = new Product();
        $product->name = $request->name;
        $product->admin_id = $admin->id;
        
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/products'), $imageName);
            $product->image_path = 'images/products/' . $imageName;
        }
        
        $product->save();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $admin = Auth::guard('admin')->user();
        
        // Vérifier que le produit appartient à cet admin
        if ($product->admin_id !== $admin->id) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Vous n\'avez pas accès à ce produit.');
        }
        
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $admin = Auth::guard('admin')->user();
        
        // Vérifier que le produit appartient à cet admin
        if ($product->admin_id !== $admin->id) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Vous n\'avez pas accès à ce produit.');
        }
        
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $admin = Auth::guard('admin')->user();
        
        // Vérifier que le produit appartient à cet admin
        if ($product->admin_id !== $admin->id) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Vous n\'avez pas accès à ce produit.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product->name = $request->name;
        
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($product->image_path && file_exists(public_path($product->image_path))) {
                unlink(public_path($product->image_path));
            }
            
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/products'), $imageName);
            $product->image_path = 'images/products/' . $imageName;
        }
        
        $product->save();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $admin = Auth::guard('admin')->user();
        
        // Vérifier que le produit appartient à cet admin
        if ($product->admin_id !== $admin->id) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Vous n\'avez pas accès à ce produit.');
        }

        // Supprimer l'image si elle existe
        if ($product->image_path && file_exists(public_path($product->image_path))) {
            unlink(public_path($product->image_path));
        }
        
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit supprimé avec succès.');
    }
}