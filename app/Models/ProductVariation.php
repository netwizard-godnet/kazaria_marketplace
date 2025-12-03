<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'old_price',
        'discount_percentage',
        'stock',
        'image',
        'is_default',
        'is_active',
        'order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'stock' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // Relations
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variation_attribute_values')
                    ->withTimestamps();
    }

    // Accesseurs
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            if (strpos($this->image, 'products/') === 0) {
                return asset('storage/' . $this->image);
            }
            elseif (strpos($this->image, 'images/') === 0) {
                return asset($this->image);
            }
            else {
                return filter_var($this->image, FILTER_VALIDATE_URL) ? $this->image : asset('storage/' . $this->image);
            }
        }
        // Si pas d'image spécifique, retourner l'image du produit
        return $this->product ? $this->product->first_image_url : null;
    }

    // Méthode pour obtenir le prix final (promo ou normal)
    public function getFinalPriceAttribute()
    {
        if ($this->old_price && $this->old_price > $this->price) {
            return $this->price; // Prix promo
        }
        return $this->price;
    }

    // Méthode pour obtenir le prix normal (sans promo)
    public function getNormalPriceAttribute()
    {
        if ($this->old_price && $this->old_price > $this->price) {
            return $this->old_price;
        }
        return $this->price;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    // Méthode pour générer un SKU automatique si non fourni
    public static function generateSku($productId, $attributeValueIds = [])
    {
        $product = Product::find($productId);
        if (!$product) {
            return null;
        }

        $baseSku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $product->name), 0, 6));
        $baseSku .= '-' . str_pad($productId, 4, '0', STR_PAD_LEFT);

        if (!empty($attributeValueIds)) {
            sort($attributeValueIds);
            $suffix = '-' . implode('-', array_slice($attributeValueIds, 0, 3));
            $baseSku .= $suffix;
        }

        // Vérifier l'unicité
        $counter = 1;
        $originalSku = $baseSku;
        while (self::where('sku', $baseSku)->exists()) {
            $baseSku = $originalSku . '-' . $counter;
            $counter++;
        }

        return $baseSku;
    }

    // Méthode pour obtenir une description textuelle des attributs
    public function getAttributesDescriptionAttribute()
    {
        $attributes = $this->attributeValues()
            ->with('attribute')
            ->get()
            ->groupBy('attribute.name')
            ->map(function ($values) {
                return $values->pluck('value')->implode(', ');
            })
            ->implode(' - ');

        return $attributes ?: 'Variation par défaut';
    }
}
