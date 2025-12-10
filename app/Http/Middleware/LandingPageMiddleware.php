<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LandingPageMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Si la landing page est activée, rediriger toutes les routes vers la landing page
     * sauf les routes exclues (assets, admin, API, etc.)
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si la landing page est activée (depuis les settings admin ou config)
        $setting = \App\Models\Setting::where('key', 'landing_page_enabled')->first();
        
        if ($setting) {
            // Si le setting existe, utiliser sa valeur convertie correctement selon le type
            // Convertir "0" en false et "1" en true
            $value = $setting->value;
            if ($value === '0' || $value === 0 || $value === false || $value === 'false') {
                $landingPageEnabled = false;
            } elseif ($value === '1' || $value === 1 || $value === true || $value === 'true') {
                $landingPageEnabled = true;
            } else {
                $landingPageEnabled = (bool) $value;
            }
        } else {
            // Fallback sur la config si le setting n'existe pas encore
            $landingPageEnabled = config('app.landing_page_enabled', false);
        }
        
        // Ne rediriger que si la landing page est explicitement activée (true)
        if ($landingPageEnabled === true) {
            // Routes exclues de la redirection vers la landing page
            $excludedPaths = [
                'landing', // La route de la landing page elle-même
                'admin', // Routes admin
                'api', // Routes API
                'newsletter/subscribe', // Route newsletter pour le formulaire de la landing page
                'images', // Images et assets
                'storage', // Fichiers de stockage
                'css', // Fichiers CSS
                'js', // Fichiers JavaScript
                'fonts', // Polices
                'sitemap.xml', // Sitemap
                'up', // Health check
                'avatar', // Avatars
            ];

            // Vérifier si la route actuelle doit être exclue
            // D'abord vérifier par le nom de la route (plus fiable)
            if ($request->routeIs('landing')) {
                return $next($request);
            }
            
            $shouldExclude = false;
            $currentPath = $request->path();
            
            foreach ($excludedPaths as $path) {
                // Vérifier avec et sans slash initial
                if ($request->is($path) || 
                    $request->is('/' . $path) || 
                    $request->is($path . '/*') || 
                    $request->is('/' . $path . '/*') ||
                    $currentPath === $path ||
                    str_starts_with($currentPath, $path . '/')) {
                    $shouldExclude = true;
                    break;
                }
            }

            // Si la route n'est pas exclue et n'est pas déjà la landing page, rediriger
            if (!$shouldExclude && $currentPath !== 'landing') {
                // Utiliser une URL directe pour éviter les problèmes de chargement de route
                return redirect('/landing');
            }
        }

        return $next($request);
    }
}

