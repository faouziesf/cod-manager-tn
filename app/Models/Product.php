<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'admin_id',
        'external_id',
        'name',
        'description',
        'sku',
        'price',
        'stock',
        'image_url',
        'image_path',
        'active',
        'category',
        'dimensions',
        'attributes',
    ];

    /**
     * Les attributs qui doivent être castés en types natifs.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'active' => 'boolean',
        'dimensions' => 'array',
        'attributes' => 'array',
    ];

    /**
     * Relation avec Admin
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Relation avec commandes
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_products')
            ->withPivot('quantity', 'confirmed_price')
            ->withTimestamps();
    }

    /**
     * Accesseur pour le prix formaté
     *
     * @return string
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' TND';
    }

    /**
     * Accesseur pour obtenir le nom de la catégorie
     *
     * @return string
     */
    public function getCategoryNameAttribute()
    {
        return $this->category ?: 'Non catégorisé';
    }

    /**
     * Accesseur pour vérifier si le stock est infini
     *
     * @return bool
     */
    public function getIsInfiniteStockAttribute()
    {
        return $this->stock === null || $this->stock === 999999;
    }

    /**
     * Accesseur pour obtenir le stock à afficher
     *
     * @return string
     */
    public function getDisplayStockAttribute()
    {
        return $this->is_infinite_stock ? 'Infini' : $this->stock;
    }

    /**
     * Scope pour filtrer les produits actifs
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope pour filtrer par catégorie
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope pour filtrer les produits à faible stock
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $threshold
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->where('stock', '<=', $threshold)
                    ->where('stock', '>', 0); // Exclure le stock infini
    }

    /**
     * Mise à jour du stock
     *
     * @param  int  $quantity
     * @param  string  $operation
     * @return bool
     */
    public function updateStock($quantity, $operation = 'subtract')
    {
        // Si le stock est infini, ne pas le modifier
        if ($this->is_infinite_stock) {
            return true;
        }

        if ($operation === 'add') {
            $this->stock += $quantity;
        } else {
            // Vérifier si le stock est suffisant
            if ($this->stock < $quantity) {
                return false;
            }
            $this->stock -= $quantity;
        }

        return $this->save();
    }
}