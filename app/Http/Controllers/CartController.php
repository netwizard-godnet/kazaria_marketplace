<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Obtenir l'ID utilisateur ou session (WEB - Sessions)
     * 
     * IMPORTANT : Il y a deux types de "session_id" différents :
     * - Session Laravel ($request->session()->getId()) : Pour authentification, CSRF, données de session
     * - Session invité (X-Session-ID header) : Pour panier/favoris des invités (stocké dans localStorage)
     * 
     * Pour les utilisateurs connectés : utiliser uniquement user_id (session_id = null)
     * Pour les invités : utiliser uniquement X-Session-ID (guest session)
     */
    private function getUserOrSession(Request $request)
    {
        // Pour les utilisateurs connectés : utiliser uniquement user_id
        // Le panier est identifié par user_id, pas par session_id
        if (auth()->check()) {
            return ['user_id' => auth()->user()->id, 'session_id' => null];
        }
        
        // Pour les invités : utiliser uniquement X-Session-ID (guest session)
        // C'est l'ID stocké dans localStorage côté client, pas la session Laravel
        $sessionId = $request->header('X-Session-ID');
        
        // Si pas de header, générer un nouvel ID invité
        // On ne doit PAS utiliser $request->session()->getId() car c'est la session Laravel,
        // pas la session invité pour le panier
        if (!$sessionId) {
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

        // Normaliser les attributs pour la comparaison
        // IMPORTANT: Utiliser input() pour récupérer les données du body JSON, pas attributes qui est pour les paramètres de route
        $attributes = $request->input('attributes', []);
        
        // Extraire la variation_id si elle existe
        $variationId = null;
        if (isset($attributes['variation_id'])) {
            $variationId = $attributes['variation_id'];
            unset($attributes['variation_id']); // Retirer de la liste des attributs
        }
        
        // Charger la variation si elle existe
        $variation = null;
        if ($variationId) {
            $variation = ProductVariation::where('id', $variationId)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->first();
        }
        
        if (empty($attributes) || (is_array($attributes) && count(array_filter($attributes)) === 0)) {
            $attributes = [];
        }
        
        // Normaliser les attributs en JSON de manière cohérente
        $attributesJson = json_encode($attributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Vérifier si le produit est déjà dans le panier avec les mêmes attributs et variation
        $existingItem = CartItem::where('product_id', $product->id)
            ->where('user_id', $identifier['user_id'])
            ->where('session_id', $identifier['session_id'])
            ->where('variation_id', $variationId)
            ->where(function($query) use ($attributesJson, $attributes) {
                // Si les attributs sont vides, chercher les entrées sans attributs ou avec attributs vides
                if (empty($attributes)) {
                    $query->where(function($q) use ($attributesJson) {
                        $q->where('attributes', $attributesJson)
                          ->orWhereNull('attributes')
                          ->orWhere('attributes', '[]')
                          ->orWhere('attributes', '{}')
                          ->orWhere('attributes', '');
                    });
                } else {
                    // Pour les attributs non vides, comparer avec JSON_EQUAL ou utiliser whereRaw
                    // Utiliser JSON_CONTAINS avec les deux sens pour vérifier l'égalité
                    $query->where(function($q) use ($attributesJson) {
                        $q->where('attributes', $attributesJson)
                          ->orWhereRaw('JSON_CONTAINS(attributes, ?) AND JSON_CONTAINS(?, attributes)', [$attributesJson, $attributesJson]);
                    });
                }
            })
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
        } else {
            // Déterminer le prix à utiliser
            $priceToUse = $product->price;
            
            // Si une variation existe, utiliser son prix
            if ($variation) {
                if ($variation->old_price && $variation->old_price > $variation->price) {
                    $priceToUse = $variation->price; // Prix promo
                } else {
                    $priceToUse = $variation->price;
                }
            } else {
                // Utiliser le prix du produit (prix promo si disponible)
                $priceToUse = ($product->old_price && $product->old_price < $product->price) 
                    ? $product->old_price 
                    : $product->price;
            }
            
            // S'assurer que les attributs sont toujours stockés comme un objet, même s'ils sont vides
            // Convertir en objet si c'est un tableau
            $attributesToStore = empty($attributes) ? (object)[] : (is_array($attributes) ? (object)$attributes : $attributes);
            
            CartItem::create([
                'product_id' => $product->id,
                'variation_id' => $variationId,
                'user_id' => $identifier['user_id'],
                'session_id' => $identifier['session_id'],
                'quantity' => $quantity,
                'price' => $priceToUse,
                'attributes' => $attributesToStore,
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

        // Normaliser les attributs pour la comparaison
        // IMPORTANT: Utiliser input() pour récupérer les données du body JSON, pas attributes qui est pour les paramètres de route
        $attributes = $request->input('attributes', []);
        
        // Extraire la variation_id si elle existe
        $variationId = null;
        if (isset($attributes['variation_id'])) {
            $variationId = $attributes['variation_id'];
            unset($attributes['variation_id']); // Retirer de la liste des attributs pour ne pas la stocker en double
        }
        
        // Charger la variation si elle existe
        $variation = null;
        if ($variationId) {
            $variation = ProductVariation::where('id', $variationId)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->first();
        }
        
        // Debug: Log des attributs reçus
        \Log::info('=== AJOUT AU PANIER ===', [
            'product_id' => $request->input('product_id'),
            'quantity' => $request->input('quantity'),
            'variation_id' => $variationId,
            'variation_found' => $variation ? true : false,
            'attributes_received' => $attributes,
        ]);
        
        if (empty($attributes) || (is_array($attributes) && count(array_filter($attributes)) === 0)) {
            $attributes = [];
        }
        
        // Normaliser les attributs en JSON de manière cohérente
        $attributesJson = json_encode($attributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Vérifier si le produit est déjà dans le panier avec les mêmes attributs et variation
        $cartItem = CartItem::where('product_id', $product->id)
            ->where(function($query) use ($identifier) {
                if ($identifier['user_id'] && $identifier['session_id']) {
                    // Utilisateur connecté avec session_id - chercher par les deux
                    $query->where('user_id', $identifier['user_id'])
                          ->where('session_id', $identifier['session_id']);
                } elseif ($identifier['user_id']) {
                    // Utilisateur connecté sans session_id - chercher par user_id seulement
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    // Utilisateur non connecté - chercher par session_id seulement
                    $query->where('session_id', $identifier['session_id']);
                }
            })
            ->where('variation_id', $variationId)
            ->where(function($query) use ($attributesJson, $attributes) {
                // Si les attributs sont vides, chercher les entrées sans attributs ou avec attributs vides
                if (empty($attributes)) {
                    $query->where(function($q) use ($attributesJson) {
                        $q->where('attributes', $attributesJson)
                          ->orWhereNull('attributes')
                          ->orWhere('attributes', '[]')
                          ->orWhere('attributes', '{}')
                          ->orWhere('attributes', '');
                    });
                } else {
                    // Pour les attributs non vides, comparer avec JSON_EQUAL ou utiliser whereRaw
                    // Utiliser JSON_CONTAINS avec les deux sens pour vérifier l'égalité
                    $query->where(function($q) use ($attributesJson) {
                        $q->where('attributes', $attributesJson)
                          ->orWhereRaw('JSON_CONTAINS(attributes, ?) AND JSON_CONTAINS(?, attributes)', [$attributesJson, $attributesJson]);
                    });
                }
            })
            ->first();

        if ($cartItem) {
            // Mettre à jour la quantité (et respecter le min)
            $cartItem->quantity += $quantity;
            if ($cartItem->quantity < (int)$minQty) {
                $cartItem->quantity = (int)$minQty;
            }
            $cartItem->save();
        } else {
            // Déterminer le prix à utiliser
            $priceToUse = $product->price;
            
            // Si une variation existe, utiliser son prix
            if ($variation) {
                // Utiliser le prix promo de la variation si disponible, sinon le prix normal
                if ($variation->old_price && $variation->old_price > $variation->price) {
                    $priceToUse = $variation->price; // Prix promo
                } else {
                    $priceToUse = $variation->price;
                }
            } else {
                // Utiliser le prix du produit (prix promo si disponible)
                $priceToUse = ($product->old_price && $product->old_price < $product->price) 
                    ? $product->old_price 
                    : $product->price;
            }
            
            // Créer un nouvel article
            // S'assurer que les attributs sont toujours un objet, jamais null
            // Convertir en objet si c'est un tableau
            $attributesToStore = empty($attributes) ? (object)[] : (is_array($attributes) ? (object)$attributes : $attributes);
            
            CartItem::create([
                'user_id' => $identifier['user_id'],
                'session_id' => $identifier['session_id'],
                'product_id' => $product->id,
                'variation_id' => $variationId,
                'quantity' => max($quantity, (int)$minQty),
                'price' => $priceToUse,
                'attributes' => $attributesToStore
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
        
        // Recherche plus flexible : si utilisateur connecté, chercher par user_id (priorité)
        $cartItem = CartItem::where('id', $request->item_id)
            ->where(function($query) use ($identifier) {
                if ($identifier['user_id']) {
                    // Utilisateur connecté - chercher par user_id (priorité absolue)
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    // Utilisateur non connecté - chercher par session_id seulement
                    $query->where('session_id', $identifier['session_id']);
                }
            })
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Article non trouvé dans votre panier. Il a peut-être déjà été retiré.'
            ], 404);
        }

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
        
        // Recherche plus flexible : si utilisateur connecté, chercher par user_id (priorité)
        // Accepte même si session_id ne correspond pas exactement
        $cartItem = CartItem::where('id', $request->item_id)
            ->where(function($query) use ($identifier) {
                if ($identifier['user_id']) {
                    // Utilisateur connecté - chercher par user_id (priorité absolue)
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    // Utilisateur non connecté - chercher par session_id seulement
                    $query->where('session_id', $identifier['session_id']);
                }
            })
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Article non trouvé dans votre panier. Il a peut-être déjà été retiré.'
            ], 404);
        }

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
        try {
            $identifier = $this->getUserOrSession($request);
            
            // Vérifier que nous avons au moins un identifiant
            if (!$identifier['user_id'] && !$identifier['session_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible d\'identifier le panier'
                ], 400);
            }
            
            // Logique plus flexible : si utilisateur connecté, chercher par user_id (priorité)
            // Accepte même si session_id ne correspond pas exactement
            $deleted = CartItem::where(function($query) use ($identifier) {
                if ($identifier['user_id']) {
                    // Utilisateur connecté - chercher par user_id (priorité absolue)
                    // Supprimer tous les items de cet utilisateur, peu importe le session_id
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    // Utilisateur non connecté - chercher par session_id seulement
                    $query->where('session_id', $identifier['session_id']);
                }
            })->delete();

            \Log::info('Panier vidé', [
                'user_id' => $identifier['user_id'],
                'session_id' => $identifier['session_id'],
                'deleted_count' => $deleted
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Panier vidé',
                'cart_count' => 0,
                'cart_total' => 0
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression du panier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du panier: ' . $e->getMessage()
            ], 500);
        }
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
                if ($identifier['user_id'] && $identifier['session_id']) {
                    // Utilisateur connecté avec session_id - chercher par les deux
                    $query->where('user_id', $identifier['user_id'])
                          ->where('session_id', $identifier['session_id']);
                } elseif ($identifier['user_id']) {
                    // Utilisateur connecté sans session_id - chercher par user_id seulement
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    // Utilisateur non connecté - chercher par session_id seulement
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
