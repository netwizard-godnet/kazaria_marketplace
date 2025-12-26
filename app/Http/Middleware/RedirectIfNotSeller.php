<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotSeller
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
            $session = app('session');
            if (!$session->isStarted()) {
                $session->start();
            }
            $request->setLaravelSession($session);
        }

        // Vérifier si l'utilisateur est connecté avec le guard web
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // Vérifier si l'utilisateur est un vendeur
        if (!Auth::guard('web')->user()->is_seller) {
            return redirect()->route('accueil')->with('error', 'Vous devez être vendeur pour accéder à cette page.');
        }

        return $next($request);
    }
}
