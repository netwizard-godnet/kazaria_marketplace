<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PageVisit;

class TrackPageVisits
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Ne tracker que les requêtes GET réussies (status 200)
        if ($request->isMethod('GET') && $response->getStatusCode() === 200) {
            // Ignorer les routes admin, API, et les assets
            $path = $request->path();
            
            if (!$this->shouldTrack($path)) {
                return $response;
            }
            
            // Extraire le nom de la page depuis la route
            $pageName = $this->getPageNameFromRoute($request);
            
            // Tracker la visite
            try {
                PageVisit::trackVisit($path, $pageName, $request);
            } catch (\Exception $e) {
                // En cas d'erreur, on continue sans tracker
                \Log::warning('Failed to track page visit: ' . $e->getMessage());
            }
        }
        
        return $response;
    }

    /**
     * Détermine si la page doit être trackée
     */
    private function shouldTrack($path)
    {
        // Ne pas tracker les routes admin
        if (str_starts_with($path, 'admin')) {
            return false;
        }
        
        // Ne pas tracker les routes API
        if (str_starts_with($path, 'api')) {
            return false;
        }
        
        // Ne pas tracker les assets
        if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path)) {
            return false;
        }
        
        // Ne pas tracker les routes de téléchargement ou d'upload
        if (str_contains($path, 'storage') || str_contains($path, 'download')) {
            return false;
        }
        
        return true;
    }

    /**
     * Extrait le nom de la page depuis la route
     */
    private function getPageNameFromRoute(Request $request)
    {
        $route = $request->route();
        
        if (!$route) {
            return null;
        }
        
        $routeName = $route->getName();
        
        // Mapper les noms de routes vers des noms lisibles
        $pageNames = [
            'accueil' => 'Page d\'accueil',
            'product-page' => 'Page produit',
            'category' => 'Page catégorie',
            'subcategory' => 'Page sous-catégorie',
            'products.index' => 'Liste des produits',
            'products.search' => 'Recherche de produits',
            'cart' => 'Panier',
            'checkout' => 'Paiement',
            'profile' => 'Profil',
            'orders' => 'Commandes',
            'stores.index' => 'Liste des boutiques',
            'stores.show' => 'Boutique',
        ];
        
        return $pageNames[$routeName] ?? null;
    }
}

