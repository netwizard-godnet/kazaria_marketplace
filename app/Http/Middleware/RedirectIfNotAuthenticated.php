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

        // S'assurer que la session est démarrée pour vérifier l'authentification
        if (!$request->hasSession() || !session()->isStarted()) {
            // Démarrer la session si elle n'est pas démarrée
            $session = app('session');
            if (!$session->isStarted()) {
                $session->start();
            }
            $request->setLaravelSession($session);
        }

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $next($request);
            }
        }

        // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
        return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
    }
}
