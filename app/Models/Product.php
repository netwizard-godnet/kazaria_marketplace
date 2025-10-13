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

    public function views()
    {
        return $this->hasMany(ProductView::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
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
}
