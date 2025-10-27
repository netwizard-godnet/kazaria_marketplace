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
        // Meilleures offres (produits des boutiques officielles EN PROMO uniquement)
        $bestOffers = Product::active()
            ->with('store')
            ->whereHas('store', function($q) {
                $q->where('is_official', true);
            })
            ->where(function($q) {
                // Produit en promo si old_price est renseigné et différent de price
                $q->where(function($subQ) {
                    $subQ->whereNotNull('old_price')
                         ->whereColumn('old_price', '>', 'price');
                })
                // OU si discount_percentage est renseigné et supérieur à 0
                ->orWhere(function($subQ) {
                    $subQ->whereNotNull('discount_percentage')
                         ->where('discount_percentage', '>', 0);
                })
                // OU si is_best_offer est true (produit marqué comme meilleure offre)
                ->orWhere('is_best_offer', true);
            })
            ->inStock()
            ->take(12)
            ->get();
        
        // Nouveautés des boutiques officielles (les plus récents, incluant aussi les produits en promo)
        $newProducts = Product::active()
            ->with('store')
            ->whereHas('store', function($q) {
                $q->where('is_official', true);
            })
            ->inStock()
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();
        
        // Tous les produits des boutiques officielles avec filtres
        $query = Product::active()
            ->with('store')
            ->whereHas('store', function($q) {
                $q->where('is_official', true);
            })
            ->inStock();
        
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
            ->whereHas('store', function($q) {
                $q->where('is_official', true);
            })
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();
        
        return view('boutique_officielle', compact('bestOffers', 'newProducts', 'products', 'categories', 'attributes', 'priceRange'));
    }

    /**
     * Afficher les produits par attribut
     */
    public function byAttribute($attributeSlug, $valueSlug = null, Request $request)
    {
        $attribute = \App\Models\Attribute::where('slug', $attributeSlug)->firstOrFail();
        
        $query = Product::active()->inStock()->with(['categories', 'attributeValues.attribute']);
        
        if ($valueSlug) {
            // Filtrer par valeur d'attribut spécifique
            $attributeValue = \App\Models\AttributeValue::where('slug', $valueSlug)
                ->where('attribute_id', $attribute->id)
                ->firstOrFail();
            
            $query->whereHas('attributeValues', function($q) use ($attributeValue) {
                $q->where('attribute_values.id', $attributeValue->id);
            });
            
            $pageTitle = "Produits {$attribute->name}: {$attributeValue->value}";
        } else {
            // Afficher tous les produits ayant cet attribut
            $query->whereHas('attributeValues', function($q) use ($attribute) {
                $q->where('attribute_id', $attribute->id);
            });
            
            $pageTitle = "Produits par {$attribute->name}";
        }
        
        // Appliquer les filtres de recherche
        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }
        
        // Filtre par prix
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Filtre par catégorie
        if ($request->filled('category')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('categories.slug', $request->category);
            });
        }
        
        // Tri
        $sortBy = $request->get('sort', 'name');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            default:
                $query->orderBy('name', 'asc');
        }
        
        $products = $query->paginate(15)->withQueryString();
        
        // Récupérer les valeurs de cet attribut pour les filtres
        $attributeValues = $attribute->attributeValues()->orderBy('order')->get();
        
        // Récupérer les autres attributs filtrables
        $otherAttributes = \App\Models\Attribute::filterable()
            ->where('id', '!=', $attribute->id)
            ->ordered()
            ->with('attributeValues')
            ->get();
        
        $categories = Category::active()->ordered()->get();
        
        $priceRange = Product::active()
            ->whereHas('attributeValues', function($q) use ($attribute) {
                $q->where('attribute_id', $attribute->id);
            })
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();
        
        return view('products.by-attribute', compact(
            'attribute', 
            'attributeValue', 
            'products', 
            'attributeValues', 
            'otherAttributes', 
            'categories', 
            'priceRange', 
            'pageTitle'
        ));
    }
}
