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
        'price',
        'attributes'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2'
        // 'attributes' n'est pas dans le cast car on le gère manuellement avec accesseur/mutateur
    ];

    /**
     * Mutateur pour s'assurer que les attributs sont stockés comme un objet JSON
     */
    public function setAttributesAttribute($value)
    {
        // Si null ou vide, stocker comme objet vide {}
        if (is_null($value) || (is_array($value) && empty($value))) {
            // Stocker comme JSON objet vide
            $this->attributes['attributes'] = json_encode((object)[], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }
        
        // Convertir en objet si c'est un tableau
        if (is_array($value)) {
            $obj = (object)$value;
            $this->attributes['attributes'] = json_encode($obj, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }
        
        // Si c'est déjà un objet, le sérialiser
        if (is_object($value)) {
            $this->attributes['attributes'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }
        
        // Par défaut, stocker comme objet vide
        $this->attributes['attributes'] = json_encode((object)[], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Accesseur pour s'assurer que les attributs retournent toujours un objet, jamais null
     * Les attributs doivent être un objet {} pour être compatibles avec JavaScript
     */
    public function getAttributesAttribute($value)
    {
        // Si null, retourner un objet vide
        if (is_null($value)) {
            return (object)[];
        }
        
        // Si c'est une chaîne JSON, la décoder
        if (is_string($value)) {
            $decoded = json_decode($value, false); // false pour obtenir un objet, pas un tableau
            return $decoded ?? (object)[];
        }
        
        // Si c'est un tableau vide, retourner un objet vide
        if (is_array($value) && empty($value)) {
            return (object)[];
        }
        
        // Si c'est un tableau avec des valeurs, le convertir en objet
        if (is_array($value)) {
            return (object)$value;
        }
        
        // Si c'est déjà un objet, le retourner tel quel
        return is_object($value) ? $value : (object)[];
    }

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
