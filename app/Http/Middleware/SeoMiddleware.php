<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SeoMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ajouter des headers SEO
        $response->headers->set('X-Robots-Tag', 'index, follow');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Optimiser le cache pour les pages statiques
        if ($this->isStaticPage($request)) {
            $response->headers->set('Cache-Control', 'public, max-age=3600');
        }

        // Optimiser le cache pour les pages dynamiques
        if ($this->isDynamicPage($request)) {
            $response->headers->set('Cache-Control', 'public, max-age=1800');
        }

        return $response;
    }

    /**
     * Vérifier si c'est une page statique
     */
    private function isStaticPage(Request $request): bool
    {
        $staticPages = [
            'contact',
            'aide-faq',
            'suivre-commande',
            'expedition-livraison',
            'politique-retour',
            'comment-commander',
            'agences-points-relais'
        ];

        foreach ($staticPages as $page) {
            if ($request->is($page)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifier si c'est une page dynamique
     */
    private function isDynamicPage(Request $request): bool
    {
        return $request->is('/') || 
               $request->is('categorie/*') || 
               $request->is('produit/*') || 
               $request->is('boutique/*');
    }
}
