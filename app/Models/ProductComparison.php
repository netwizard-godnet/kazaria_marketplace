<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'product_ids',
        'name',
    ];

    protected $casts = [
        'product_ids' => 'array',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir les produits de cette comparaison
     */
    public function getProductsAttribute()
    {
        if (empty($this->product_ids)) {
            return collect([]);
        }
        
        return Product::whereIn('id', $this->product_ids)->get();
    }

    /**
     * Obtenir les comparaisons pour un utilisateur ou session
     */
    public static function getComparisons($userId = null, $sessionId = null)
    {
        $query = self::query();
        
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Ajouter un produit à la comparaison
     */
    public function addProduct($productId)
    {
        $productIds = $this->product_ids ?? [];
        
        if (!in_array($productId, $productIds)) {
            $productIds[] = $productId;
            $this->product_ids = $productIds;
            $this->save();
        }
        
        return $this;
    }

    /**
     * Retirer un produit de la comparaison
     */
    public function removeProduct($productId)
    {
        $productIds = $this->product_ids ?? [];
        $productIds = array_values(array_filter($productIds, fn($id) => $id != $productId));
        
        $this->product_ids = $productIds;
        $this->save();
        
        return $this;
    }
}
