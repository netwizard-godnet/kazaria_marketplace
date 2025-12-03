<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    const LOW_STOCK_THRESHOLD = 5; // Seuil pour les alertes de stock faible

    /**
     * Vérifie la disponibilité du stock pour les articles du panier.
     *
     * @param \Illuminate\Support\Collection $cartItems
     * @return array Tableau des erreurs de stock (produit => message)
     */
    public static function checkStockAvailability($cartItems): array
    {
        $errors = [];
        foreach ($cartItems as $cartItem) {
            $product = Product::find($cartItem->product_id);
            if (!$product || $product->stock < $cartItem->quantity) {
                $errors[$product->name ?? 'Produit inconnu'] = "Stock insuffisant pour {$product->name} (disponible: {$product->stock}, demandé: {$cartItem->quantity})";
            }
        }
        return $errors;
    }

    /**
     * Réserve le stock pour une commande.
     * Diminue le stock des produits.
     *
     * @param Order $order
     * @return bool
     */
    public static function reserveStock(Order $order): bool
    {
        try {
            // S'assurer que les orderItems sont chargés
            if (!$order->relationLoaded('orderItems')) {
                $order->load('orderItems');
            }
            
            // Si toujours vide, récupérer directement depuis la base
            if ($order->orderItems->isEmpty()) {
                $orderItems = \App\Models\OrderItem::where('order_id', $order->id)->get();
            } else {
                $orderItems = $order->orderItems;
            }
            
            foreach ($orderItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $oldStock = $product->stock;
                    
                    // Utiliser une mise à jour directe en base pour garantir la persistance
                    DB::table('products')
                        ->where('id', $product->id)
                        ->decrement('stock', $item->quantity);
                    
                    // Recharger le produit pour vérifier le nouveau stock
                    $product->refresh();
                    $newStock = $product->stock;
                    
                    Log::info("Stock réservé pour le produit {$product->name} (ID: {$product->id}). Quantité: {$item->quantity}. Ancien stock: {$oldStock}, Nouveau stock: {$newStock}");
                } else {
                    Log::warning("Produit introuvable pour OrderItem ID: {$item->id}, Product ID: {$item->product_id}");
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Erreur lors de la réservation du stock pour la commande {$order->id}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Libère le stock pour une commande annulée ou retournée.
     * Augmente le stock des produits.
     *
     * @param Order $order
     * @return bool
     */
    public static function releaseStock(Order $order): bool
    {
        try {
            foreach ($order->orderItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->quantity);
                    Log::info("Stock libéré pour le produit {$product->name} (ID: {$product->id}). Quantité: {$item->quantity}. Nouveau stock: {$product->stock}");
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Erreur lors de la libération du stock pour la commande {$order->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Confirme la vente (le stock est déjà réservé, cette méthode ne fait rien d'autre que de logguer).
     *
     * @param Order $order
     * @return bool
     */
    public static function confirmSale(Order $order): bool
    {
        Log::info("Vente confirmée pour la commande {$order->id}. Le stock a été définitivement déduit.");
        return true;
    }

    /**
     * Récupère les produits en rupture de stock pour une boutique donnée.
     *
     * @param int $storeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getOutOfStockProducts(int $storeId)
    {
        return Product::where('store_id', $storeId)
                    ->where('stock', '<=', 0)
                    ->get();
    }

    /**
     * Récupère les produits avec un stock faible pour une boutique donnée.
     *
     * @param int $storeId
     * @param int $threshold
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLowStockProducts(int $storeId, int $threshold = self::LOW_STOCK_THRESHOLD)
    {
        return Product::where('store_id', $storeId)
                    ->where('stock', '>', 0)
                    ->where('stock', '<=', $threshold)
                    ->get();
    }

    /**
     * Récupère le statut du stock d'un produit.
     *
     * @param Product $product
     * @return string
     */
    public static function getStockStatus(Product $product): string
    {
        if ($product->stock <= 0) {
            return 'out_of_stock';
        } elseif ($product->stock <= self::LOW_STOCK_THRESHOLD) {
            return 'low_stock';
        }
        return 'in_stock';
    }
}
