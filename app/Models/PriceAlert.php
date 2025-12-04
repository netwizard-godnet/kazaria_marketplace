<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'target_price',
        'is_active',
        'notified_at',
    ];

    protected $casts = [
        'target_price' => 'decimal:2',
        'is_active' => 'boolean',
        'notified_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le produit
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope pour les alertes actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Vérifier si le prix cible est atteint
     */
    public function checkPriceReached()
    {
        if (!$this->is_active) {
            return false;
        }

        $currentPrice = $this->product->price;
        
        if ($currentPrice <= $this->target_price) {
            $this->notified_at = now();
            $this->is_active = false;
            $this->save();
            
            return true;
        }
        
        return false;
    }
}
