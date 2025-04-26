<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id', 
        'user_id', 
        'product_id', 
        'quantity', 
        'customer_name',
        'customer_phone1', 
        'customer_phone2', 
        'delivery_address', 
        'region', 
        'city',
        'status', 
        'callback_date', 
        'max_attempts', 
        'current_attempts',
    ];

    protected $casts = [
        'callback_date' => 'date',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function history()
    {
        return $this->hasMany(OrderHistory::class);
    }
}