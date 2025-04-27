<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function decrementStock($quantity = 1)
{
    $this->decrement('stock', $quantity);
    
    // Vérifier si le stock est épuisé
    if ($this->stock <= 0) {
        $this->update(['is_active' => false]);
    }
    
    return $this;
}
}
