<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Métadonnées SEO
        $seoData = \App\Http\Controllers\SeoController::getHomeSeo();
        foreach ($seoData as $key => $value) {
            $seoKey = 'seo' . ucfirst($key);
            view()->share($seoKey, $value);
        }
        
        // Récupérer les catégories principales
        $categories = Category::active()->ordered()->with('subcategories')->get();
        
        // Récupérer les deals du jour (produits en promotion)
        $dealsProducts = Product::active()
            ->whereNotNull('old_price')
            ->inStock()
            ->orderBy('discount_percentage', 'desc')
            ->take(16)
            ->get();
        
        // Récupérer les produits par catégorie
        $phoneProducts = Product::active()
            ->whereHas('category', function($query) {
                $query->where('slug', 'telephones-et-tablettes');
            })
            ->inStock()
            ->take(12)
            ->get();
            
        $tvProducts = Product::active()
            ->whereHas('category', function($query) {
                $query->where('slug', 'tv-et-electronique');
            })
            ->inStock()
            ->take(12)
            ->get();
            
        $electroProducts = Product::active()
            ->whereHas('category', function($query) {
                $query->where('slug', 'electromenager');
            })
            ->inStock()
            ->take(12)
            ->get();
            
        $computerProducts = Product::active()
            ->whereHas('category', function($query) {
                $query->where('slug', 'ordinateurs-et-accessoires');
            })
            ->inStock()
            ->take(12)
            ->get();
        
        // Récupérer les produits tendance
        $trendingProducts = Product::active()
            ->trending()
            ->inStock()
            ->take(12)
            ->get();
        
        return view('accueil', compact(
            'categories',
            'dealsProducts',
            'phoneProducts',
            'tvProducts',
            'electroProducts',
            'computerProducts',
            'trendingProducts'
        ));
    }
}
