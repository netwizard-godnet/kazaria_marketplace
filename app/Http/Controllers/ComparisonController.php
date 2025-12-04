<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductComparison;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComparisonController extends Controller
{
    /**
     * Obtenir l'identifiant utilisateur ou session
     */
    private function getUserOrSession(Request $request)
    {
        if (Auth::check()) {
            return ['user_id' => Auth::id(), 'session_id' => null];
        }
        
        $sessionId = $request->header('X-Session-ID') ?? session()->getId();
        return ['user_id' => null, 'session_id' => $sessionId];
    }

    /**
     * Créer une nouvelle comparaison
     */
    public function create(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:2|max:4',
            'product_ids.*' => 'required|exists:products,id',
            'name' => 'nullable|string|max:255',
        ]);

        $identifier = $this->getUserOrSession($request);

        $comparison = ProductComparison::create([
            'user_id' => $identifier['user_id'],
            'session_id' => $identifier['session_id'],
            'product_ids' => $request->product_ids,
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comparaison créée avec succès',
            'comparison' => $this->formatComparison($comparison)
        ]);
    }

    /**
     * Obtenir toutes les comparaisons
     */
    public function index(Request $request)
    {
        $identifier = $this->getUserOrSession($request);
        $comparisons = ProductComparison::getComparisons(
            $identifier['user_id'],
            $identifier['session_id']
        );

        return response()->json([
            'success' => true,
            'comparisons' => $comparisons->map(fn($c) => $this->formatComparison($c))
        ]);
    }

    /**
     * Obtenir une comparaison spécifique
     */
    public function show(Request $request, $id)
    {
        $identifier = $this->getUserOrSession($request);
        
        $comparison = ProductComparison::where('id', $id)
            ->where(function($query) use ($identifier) {
                if ($identifier['user_id']) {
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    $query->where('session_id', $identifier['session_id']);
                }
            })
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'comparison' => $this->formatComparison($comparison, true)
        ]);
    }

    /**
     * Comparer des produits (sans sauvegarder)
     */
    public function compare(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:2|max:4',
            'product_ids.*' => 'required|exists:products,id',
        ]);

        $products = Product::with(['category', 'subcategory', 'store'])
            ->whereIn('id', $request->product_ids)
            ->get();

        return response()->json([
            'success' => true,
            'products' => $products->map(fn($p) => $this->formatProduct($p))
        ]);
    }

    /**
     * Ajouter un produit à une comparaison existante
     */
    public function addProduct(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $identifier = $this->getUserOrSession($request);
        
        $comparison = ProductComparison::where('id', $id)
            ->where(function($query) use ($identifier) {
                if ($identifier['user_id']) {
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    $query->where('session_id', $identifier['session_id']);
                }
            })
            ->firstOrFail();

        if (count($comparison->product_ids ?? []) >= 4) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum 4 produits par comparaison'
            ], 400);
        }

        $comparison->addProduct($request->product_id);

        return response()->json([
            'success' => true,
            'message' => 'Produit ajouté à la comparaison',
            'comparison' => $this->formatComparison($comparison)
        ]);
    }

    /**
     * Retirer un produit d'une comparaison
     */
    public function removeProduct(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        $identifier = $this->getUserOrSession($request);
        
        $comparison = ProductComparison::where('id', $id)
            ->where(function($query) use ($identifier) {
                if ($identifier['user_id']) {
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    $query->where('session_id', $identifier['session_id']);
                }
            })
            ->firstOrFail();

        $comparison->removeProduct($request->product_id);

        return response()->json([
            'success' => true,
            'message' => 'Produit retiré de la comparaison',
            'comparison' => $this->formatComparison($comparison)
        ]);
    }

    /**
     * Supprimer une comparaison
     */
    public function destroy(Request $request, $id)
    {
        $identifier = $this->getUserOrSession($request);
        
        $comparison = ProductComparison::where('id', $id)
            ->where(function($query) use ($identifier) {
                if ($identifier['user_id']) {
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    $query->where('session_id', $identifier['session_id']);
                }
            })
            ->firstOrFail();

        $comparison->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comparaison supprimée'
        ]);
    }

    /**
     * Formater une comparaison pour l'API
     */
    private function formatComparison($comparison, $includeProducts = true)
    {
        $data = [
            'id' => $comparison->id,
            'name' => $comparison->name,
            'product_ids' => $comparison->product_ids,
            'product_count' => count($comparison->product_ids ?? []),
            'created_at' => $comparison->created_at->toISOString(),
        ];

        if ($includeProducts) {
            $data['products'] = $comparison->products->map(fn($p) => $this->formatProduct($p));
        }

        return $data;
    }

    /**
     * Formater un produit pour l'API
     */
    private function formatProduct($product)
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'old_price' => $product->old_price,
            'discount_percentage' => $product->discount_percentage,
            'image' => $product->first_image_url,
            'images' => $product->images_urls,
            'rating' => $product->rating,
            'reviews_count' => $product->reviews_count,
            'stock' => $product->stock,
            'brand' => $product->brand,
            'model' => $product->model,
            'warranty' => $product->warranty,
            'attributes' => $product->attributes,
            'description' => $product->description,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'store' => $product->store ? [
                'id' => $product->store->id,
                'name' => $product->store->name,
                'slug' => $product->store->slug,
            ] : null,
        ];
    }
}
