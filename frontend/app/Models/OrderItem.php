<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_image',
        'product_sku',
        'price',
        'quantity',
        'total',
        'attributes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'total' => 'decimal:2'
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
     * Les attributs doivent être un objet {} pour être compatibles avec JavaScript et l'affichage
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
     * Relation avec la commande
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relation avec le produit
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
