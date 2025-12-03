<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'store_id',
        'payment_method',
        'payment_reference',
        'amount',
        'commission_amount',
        'commission_rate',
        'status',
        'gateway_response',
        'transaction_id',
        'paid_at',
        'refunded_at',
        'refund_amount',
        'refund_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime'
    ];

    /**
     * Relation avec la commande
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec la boutique
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Obtenir le statut formaté
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'En attente',
            'processing' => 'En cours',
            'completed' => 'Terminé',
            'failed' => 'Échoué',
            'cancelled' => 'Annulé',
            'refunded' => 'Remboursé',
            'partially_refunded' => 'Partiellement remboursé'
        ];
        
        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Obtenir la classe CSS du badge de statut
     */
    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'pending' => 'bg-warning',
            'processing' => 'bg-info',
            'completed' => 'bg-success',
            'failed' => 'bg-danger',
            'cancelled' => 'bg-secondary',
            'refunded' => 'bg-warning',
            'partially_refunded' => 'bg-warning'
        ];
        
        return $classes[$this->status] ?? 'bg-secondary';
    }

    /**
     * Calculer la commission
     */
    public function calculateCommission()
    {
        $this->commission_amount = $this->amount * ($this->commission_rate / 100);
        return $this->commission_amount;
    }

    /**
     * Marquer comme payé
     */
    public function markAsPaid()
    {
        $this->update([
            'status' => 'completed',
            'paid_at' => now()
        ]);
    }

    /**
     * Rembourser
     */
    public function refund($amount = null, $reason = null)
    {
        $refundAmount = $amount ?? $this->amount;
        
        $this->update([
            'status' => $refundAmount < $this->amount ? 'partially_refunded' : 'refunded',
            'refund_amount' => $refundAmount,
            'refund_reason' => $reason,
            'refunded_at' => now()
        ]);
    }

    /**
     * Scope pour les paiements d'un utilisateur
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour les paiements d'une boutique
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Scope pour les paiements terminés
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope pour les paiements en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
