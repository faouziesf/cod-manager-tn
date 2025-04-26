<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
    ];

    protected $casts = [
        'callback_date' => 'date',
        'last_attempt_at' => 'datetime',
        'total_price' => 'decimal:2',
        'confirmed_total_price' => 'decimal:2',
    ];

    protected $appends = [
        'can_be_processed',
    ];

    const STATUS_NEW = 'new';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_DATED = 'dated';
    const STATUS_OLD = 'old';
    const STATUS_CANCELED = 'canceled';

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->withPivot('quantity', 'confirmed_price')
            ->withTimestamps();
    }

    public function history()
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function lastHistory()
    {
        return $this->history()->latest()->first();
    }

    public function getCanBeProcessedAttribute()
    {
        // Vérifier si tous les produits sont en stock
        foreach ($this->products as $product) {
            if (!$product->isInStock()) {
                return false;
            }
        }

        // Vérifier les tentatives journalières
        $maxDailyAttempts = $this->status === self::STATUS_DATED ? 2 : 3;
        if ($this->current_daily_attempts >= $maxDailyAttempts) {
            return false;
        }

        // Vérifier le délai entre les tentatives
        if ($this->last_attempt_at) {
            $minDelay = $this->status === self::STATUS_DATED ? 3.5 : 2.5;
            $minTime = $this->last_attempt_at->addHours($minDelay);
            if (now()->lt($minTime)) {
                return false;
            }
        }

        // Pour les commandes datées, vérifier si c'est le jour J
        if ($this->status === self::STATUS_DATED && $this->callback_date) {
            $today = Carbon::today();
            if (!$this->callback_date->isSameDay($today)) {
                return false;
            }
        }

        return true;
    }

    public function recordAttempt($status, $note, $user)
    {
        // Enregistrer l'historique
        $history = new OrderHistory();
        $history->order_id = $this->id;
        $history->user_id = $user->id;
        $history->status = $status;
        $history->private_note = $note;
        $history->save();

        // Mettre à jour les tentatives
        $this->current_attempts += 1;
        $this->current_daily_attempts += 1;
        $this->last_attempt_at = now();

        // Vérifier si le nombre max de tentatives est atteint
        $maxAttempts = $this->status === self::STATUS_DATED ? 5 : 9;
        if ($this->current_attempts >= $maxAttempts && $this->status !== self::STATUS_OLD) {
            $this->status = self::STATUS_OLD;
        }

        $this->save();
    }

    // Réinitialiser les tentatives journalières à minuit
    public static function resetDailyAttempts()
    {
        self::query()->update(['current_daily_attempts' => 0]);
    }
}