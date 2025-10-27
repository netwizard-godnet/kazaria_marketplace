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
        // Vérifier si l'utilisateur est connecté via Sanctum
        if (auth('sanctum')->check()) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a un token valide dans la session
        $token = $request->cookie('auth_token') ?? session('auth_token');
        if ($token) {
            // Vérifier si le token est valide
            $user = \App\Models\User::where('remember_token', $token)->first();
            if ($user) {
                auth()->login($user);
                return $next($request);
            }
        }

        // Rediriger vers la page de connexion
        return redirect()->route('login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
    }
}
