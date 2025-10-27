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
        // Vérifier d'abord l'authentification par session (web)
        if (Auth::check()) {
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
                    Auth::login($user);
                    return $next($request);
                }
            } catch (\Exception $e) {
                // Token invalide, continuer vers la redirection
            }
        }

        // Aucune authentification valide trouvée, rediriger vers la page de connexion
        return redirect()->route('login');
    }
}