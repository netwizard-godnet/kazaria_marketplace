<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\PriceAlert;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    /**
     * Obtenir toutes les wishlists de l'utilisateur
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise'
            ], 401);
        }

        $wishlists = Wishlist::where('user_id', $user->id)
            ->withCount('products')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'wishlists' => $wishlists->map(fn($w) => $this->formatWishlist($w, false))
        ]);
    }

    /**
     * Créer une nouvelle wishlist
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise'
            ], 401);
        }

        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'is_public' => $request->boolean('is_public'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wishlist créée avec succès',
            'wishlist' => $this->formatWishlist($wishlist)
        ]);
    }

    /**
     * Obtenir une wishlist spécifique
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $wishlist = Wishlist::with('products')->findOrFail($id);

        // Vérifier les permissions
        if (!$wishlist->is_public && (!$user || $wishlist->user_id !== $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'wishlist' => $this->formatWishlist($wishlist, true)
        ]);
    }

    /**
     * Obtenir une wishlist par son token de partage
     */
    public function showByToken(Request $request, $token)
    {
        $wishlist = Wishlist::where('share_token', $token)
            ->where('is_public', true)
            ->with('products')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'wishlist' => $this->formatWishlist($wishlist, true)
        ]);
    }

    /**
     * Mettre à jour une wishlist
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $wishlist->update($request->only(['name', 'description', 'is_public']));

        return response()->json([
            'success' => true,
            'message' => 'Wishlist mise à jour',
            'wishlist' => $this->formatWishlist($wishlist)
        ]);
    }

    /**
     * Supprimer une wishlist
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist supprimée'
        ]);
    }

    /**
     * Ajouter un produit à une wishlist
     */
    public function addProduct(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'priority' => 'nullable|integer|min:0|max:10',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $wishlist->addProduct(
            $request->product_id,
            $request->priority ?? 0,
            $request->notes
        );

        return response()->json([
            'success' => true,
            'message' => 'Produit ajouté à la wishlist',
            'wishlist' => $this->formatWishlist($wishlist->fresh('products'))
        ]);
    }

    /**
     * Retirer un produit d'une wishlist
     */
    public function removeProduct(Request $request, $id, $productId)
    {
        $user = $request->user();
        
        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $wishlist->removeProduct($productId);

        return response()->json([
            'success' => true,
            'message' => 'Produit retiré de la wishlist'
        ]);
    }

    /**
     * Créer une alerte de prix
     */
    public function createPriceAlert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'target_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise'
            ], 401);
        }

        // Vérifier si une alerte existe déjà pour ce produit
        $existingAlert = PriceAlert::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->where('is_active', true)
            ->first();

        if ($existingAlert) {
            // Mettre à jour l'alerte existante
            $existingAlert->update(['target_price' => $request->target_price]);
            
            return response()->json([
                'success' => true,
                'message' => 'Alerte de prix mise à jour',
                'alert' => $this->formatPriceAlert($existingAlert)
            ]);
        }

        $alert = PriceAlert::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
            'target_price' => $request->target_price,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alerte de prix créée',
            'alert' => $this->formatPriceAlert($alert)
        ]);
    }

    /**
     * Obtenir toutes les alertes de prix
     */
    public function getPriceAlerts(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise'
            ], 401);
        }

        $alerts = PriceAlert::where('user_id', $user->id)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'alerts' => $alerts->map(fn($a) => $this->formatPriceAlert($a))
        ]);
    }

    /**
     * Supprimer une alerte de prix
     */
    public function deletePriceAlert(Request $request, $id)
    {
        $user = $request->user();
        
        $alert = PriceAlert::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $alert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alerte supprimée'
        ]);
    }

    /**
     * Formater une wishlist pour l'API
     */
    private function formatWishlist($wishlist, $includeProducts = true)
    {
        $data = [
            'id' => $wishlist->id,
            'name' => $wishlist->name,
            'description' => $wishlist->description,
            'is_public' => $wishlist->is_public,
            'share_token' => $wishlist->share_token,
            'products_count' => $wishlist->products_count ?? $wishlist->products()->count(),
            'created_at' => $wishlist->created_at->toISOString(),
        ];

        if ($includeProducts && $wishlist->relationLoaded('products')) {
            $data['products'] = $wishlist->products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'old_price' => $product->old_price,
                    'discount_percentage' => $product->discount_percentage,
                    'image' => $product->first_image_url,
                    'stock' => $product->stock,
                    'rating' => $product->rating,
                    'reviews_count' => $product->reviews_count,
                    'priority' => $product->pivot->priority ?? 0,
                    'notes' => $product->pivot->notes,
                ];
            });
        }

        return $data;
    }

    /**
     * Formater une alerte de prix pour l'API
     */
    private function formatPriceAlert($alert)
    {
        return [
            'id' => $alert->id,
            'product_id' => $alert->product_id,
            'target_price' => $alert->target_price,
            'is_active' => $alert->is_active,
            'notified_at' => $alert->notified_at?->toISOString(),
            'created_at' => $alert->created_at->toISOString(),
            'product' => $alert->product ? [
                'id' => $alert->product->id,
                'name' => $alert->product->name,
                'slug' => $alert->product->slug,
                'price' => $alert->product->price,
                'image' => $alert->product->first_image_url,
            ] : null,
        ];
    }
}
