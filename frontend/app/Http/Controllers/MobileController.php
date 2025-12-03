<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Store;
use App\Models\Banner;
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
            // Catégories populaires
            $categories = Category::query()
                ->leftJoin('products', 'categories.id', '=', 'products.category_id')
                ->where('categories.is_active', true)
                ->selectRaw('categories.*, COALESCE(SUM(products.views), 0) as total_views')
                ->groupBy('categories.id')
                ->orderBy('total_views', 'desc')
                ->take(7)
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'image' => $category->image ? asset('storage/' . $category->image) : null,
                        'icon' => $category->icon,
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

            // Bannières
            $banners = Banner::where('is_active', true)
                ->orderBy('order', 'asc')
                ->get()
                ->map(function ($banner) {
                    return [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'image' => $banner->image ? asset('storage/' . $banner->image) : null,
                        'link' => $banner->link,
                        'type' => $banner->type,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $categories,
                    'featured_products' => $featuredProducts,
                    'trending_products' => $trendingProducts,
                    'new_products' => $newProducts,
                    'best_offers' => $bestOffers,
                    'banners' => $banners,
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
                        'subcategories' => $category->subcategories->map(function ($subcategory) {
                            return [
                                'id' => $subcategory->id,
                                'name' => $subcategory->name,
                                'slug' => $subcategory->slug,
                                'image' => $subcategory->image ? asset('storage/' . $subcategory->image) : null,
                            ];
                        }),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $categories,
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
                'data' => $products->items()->map(function ($product) {
                    return $this->formatProduct($product);
                }),
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
            $banners = Banner::where('is_active', true)
                ->orderBy('order', 'asc')
                ->get()
                ->map(function ($banner) {
                    return [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'image' => $banner->image ? asset('storage/' . $banner->image) : null,
                        'link' => $banner->link,
                        'type' => $banner->type,
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
            $stores = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $stores->items()->map(function ($store) {
                    return $this->formatStore($store);
                }),
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
     * Récupérer les détails d'une boutique pour mobile
     */
    public function getStoreDetails($id, Request $request)
    {
        try {
            $store = Store::findOrFail($id);

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
                'data' => [
                    'store' => $this->formatStore($store, true),
                    'products' => $products,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Boutique introuvable: ' . $e->getMessage(),
            ], 404);
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
            'images' => $product->images ? json_decode($product->images, true) : [],
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
            $data['attributes'] = $product->attributes ? json_decode($product->attributes, true) : [];
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
            $data['social_links'] = $store->social_links ? json_decode($store->social_links, true) : [];
            $data['total_sales'] = $store->total_sales ?? 0;
            $data['total_orders'] = $store->total_orders ?? 0;
        }

        return $data;
    }
}

