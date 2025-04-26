<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name', 'price', 'stock', 'is_active',
        // autres champs existants
    ];
    
    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];
    
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_products')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }
    
    public function decrementStock($quantity = 1)
    {
        $this->decrement('stock', $quantity);
        
        // Vérifier si le stock est épuisé et notifier les admins si nécessaire
        if ($this->stock <= 0) {
            $this->update(['is_active' => false]);
            // Logique pour suspendre les commandes contenant ce produit
        }
    }
}