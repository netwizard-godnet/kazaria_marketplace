<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? ['web'] : $guards;

        // Vérifier l'authentification avec chaque guard
        foreach ($guards as $guard) {
            // Utiliser le guard explicite pour éviter les problèmes de session
            if (Auth::guard($guard)->check()) {
                return $next($request);
            }
        }

        // Si l'utilisateur n'est pas connecté, vérifier si c'est une requête AJAX/API
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        // Pour les requêtes web, rediriger vers la page de connexion
        return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
    }
}
