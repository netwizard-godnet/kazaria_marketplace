<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_public',
        'share_token',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Générer un token de partage unique
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($wishlist) {
            if (empty($wishlist->share_token)) {
                $wishlist->share_token = Str::random(32);
            }
        });
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les produits
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'wishlist_products')
            ->withPivot(['priority', 'notes'])
            ->withTimestamps()
            ->orderByPivot('priority', 'desc');
    }

    /**
     * Ajouter un produit à la wishlist
     */
    public function addProduct($productId, $priority = 0, $notes = null)
    {
        if (!$this->products()->where('product_id', $productId)->exists()) {
            $this->products()->attach($productId, [
                'priority' => $priority,
                'notes' => $notes,
            ]);
        }
        
        return $this;
    }

    /**
     * Retirer un produit de la wishlist
     */
    public function removeProduct($productId)
    {
        $this->products()->detach($productId);
        return $this;
    }

    /**
     * Vérifier si un produit est dans la wishlist
     */
    public function hasProduct($productId)
    {
        return $this->products()->where('product_id', $productId)->exists();
    }
}
