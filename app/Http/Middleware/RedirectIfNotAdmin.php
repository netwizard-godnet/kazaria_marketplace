<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotAdmin
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

        // Vérifier si l'utilisateur est connecté avec le guard web
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // Vérifier si l'utilisateur est un administrateur
        if (!Auth::guard('web')->user()->is_admin) {
            return redirect()->route('accueil')->with('error', 'Vous devez être administrateur pour accéder à cette page.');
        }

        return $next($request);
    }
}
