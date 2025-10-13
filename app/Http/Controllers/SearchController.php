<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Obtenir des suggestions de recherche basées sur la requête
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = [];

        // 1. Rechercher des produits correspondants
        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->where('is_active', true)
            ->with(['categories', 'category'])
            ->limit(5)
            ->get(['id', 'name', 'slug', 'category_id']);

        foreach ($products as $product) {
            // Utiliser la catégorie principale ou la première catégorie
            $primaryCategory = $product->primaryCategory() ?? $product->categories->first();
            $categoryName = $primaryCategory ? $primaryCategory->name : ($product->category->name ?? '');
            
            $suggestions[] = [
                'type' => 'product',
                'text' => $product->name,
                'url' => route('product-page', $product->slug),
                'category' => $categoryName,
                'icon' => 'fa-solid fa-box'
            ];
        }

        // 2. Rechercher des catégories correspondantes
        $categories = Category::where('name', 'LIKE', "%{$query}%")
            ->where('is_active', true)
            ->limit(3)
            ->get(['id', 'name', 'slug', 'icon']);

        foreach ($categories as $category) {
            $suggestions[] = [
                'type' => 'category',
                'text' => $category->name,
                'url' => route('categorie', $category->slug),
                'category' => 'Catégorie',
                'icon' => $category->icon ?: 'fa-solid fa-folder'
            ];
        }

        // 3. Rechercher des sous-catégories correspondantes
        $subcategories = Subcategory::where('name', 'LIKE', "%{$query}%")
            ->where('is_active', true)
            ->with('category:id,name,slug')
            ->limit(3)
            ->get(['id', 'name', 'slug', 'icon', 'category_id']);

        foreach ($subcategories as $subcategory) {
            $suggestions[] = [
                'type' => 'subcategory',
                'text' => $subcategory->name,
                'url' => route('categorie', $subcategory->category->slug),
                'category' => $subcategory->category->name,
                'icon' => $subcategory->icon ?: 'fa-solid fa-tag'
            ];
        }

        // 4. Rechercher des marques populaires
        $brands = Product::where('brand', 'LIKE', "%{$query}%")
            ->where('is_active', true)
            ->distinct()
            ->limit(3)
            ->pluck('brand')
            ->filter()
            ->values();

        foreach ($brands as $brand) {
            $suggestions[] = [
                'type' => 'brand',
                'text' => $brand,
                'url' => route('search_product', ['q' => $brand]),
                'category' => 'Marque',
                'icon' => 'fa-solid fa-award'
            ];
        }

        // 5. Suggestions populaires basées sur des mots-clés
        $popularKeywords = [
            'pc portable', 'smartphone', 'tablette', 'télévision', 'réfrigérateur',
            'machine à laver', 'ordinateur', 'gaming', 'iphone', 'samsung',
            'laptop', 'écran', 'casque', 'souris', 'clavier'
        ];

        foreach ($popularKeywords as $keyword) {
            if (stripos($keyword, $query) !== false && !in_array($keyword, array_column($suggestions, 'text'))) {
                $suggestions[] = [
                    'type' => 'keyword',
                    'text' => $keyword,
                    'url' => route('search_product', ['q' => $keyword]),
                    'category' => 'Recherche populaire',
                    'icon' => 'fa-solid fa-fire'
                ];
            }
        }

        // Limiter le nombre total de suggestions
        $suggestions = array_slice($suggestions, 0, 10);

        return response()->json($suggestions);
    }
}
