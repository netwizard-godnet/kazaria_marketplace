<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ProductView;

class TrackProductViews
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Tracker les vues pour les routes de produits
        if ($request->route() && $request->route()->getName() === 'product-page') {
            $productId = $request->route('slug');
            
            // Récupérer l'ID du produit depuis le slug
            $product = \App\Models\Product::where('slug', $productId)->first();
            
            if ($product) {
                // trackView() gère à la fois l'enregistrement de la vue et l'incrémentation du compteur
                ProductView::trackView($product->id, $request);
            }
        }
        
        return $response;
    }
}
