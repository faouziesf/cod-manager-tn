<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
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
        'assigned_to',
        'external_id',
        'external_source'
    ];

    /**
     * Les attributs qui doivent être convertis en types natifs.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'callback_date' => 'date',
        'last_attempt_at' => 'datetime',
        'next_attempt_at' => 'datetime',
        'scheduled_date' => 'datetime',
        'total_price' => 'decimal:2',
        'confirmed_total_price' => 'decimal:2',
        'confirmed_price' => 'decimal:2',
    ];

    /**
     * Get the admin that owns the order.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the user that created the order (if any).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that is assigned to the order.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the products in this order.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->withPivot('quantity', 'confirmed_price')
            ->withTimestamps();
    }

    /**
     * Get the history entries for this order.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class);
    }

    /**
     * Déterminer si cette commande peut être tentée aujourd'hui
     *
     * @return bool
     */
    public function canBeAttemptedToday(): bool
    {
        // Vérifier si le nombre total de tentatives est dépassé
        if ($this->attempt_count >= $this->max_attempts) {
            return false;
        }

        // Vérifier si le nombre de tentatives quotidiennes est dépassé
        if ($this->daily_attempt_count >= $this->max_daily_attempts) {
            // Vérifier si les tentatives ont été faites aujourd'hui
            if ($this->last_attempt_at && $this->last_attempt_at->isToday()) {
                return false;
            }
            
            // Si la dernière tentative n'est pas aujourd'hui, réinitialiser le compteur quotidien
            $this->daily_attempt_count = 0;
            $this->save();
        }

        // Vérifier si la commande est programmée pour une date future
        if ($this->scheduled_date && $this->scheduled_date->isFuture()) {
            return false;
        }

        // Vérifier si la prochaine tentative est dans le futur
        if ($this->next_attempt_at && $this->next_attempt_at->isFuture()) {
            return false;
        }

        return true;
    }

    /**
     * Enregistrer une tentative pour cette commande
     *
     * @param string $result  Le résultat de la tentative
     * @param string|null $note  Note optionnelle
     * @param int $userId  ID de l'utilisateur qui a effectué la tentative
     * @return bool  True si la tentative est enregistrée avec succès
     */
    public function recordAttempt(string $result, ?string $note, int $userId): bool
    {
        // Incrémenter les compteurs de tentatives
        $this->attempt_count += 1;
        $this->daily_attempt_count += 1;
        $this->last_attempt_at = now();

        // Calculer la prochaine tentative en fonction des paramètres
        $intervalKey = $this->scheduled_date ? 'scheduled_attempt_interval' : 'standard_attempt_interval';
        $interval = Setting::where('key', $intervalKey)->value('value') ?? 2.5;
        
        $this->next_attempt_at = now()->addHours($interval);

        // Mettre à jour le statut en fonction du résultat
        if ($result === 'confirmed') {
            $this->status = 'confirmed';
        } elseif ($result === 'cancelled') {
            $this->status = 'cancelled';
        } elseif ($result === 'returned') {
            $this->status = 'returned';
        } elseif ($this->attempt_count >= $this->max_attempts) {
            $this->status = 'cancelled';
        }

        $saved = $this->save();

        // Créer une entrée d'historique
        if ($saved) {
            OrderHistory::create([
                'order_id' => $this->id,
                'user_id' => $userId,
                'action' => 'Tentative #' . $this->attempt_count,
                'note' => $result . ($note ? ' - ' . $note : '')
            ]);
        }

        return $saved;
    }

    /**
     * Réinitialiser les tentatives quotidiennes
     *
     * @return bool
     */
    public function resetDailyAttempts(): bool
    {
        $this->daily_attempt_count = 0;
        return $this->save();
    }

    /**
     * Confirmer la commande
     *
     * @param float $confirmedPrice  Le prix confirmé
     * @param string|null $note  Note optionnelle
     * @param int $userId  ID de l'utilisateur qui a confirmé la commande
     * @return bool  True si la commande est confirmée avec succès
     */
    public function confirm(float $confirmedPrice, ?string $note, int $userId): bool
    {
        $this->status = 'confirmed';
        $this->confirmed_price = $confirmedPrice;
        $saved = $this->save();

        if ($saved) {
            OrderHistory::create([
                'order_id' => $this->id,
                'user_id' => $userId,
                'action' => 'Confirmation',
                'note' => 'Prix confirmé: ' . $confirmedPrice . ($note ? ' - ' . $note : '')
            ]);
        }

        return $saved;
    }

    /**
     * Obtenir un tableau des statuts humainement lisibles
     *
     * @return array
     */
    public static function getStatusLabels(): array
    {
        return [
            'new' => 'Nouvelle',
            'processing' => 'En traitement',
            'confirmed' => 'Confirmée',
            'shipped' => 'Expédiée',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
            'returned' => 'Retournée'
        ];
    }

    /**
     * Obtenir le libellé du statut
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = self::getStatusLabels();
        return $labels[$this->status] ?? $this->status;
    }
}