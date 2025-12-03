<?php

namespace App\Models;

use App\Services\StockService;
use App\Services\OrderStatusService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_postal_code',
        'shipping_country',
        'subtotal',
        'shipping_cost',
        'tax',
        'discount',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'invoice_path',
        'customer_notes',
        'admin_notes',
        'paid_at',
        'shipped_at',
        'delivered_at'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime'
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les items de la commande
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relation avec les articles de la commande
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Générer un numéro de commande unique
     */
    public static function generateOrderNumber()
    {
        do {
            $orderNumber = 'KAZ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (self::where('order_number', $orderNumber)->exists());
        
        return $orderNumber;
    }

    /**
     * Obtenir le statut de paiement formaté
     */
    public function getPaymentStatusLabelAttribute()
    {
        return OrderStatusService::getPaymentStatusLabel($this->payment_status);
    }

    /**
     * Changer le statut de la commande
     */
    public function changeStatus(string $newStatus, string $reason = null)
    {
        return OrderStatusService::changeOrderStatus($this, $newStatus, $reason);
    }

    /**
     * Changer le statut de paiement
     */
    public function changePaymentStatus(string $newPaymentStatus, string $reason = null)
    {
        return OrderStatusService::changePaymentStatus($this, $newPaymentStatus, $reason);
    }

    /**
     * Obtenir les statuts disponibles
     */
    public function getAvailableStatuses()
    {
        return OrderStatusService::getAvailableStatuses($this->status);
    }

    /**
     * Obtenir les statuts de paiement disponibles
     */
    public function getAvailablePaymentStatuses()
    {
        return OrderStatusService::getAvailablePaymentStatuses($this->payment_status);
    }

    /**
     * Obtenir le label du statut
     */
    public function getStatusLabelAttribute()
    {
        return OrderStatusService::getStatusLabel($this->status);
    }

    /**
     * Obtenir la classe CSS du statut
     */
    public function getStatusClassAttribute()
    {
        return OrderStatusService::getStatusClass($this->status);
    }

    /**
     * Obtenir la classe CSS du statut de paiement
     */
    public function getPaymentStatusClassAttribute()
    {
        return OrderStatusService::getPaymentStatusClass($this->payment_status);
    }

    /**
     * Scope pour les commandes d'un utilisateur
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour trier par date récente
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
