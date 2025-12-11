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
        
        // Récupérer les produits par catégorie (uniquement si les catégories sont actives)
        $phoneCategory = Category::where('slug', 'telephones-et-tablettes')->first();
        $phoneProducts = collect();
        $isPhoneCategoryActive = false;
        if ($phoneCategory && $phoneCategory->is_active) {
            $isPhoneCategoryActive = true;
            $phoneProducts = Product::active()
                ->whereHas('category', function($query) {
                    $query->where('slug', 'telephones-et-tablettes')
                          ->where('is_active', true);
                })
                ->inStock()
                ->take(12)
                ->get();
        }
            
        $tvCategory = Category::where('slug', 'tv-et-electronique')->first();
        $tvProducts = collect();
        $isTvCategoryActive = false;
        if ($tvCategory && $tvCategory->is_active) {
            $isTvCategoryActive = true;
            $tvProducts = Product::active()
                ->whereHas('category', function($query) {
                    $query->where('slug', 'tv-et-electronique')
                          ->where('is_active', true);
                })
                ->inStock()
                ->take(12)
                ->get();
        }
            
        $electroCategory = Category::where('slug', 'electromenager')->first();
        $electroProducts = collect();
        $isElectroCategoryActive = false;
        if ($electroCategory && $electroCategory->is_active) {
            $isElectroCategoryActive = true;
            $electroProducts = Product::active()
                ->whereHas('category', function($query) {
                    $query->where('slug', 'electromenager')
                          ->where('is_active', true);
                })
                ->inStock()
                ->take(12)
                ->get();
        }
            
        $computerCategory = Category::where('slug', 'ordinateurs-et-accessoires')->first();
        $computerProducts = collect();
        $isComputerCategoryActive = false;
        if ($computerCategory && $computerCategory->is_active) {
            $isComputerCategoryActive = true;
            $computerProducts = Product::active()
                ->whereHas('category', function($query) {
                    $query->where('slug', 'ordinateurs-et-accessoires')
                          ->where('is_active', true);
                })
                ->inStock()
                ->take(12)
                ->get();
        }
        
        // Récupérer les produits tendance
        $trendingProducts = Product::active()
            ->trending()
            ->inStock()
            ->take(12)
            ->get();
        
        // Récupérer les 12 marques actives depuis la table brands
        $topBrands = \App\Models\Brand::active()
            ->ordered()
            ->take(12)
            ->get();
        
        return view('accueil', compact(
            'categories',
            'dealsProducts',
            'phoneProducts',
            'tvProducts',
            'electroProducts',
            'computerProducts',
            'trendingProducts',
            'countdownEndTime',
            'topBrands',
            'isPhoneCategoryActive',
            'isTvCategoryActive',
            'isElectroCategoryActive',
            'isComputerCategoryActive'
        ));
    }
}
