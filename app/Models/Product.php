<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'name',
        'image_path',
        'price',
        'stock',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'active' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_products')
            ->withPivot('quantity', 'confirmed_price')
            ->withTimestamps();
    }

    public function isInStock()
    {
        return $this->stock > 0 && $this->active;
    }
}