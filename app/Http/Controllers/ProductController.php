<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductView;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show($slug, Request $request)
    {
        $product = Product::where('slug', $slug)->with(['categories', 'subcategories', 'category', 'subcategory'])->firstOrFail();
        
        // Métadonnées SEO
        $seoData = \App\Http\Controllers\SeoController::getProductSeo($product);
        foreach ($seoData as $key => $value) {
            $seoKey = 'seo' . ucfirst($key);
            view()->share($seoKey, $value);
        }
        
        // Tracker la vue de ce produit
        ProductView::trackView($product->id, $request);
        
        // Produits similaires (même catégories)
        $categoryIds = $product->categories->pluck('id')->toArray();
        $similarProducts = Product::active()
            ->whereHas('categories', function($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->where('id', '!=', $product->id)
            ->inStock()
            ->take(12)
            ->get();
        
        // Vues récentes réelles
        $recentProducts = ProductView::getRecentViews(12, $product->id);
        
        // Si pas assez de vues récentes, compléter avec des produits populaires
        if ($recentProducts->count() < 6) {
            $popularProducts = Product::active()
                ->inStock()
                ->where('id', '!=', $product->id)
                ->whereNotIn('id', $recentProducts->pluck('id'))
                ->orderBy('views_count', 'desc')
                ->take(12 - $recentProducts->count())
                ->get();
            
            $recentProducts = $recentProducts->merge($popularProducts);
        }
        
        return view('product', compact('product', 'similarProducts', 'recentProducts'));
    }
    
    public function category($slug, Request $request)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        
        // Métadonnées SEO
        $seoData = \App\Http\Controllers\SeoController::getCategorySeo($category);
        foreach ($seoData as $key => $value) {
            $seoKey = 'seo' . ucfirst($key);
            view()->share($seoKey, $value);
        }
        $category = $category->load('subcategories');
        
        // Meilleures offres de la catégorie
        $bestOffers = Product::active()
            ->whereHas('categories', function($query) use ($category) {
                $query->where('categories.id', $category->id);
            })
            ->bestOffer()
            ->inStock()
            ->take(12)
            ->get();
        
        // Nouveautés de la catégorie
        $newProducts = Product::active()
            ->whereHas('categories', function($query) use ($category) {
                $query->where('categories.id', $category->id);
            })
            ->new()
            ->inStock()
            ->take(12)
            ->get();
        
        // Construire la requête avec filtres
        $query = Product::active()
            ->whereHas('categories', function($query) use ($category) {
                $query->where('categories.id', $category->id);
            })
            ->inStock();
        
        // Filtre par sous-catégorie
        if ($request->filled('subcategory')) {
            $query->whereHas('subcategories', function($query) use ($request) {
                $query->where('subcategories.id', $request->subcategory);
            });
        }
        
        // Filtre par prix
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Filtre par note
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }
        
        // Filtre par attributs
        if ($request->filled('attributes')) {
            foreach ($request->attributes as $attributeValues) {
                if (!empty($attributeValues)) {
                    $query->whereHas('attributeValues', function($q) use ($attributeValues) {
                        $q->whereIn('attribute_values.id', $attributeValues);
                    });
                }
            }
        }
        
        // Tri
        $sortBy = $request->input('sort', 'created_at');
        $sortOrder = $request->input('order', 'desc');
        
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'popular':
                $query->orderBy('reviews_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        $products = $query->paginate(15)->withQueryString();
        
        // Récupérer les attributs filtrables pour cette catégorie
        $attributes = \App\Models\Attribute::filterable()
            ->ordered()
            ->with('attributeValues')
            ->get();
        
        // Calculer les plages de prix
        $priceRange = Product::active()
            ->where('category_id', $category->id)
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();
        
        return view('categorie', compact('category', 'bestOffers', 'newProducts', 'products', 'attributes', 'priceRange'));
    }
    
    public function search(Request $request)
    {
        $searchQuery = $request->input('q');
        $categoryId = $request->input('category_id');
        
        $query = Product::active()->inStock();
        
        // Recherche textuelle
        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%")
                  ->orWhere('brand', 'like', "%{$searchQuery}%");
            });
        }
        
        // Filtre par catégorie
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        // Filtre par sous-catégorie
        if ($request->filled('subcategory')) {
            $query->where('subcategory_id', $request->subcategory);
        }
        
        // Filtre par prix
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Filtre par note
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }
        
        // Filtre par attributs
        if ($request->filled('attributes')) {
            foreach ($request->attributes as $attributeValues) {
                if (!empty($attributeValues)) {
                    $query->whereHas('attributeValues', function($q) use ($attributeValues) {
                        $q->whereIn('attribute_values.id', $attributeValues);
                    });
                }
            }
        }
        
        // Tri
        switch ($request->input('sort')) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'popular':
                $query->orderBy('reviews_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        $products = $query->paginate(15)->withQueryString();
        
        $categories = Category::active()->ordered()->get();
        
        // Récupérer les attributs filtrables
        $attributes = \App\Models\Attribute::filterable()
            ->ordered()
            ->with('attributeValues')
            ->get();
        
        // Calculer les plages de prix
        $priceRange = Product::active()
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();
        
        return view('search_product', compact('products', 'categories', 'searchQuery', 'attributes', 'priceRange'));
    }
    
    public function boutique(Request $request)
    {
        // Meilleures offres (produits en vedette)
        $bestOffers = Product::active()
            ->featured()
            ->inStock()
            ->take(12)
            ->get();
        
        // Nouveautés
        $newProducts = Product::active()
            ->new()
            ->inStock()
            ->take(12)
            ->get();
        
        // Tous les produits en vedette avec filtres
        $query = Product::active()->featured()->inStock();
        
        // Filtres
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }
        
        // Tri
        switch ($request->input('sort')) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'popular':
                $query->orderBy('reviews_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        $products = $query->paginate(15)->withQueryString();
        
        $categories = Category::active()->ordered()->get();
        
        $attributes = \App\Models\Attribute::filterable()
            ->ordered()
            ->with('attributeValues')
            ->get();
        
        $priceRange = Product::active()
            ->featured()
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();
        
        return view('boutique_officielle', compact('bestOffers', 'newProducts', 'products', 'categories', 'attributes', 'priceRange'));
    }
}
