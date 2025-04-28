<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'admin_id',
        'user_id',
        'customer_name',
        'customer_phone1',
        'customer_phone2',
        'delivery_address',
        'region',
        'city',
        'status',
        'callback_date',
        'current_attempts',
        'current_daily_attempts',
        'last_attempt_at',
        'total_price',
        'confirmed_total_price',
        'attempt_count',
        'daily_attempt_count',
        'next_attempt_at',
        'scheduled_date',
        'max_attempts',
        'max_daily_attempts',
        'confirmed_price',
        'assigned_to'
    ];
    
    protected $dates = [
        'created_at',
        'updated_at',
        'last_attempt_at',
        'next_attempt_at',
        'scheduled_date',
        'callback_date'
    ];
    
    protected $casts = [
        'total_price' => 'float',
        'confirmed_total_price' => 'float',
        'confirmed_price' => 'float',
    ];
    
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Cette méthode est définie deux fois - garder celle-ci et supprimer la seconde
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->withPivot('quantity', 'confirmed_price')
            ->withTimestamps();
    }
    
    public function histories()
    {
        return $this->hasMany(OrderHistory::class);
    }
    
    public function addHistory($action, $note = null)
    {
        return $this->histories()->create([
            'user_id' => auth()->id() ?? auth()->guard('admin')->id(),
            'action' => $action,
            'note' => $note,
        ]);
    }
    
    public function recordAttempt($action, $note = null)
    {
        $this->increment('attempt_count');
        $this->increment('daily_attempt_count');
        $this->last_attempt_at = now();
        
        // Calculer le prochain temps de tentative en fonction du statut
        $intervalHours = 2.5; // Par défaut
        if ($this->status === 'scheduled') {
            $intervalHours = getSetting('scheduled_attempt_interval', 3.5);
        } elseif ($this->status === 'old') {
            $intervalHours = getSetting('old_attempt_interval', 3.5);
        } else {
            $intervalHours = getSetting('standard_attempt_interval', 2.5);
        }
        
        $this->next_attempt_at = now()->addHours($intervalHours);
        $this->save();
        
        // Vérifier si le nombre maximum de tentatives est atteint
        if ($this->status !== 'old' && $this->attempt_count >= $this->max_attempts) {
            $this->status = 'old';
            $this->save();
        }
        
        // Ajouter à l'historique
        $this->addHistory($action, $note);
    }
    
    public function confirm($confirmedPrice = null, $note = null)
    {
        // Mettre à jour le statut et le prix confirmé
        $this->status = 'confirmed';
        if ($confirmedPrice) {
            $this->confirmed_price = $confirmedPrice;
        } else {
            $this->confirmed_price = $this->total_price;
        }
        $this->save();
        
        // Décrémenter le stock des produits
        foreach ($this->products as $product) {
            $product->decrementStock($product->pivot->quantity);
        }
        
        // Ajouter à l'historique
        $this->addHistory('confirm', $note);
    }
    
    public function cancel($note)
    {
        $this->status = 'cancelled';
        $this->save();
        
        // Ajouter à l'historique
        $this->addHistory('cancel', $note);
    }
    
    public function schedule($scheduledDate, $note)
    {
        $this->status = 'scheduled';
        $this->scheduled_date = $scheduledDate;
        $this->save();
        
        // Ajouter à l'historique
        $this->addHistory('schedule', $note);
    }
    
    public function scopeStandard($query)
    {
        return $query->where('status', 'new')
            ->where(function($q) {
                $q->where('next_attempt_at', '<=', now())
                   ->orWhereNull('next_attempt_at');
            })
            ->where(function($q) {
                $q->where('daily_attempt_count', '<', DB::raw('max_daily_attempts'))
                   ->orWhereDate('last_attempt_at', '<', now()->toDateString())
                   ->orWhereNull('last_attempt_at');
            });
    }
    
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
            ->whereDate('scheduled_date', '<=', now())
            ->where(function($q) {
                $q->where('next_attempt_at', '<=', now())
                   ->orWhereNull('next_attempt_at');
            })
            ->where(function($q) {
                $q->where('daily_attempt_count', '<', DB::raw('max_daily_attempts'))
                   ->orWhereDate('last_attempt_at', '<', now()->toDateString())
                   ->orWhereNull('last_attempt_at');
            });
    }
    
    public function scopeOld($query)
    {
        return $query->where('status', 'old')
            ->where(function($q) {
                $q->where('next_attempt_at', '<=', now())
                   ->orWhereNull('next_attempt_at');
            });
    }
    
    public function scopeNeedsVerification($query)
    {
        return $query->whereHas('products', function($q) {
            $q->where('stock', '<=', 0);
        });
    }
    
}