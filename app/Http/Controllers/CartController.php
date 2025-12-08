<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Obtenir l'ID utilisateur ou session (WEB - Sessions)
     */
    private function getUserOrSession(Request $request)
    {
        // Pour les pages web, utiliser l'authentification par session
        if (Auth::check()) {
            // Utilisateur connecté - utiliser l'ID utilisateur
            // Prioriser le header X-Session-ID pour la cohérence avec le frontend
            $sessionId = $request->header('X-Session-ID');
            if (!$sessionId && $request->hasSession()) {
                $sessionId = $request->session()->getId();
            }
            return ['user_id' => Auth::user()->id, 'session_id' => $sessionId];
        }
        
        // Pour les invités, prioriser le header X-Session-ID (celui utilisé par le frontend)
        // car c'est celui qui est stocké dans localStorage et utilisé lors de l'ajout au panier
        $sessionId = $request->header('X-Session-ID');
        
        // Si pas de header, essayer la session Laravel
        if (!$sessionId && $request->hasSession()) {
            $sessionId = $request->session()->getId();
        }
        
        // Si toujours pas de session_id, générer un nouvel ID (ne devrait pas arriver normalement)
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
        Log::info('Paramètres de livraison récupérés:', $shippingSettings);
        Log::info('Vérification des clés:', array_keys($shippingSettings));
        Log::info('shipping_cost existe:', ['exists' => isset($shippingSettings['shipping_cost']) ? 'OUI' : 'NON']);
        
        // Vérifications supplémentaires
        Log::info('$shippingSettings existe:', ['exists' => isset($shippingSettings) ? 'OUI' : 'NON']);
        Log::info('$shippingSettings est un array:', ['is_array' => is_array($shippingSettings) ? 'OUI' : 'NON']);
        Log::info('Type de $shippingSettings:', ['type' => gettype($shippingSettings)]);
        Log::info('Contenu de $shippingSettings:', ['content' => $shippingSettings]);
        
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
            // Si le prix est 0 ou null, mettre à jour avec le prix actuel du produit
            if (!$existingItem->price || $existingItem->price == 0) {
                $existingItem->price = $product->price;
                $existingItem->save();
            }
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
            
            // Déterminer le prix à utiliser (prix actuel du produit)
            // Utiliser le prix promo si disponible, sinon le prix normal
            $priceToUse = $product->price;
            if ($product->old_price && $product->old_price > $product->price) {
                // Si old_price est plus élevé, c'est probablement une promotion
                // Dans ce cas, utiliser le prix actuel (qui est le prix promo)
                $priceToUse = $product->price;
            }
            
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
        Log::info('=== AJOUT AU PANIER ===', [
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

            Log::info('Panier vidé', [
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
            Log::error('Erreur lors de la suppression du panier: ' . $e->getMessage());
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
     * Obtenir l'identifiant utilisateur (détecte automatiquement API ou Web)
     */
    private function getIdentifier(Request $request)
    {
        // D'abord vérifier si c'est une requête API avec token
        $token = $request->bearerToken();
        if ($token) {
            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($personalAccessToken) {
                // API avec token - utiliser uniquement user_id
                return ['user_id' => $personalAccessToken->tokenable->id, 'session_id' => null];
            }
        }
        
        // Sinon, utiliser la méthode web (sessions)
        return $this->getUserOrSession($request);
    }

    /**
     * Ajouter/Retirer des favoris
     */
    public function toggleFavorite(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $identifier = $this->getIdentifier($request);
        
        // Pour les utilisateurs authentifiés, chercher uniquement par user_id (peu importe la session)
        // Pour les invités, chercher par session_id
        if ($identifier['user_id']) {
        $favorite = Favorite::where('product_id', $request->product_id)
                ->where('user_id', $identifier['user_id'])
                ->first();
                } else {
            $favorite = Favorite::where('product_id', $request->product_id)
                ->where('session_id', $identifier['session_id'])
                ->whereNull('user_id')
            ->first();
        }

        if ($favorite) {
            // Retirer des favoris
            $favorite->delete();
            $isFavorite = false;
            $message = 'Retiré des favoris';
        } else {
            // Ajouter aux favoris
            // Pour les utilisateurs authentifiés, ne pas utiliser session_id
            Favorite::create([
                'user_id' => $identifier['user_id'],
                'session_id' => $identifier['user_id'] ? null : $identifier['session_id'],
                'product_id' => $request->product_id
            ]);
            $isFavorite = true;
            $message = 'Ajouté aux favoris';
        }

        // Compter les favoris selon le type d'utilisateur
        if ($identifier['user_id']) {
            $favoritesCount = Favorite::where('user_id', $identifier['user_id'])->count();
        } else {
            $favoritesCount = Favorite::where('session_id', $identifier['session_id'])
                ->whereNull('user_id')
                ->count();
        }

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
        $identifier = $this->getIdentifier($request);
        
        // Pour les utilisateurs authentifiés, récupérer tous les favoris de l'utilisateur
        // Pour les invités, récupérer uniquement ceux de la session
        if ($identifier['user_id']) {
            $favoritesQuery = Favorite::where('user_id', $identifier['user_id'])
                ->with('product');
        } else {
            $favoritesQuery = Favorite::where('session_id', $identifier['session_id'])
                ->whereNull('user_id')
                ->with('product');
        }
        
        $favorites = $favoritesQuery->get();
        
        // Filtrer les favoris qui ont un produit valide (non supprimé)
        $validFavorites = $favorites->filter(function($favorite) {
            return $favorite->product !== null;
        });
        
        \Log::info('Favoris récupérés:', [
            'total' => $favorites->count(),
            'valides' => $validFavorites->count(),
            'user_id' => $identifier['user_id'],
            'session_id' => $identifier['session_id']
        ]);
        
        // Format pour le web : tableau d'objets avec structure {product: {...}}
        $favoritesForWeb = $validFavorites->map(function($favorite) {
            $product = $favorite->product;
            // Créer un tableau avec tous les attributs nécessaires
            $productArray = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => (float) $product->price,
                'old_price' => $product->old_price ? (float) $product->old_price : null,
                'discount_percentage' => $product->discount_percentage,
                'image' => $product->image,
                'images' => $product->images,
                'rating' => $product->rating ? (float) $product->rating : 0,
                'reviews_count' => $product->reviews_count ?? 0,
                'stock' => $product->stock,
                'is_active' => $product->is_active,
                'store_id' => $product->store_id,
                'category_id' => $product->category_id,
            ];
            
            return [
                'id' => $favorite->id,
                'product_id' => $favorite->product_id,
                'product' => $productArray
            ];
        })->values()->toArray();
        
        \Log::info('Favoris formatés pour le web:', ['count' => count($favoritesForWeb)]);
        
        // Format pour le mobile : tableau direct de produits
        $favoritesForMobile = $validFavorites->map(function($favorite) {
            $product = $favorite->product;
            // Utiliser toArray() si disponible, sinon créer manuellement
            if (method_exists($product, 'toArray')) {
                return $product->toArray();
            }
            // Fallback : créer le tableau manuellement
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => (float) $product->price,
                'old_price' => $product->old_price ? (float) $product->old_price : null,
                'image' => $product->image,
                'images' => $product->images,
                'rating' => $product->rating ? (float) $product->rating : 0,
                'reviews_count' => $product->reviews_count ?? 0,
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'favorites' => $favoritesForWeb, // Format attendu par le web (avec structure favorite.product)
            'data' => $favoritesForMobile // Format attendu par le mobile (produits directs)
        ]);
    }

}
