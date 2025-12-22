<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour l'authentification API avec support des sessions web
 * Utilisé pour les routes API appelées depuis le frontend web
 * 
 * Ce middleware garantit que la session est démarrée même si EnsureFrontendRequestsAreStateful
 * ne l'a pas fait (par exemple si le referer ne correspond pas aux domaines configurés)
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
        // S'assurer que la session est démarrée pour lire les cookies de session
        // EnsureFrontendRequestsAreStateful devrait le faire, mais on s'assure ici au cas où
        if (!$request->hasSession()) {
            // Obtenir le store de session et l'attacher à la requête
            $session = app('session.store');
            $request->setLaravelSession($session);
        }
        
        // Démarrer la session si elle n'est pas déjà démarrée
        $session = $request->session();
        if (!$session->isStarted()) {
            // Lire l'ID de session depuis les cookies
            $sessionId = $request->cookies->get($session->getName());
            if ($sessionId) {
                // Si un cookie de session existe, l'utiliser
                $session->setId($sessionId);
            }
            // Démarrer la session
            $session->start();
        }
        
        // Vérifier l'authentification avec le guard web
        // Le guard web charge automatiquement l'utilisateur depuis la session
        if (!Auth::guard('web')->check()) {
            // Pour les requêtes API, retourner une réponse JSON 401
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié. Veuillez vous reconnecter.'
            ], 401);
        }

        return $next($request);
    }
}

