<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProduct extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'confirmed_price'
    ];

    /**
     * Les attributs qui doivent être convertis en types natifs.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'confirmed_price' => 'decimal:2'
    ];

    /**
     * Get the order that owns the order product.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that owns the order product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the subtotal (quantity * price)
     *
     * @return float
     */
    public function getSubtotalAttribute(): float
    {
        if ($this->confirmed_price) {
            return $this->confirmed_price;
        }
        
        $price = $this->product ? $this->product->price : 0;
        return $price * $this->quantity;
    }
}