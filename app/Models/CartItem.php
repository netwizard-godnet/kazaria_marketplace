<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'quantity',
        'price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2'
    ];

    /**
     * Relation avec le produit
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir les articles du panier pour un utilisateur ou une session
     */
    public static function getCartItems($userId = null, $sessionId = null)
    {
        $query = self::with('product');
        
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }
        
        $items = $query->get();
        
        // Formatter l'image pour chaque produit
        $items->transform(function($item) {
            if ($item->product) {
                // Préparer l'URL de l'image
                $imageUrl = asset('images/produit.jpg');
                
                // Priorité 1: images (array)
                if ($item->product->images && is_array($item->product->images) && count($item->product->images) > 0) {
                    $firstImg = $item->product->images[0];
                    
                    if (filter_var($firstImg, FILTER_VALIDATE_URL)) {
                        $imageUrl = $firstImg;
                    } elseif (strpos($firstImg, 'products/') === 0) {
                        $imageUrl = asset('storage/' . $firstImg);
                    } elseif (str_starts_with($firstImg, 'images/')) {
                        $imageUrl = asset($firstImg);
                    } else {
                        $imageUrl = asset($firstImg);
                    }
                }
                // Priorité 2: image (string)
                elseif ($item->product->image) {
                    if (filter_var($item->product->image, FILTER_VALIDATE_URL)) {
                        $imageUrl = $item->product->image;
                    } elseif (str_starts_with($item->product->image, 'storage/')) {
                        $imageUrl = asset($item->product->image);
                    } elseif (strpos($item->product->image, 'products/') === 0) {
                        $imageUrl = asset('storage/' . $item->product->image);
                    } else {
                        $imageUrl = asset($item->product->image);
                    }
                }
                
                $item->product->image = $imageUrl;
            }
            return $item;
        });
        
        return $items;
    }

    /**
     * Calculer le total du panier
     */
    public static function getCartTotal($userId = null, $sessionId = null)
    {
        $items = self::getCartItems($userId, $sessionId);
        return $items->sum(function($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Compter les articles dans le panier (nombre de produits distincts, pas la quantité)
     */
    public static function getCartCount($userId = null, $sessionId = null)
    {
        $query = self::query();
        
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }
        
        return $query->count(); // Nombre de produits distincts
    }
}
