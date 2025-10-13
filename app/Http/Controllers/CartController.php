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
     * Obtenir l'ID utilisateur ou session
     */
    private function getUserOrSession(Request $request)
    {
        // Vérifier si l'utilisateur est authentifié
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
        
        return view('cart', compact('cartItems', 'total'));
    }

    /**
     * Ajouter un produit au panier
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:100'
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;
        $identifier = $this->getUserOrSession($request);

        // Vérifier si le produit est déjà dans le panier
        $cartItem = CartItem::where('product_id', $product->id)
            ->where(function($query) use ($identifier) {
                if ($identifier['user_id']) {
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    $query->where('session_id', $identifier['session_id']);
                }
            })
            ->first();

        if ($cartItem) {
            // Mettre à jour la quantité
            $cartItem->quantity += $quantity;
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
                'quantity' => $quantity,
                'price' => $priceToUse
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
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100'
        ]);

        $identifier = $this->getUserOrSession($request);
        
        $cartItem = CartItem::where('id', $id)
            ->where(function($query) use ($identifier) {
                if ($identifier['user_id']) {
                    $query->where('user_id', $identifier['user_id']);
                } else {
                    $query->where('session_id', $identifier['session_id']);
                }
            })
            ->firstOrFail();

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
    public function remove(Request $request, $id)
    {
        $identifier = $this->getUserOrSession($request);
        
        $cartItem = CartItem::where('id', $id)
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
