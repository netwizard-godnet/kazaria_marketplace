<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HybridAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // S'assurer que la session est démarrée pour les requêtes API depuis le frontend
        // Cela permet de lire les cookies de session même si EnsureFrontendRequestsAreStateful
        // n'a pas démarré la session (par exemple si le referer n'est pas configuré)
        if ($request->is('api/*')) {
            // Vérifier si la session existe mais n'est pas démarrée
            if (!$request->hasSession() || !session()->isStarted()) {
                // Démarrer la session manuellement pour lire les cookies
                // Utiliser session.store au lieu de session (correct)
                $session = app('session.store');
                if (!$session->isStarted()) {
                    $session->start();
                }
                // Attacher la session à la requête
                $request->setLaravelSession($session);
            }
        }
        
        // Vérifier d'abord l'authentification par session (web)
        // Utiliser le guard web explicitement
        if (Auth::guard('web')->check()) {
            return $next($request);
        }

        // Si pas de session, vérifier l'authentification par token (API)
        $token = $request->bearerToken() ?? $request->cookie('auth_token') ?? session('auth_token') ?? $request->query('token');
        
        if ($token) {
            try {
                // Vérifier le token avec Sanctum
                $user = \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable;
                
                if ($user) {
                    // Connecter l'utilisateur via session pour les routes web
                    Auth::guard('web')->login($user);
                    return $next($request);
                }
            } catch (\Exception $e) {
                // Token invalide, continuer vers la redirection
                \Log::warning('Token invalide dans HybridAuthMiddleware: ' . $e->getMessage());
            }
        }

        // Aucune authentification valide trouvée
        // Pour les requêtes API (JSON), retourner une réponse JSON 401
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        // Pour les requêtes web, rediriger vers la page de connexion
        return redirect()->route('login');
    }
}