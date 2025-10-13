<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class ClientAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est authentifié via token
        $token = $this->getTokenFromRequest($request);
        
        if (!$token) {
            return $this->handleUnauthenticated($request);
        }

        // Vérifier la validité du token
        $personalAccessToken = PersonalAccessToken::findToken($token);
        
        if (!$personalAccessToken || $personalAccessToken->expires_at && $personalAccessToken->expires_at->isPast()) {
            return $this->handleUnauthenticated($request);
        }

        // Ajouter l'utilisateur à la requête
        $request->setUserResolver(function () use ($personalAccessToken) {
            return $personalAccessToken->tokenable;
        });

        return $next($request);
    }

    /**
     * Récupérer le token depuis la requête
     */
    private function getTokenFromRequest(Request $request): ?string
    {
        // 1. Depuis l'en-tête Authorization
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        // 2. Depuis un cookie (si utilisé)
        $token = $request->cookie('auth_token');
        if ($token) {
            return $token;
        }

        // 3. Depuis un paramètre de requête (pour les tests)
        $token = $request->query('token');
        if ($token) {
            return $token;
        }

        return null;
    }

    /**
     * Gérer les requêtes non authentifiées
     */
    private function handleUnauthenticated(Request $request)
    {
        // Pour les requêtes AJAX ou API
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié',
                'redirect' => route('login')
            ], 401);
        }

        // Pour les requêtes web normales
        return redirect()->route('login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
    }
}