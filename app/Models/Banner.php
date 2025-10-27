<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'subtitle', 'image_path', 'link_url', 'placement', 'banner_type', 'sort_order', 'is_active', 'starts_at', 'ends_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        
        // Si l'image est déjà dans le dossier public/images, retourner directement
        if (strpos($this->image_path, 'images/') === 0) {
            return asset($this->image_path);
        }
        
        // Sinon, c'est dans le storage
        return asset('storage/' . ltrim($this->image_path, '/'));
    }
    
    /**
     * Récupérer la première bannière d'accueil
     */
    public static function getHomepageBanner1()
    {
        return self::where('banner_type', 'homepage_banner_1')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la deuxième bannière d'accueil
     */
    public static function getHomepageBanner2()
    {
        return self::where('banner_type', 'homepage_banner_2')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la première publicité
     */
    public static function getPublicite1()
    {
        return self::where('banner_type', 'publicite_1')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la deuxième publicité
     */
    public static function getPublicite2()
    {
        return self::where('banner_type', 'publicite_2')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la troisième publicité
     */
    public static function getPublicite3()
    {
        return self::where('banner_type', 'publicite_3')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la quatrième publicité
     */
    public static function getPublicite4()
    {
        return self::where('banner_type', 'publicite_4')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la cinquième publicité
     */
    public static function getPublicite5()
    {
        return self::where('banner_type', 'publicite_5')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la première image du carousel boutique
     */
    public static function getBoutiqueCarousel1()
    {
        return self::where('banner_type', 'boutique_carousel_1')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la deuxième image du carousel boutique
     */
    public static function getBoutiqueCarousel2()
    {
        return self::where('banner_type', 'boutique_carousel_2')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la troisième image du carousel boutique
     */
    public static function getBoutiqueCarousel3()
    {
        return self::where('banner_type', 'boutique_carousel_3')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer toutes les images du carousel boutique
     */
    public static function getBoutiqueCarouselImages()
    {
        return self::where('banner_type', 'like', 'boutique_carousel_%')
                  ->where('is_active', true)
                  ->orderBy('sort_order')
                  ->get();
    }
    
    /**
     * Récupérer la première publicité boutique
     */
    public static function getBoutiquePub1()
    {
        return self::where('banner_type', 'boutique_pub_1')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la deuxième publicité boutique
     */
    public static function getBoutiquePub2()
    {
        return self::where('banner_type', 'boutique_pub_2')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la troisième publicité boutique
     */
    public static function getBoutiquePub3()
    {
        return self::where('banner_type', 'boutique_pub_3')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la quatrième publicité boutique
     */
    public static function getBoutiquePub4()
    {
        return self::where('banner_type', 'boutique_pub_4')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la cinquième publicité boutique
     */
    public static function getBoutiquePub5()
    {
        return self::where('banner_type', 'boutique_pub_5')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la première publicité catégorie
     */
    public static function getCategoriePub1()
    {
        return self::where('banner_type', 'categorie_pub_1')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la deuxième publicité catégorie
     */
    public static function getCategoriePub2()
    {
        return self::where('banner_type', 'categorie_pub_2')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la troisième publicité catégorie
     */
    public static function getCategoriePub3()
    {
        return self::where('banner_type', 'categorie_pub_3')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la quatrième publicité catégorie
     */
    public static function getCategoriePub4()
    {
        return self::where('banner_type', 'categorie_pub_4')
                  ->where('is_active', true)
                  ->first();
    }
    
    /**
     * Récupérer la cinquième publicité catégorie
     */
    public static function getCategoriePub5()
    {
        return self::where('banner_type', 'categorie_pub_5')
                  ->where('is_active', true)
                  ->first();
    }
}
