<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'category_id',
        'subcategory_id',
        'name',
        'slug',
        'description',
        'meta_description',
        'meta_keywords',
        'price',
        'old_price',
        'discount_percentage',
        'brand',
        'model',
        'warranty',
        'stock',
        'image',
        'images',
        'attributes',
        'tags',
        'rating',
        'reviews_count',
        'views',
        'views_count',
        'is_featured',
        'is_trending',
        'is_new',
        'is_best_offer',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'rating' => 'decimal:1',
        'images' => 'array',
        'attributes' => 'array',
        'tags' => 'array',
        'is_featured' => 'boolean',
        'is_trending' => 'boolean',
        'is_new' => 'boolean',
        'is_best_offer' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Générer automatiquement le slug
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    // Relations
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    // Nouvelles relations many-to-many
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories')
                    ->withPivot('is_primary', 'order')
                    ->withTimestamps()
                    ->orderBy('pivot_order');
    }

    public function subcategories()
    {
        return $this->belongsToMany(Subcategory::class, 'product_subcategories')
                    ->withPivot('is_primary', 'order')
                    ->withTimestamps()
                    ->orderBy('pivot_order');
    }

    // Catégorie principale
    public function primaryCategory()
    {
        return $this->categories()->wherePivot('is_primary', true)->first();
    }

    // Sous-catégorie principale
    public function primarySubcategory()
    {
        return $this->subcategories()->wherePivot('is_primary', true)->first();
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values')
                    ->withTimestamps();
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class)->orderBy('order')->orderBy('id');
    }

    public function activeVariations()
    {
        return $this->hasMany(ProductVariation::class)->where('is_active', true)->orderBy('order')->orderBy('id');
    }

    public function defaultVariation()
    {
        return $this->hasOne(ProductVariation::class)->where('is_default', true);
    }

    public function views()
    {
        return $this->hasMany(ProductView::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Articles de commande liés à ce produit
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getAttributesByName($attributeName)
    {
        return $this->attributeValues()
                    ->whereHas('attribute', function($query) use ($attributeName) {
                        $query->where('name', $attributeName);
                    })
                    ->get();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeTrending($query)
    {
        return $query->where('is_trending', true);
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeBestOffer($query)
    {
        return $query->where('is_best_offer', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    // Méthodes utilitaires pour les catégories multiples
    public function addCategory($categoryId, $isPrimary = false, $order = 0)
    {
        $this->categories()->syncWithoutDetaching([
            $categoryId => [
                'is_primary' => $isPrimary,
                'order' => $order,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function addSubcategory($subcategoryId, $isPrimary = false, $order = 0)
    {
        $this->subcategories()->syncWithoutDetaching([
            $subcategoryId => [
                'is_primary' => $isPrimary,
                'order' => $order,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function setPrimaryCategory($categoryId)
    {
        // Retirer le statut principal de toutes les catégories
        $this->categories()->updateExistingPivot($this->categories()->pluck('id')->toArray(), ['is_primary' => false]);
        
        // Définir la nouvelle catégorie principale
        $this->categories()->updateExistingPivot($categoryId, ['is_primary' => true]);
    }

    public function setPrimarySubcategory($subcategoryId)
    {
        // Retirer le statut principal de toutes les sous-catégories
        $this->subcategories()->updateExistingPivot($this->subcategories()->pluck('id')->toArray(), ['is_primary' => false]);
        
        // Définir la nouvelle sous-catégorie principale
        $this->subcategories()->updateExistingPivot($subcategoryId, ['is_primary' => true]);
    }

    // Méthodes d'accès aux images
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            // Vérifier si c'est un chemin storage (commence par "products/")
            if (strpos($this->image, 'products/') === 0) {
                return asset('storage/' . $this->image);
            }
            // Vérifier si c'est un chemin public (commence par "images/")
            elseif (strpos($this->image, 'images/') === 0) {
                return asset('storage/' . $this->image);
            }
            // URL complète ou autre format
            else {
                return filter_var($this->image, FILTER_VALIDATE_URL) ? $this->image : asset('storage/' . $this->image);
            }
        }
        return null;
    }

    public function getImagesUrlsAttribute()
    {
        if ($this->images && is_array($this->images)) {
            return array_map(function($image) {
                // Vérifier si c'est un chemin storage (commence par "products/")
                if (strpos($image, 'products/') === 0) {
                    return asset('storage/' . $image);
                }
                // Vérifier si c'est un chemin public (commence par "images/")
                elseif (strpos($image, 'images/') === 0) {
                    return asset('storage/' . $image);
                }
                // URL complète ou autre format
                else {
                    return filter_var($image, FILTER_VALIDATE_URL) ? $image : asset('storage/' . $image);
                }
            }, $this->images);
        }
        return [];
    }

    public function getFirstImageUrlAttribute()
    {
        // Priorité 1: Tableau images (plus récent et plus complet)
        if ($this->images && is_array($this->images) && count($this->images) > 0) {
            $firstImage = $this->images[0];
            
            // Vérifier si c'est un chemin storage (commence par "products/")
            if (strpos($firstImage, 'products/') === 0) {
                return asset('storage/' . $firstImage);
            }
            // Vérifier si c'est un chemin public (commence par "images/")
            elseif (strpos($firstImage, 'images/') === 0) {
                return asset($firstImage);
            }
            // URL complète ou autre format
            else {
                return filter_var($firstImage, FILTER_VALIDATE_URL) ? $firstImage : asset('storage/' . $firstImage);
            }
        }
        
        // Priorité 2: Champ image (string) - fallback
        if ($this->image) {
            // Vérifier si c'est un chemin storage (commence par "products/")
            if (strpos($this->image, 'products/') === 0) {
                return asset('storage/' . $this->image);
            }
            // Vérifier si c'est un chemin public (commence par "images/")
            elseif (strpos($this->image, 'images/') === 0) {
                return asset($this->image);
            }
            // URL complète ou autre format
            else {
                return filter_var($this->image, FILTER_VALIDATE_URL) ? $this->image : asset('storage/' . $this->image);
            }
        }
        
        return null;
    }
}
