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
        
        // Récupérer les catégories/sous-catégories configurées pour la page d'accueil
        $homepageCategories = \App\Helpers\SettingHelper::get('homepage_categories', '');
        $homepageSubcategories = \App\Helpers\SettingHelper::get('homepage_subcategories', '');
        
        $allTopCategories = collect();
        
        // Si des catégories/sous-catégories sont configurées, les utiliser
        if (!empty($homepageCategories) || !empty($homepageSubcategories)) {
            // Charger les catégories configurées
            if (!empty($homepageCategories)) {
                $categoryIds = array_map('trim', explode(',', $homepageCategories));
                $configuredCategories = Category::active()
                    ->whereIn('id', $categoryIds)
                    ->ordered()
                    ->get();
                
                foreach ($configuredCategories as $category) {
                    $allTopCategories->push([
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'image' => $category->image,
                        'views' => 0,
                        'type' => 'category',
                        'route' => route('categorie', $category->slug),
                        'order' => array_search($category->id, $categoryIds)
                    ]);
                }
            }
            
            // Charger les sous-catégories configurées
            if (!empty($homepageSubcategories)) {
                $subcategoryIds = array_map('trim', explode(',', $homepageSubcategories));
                $configuredSubcategories = \App\Models\Subcategory::active()
                    ->whereIn('id', $subcategoryIds)
                    ->with('category')
                    ->ordered()
                    ->get();
                
                foreach ($configuredSubcategories as $subcategory) {
                    $route = '#';
                    if ($subcategory->category) {
                        $route = route('categorie', $subcategory->category->slug) . '?subcategory=' . $subcategory->slug;
                    }
                    
                    $allTopCategories->push([
                        'id' => $subcategory->id,
                        'name' => $subcategory->name,
                        'slug' => $subcategory->slug,
                        'image' => $subcategory->image,
                        'views' => 0,
                        'type' => 'subcategory',
                        'route' => $route,
                        'order' => array_search($subcategory->id, $subcategoryIds) + 1000 // Pour garder l'ordre mais après les catégories
                    ]);
                }
            }
            
            // Trier par ordre de configuration et limiter à 7
            $categories = $allTopCategories->sortBy('order')->take(7)->values();
        } else {
            // Sinon, utiliser le système automatique basé sur les vues
            // Récupérer les catégories avec le nombre total de vues de leurs produits
            $categoriesWithViews = Category::query()
                ->leftJoin('products', 'categories.id', '=', 'products.category_id')
                ->where('categories.is_active', true)
                ->selectRaw('categories.*, COALESCE(SUM(products.views), 0) as total_views')
                ->groupBy('categories.id')
                ->orderBy('total_views', 'desc')
                ->take(7)
                ->get();
            
            // Récupérer les sous-catégories avec le nombre total de vues de leurs produits
            $subcategoriesWithViews = \App\Models\Subcategory::query()
                ->leftJoin('products', 'subcategories.id', '=', 'products.subcategory_id')
                ->where('subcategories.is_active', true)
                ->selectRaw('subcategories.*, COALESCE(SUM(products.views), 0) as total_views')
                ->groupBy('subcategories.id')
                ->orderBy('total_views', 'desc')
                ->take(7)
                ->get();
            
            // Combiner catégories et sous-catégories et trier par nombre de vues
            foreach ($categoriesWithViews as $category) {
                $allTopCategories->push([
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->image,
                    'views' => $category->total_views ?? 0,
                    'type' => 'category',
                    'route' => route('categorie', $category->slug)
                ]);
            }
            
            foreach ($subcategoriesWithViews as $subcategory) {
                // Utiliser la route de la catégorie parente avec le slug de la sous-catégorie
                $route = '#';
                if ($subcategory->category) {
                    $route = route('categorie', $subcategory->category->slug) . '?subcategory=' . $subcategory->slug;
                }
                
                $allTopCategories->push([
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'slug' => $subcategory->slug,
                    'image' => $subcategory->image,
                    'views' => $subcategory->total_views ?? 0,
                    'type' => 'subcategory',
                    'route' => $route
                ]);
            }
            
            // Prendre les 7 plus visitées (catégories + sous-catégories combinées)
            $top7 = $allTopCategories->sortByDesc('views')->take(7)->values();
            
            // Si on a moins de 7 résultats, compléter avec des catégories/sous-catégories actives
            if ($top7->count() < 7) {
                $needed = 7 - $top7->count();
                
                // Récupérer des catégories/sous-catégories qui ne sont pas déjà dans le top
                $existingIds = $top7->pluck('id')->toArray();
                
                $additionalCategories = Category::active()
                    ->whereNotIn('id', $existingIds)
                    ->take($needed)
                    ->get();
                
                foreach ($additionalCategories as $category) {
                    $top7->push([
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'image' => $category->image,
                        'views' => 0,
                        'type' => 'category',
                        'route' => route('categorie', $category->slug)
                    ]);
                }
                
                // Si on a encore besoin d'éléments, ajouter des sous-catégories
                if ($top7->count() < 7) {
                    $needed = 7 - $top7->count();
                    $existingIds = $top7->pluck('id')->toArray();
                    
                    $additionalSubcategories = \App\Models\Subcategory::active()
                        ->whereNotIn('id', $existingIds)
                        ->take($needed)
                        ->get();
                    
                    foreach ($additionalSubcategories as $subcategory) {
                        $route = '#';
                        if ($subcategory->category) {
                            $route = route('categorie', $subcategory->category->slug) . '?subcategory=' . $subcategory->slug;
                        }
                        
                        $top7->push([
                            'id' => $subcategory->id,
                            'name' => $subcategory->name,
                            'slug' => $subcategory->slug,
                            'image' => $subcategory->image,
                            'views' => 0,
                            'type' => 'subcategory',
                            'route' => $route
                        ]);
                    }
                }
            }
            
            $categories = $top7;
        }
        
        // Récupérer la durée du countdown pour les deals (en minutes, défaut: 60)
        $countdownDuration = \App\Helpers\SettingHelper::get('deals_countdown_duration', '60');
        
        // Calculer la date de fin du countdown de manière déterministe :
        // - Prendre le début du jour (minuit) comme référence fixe
        // - Ajouter la durée configurée depuis la base de données
        // - Si cette heure est déjà passée aujourd'hui, utiliser minuit du jour suivant + durée
        // Cela garantit que le compte à rebours reste identique toute la journée,
        // même après plusieurs actualisations, tout en respectant la configuration admin
        $todayStart = now()->startOfDay();
        $calculatedEndTime = $todayStart->copy()->addMinutes((int)$countdownDuration);
        
        // Si l'heure calculée est déjà passée aujourd'hui, utiliser le jour suivant
        if ($calculatedEndTime->isPast()) {
            $countdownEndTime = now()->addDay()->startOfDay()->addMinutes((int)$countdownDuration)->timestamp * 1000;
        } else {
            $countdownEndTime = $calculatedEndTime->timestamp * 1000;
        }
        
        // Récupérer la configuration des deals du jour
        $dealsCategories = \App\Helpers\SettingHelper::get('deals_categories', ''); // IDs de catégories séparées par des virgules
        $dealsSubcategories = \App\Helpers\SettingHelper::get('deals_subcategories', ''); // IDs de sous-catégories séparées par des virgules
        $dealsMinDiscount = \App\Helpers\SettingHelper::get('deals_min_discount', '10'); // Pourcentage minimum de promo
        $dealsMaxDiscount = \App\Helpers\SettingHelper::get('deals_max_discount', '25'); // Pourcentage maximum de promo
        
        // Récupérer les deals du jour avec filtres configurables
        $dealsQuery = Product::active()
            ->whereNotNull('old_price')
            ->inStock()
            ->where('discount_percentage', '>=', (int)$dealsMinDiscount)
            ->where('discount_percentage', '<=', (int)$dealsMaxDiscount);
        
        // Filtrer par catégories si configuré
        if (!empty($dealsCategories)) {
            $categoryIds = array_map('trim', explode(',', $dealsCategories));
            $dealsQuery->whereIn('category_id', $categoryIds);
        }
        
        // Filtrer par sous-catégories si configuré
        if (!empty($dealsSubcategories)) {
            $subcategoryIds = array_map('trim', explode(',', $dealsSubcategories));
            $dealsQuery->whereIn('subcategory_id', $subcategoryIds);
        }
        
        $dealsProducts = $dealsQuery->orderBy('discount_percentage', 'desc')
            ->take(16)
            ->get();
        
        // Réorganiser les produits pour éviter que les mêmes produits se suivent
        $dealsProducts = $this->reorganizeProducts($dealsProducts);
        
        // Récupérer les sections de catégories/sous-catégories configurées pour la page d'accueil
        $homepageCategorySections = \App\Helpers\SettingHelper::get('homepage_category_sections', '');
        $categorySections = collect();
        
        if (!empty($homepageCategorySections)) {
            // Si des catégories/sous-catégories sont configurées, les utiliser
            // Format attendu: "category:ID" ou "subcategory:ID" séparés par des virgules
            $items = array_map('trim', explode(',', $homepageCategorySections));
            
            // Préserver l'ordre de configuration
            foreach ($items as $item) {
                if (str_starts_with($item, 'category:')) {
                    // C'est une catégorie
                    $categoryId = (int) str_replace('category:', '', $item);
                    $category = Category::active()->where('id', $categoryId)->first();
                    
                    if ($category) {
                        $products = Product::active()
                            ->whereHas('category', function($query) use ($category) {
                                $query->where('id', $category->id)
                                      ->where('is_active', true);
                            })
                            ->inStock()
                            ->take(12)
                            ->get();
                        
                        if ($products->count() > 0) {
                            $categorySections->push([
                                'category' => $category,
                                'subcategory' => null,
                                'products' => $products,
                                'is_active' => true,
                                'type' => 'category'
                            ]);
                        }
                    }
                } elseif (str_starts_with($item, 'subcategory:')) {
                    // C'est une sous-catégorie
                    $subcategoryId = (int) str_replace('subcategory:', '', $item);
                    $subcategory = \App\Models\Subcategory::active()
                        ->with('category')
                        ->where('id', $subcategoryId)
                        ->first();
                    
                    if ($subcategory && $subcategory->category && $subcategory->category->is_active) {
                        $products = Product::active()
                            ->whereHas('subcategory', function($query) use ($subcategory) {
                                $query->where('id', $subcategory->id)
                                      ->where('is_active', true);
                            })
                            ->inStock()
                            ->take(12)
                            ->get();
                        
                        // Réorganiser les produits pour éviter que les mêmes produits se suivent
                        $products = $this->reorganizeProducts($products);
                        
                        if ($products->count() > 0) {
                            $categorySections->push([
                                'category' => $subcategory->category,
                                'subcategory' => $subcategory,
                                'products' => $products,
                                'is_active' => true,
                                'type' => 'subcategory'
                            ]);
                        }
                    }
                } elseif (is_numeric($item)) {
                    // Rétrocompatibilité: format ancien (juste l'ID, considéré comme catégorie)
                    $categoryId = (int) $item;
                    $category = Category::active()->where('id', $categoryId)->first();
                    
                    if ($category) {
                        $products = Product::active()
                            ->whereHas('category', function($query) use ($category) {
                                $query->where('id', $category->id)
                                      ->where('is_active', true);
                            })
                            ->inStock()
                            ->take(12)
                            ->get();
                        
                        // Réorganiser les produits pour éviter que les mêmes produits se suivent
                        $products = $this->reorganizeProducts($products);
                        
                        if ($products->count() > 0) {
                            $categorySections->push([
                                'category' => $category,
                                'subcategory' => null,
                                'products' => $products,
                                'is_active' => true,
                                'type' => 'category'
                            ]);
                        }
                    }
                }
            }
        } else {
            // Par défaut, utiliser les 4 catégories existantes (rétrocompatibilité)
            $defaultSlugs = [
                'telephones-et-tablettes',
                'tv-et-electronique',
                'electromenager',
                'ordinateurs-et-accessoires'
            ];
            
            foreach ($defaultSlugs as $slug) {
                $category = Category::where('slug', $slug)->first();
                if ($category && $category->is_active) {
                    $products = Product::active()
                        ->whereHas('category', function($query) use ($category) {
                            $query->where('id', $category->id)
                                  ->where('is_active', true);
                        })
                        ->inStock()
                        ->take(12)
                        ->get();
                    
                    // Réorganiser les produits pour éviter que les mêmes produits se suivent
                    $products = $this->reorganizeProducts($products);
                    
                    if ($products->count() > 0) {
                        $categorySections->push([
                            'category' => $category,
                            'subcategory' => null,
                            'products' => $products,
                            'is_active' => true,
                            'type' => 'category'
                        ]);
                    }
                }
            }
        }
        
        // Récupérer les produits tendance
        $trendingProducts = Product::active()
            ->trending()
            ->inStock()
            ->take(12)
            ->get();
        
        // Réorganiser les produits pour éviter que les mêmes produits se suivent
        $trendingProducts = $this->reorganizeProducts($trendingProducts);
        
        // Récupérer les 12 marques actives depuis la table brands
        $topBrands = \App\Models\Brand::active()
            ->ordered()
            ->take(12)
            ->get();
        
        return view('accueil', compact(
            'categories',
            'dealsProducts',
            'categorySections',
            'trendingProducts',
            'countdownEndTime',
            'topBrands'
        ));
    }

    /**
     * Réorganise les produits pour éviter que les mêmes produits se suivent
     * Compare les produits par leur nom, marque et modèle plutôt que par ID
     * 
     * @param \Illuminate\Support\Collection $products
     * @return \Illuminate\Support\Collection
     */
    private function reorganizeProducts($products)
    {
        if ($products->isEmpty()) {
            return $products;
        }

        $reorganized = collect();
        $remaining = $products->values();
        $lastProductSignature = null;

        // Fonction pour générer une signature unique d'un produit basée sur son nom et caractéristiques
        $getProductSignature = function($product) {
            $name = strtolower(trim($product->name ?? ''));
            $brand = strtolower(trim($product->brand ?? ''));
            $model = strtolower(trim($product->model ?? ''));
            
            // Créer une signature basée sur le nom, et optionnellement la marque et le modèle
            $signature = $name;
            if (!empty($brand)) {
                $signature .= '|' . $brand;
            }
            if (!empty($model)) {
                $signature .= '|' . $model;
            }
            
            return $signature;
        };

        while ($remaining->isNotEmpty()) {
            // Trouver le premier produit qui est différent du précédent
            $found = false;
            foreach ($remaining as $index => $product) {
                $currentSignature = $getProductSignature($product);
                
                if ($currentSignature !== $lastProductSignature) {
                    $reorganized->push($product);
                    $lastProductSignature = $currentSignature;
                    $remaining->forget($index);
                    $found = true;
                    break;
                }
            }

            // Si aucun produit différent n'a été trouvé, prendre le premier disponible
            // (cela peut arriver si tous les produits restants sont identiques)
            if (!$found && $remaining->isNotEmpty()) {
                $product = $remaining->first();
                $reorganized->push($product);
                $lastProductSignature = $getProductSignature($product);
                $remaining->shift();
            }
        }

        return $reorganized->values();
    }
}
