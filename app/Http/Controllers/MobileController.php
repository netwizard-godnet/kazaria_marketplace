<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Store;
use App\Models\Banner;
use App\Models\CarouselSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileController extends Controller
{
    /**
     * Récupérer les données de la page d'accueil pour mobile
     */
    public function getHomeData(Request $request)
    {
        try {
            // Catégories actives avec sous-catégories (comme dans getCategories)
            // Retourner toutes les catégories actives triées par ordre, pas seulement 7
            $categories = Category::where('is_active', true)
                ->orderBy('order', 'asc')
                ->with(['subcategories' => function ($query) {
                    $query->where('is_active', true)->orderBy('order', 'asc');
                }])
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'image' => $category->image ? asset('storage/' . $category->image) : null,
                        'icon' => $category->icon,
                        'description' => $category->description,
                        'is_active' => $category->is_active ?? true,
                        'order' => $category->order ?? 0,
                        'subcategories' => $category->subcategories->map(function ($subcategory) {
                            return [
                                'id' => $subcategory->id,
                                'category_id' => $subcategory->category_id,
                                'name' => $subcategory->name,
                                'slug' => $subcategory->slug,
                                'image' => $subcategory->image ? asset('storage/' . $subcategory->image) : null,
                                'icon' => $subcategory->icon,
                                'is_active' => $subcategory->is_active ?? true,
                                'order' => $subcategory->order ?? 0,
                            ];
                        }),
                    ];
                });

            // Produits en vedette
            $featuredProducts = Product::active()
                ->where('is_featured', true)
                ->inStock()
                ->with(['category', 'store'])
                ->take(10)
                ->get()
                ->map(function ($product) {
                    return $this->formatProduct($product);
                });

            // Produits tendances
            $trendingProducts = Product::active()
                ->where('is_trending', true)
                ->inStock()
                ->with(['category', 'store'])
                ->take(10)
                ->get()
                ->map(function ($product) {
                    return $this->formatProduct($product);
                });

            // Produits nouveaux
            $newProducts = Product::active()
                ->where('is_new', true)
                ->inStock()
                ->with(['category', 'store'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($product) {
                    return $this->formatProduct($product);
                });

            // Meilleures offres
            $bestOffers = Product::active()
                ->where('is_best_offer', true)
                ->inStock()
                ->whereNotNull('old_price')
                ->whereRaw('old_price > price')
                ->with(['category', 'store'])
                ->orderByRaw('((old_price - price) / old_price * 100) DESC')
                ->take(10)
                ->get()
                ->map(function ($product) {
                    return $this->formatProduct($product);
                });

            // Bannières pour le carousel principal de la page d'accueil
            // 1. Slides du carousel principal (CarouselSlide)
            $carouselSlides = CarouselSlide::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get()
                ->map(function ($slide) {
                    // Utiliser l'accessor du modèle qui gère correctement les URLs
                    return [
                        'id' => $slide->id,
                        'title' => $slide->title ?? '',
                        'description' => $slide->description ?? '',
                        'image' => $slide->image_url,
                        'link' => $slide->link_url ?? null,
                        'button_text' => $slide->button_text ?? null,
                        'type' => 'carousel',
                    ];
                });

            // 2. Première bannière d'accueil
            $homepageBanner1 = Banner::where('banner_type', 'homepage_banner_1')
                ->where('is_active', true)
                ->where('show_on_mobile', true)
                ->first();
            
            // 3. Deuxième bannière d'accueil
            $homepageBanner2 = Banner::where('banner_type', 'homepage_banner_2')
                ->where('is_active', true)
                ->where('show_on_mobile', true)
                ->first();

            // Combiner toutes les bannières dans l'ordre :
            // 1. Slides du carousel principal
            // 2. Première bannière d'accueil
            // 3. Deuxième bannière d'accueil
            $banners = collect($carouselSlides);

            if ($homepageBanner1) {
                $banners->push([
                    'id' => $homepageBanner1->id,
                    'title' => $homepageBanner1->title ?? '',
                    'description' => $homepageBanner1->subtitle ?? '',
                    'image' => $this->getBannerImageUrl($homepageBanner1->image_path),
                    'link' => $homepageBanner1->link_url ?? null,
                    'button_text' => null,
                    'type' => 'homepage_banner_1',
                ]);
            }

            if ($homepageBanner2) {
                $banners->push([
                    'id' => $homepageBanner2->id,
                    'title' => $homepageBanner2->title ?? '',
                    'description' => $homepageBanner2->subtitle ?? '',
                    'image' => $this->getBannerImageUrl($homepageBanner2->image_path),
                    'link' => $homepageBanner2->link_url ?? null,
                    'button_text' => null,
                    'type' => 'homepage_banner_2',
                ]);
            }

            // Publicités de la page d'accueil (publicite_1 à publicite_5)
            $homepageAds = [];
            
            // Récupérer les 5 publicités de la page d'accueil
            $publiciteTypes = ['publicite_1', 'publicite_2', 'publicite_3', 'publicite_4', 'publicite_5'];
            
            foreach ($publiciteTypes as $publiciteType) {
                $publicite = Banner::where('banner_type', $publiciteType)
                    ->where('is_active', true)
                    ->where('show_on_mobile', true)
                    ->first();
                    
                if ($publicite) {
                    $homepageAds[] = [
                        'id' => $publicite->id,
                        'title' => $publicite->title ?? '',
                        'image' => $this->getBannerImageUrl($publicite->image_path),
                        'link' => $publicite->link_url ?? null,
                        'type' => $publiciteType,
                    ];
                }
            }

            // Deals du jour (basé sur HomeController)
            $countdownDuration = \App\Helpers\SettingHelper::get('deals_countdown_duration', '60');
            $todayStart = now()->startOfDay();
            $calculatedEndTime = $todayStart->copy()->addMinutes((int)$countdownDuration);
            
            if ($calculatedEndTime->isPast()) {
                $countdownEndTime = now()->addDay()->startOfDay()->addMinutes((int)$countdownDuration)->timestamp * 1000;
            } else {
                $countdownEndTime = $calculatedEndTime->timestamp * 1000;
            }
            
            $dealsCategories = \App\Helpers\SettingHelper::get('deals_categories', '');
            $dealsSubcategories = \App\Helpers\SettingHelper::get('deals_subcategories', '');
            $dealsMinDiscount = \App\Helpers\SettingHelper::get('deals_min_discount', '10');
            $dealsMaxDiscount = \App\Helpers\SettingHelper::get('deals_max_discount', '25');
            
            $dealsQuery = Product::active()
                ->whereNotNull('old_price')
                ->inStock()
                ->where('discount_percentage', '>=', (int)$dealsMinDiscount)
                ->where('discount_percentage', '<=', (int)$dealsMaxDiscount);
            
            if (!empty($dealsCategories)) {
                $categoryIds = array_map('trim', explode(',', $dealsCategories));
                $dealsQuery->whereIn('category_id', $categoryIds);
            }
            
            if (!empty($dealsSubcategories)) {
                $subcategoryIds = array_map('trim', explode(',', $dealsSubcategories));
                $dealsQuery->whereIn('subcategory_id', $subcategoryIds);
            }
            
            $dealsProducts = $dealsQuery->orderBy('discount_percentage', 'desc')
                ->take(16)
                ->with(['category', 'store'])
                ->get()
                ->map(function ($product) {
                    return $this->formatProduct($product);
                });

            // Promotions (black_friday, flash_sales, etc.)
            $promotions = [
                'black_friday' => [
                    'enabled' => \App\Helpers\SettingHelper::get('black_friday_enabled', false) == '1',
                    'start_date' => \App\Helpers\SettingHelper::get('black_friday_start', null),
                    'end_date' => \App\Helpers\SettingHelper::get('black_friday_end', null),
                ],
                'flash_sales' => [
                    'enabled' => \App\Helpers\SettingHelper::get('flash_sales_enabled', false) == '1',
                    'start_date' => \App\Helpers\SettingHelper::get('flash_sales_start', null),
                    'end_date' => \App\Helpers\SettingHelper::get('flash_sales_end', null),
                ],
            ];

            // Produits par catégorie (comme sur le web)
            $phoneProducts = Product::active()
                ->whereHas('category', function($query) {
                    $query->where('slug', 'telephones-et-tablettes');
                })
                ->inStock()
                ->with(['category', 'store'])
                ->take(12)
                ->get()
                ->map(function ($product) {
                    return $this->formatProduct($product);
                });

            $tvProducts = Product::active()
                ->whereHas('category', function($query) {
                    $query->where('slug', 'tv-et-electronique');
                })
                ->inStock()
                ->with(['category', 'store'])
                ->take(12)
                ->get()
                ->map(function ($product) {
                    return $this->formatProduct($product);
                });

            $electroProducts = Product::active()
                ->whereHas('category', function($query) {
                    $query->where('slug', 'electromenager');
                })
                ->inStock()
                ->with(['category', 'store'])
                ->take(12)
                ->get()
                ->map(function ($product) {
                    return $this->formatProduct($product);
                });

            $computerProducts = Product::active()
                ->whereHas('category', function($query) {
                    $query->where('slug', 'ordinateurs-et-accessoires');
                })
                ->inStock()
                ->with(['category', 'store'])
                ->take(12)
                ->get()
                ->map(function ($product) {
                    return $this->formatProduct($product);
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $categories->values()->all(),
                    'featured_products' => $featuredProducts->values()->all(),
                    'trending_products' => $trendingProducts->values()->all(),
                    'new_products' => $newProducts->values()->all(),
                    'best_offers' => $bestOffers->values()->all(),
                    'banners' => $banners->values()->all(),
                    'homepage_ads' => $homepageAds,
                    'deals' => [
                        'products' => $dealsProducts->values()->all(),
                        'countdown_end' => $countdownEndTime,
                        'settings' => [
                            'duration' => (int)$countdownDuration,
                            'min_discount' => (int)$dealsMinDiscount,
                            'max_discount' => (int)$dealsMaxDiscount,
                        ],
                    ],
                    'promotions' => $promotions,
                    'category_products' => [
                        'phones' => [
                            'category' => [
                                'id' => null,
                                'name' => 'Téléphones et Tablettes',
                                'slug' => 'telephones-et-tablettes',
                            ],
                            'products' => $phoneProducts->values()->all(),
                        ],
                        'tv' => [
                            'category' => [
                                'id' => null,
                                'name' => 'TV et Électronique',
                                'slug' => 'tv-et-electronique',
                            ],
                            'products' => $tvProducts->values()->all(),
                        ],
                        'electro' => [
                            'category' => [
                                'id' => null,
                                'name' => 'Électroménager',
                                'slug' => 'electromenager',
                            ],
                            'products' => $electroProducts->values()->all(),
                        ],
                        'computers' => [
                            'category' => [
                                'id' => null,
                                'name' => 'Ordinateurs et Accessoires',
                                'slug' => 'ordinateurs-et-accessoires',
                            ],
                            'products' => $computerProducts->values()->all(),
                        ],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des données: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer les catégories pour mobile
     */
    public function getCategories(Request $request)
    {
        try {
            $categories = Category::where('is_active', true)
                ->orderBy('order', 'asc')
                ->with(['subcategories' => function ($query) {
                    $query->where('is_active', true)->orderBy('order', 'asc');
                }])
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'image' => $category->image ? asset('storage/' . $category->image) : null,
                        'icon' => $category->icon,
                        'description' => $category->description,
                        'is_active' => $category->is_active ?? true,
                        'order' => $category->order ?? 0,
                        'subcategories' => $category->subcategories->map(function ($subcategory) {
                            return [
                                'id' => $subcategory->id,
                                'category_id' => $subcategory->category_id,
                                'name' => $subcategory->name,
                                'slug' => $subcategory->slug,
                                'image' => $subcategory->image ? asset('storage/' . $subcategory->image) : null,
                                'icon' => $subcategory->icon,
                                'is_active' => $subcategory->is_active ?? true,
                                'order' => $subcategory->order ?? 0,
                            ];
                        }),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $categories->values()->all(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des catégories: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer les produits pour mobile
     */
    public function getProducts(Request $request)
    {
        try {
            $query = Product::active()->inStock()->with(['category', 'store']);

            // Filtres
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('store_id')) {
                $query->where('store_id', $request->store_id);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            }

            // Filtres spéciaux
            if ($request->has('featured') && $request->featured == '1') {
                $query->where('is_featured', true);
            }

            if ($request->has('trending') && $request->trending == '1') {
                $query->where('is_trending', true);
            }

            if ($request->has('best_offers') && $request->best_offers == '1') {
                $query->where('is_best_offer', true)
                    ->whereNotNull('old_price')
                    ->whereRaw('old_price > price');
            }

            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            if ($sortBy === 'price') {
                $query->orderBy('price', $sortOrder);
            } elseif ($sortBy === 'name') {
                $query->orderBy('name', $sortOrder);
            } elseif ($sortBy === 'rating') {
                $query->orderBy('rating', $sortOrder);
            } else {
                $query->orderBy('created_at', $sortOrder);
            }

            // Pagination
            $perPage = $request->get('limit', 20);
            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => collect($products->items())->map(function ($product) {
                    return $this->formatProduct($product);
                })->values()->all(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des produits: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer les détails d'un produit pour mobile
     */
    public function getProductDetails($id, Request $request)
    {
        try {
            $product = Product::with(['category', 'subcategory', 'store'])
                ->findOrFail($id);

            // Produits similaires
            $similarProducts = Product::active()
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->inStock()
                ->take(10)
                ->get()
                ->map(function ($p) {
                    return $this->formatProduct($p);
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $this->formatProduct($product, true),
                    'similar_products' => $similarProducts,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produit introuvable: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Récupérer les bannières pour mobile
     */
    public function getBanners(Request $request)
    {
        try {
            $query = Banner::where('is_active', true);
            
            // Filtrer par type si fourni
            if ($request->has('type')) {
                $query->where('banner_type', $request->type);
            }
            
            $banners = $query->orderBy('sort_order', 'asc')
                ->get()
                ->map(function ($banner) {
                    return [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'image' => $banner->image_path ? asset('storage/' . $banner->image_path) : null,
                        'link' => $banner->link_url,
                        'type' => $banner->banner_type,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $banners,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des bannières: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer les boutiques pour mobile
     */
    public function getStores(Request $request)
    {
        try {
            $query = Store::where('status', 'active');

            if ($request->has('verified') && $request->verified == '1') {
                $query->where('is_verified', true);
            }

            if ($request->has('official') && $request->official == '1') {
                $query->where('is_official', true);
            }

            // Pagination
            $perPage = $request->get('limit', 20);
            $perPage = $request->get('per_page', $perPage);
            $stores = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => collect($stores->items())->map(function ($store) {
                    return $this->formatStore($store);
                })->values()->all(),
                'pagination' => [
                    'current_page' => $stores->currentPage(),
                    'last_page' => $stores->lastPage(),
                    'per_page' => $stores->perPage(),
                    'total' => $stores->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des boutiques: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer les boutiques vérifiées
     */
    public function getVerifiedStores(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $stores = Store::where('status', 'active')
                ->where('is_verified', true)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => collect($stores->items())->map(function ($store) {
                    return $this->formatStore($store);
                })->values()->all(),
                'pagination' => [
                    'current_page' => $stores->currentPage(),
                    'last_page' => $stores->lastPage(),
                    'per_page' => $stores->perPage(),
                    'total' => $stores->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des boutiques vérifiées: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer les boutiques populaires
     */
    public function getPopularStores(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $stores = Store::where('status', 'active')
                ->orderBy('total_sales', 'desc')
                ->orderBy('rating', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => collect($stores->items())->map(function ($store) {
                    return $this->formatStore($store);
                })->values()->all(),
                'pagination' => [
                    'current_page' => $stores->currentPage(),
                    'last_page' => $stores->lastPage(),
                    'per_page' => $stores->perPage(),
                    'total' => $stores->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des boutiques populaires: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer les meilleures offres des boutiques officielles
     */
    public function getBestOffersStores(Request $request)
    {
        try {
            // Produits avec meilleures offres des boutiques officielles
            $products = Product::active()
                ->whereHas('store', function ($query) {
                    $query->where('status', 'active')
                        ->where('is_official', true);
                })
                ->where('is_best_offer', true)
                ->whereNotNull('old_price')
                ->whereRaw('old_price > price')
                ->with(['category', 'store'])
                ->orderByRaw('((old_price - price) / old_price * 100) DESC')
                ->take(20)
                ->get()
                ->map(function ($product) {
                    return $this->formatProduct($product);
                });

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des meilleures offres: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer les nouveautés des boutiques officielles
     */
    public function getNewProductsStores(Request $request)
    {
        try {
            // Nouveaux produits des boutiques officielles
            $products = Product::active()
                ->whereHas('store', function ($query) {
                    $query->where('status', 'active')
                        ->where('is_official', true);
                })
                ->where('is_new', true)
                ->inStock()
                ->with(['category', 'store'])
                ->orderBy('created_at', 'desc')
                ->take(20)
                ->get()
                ->map(function ($product) {
                    return $this->formatProduct($product);
                });

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des nouveautés: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer les détails d'une boutique pour mobile
     */
    public function getStoreDetails($id, Request $request)
    {
        try {
            $store = Store::with(['category', 'subcategory'])->findOrFail($id);

            // Produits de la boutique
            $products = Product::active()
                ->where('store_id', $store->id)
                ->inStock()
                ->with(['category'])
                ->take(20)
                ->get()
                ->map(function ($product) {
                    return $this->formatProduct($product);
                });

            return response()->json([
                'success' => true,
                'store' => $this->formatStore($store, true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Boutique introuvable: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Récupérer les produits d'une boutique pour mobile
     */
    public function getStoreProducts($id, Request $request)
    {
        try {
            $store = Store::findOrFail($id);

            $query = Product::where('store_id', $store->id);

            // Filtrer seulement les produits actifs par défaut
            if (!$request->has('include_inactive')) {
                $query->active();
            }

            // Filtres de prix
            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            // Filtre de disponibilité
            if ($request->has('in_stock') && $request->in_stock == '1') {
                $query->where('stock', '>', 0);
            }

            // Recherche
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Gérer les tris spéciaux
            if ($sortBy === 'rating') {
                $query->orderBy('rating', $sortOrder);
            } elseif ($sortBy === 'sales') {
                // Tri par nombre de ventes via la relation orderItems
                $query->withCount('orderItems as sales_count')
                      ->orderBy('sales_count', $sortOrder);
            } elseif ($sortBy === 'views') {
                $query->orderBy('views_count', $sortOrder);
            } elseif ($sortBy === 'popular') {
                // Combinaison de vues et rating pour la popularité
                $query->orderBy('views_count', 'desc')
                      ->orderBy('rating', 'desc');
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            $perPage = min($request->get('per_page', 20), 50);
            $products = $query->with(['category', 'store'])
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'products' => $products->map(function ($product) {
                    return $this->formatProduct($product);
                })->values()->all(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des produits: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer les ventes flash pour mobile
     */
    public function getFlashSales(Request $request)
    {
        try {
            // Ventes flash = produits avec réduction importante et en stock limité
            $flashSales = Product::active()
                ->whereNotNull('old_price')
                ->whereRaw('old_price > price')
                ->whereRaw('((old_price - price) / old_price * 100) >= 20') // Au moins 20% de réduction
                ->inStock()
                ->with(['category', 'store'])
                ->orderByRaw('((old_price - price) / old_price * 100) DESC')
                ->take(20)
                ->get()
                ->map(function ($product) {
                    return $this->formatProduct($product);
                });

            return response()->json([
                'success' => true,
                'data' => $flashSales,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des ventes flash: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Formater un produit pour la réponse JSON
     */
    private function formatProduct($product, $fullDetails = false)
    {
        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => (float) $product->price,
            'old_price' => $product->old_price ? (float) $product->old_price : null,
            'discount_percentage' => $product->discount_percentage ? (float) $product->discount_percentage : null,
            'stock' => (int) $product->stock,
            'rating' => $product->rating ? (float) $product->rating : 0,
            'reviews_count' => $product->reviews_count ?? 0,
            'image' => $product->image ? asset('storage/' . $product->image) : null,
            'images' => $this->parseJsonField($product->images),
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'store' => $product->store ? [
                'id' => $product->store->id,
                'name' => $product->store->name,
                'slug' => $product->store->slug,
                'is_verified' => $product->store->is_verified,
            ] : null,
            'is_featured' => $product->is_featured ?? false,
            'is_trending' => $product->is_trending ?? false,
            'is_new' => $product->is_new ?? false,
            'is_best_offer' => $product->is_best_offer ?? false,
        ];

        if ($fullDetails) {
            $data['description'] = $product->description;
            $data['brand'] = $product->brand;
            $data['model'] = $product->model;
            $data['warranty'] = $product->warranty;
            $data['attributes'] = $this->parseJsonField($product->attributes);
            $data['views'] = $product->views_count ?? 0;
            $data['created_at'] = $product->created_at ? $product->created_at->toISOString() : null;
        }

        return $data;
    }

    /**
     * Formater une boutique pour la réponse JSON
     */
    private function formatStore($store, $fullDetails = false)
    {
        $data = [
            'id' => $store->id,
            'name' => $store->name,
            'slug' => $store->slug,
            'logo' => $store->logo ? asset('storage/' . $store->logo) : null,
            'banner' => $store->banner ? asset('storage/' . $store->banner) : null,
            'is_verified' => $store->is_verified,
            'is_official' => $store->is_official,
            'rating' => $store->rating ? (float) $store->rating : 0,
            'reviews_count' => $store->reviews_count ?? 0,
            'total_products' => $store->total_products ?? 0,
        ];

        if ($fullDetails) {
            $data['description'] = $store->description;
            $data['phone'] = $store->phone;
            $data['email'] = $store->email;
            $data['address'] = $store->address;
            $data['city'] = $store->city;
            $data['social_links'] = $this->parseJsonField($store->social_links);
            $data['total_sales'] = $store->total_sales ?? 0;
            $data['total_orders'] = $store->total_orders ?? 0;
            
            // Catégorie et sous-catégorie
            if ($store->category) {
                $data['category'] = [
                    'id' => $store->category->id,
                    'name' => $store->category->name,
                    'slug' => $store->category->slug,
                ];
            }
            if ($store->subcategory) {
                $data['subcategory'] = [
                    'id' => $store->subcategory->id,
                    'name' => $store->subcategory->name,
                    'slug' => $store->subcategory->slug,
                ];
            }
        }

        return $data;
    }

    /**
     * Parser un champ JSON qui peut être une chaîne ou déjà un tableau
     */
    private function parseJsonField($value)
    {
        if (empty($value)) {
            return [];
        }

        // Si c'est déjà un tableau, le retourner tel quel
        if (is_array($value)) {
            return $value;
        }

        // Si c'est une chaîne JSON, la décoder
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        return [];
    }

    /**
     * Helper pour obtenir l'URL correcte d'une image de bannière
     * Gère les cas où l'image est dans public/images/ ou storage/
     */
    private function getBannerImageUrl(?string $imagePath): ?string
    {
        if (!$imagePath) {
            return null;
        }

        // Si l'image est déjà dans le dossier public/images, retourner directement
        if (strpos($imagePath, 'images/') === 0) {
            return asset($imagePath);
        }

        // Sinon, c'est dans le storage (via lien symbolique)
        return asset('storage/' . ltrim($imagePath, '/'));
    }
}

