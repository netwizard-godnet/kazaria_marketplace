<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'image',
        'description',
        'is_active',
        'is_customized',
        'custom_layout',
        'custom_banners',
        'custom_carousels',
        'custom_colors',
        'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_customized' => 'boolean',
        'custom_layout' => 'array',
        'custom_banners' => 'array',
        'custom_carousels' => 'array',
        'custom_colors' => 'array',
    ];

    // Générer automatiquement le slug
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relations
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class)->orderBy('order');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Relation many-to-many avec les produits
    public function productsMany()
    {
        return $this->belongsToMany(Product::class, 'product_categories')
                    ->withPivot('is_primary', 'order')
                    ->withTimestamps()
                    ->orderBy('pivot_order');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // Méthodes d'accès aux images
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return null;
    }

    public function getIconClassAttribute()
    {
        return $this->icon ?: 'fas fa-folder';
    }
}
