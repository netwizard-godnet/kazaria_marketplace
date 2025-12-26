<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
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
        if (!auth()->guard('web')->check()) {
            // Rediriger vers la page de connexion admin
            return redirect()->route('admin.login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        $user = auth()->guard('web')->user();
        
        // Charger la relation role
        $user->load('role');

        // Vérifier si l'utilisateur est admin OU a un rôle admin actif
        if (!$user->is_admin && (!$user->role_id || !$user->role || !$user->role->is_active)) {
            abort(403, 'Accès refusé. Vous devez être administrateur ou avoir un rôle admin actif pour accéder à cette page.');
        }

        return $next($request);
    }
}