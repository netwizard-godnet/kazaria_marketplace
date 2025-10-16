<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'category_id',
        'subcategory_id',
        'phone',
        'email',
        'address',
        'city',
        'logo',
        'banner',
        'dfe_document',
        'commerce_register',
        'status',
        'is_verified',
        'is_official',
        'commission_rate',
        'business_hours',
        'social_links',
        'total_products',
        'total_orders',
        'total_sales',
        'rating',
        'reviews_count',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_official' => 'boolean',
        'commission_rate' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'rating' => 'decimal:2',
        'business_hours' => 'array',
        'social_links' => 'array',
    ];

    /**
     * Boot function
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($store) {
            if (empty($store->slug)) {
                $store->slug = Str::slug($store->name);
                
                // S'assurer que le slug est unique
                $originalSlug = $store->slug;
                $count = 1;
                while (static::where('slug', $store->slug)->exists()) {
                    $store->slug = $originalSlug . '-' . $count++;
                }
            }
        });
    }

    /**
     * Relation avec l'utilisateur propriétaire
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec la catégorie
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relation avec la sous-catégorie
     */
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Relation avec les produits
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relation avec les commandes
     */
    public function orders()
    {
        return $this->hasManyThrough(Order::class, Product::class);
    }

    /**
     * Vérifier si la boutique est active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Vérifier si la boutique est en attente
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Obtenir l'URL complète du logo
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return asset('images/logo-orange.png');
        }
        
        // Vérifier si c'est une URL externe
        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }
        
        // Vérifier si le fichier existe dans storage
        $storagePath = storage_path('app/public/' . $this->logo);
        if (file_exists($storagePath)) {
            // Utiliser le lien symbolique standard de Laravel
            return asset('storage/' . $this->logo);
        }
        
        // Fallback vers une image par défaut
        return asset('images/logo-orange.png');
    }

    /**
     * Obtenir l'URL complète de la bannière
     */
    public function getBannerUrlAttribute()
    {
        if (!$this->banner) {
            return asset('images/bg-1.jpg');
        }
        
        // Vérifier si c'est une URL externe
        if (filter_var($this->banner, FILTER_VALIDATE_URL)) {
            return $this->banner;
        }
        
        // Vérifier si le fichier existe dans storage
        $storagePath = storage_path('app/public/' . $this->banner);
        if (file_exists($storagePath)) {
            // Utiliser le lien symbolique standard de Laravel
            return asset('storage/' . $this->banner);
        }
        
        // Fallback vers une image par défaut
        return asset('images/bg-1.jpg');
    }
}
