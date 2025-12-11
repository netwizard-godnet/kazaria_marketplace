<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'icon',
        'image',
        'description',
        'is_active',
        'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Générer automatiquement le slug
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($subcategory) {
            if (empty($subcategory->slug)) {
                $subcategory->slug = Str::slug($subcategory->name);
            }
        });
    }

    // Relations
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Relation many-to-many avec les produits
    public function productsMany()
    {
        return $this->belongsToMany(Product::class, 'product_subcategories')
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
        if (!$this->image) {
            return null;
        }

        $image = trim($this->image);
        
        // Vérifier si c'est une URL complète
        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }
        
        // Vérifier si c'est un chemin public (commence par "images/")
        if (strpos($image, 'images/') === 0) {
            return asset($image);
        }
        
        // Vérifier si le chemin contient déjà "storage/"
        if (strpos($image, 'storage/') !== false) {
            return asset($image);
        }
        
        // Vérifier si c'est un chemin storage (commence par "subcategories/")
        if (strpos($image, 'subcategories/') === 0) {
            return asset('storage/' . $image);
        }
        
        // Par défaut, traiter comme un chemin storage dans le dossier subcategories
        // Si le chemin ne contient pas déjà "subcategories/", l'ajouter
        if (strpos($image, 'subcategories/') === false) {
            return asset('storage/subcategories/' . basename($image));
        }
        
        return asset('storage/' . $image);
    }
}
