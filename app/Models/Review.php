<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'rating',
        'title',
        'comment',
        'is_verified_purchase',
        'is_approved',
        'helpful_count',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'helpful_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        
        // Mettre à jour les statistiques du produit quand un avis est créé/modifié/supprimé
        static::created(function ($review) {
            static::updateProductStats($review->product_id);
        });
        
        static::updated(function ($review) {
            static::updateProductStats($review->product_id);
        });
        
        static::deleted(function ($review) {
            static::updateProductStats($review->product_id);
        });
    }
    
    /**
     * Mettre à jour les statistiques d'un produit
     */
    public static function updateProductStats($productId)
    {
        $product = \App\Models\Product::find($productId);
        if ($product) {
            $averageRating = static::where('product_id', $productId)
                ->approved()
                ->avg('rating') ?? 0;
            $totalReviews = static::where('product_id', $productId)
                ->approved()
                ->count();
            
            $product->update([
                'rating' => round($averageRating, 1),
                'reviews_count' => $totalReviews,
            ]);
        }
    }

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
     * Relation avec la commande
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relation avec les votes
     */
    public function votes()
    {
        return $this->hasMany(ReviewVote::class);
    }

    /**
     * Scope pour les avis approuvés
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope pour les achats vérifiés
     */
    public function scopeVerifiedPurchase($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    /**
     * Obtenir la note moyenne d'un produit
     */
    public static function getAverageRating($productId)
    {
        return self::where('product_id', $productId)
            ->approved()
            ->avg('rating') ?? 0;
    }

    /**
     * Obtenir le nombre total d'avis d'un produit
     */
    public static function getTotalReviews($productId)
    {
        return self::where('product_id', $productId)
            ->approved()
            ->count();
    }

    /**
     * Obtenir la distribution des notes
     */
    public static function getRatingDistribution($productId)
    {
        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribution[$i] = self::where('product_id', $productId)
                ->approved()
                ->where('rating', $i)
                ->count();
        }
        return $distribution;
    }
}
