<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    /**
     * Générer un lien de partage pour un produit
     */
    public function getProductShareLink(Request $request, $productId)
    {
        try {
            $product = Product::findOrFail($productId);
            
            // Générer un lien de partage (URL courte ou lien direct)
            // Pour la démo, on utilise une URL simple avec l'ID du produit
            $shareUrl = url('/products/' . $product->id);
            
            // Texte de partage personnalisé
            $shareText = 'Découvrez ce produit : ' . $product->name;
            if ($product->store) {
                $shareText .= ' - ' . $product->store->name;
            }
            
            return response()->json([
                'success' => true,
                'share_url' => $shareUrl,
                'share_text' => $shareText,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du lien de partage: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Générer un lien de partage pour une boutique
     */
    public function getStoreShareLink(Request $request, $storeId)
    {
        try {
            $store = Store::findOrFail($storeId);
            
            // Générer un lien de partage
            $shareUrl = url('/stores/' . $store->id);
            
            // Texte de partage personnalisé
            $shareText = 'Découvrez la boutique ' . $store->name;
            
            return response()->json([
                'success' => true,
                'share_url' => $shareUrl,
                'share_text' => $shareText,
                'store' => [
                    'id' => $store->id,
                    'name' => $store->name,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du lien de partage: ' . $e->getMessage()
            ], 404);
        }
    }
}
