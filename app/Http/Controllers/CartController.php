<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Obtenir l'ID utilisateur ou session (WEB - Sessions)
     */
    private function getUserOrSession(Request $request)
    {
        // Pour les pages web, utiliser l'authentification par session
        if (auth()->check()) {
            return ['user_id' => auth()->user()->id, 'session_id' => null];
        }
        
        // Pour les invités, utiliser un ID de session depuis le header
        $sessionId = $request->header('X-Session-ID');
        
        if (!$sessionId) {
            // Générer un nouvel ID si non fourni
            $sessionId = uniqid('guest_', true);
        }
        
        return ['user_id' => null, 'session_id' => $sessionId];
    }

    /**
     * Obtenir l'ID utilisateur ou session (API - Tokens)
     */
    private function getUserOrSessionApi(Request $request)
    {
        // Pour les API, utiliser l'authentification par token
        $token = $request->bearerToken();
        
        if ($token) {
            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($personalAccessToken) {
                return ['user_id' => $personalAccessToken->tokenable->id, 'session_id' => null];
            }
        }
        
        // Pour les invités, utiliser un ID de session depuis le header
        $sessionId = $request->header('X-Session-ID');
        
        if (!$sessionId) {
            // Générer un nouvel ID si non fourni
            $sessionId = uniqid('guest_', true);
        }
        
        return ['user_id' => null, 'session_id' => $sessionId];
    }

    /**
     * Afficher la page du panier
     */
    public function index(Request $request)
    {
        // Pour la page web, utiliser une approche différente
        // On va charger le panier côté client avec JavaScript
        $cartItems = collect([]); // Collection vide par défaut
        $total = 0;
        
        // Récupérer les paramètres de livraison depuis la base de données
        $shippingCostSetting = \App\Models\Setting::where('key', 'shipping_cost')->first();
        $freeThresholdSetting = \App\Models\Setting::where('key', 'free_shipping_threshold')->first();
        $currencySymbolSetting = \App\Models\Setting::where('key', 'currency_symbol')->first();
        $minOrderQuantitySetting = \App\Models\Setting::where('key', 'min_order_quantity')->first();
        
        $shippingSettings = [
            'min_order_quantity' => $minOrderQuantitySetting ? (int) $minOrderQuantitySetting->value : 1,
            'currency_symbol' => $currencySymbolSetting ? $currencySymbolSetting->value : 'FCFA',
            'shipping_cost' => $shippingCostSetting ? (float) $shippingCostSetting->value : 0,
            'free_shipping_threshold' => $freeThresholdSetting ? (float) $freeThresholdSetting->value : 0
        ];
        
        // Debug: Log des paramètres récupérés
        \Log::info('Paramètres de livraison récupérés:', $shippingSettings);
        \Log::info('Vérification des clés:', array_keys($shippingSettings));
        \Log::info('shipping_cost existe:', ['exists' => isset($shippingSettings['shipping_cost']) ? 'OUI' : 'NON']);
        
        // Vérifications supplémentaires
        \Log::info('$shippingSettings existe:', ['exists' => isset($shippingSettings) ? 'OUI' : 'NON']);
        \Log::info('$shippingSettings est un array:', ['is_array' => is_array($shippingSettings) ? 'OUI' : 'NON']);
        \Log::info('Type de $shippingSettings:', ['type' => gettype($shippingSettings)]);
        \Log::info('Contenu de $shippingSettings:', ['content' => $shippingSettings]);
        
        return view('cart', compact('cartItems', 'total', 'shippingSettings'));
    }

    /**
     * Ajouter un produit au panier (API - Tokens)
     */
    public function addApi(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:100',
            'attributes' => 'array'
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;
        // Enforce min order quantity from settings
        $minQty = \App\Models\Setting::get('min_order_quantity', 1);
        if ($quantity < (int)$minQty) {
            return response()->json([
                'success' => false,
                'message' => 'Quantité minimale de commande: ' . (int)$minQty,
            ], 422);
        }
        $identifier = $this->getUserOrSessionApi($request);

        // Vérifier si le produit est déjà dans le panier avec les mêmes attributs
        $attributes = $request->attributes ?? [];
        $existingItem = CartItem::where('product_id', $product->id)
            ->where('user_id', $identifier['user_id'])
            ->where('session_id', $identifier['session_id'])
            ->where('attributes', json_encode($attributes))
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
        } else {
            CartItem::create([
                'product_id' => $product->id,
                'user_id' => $identifier['user_id'],
                'session_id' => $identifier['session_id'],
                'quantity' => $quantity,
                'attributes' => $attributes,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produit ajouté au panier',
            'cart_count' => CartItem::getCartCount($identifier['user_id'], $identifier['session_id'])
        ]);
    }

    /**
     * Obtenir le contenu du panier (API - Tokens)
     */
    public function getCartApi(Request $request)
    {
        $identifier = $this->getUserOrSessionApi($request);
        $cartItems = CartItem::getCartItems($identifier['user_id'], $identifier['session_id']);
        $total = CartItem::getCartTotal($identifier['user_id'], $identifier['session_id']);

        return response()->json([
            'success' => true,
            'cart_items' => $cartItems,
            'total' => $total,
            'count' => $cartItems->count()
        ]);
    }

    /**
     * Ajouter un produit au panier
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:100',
            'attributes' => 'array'
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;
        // Enforce min order quantity from settings
        $minQty = \App\Models\Setting::get('min_order_quantity', 1);
        if ($quantity < (int)$minQty) {
            return response()->json([
                'success' => false,
                'message' => 'Quantité minimale de commande: ' . (int)$minQty,
            ], 422);
        }
        $identifier = $this->getUserOrSession($request);

        // Vérifier si le produit est déjà dans le panier avec les mêmes attributs
        $attributes = $request->attributes ?? [];
        $cartItem = CartItem::where('product_id', $product->id)
            ->where(function($query) use ($identifier) {
                if ($identifier['user_id']) {
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    $query->where('session_id', $identifier['session_id']);
                }
            })
            ->where('attributes', json_encode($attributes))
            ->first();

        if ($cartItem) {
            // Mettre à jour la quantité (et respecter le min)
            $cartItem->quantity += $quantity;
            if ($cartItem->quantity < (int)$minQty) {
                $cartItem->quantity = (int)$minQty;
            }
            $cartItem->save();
        } else {
            // Déterminer le prix à utiliser (prix promo si disponible)
            $priceToUse = ($product->old_price && $product->old_price < $product->price) 
                ? $product->old_price 
                : $product->price;
            
            // Créer un nouvel article
            CartItem::create([
                'user_id' => $identifier['user_id'],
                'session_id' => $identifier['session_id'],
                'product_id' => $product->id,
                'quantity' => max($quantity, (int)$minQty),
                'price' => $priceToUse,
                'attributes' => $attributes
            ]);
        }

        $cartCount = CartItem::getCartCount($identifier['user_id'], $identifier['session_id']);

        return response()->json([
            'success' => true,
            'message' => 'Produit ajouté au panier',
            'cart_count' => $cartCount
        ]);
    }

    /**
     * Mettre à jour la quantité d'un article
     */
    public function update(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer|exists:cart_items,id',
            'quantity' => 'required|integer|min:1|max:100'
        ]);

        $identifier = $this->getUserOrSession($request);
        $minQty = \App\Models\Setting::get('min_order_quantity', 1);
        
        $cartItem = CartItem::where('id', $request->item_id)
            ->where(function($query) use ($identifier) {
                if ($identifier['user_id']) {
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    $query->where('session_id', $identifier['session_id']);
                }
            })
            ->firstOrFail();

        if ((int)$request->quantity < (int)$minQty) {
            return response()->json([
                'success' => false,
                'message' => 'Quantité minimale de commande: ' . (int)$minQty,
            ], 422);
        }
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        $total = CartItem::getCartTotal($identifier['user_id'], $identifier['session_id']);

        return response()->json([
            'success' => true,
            'message' => 'Quantité mise à jour',
            'item_total' => $cartItem->price * $cartItem->quantity,
            'cart_total' => $total
        ]);
    }

    /**
     * Supprimer un article du panier
     */
    public function remove(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer|exists:cart_items,id'
        ]);
        
        $identifier = $this->getUserOrSession($request);
        
        $cartItem = CartItem::where('id', $request->item_id)
            ->where(function($query) use ($identifier) {
                if ($identifier['user_id']) {
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    $query->where('session_id', $identifier['session_id']);
                }
            })
            ->firstOrFail();

        $cartItem->delete();

        $cartCount = CartItem::getCartCount($identifier['user_id'], $identifier['session_id']);
        $total = CartItem::getCartTotal($identifier['user_id'], $identifier['session_id']);

        return response()->json([
            'success' => true,
            'message' => 'Produit retiré du panier',
            'cart_count' => $cartCount,
            'cart_total' => $total
        ]);
    }

    /**
     * Vider le panier
     */
    public function clear(Request $request)
    {
        $identifier = $this->getUserOrSession($request);
        
        CartItem::where(function($query) use ($identifier) {
            if ($identifier['user_id']) {
                $query->where('user_id', $identifier['user_id']);
            } else {
                $query->where('session_id', $identifier['session_id']);
            }
        })->delete();

        return response()->json([
            'success' => true,
            'message' => 'Panier vidé',
            'cart_count' => 0,
            'cart_total' => 0
        ]);
    }

    /**
     * Obtenir le contenu du panier (API)
     */
    public function getCart(Request $request)
    {
        $identifier = $this->getUserOrSession($request);
        $cartItems = CartItem::getCartItems($identifier['user_id'], $identifier['session_id']);
        $total = CartItem::getCartTotal($identifier['user_id'], $identifier['session_id']);
        $count = CartItem::getCartCount($identifier['user_id'], $identifier['session_id']);

        return response()->json([
            'success' => true,
            'items' => $cartItems,
            'total' => $total,
            'count' => $count
        ]);
    }

    /**
     * Ajouter/Retirer des favoris
     */
    public function toggleFavorite(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $identifier = $this->getUserOrSession($request);
        
        $favorite = Favorite::where('product_id', $request->product_id)
            ->where(function($query) use ($identifier) {
                if ($identifier['user_id']) {
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    $query->where('session_id', $identifier['session_id']);
                }
            })
            ->first();

        if ($favorite) {
            // Retirer des favoris
            $favorite->delete();
            $isFavorite = false;
            $message = 'Retiré des favoris';
        } else {
            // Ajouter aux favoris
            Favorite::create([
                'user_id' => $identifier['user_id'],
                'session_id' => $identifier['session_id'],
                'product_id' => $request->product_id
            ]);
            $isFavorite = true;
            $message = 'Ajouté aux favoris';
        }

        $favoritesCount = Favorite::getFavoritesCount($identifier['user_id'], $identifier['session_id']);

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorite' => $isFavorite,
            'favorites_count' => $favoritesCount
        ]);
    }

    /**
     * Obtenir la liste des favoris
     */
    public function getFavorites(Request $request)
    {
        $identifier = $this->getUserOrSession($request);
        $favorites = Favorite::getFavorites($identifier['user_id'], $identifier['session_id']);

        return response()->json([
            'success' => true,
            'favorites' => $favorites
        ]);
    }

}
