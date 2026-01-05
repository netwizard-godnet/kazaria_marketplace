<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // S'assurer que la session est démarrée
        if (!$request->hasSession() || !session()->isStarted()) {
            $session = app('session.store');
            // ⚠️ IMPORTANT : Lire l'ID de session depuis les cookies AVANT de démarrer
            // Sinon, une nouvelle session sera créée et l'utilisateur sera déconnecté
            $sessionId = $request->cookies->get($session->getName());
            if ($sessionId) {
                $session->setId($sessionId);
            }
            if (!$session->isStarted()) {
                $session->start();
            }
            $request->setLaravelSession($session);
        }

        // Vérifier d'abord l'authentification par session web
        if (auth()->guard('web')->check()) {
            return $next($request);
        }

        // Vérifier si l'utilisateur est connecté via Sanctum
        if (auth('sanctum')->check()) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a un token Sanctum valide
        $token = $request->bearerToken() ?? $request->cookie('auth_token') ?? session('auth_token') ?? $request->query('token');
        if ($token) {
            try {
                // Vérifier le token avec Sanctum
                $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($personalAccessToken && (!$personalAccessToken->expires_at || !$personalAccessToken->expires_at->isPast())) {
                    $user = $personalAccessToken->tokenable;
                    if ($user) {
                        // Connecter l'utilisateur via session pour les routes web
                        auth()->guard('web')->login($user);
                        return $next($request);
                    }
                }
            } catch (\Exception $e) {
                // Token invalide, continuer vers la redirection
            }
        }

        // Rediriger vers la page de connexion
        return redirect()->route('login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
    }
}
