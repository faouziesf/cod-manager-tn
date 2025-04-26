<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id', 'name', 'image_path',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}