<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour l'authentification API avec support des sessions web
 * Utilisé pour les routes API appelées depuis le frontend web
 */
class ApiWebAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // S'assurer que la session est démarrée pour les routes API depuis le frontend
        // Cela permet de lire les cookies de session même si le middleware 'web' 
        // n'a pas démarré la session correctement
        if (!$request->hasSession() || !session()->isStarted()) {
            // Démarrer la session manuellement pour lire les cookies
            $session = app('session');
            if (!$session->isStarted()) {
                $session->start();
            }
            // Attacher la session à la requête
            $request->setLaravelSession($session);
        }
        
        // Vérifier l'authentification avec le guard web
        if (!Auth::guard('web')->check()) {
            // Pour les requêtes API, retourner une réponse JSON 401
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        return $next($request);
    }
}

