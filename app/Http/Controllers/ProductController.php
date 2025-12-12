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
        // Charger le produit avec ses relations
        $product = Product::where('slug', $slug)
            ->with([
                'categories', 
                'subcategories', 
                'category', 
                'subcategory',
                'attributeValues.attribute',
                'variations.attributeValues.attribute'
            ])
            ->firstOrFail();
        
        // Recharger le stock directement depuis la base de données AVANT de charger les relations
        // Cela garantit que le stock est toujours à jour, même après des commandes récentes
        $freshStock = \Illuminate\Support\Facades\DB::table('products')
            ->where('id', $product->id)
            ->value('stock');
        
        // Mettre à jour le stock du produit avec la valeur fraîche depuis la base
        if ($freshStock !== null) {
            $product->stock = (int)$freshStock;
        }
        
        // Recharger complètement le produit depuis la base pour garantir la cohérence
        // Cela force Laravel à récupérer toutes les données fraîches, y compris le stock
        // IMPORTANT: Préserver les variations et les attributs pour la page produit
        $freshProduct = Product::where('id', $product->id)
            ->with([
                'categories', 
                'subcategories', 
                'category', 
                'subcategory',
                'attributeValues.attribute',
                'variations.attributeValues.attribute'
            ])
            ->first();
        
        if ($freshProduct) {
            // Préserver les relations chargées du produit original
            $freshProduct->setRelation('variations', $product->variations);
            $freshProduct->setRelation('attributeValues', $product->attributeValues);
            $product = $freshProduct;
        }
        
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
        
        // Ajouter des headers pour empêcher le cache navigateur et forcer le rechargement
        $response = response()->view('product', compact('product', 'similarProducts', 'recentProducts'));
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }
    
    public function category($slug, Request $request)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        
        // Vérifier si la catégorie est active, sinon retourner 404
        if (!$category->is_active) {
            abort(404);
        }
        
        // Métadonnées SEO
        $seoData = \App\Http\Controllers\SeoController::getCategorySeo($category);
        foreach ($seoData as $key => $value) {
            $seoKey = 'seo' . ucfirst($key);
            view()->share($seoKey, $value);
        }
        // Charger les sous-catégories qui ont des produits disponibles dans cette catégorie
        $category->load(['subcategories' => function($query) use ($category) {
            $query->where('is_active', true)
                  ->whereHas('productsMany', function($q) use ($category) {
                      $q->where('is_active', true)
                        ->where('stock', '>', 0)
                        ->whereHas('categories', function($catQuery) use ($category) {
                            $catQuery->where('categories.id', $category->id);
                        });
                  })
                  ->orderBy('order')
                  ->orderBy('name');
        }]);
        
        // Détecter la sous-catégorie si elle est dans la requête
        $subcategory = null;
        if ($request->filled('subcategory')) {
            $subcategoryValue = $request->subcategory;
            // Vérifier si c'est un ID numérique ou un slug
            if (is_numeric($subcategoryValue)) {
                $subcategory = \App\Models\Subcategory::where('id', $subcategoryValue)->first();
            } else {
                // C'est un slug
                $subcategory = \App\Models\Subcategory::where('slug', $subcategoryValue)->first();
            }
        }
        
        // Meilleures offres de la catégorie/sous-catégorie (produits en promo)
        $bestOffersQuery = Product::active()
            ->whereHas('categories', function($query) use ($category) {
                $query->where('categories.id', $category->id);
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
                });
            })
            ->inStock();
        
        // Appliquer le filtre de sous-catégorie si une sous-catégorie est sélectionnée
        if ($subcategory) {
            $bestOffersQuery->whereHas('subcategories', function($query) use ($subcategory) {
                $query->where('subcategories.id', $subcategory->id);
            });
        }
        
        $bestOffers = $bestOffersQuery->take(12)->get();
        
        // Nouveautés de la catégorie/sous-catégorie (produits récemment ajoutés)
        $newProductsQuery = Product::active()
            ->whereHas('categories', function($query) use ($category) {
                $query->where('categories.id', $category->id);
            })
            ->inStock();
        
        // Appliquer le filtre de sous-catégorie si une sous-catégorie est sélectionnée
        if ($subcategory) {
            $newProductsQuery->whereHas('subcategories', function($query) use ($subcategory) {
                $query->where('subcategories.id', $subcategory->id);
            });
        }
        
        $newProducts = $newProductsQuery->orderBy('created_at', 'desc')->take(12)->get();
        
        // Construire la requête avec filtres
        $query = Product::active()
            ->whereHas('categories', function($query) use ($category) {
                $query->where('categories.id', $category->id);
            })
            ->inStock();
        
        // Filtre par sous-catégorie
        if ($subcategory) {
            $query->whereHas('subcategories', function($query) use ($subcategory) {
                $query->where('subcategories.id', $subcategory->id);
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
        
        // Filtre par marque
        if ($request->filled('brand')) {
            $brands = is_array($request->brand) ? $request->brand : [$request->brand];
            $query->where(function($q) use ($brands) {
                foreach ($brands as $brand) {
                    $q->orWhere('brand', 'like', "%{$brand}%");
                }
            });
        }
        
        // Filtre par boutique
        if ($request->filled('store_id')) {
            $storeIds = is_array($request->store_id) ? $request->store_id : [$request->store_id];
            $query->whereIn('store_id', $storeIds);
        }
        
        // Filtre par disponibilité/stock
        if ($request->filled('in_stock')) {
            if ($request->in_stock == '1') {
                $query->where('stock', '>', 0);
            } elseif ($request->in_stock == '0') {
                $query->where('stock', '<=', 0);
            }
        }
        
        // Filtre par promotions
        if ($request->filled('on_sale')) {
            if ($request->on_sale == '1') {
                $query->where(function($q) {
                    $q->whereNotNull('old_price')
                      ->whereColumn('old_price', '>', 'price')
                      ->orWhere(function($subQ) {
                          $subQ->whereNotNull('discount_percentage')
                               ->where('discount_percentage', '>', 0);
                      });
                });
            }
        }
        
        // Filtre par nouveautés
        if ($request->filled('is_new')) {
            if ($request->is_new == '1') {
                $query->where('is_new', true);
            }
        }
        
        // Filtre par tendance
        if ($request->filled('is_trending')) {
            if ($request->is_trending == '1') {
                $query->where('is_trending', true);
            }
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
        
        // Récupérer les attributs filtrables pour cette catégorie qui ont des produits disponibles
        $attributes = \App\Models\Attribute::filterable()
            ->ordered()
            ->whereHas('attributeValues.products', function($q) use ($category) {
                $q->where('is_active', true)
                  ->where('stock', '>', 0)
                  ->whereHas('categories', function($query) use ($category) {
                      $query->where('categories.id', $category->id);
                  });
            })
            ->with(['attributeValues' => function($query) use ($category) {
                $query->whereHas('products', function($q) use ($category) {
                    $q->where('is_active', true)
                      ->where('stock', '>', 0)
                      ->whereHas('categories', function($query) use ($category) {
                          $query->where('categories.id', $category->id);
                      });
                })->orderBy('value');
            }])
            ->get();
        
        // Calculer les plages de prix pour cette catégorie (produits disponibles uniquement)
        $priceRange = Product::active()
            ->where('stock', '>', 0)
            ->whereHas('categories', function($query) use ($category) {
                $query->where('categories.id', $category->id);
            })
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();
        
        // Récupérer les marques disponibles pour cette catégorie
        $availableBrands = Product::active()
            ->whereHas('categories', function($query) use ($category) {
                $query->where('categories.id', $category->id);
            })
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->selectRaw('DISTINCT brand')
            ->orderBy('brand')
            ->pluck('brand')
            ->filter()
            ->values();
        
        // Récupérer les boutiques disponibles pour cette catégorie
        $availableStores = \App\Models\Store::whereHas('products', function($query) use ($category) {
                $query->whereHas('categories', function($q) use ($category) {
                    $q->where('categories.id', $category->id);
                })->where('is_active', true);
            })
            ->select('id', 'name', 'slug')
            ->orderBy('name')
            ->get();
        
        return view('categorie', compact('category', 'subcategory', 'bestOffers', 'newProducts', 'products', 'attributes', 'priceRange', 'availableBrands', 'availableStores'));
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
        
        // Filtre par marque
        if ($request->filled('brand')) {
            $brands = is_array($request->brand) ? $request->brand : [$request->brand];
            $query->where(function($q) use ($brands) {
                foreach ($brands as $brand) {
                    $q->orWhere('brand', 'like', "%{$brand}%");
                }
            });
        }
        
        // Filtre par boutique
        if ($request->filled('store_id')) {
            $storeIds = is_array($request->store_id) ? $request->store_id : [$request->store_id];
            $query->whereIn('store_id', $storeIds);
        }
        
        // Filtre par disponibilité/stock
        if ($request->filled('in_stock')) {
            if ($request->in_stock == '1') {
                $query->where('stock', '>', 0);
            } elseif ($request->in_stock == '0') {
                $query->where('stock', '<=', 0);
            }
        }
        
        // Filtre par promotions
        if ($request->filled('on_sale')) {
            if ($request->on_sale == '1') {
                $query->where(function($q) {
                    $q->whereNotNull('old_price')
                      ->whereColumn('old_price', '>', 'price')
                      ->orWhere(function($subQ) {
                          $subQ->whereNotNull('discount_percentage')
                               ->where('discount_percentage', '>', 0);
                      });
                });
            }
        }
        
        // Filtre par nouveautés
        if ($request->filled('is_new')) {
            if ($request->is_new == '1') {
                $query->where('is_new', true);
            }
        }
        
        // Filtre par tendance
        if ($request->filled('is_trending')) {
            if ($request->is_trending == '1') {
                $query->where('is_trending', true);
            }
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
        
        // Récupérer uniquement les catégories qui ont des produits correspondant à la recherche
        $categories = Category::active()
            ->whereHas('products', function($q) use ($searchQuery, $categoryId) {
                $q->where('is_active', true)->where('stock', '>', 0);
                if ($searchQuery) {
                    $q->where(function($query) use ($searchQuery) {
                        $query->where('name', 'like', "%{$searchQuery}%")
                              ->orWhere('description', 'like', "%{$searchQuery}%")
                              ->orWhere('brand', 'like', "%{$searchQuery}%");
                    });
                }
                if ($categoryId) {
                    $q->where('category_id', $categoryId);
                }
            })
            ->ordered()
            ->get();
        
        // Récupérer les attributs filtrables qui ont des produits correspondants
        $attributes = \App\Models\Attribute::filterable()
            ->ordered()
            ->whereHas('attributeValues.products', function($q) use ($searchQuery, $categoryId) {
                $q->where('is_active', true)->where('stock', '>', 0);
                if ($searchQuery) {
                    $q->where(function($query) use ($searchQuery) {
                        $query->where('name', 'like', "%{$searchQuery}%")
                              ->orWhere('description', 'like', "%{$searchQuery}%")
                              ->orWhere('brand', 'like', "%{$searchQuery}%");
                    });
                }
                if ($categoryId) {
                    $q->where('category_id', $categoryId);
                }
            })
            ->with(['attributeValues' => function($query) use ($searchQuery, $categoryId) {
                $query->whereHas('products', function($q) use ($searchQuery, $categoryId) {
                    $q->where('is_active', true)->where('stock', '>', 0);
                    if ($searchQuery) {
                        $q->where(function($query) use ($searchQuery) {
                            $query->where('name', 'like', "%{$searchQuery}%")
                                  ->orWhere('description', 'like', "%{$searchQuery}%")
                                  ->orWhere('brand', 'like', "%{$searchQuery}%");
                        });
                    }
                    if ($categoryId) {
                        $q->where('category_id', $categoryId);
                    }
                })->orderBy('value');
            }])
            ->get();
        
        // Calculer les plages de prix pour les produits correspondants
        $priceQuery = Product::active()->where('stock', '>', 0);
        if ($searchQuery) {
            $priceQuery->where(function($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%")
                  ->orWhere('brand', 'like', "%{$searchQuery}%");
            });
        }
        if ($categoryId) {
            $priceQuery->where('category_id', $categoryId);
        }
        $priceRange = $priceQuery->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
        
        // Récupérer les marques disponibles
        $availableBrands = Product::active()
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->selectRaw('DISTINCT brand')
            ->orderBy('brand')
            ->pluck('brand')
            ->filter()
            ->values();
        
        // Récupérer les boutiques disponibles
        $availableStores = \App\Models\Store::whereHas('products', function($query) {
                $query->where('is_active', true);
            })
            ->select('id', 'name', 'slug')
            ->orderBy('name')
            ->get();
        
        return view('search_product', compact('products', 'categories', 'searchQuery', 'attributes', 'priceRange', 'availableBrands', 'availableStores'));
    }
    
    public function boutique(Request $request)
    {
        // Meilleures offres (produits des boutiques officielles en promo)
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
                });
            })
            ->inStock()
            ->take(12)
            ->get();
        
        // Nouveautés des boutiques officielles (produits récemment ajoutés)
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
        
        // Filtre par marque
        if ($request->filled('brand')) {
            $brands = is_array($request->brand) ? $request->brand : [$request->brand];
            $query->where(function($q) use ($brands) {
                foreach ($brands as $brand) {
                    $q->orWhere('brand', 'like', "%{$brand}%");
                }
            });
        }
        
        // Filtre par disponibilité/stock
        if ($request->filled('in_stock')) {
            if ($request->in_stock == '1') {
                $query->where('stock', '>', 0);
            } elseif ($request->in_stock == '0') {
                $query->where('stock', '<=', 0);
            }
        }
        
        // Filtre par promotions
        if ($request->filled('on_sale')) {
            if ($request->on_sale == '1') {
                $query->where(function($q) {
                    $q->whereNotNull('old_price')
                      ->whereColumn('old_price', '>', 'price')
                      ->orWhere(function($subQ) {
                          $subQ->whereNotNull('discount_percentage')
                               ->where('discount_percentage', '>', 0);
                      });
                });
            }
        }
        
        // Filtre par nouveautés
        if ($request->filled('is_new')) {
            if ($request->is_new == '1') {
                $query->where('is_new', true);
            }
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
        
        // Récupérer uniquement les catégories qui ont des produits dans les boutiques officielles
        $categories = Category::active()
            ->whereHas('products', function($q) {
                $q->where('is_active', true)
                  ->where('stock', '>', 0)
                  ->whereHas('store', function($storeQuery) {
                      $storeQuery->where('is_official', true);
                  });
            })
            ->ordered()
            ->get();
        
        // Récupérer les attributs filtrables qui ont des produits dans les boutiques officielles
        $attributes = \App\Models\Attribute::filterable()
            ->ordered()
            ->whereHas('attributeValues.products', function($q) {
                $q->where('is_active', true)
                  ->where('stock', '>', 0)
                  ->whereHas('store', function($storeQuery) {
                      $storeQuery->where('is_official', true);
                  });
            })
            ->with(['attributeValues' => function($query) {
                $query->whereHas('products', function($q) {
                    $q->where('is_active', true)
                      ->where('stock', '>', 0)
                      ->whereHas('store', function($storeQuery) {
                          $storeQuery->where('is_official', true);
                      });
                })->orderBy('value');
            }])
            ->get();
        
        // Calculer les plages de prix pour les produits des boutiques officielles
        $priceRange = Product::active()
            ->where('stock', '>', 0)
            ->whereHas('store', function($q) {
                $q->where('is_official', true);
            })
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();
        
        // Récupérer les marques disponibles pour les boutiques officielles
        $availableBrands = Product::active()
            ->whereHas('store', function($q) {
                $q->where('is_official', true);
            })
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->selectRaw('DISTINCT brand')
            ->orderBy('brand')
            ->pluck('brand')
            ->filter()
            ->values();
        
        return view('boutique_officielle', compact('bestOffers', 'newProducts', 'products', 'categories', 'attributes', 'priceRange', 'availableBrands'));
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
