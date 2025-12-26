<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
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
            abort(403, 'Vous devez être connecté pour accéder à cette ressource.');
        }

        $user = auth()->guard('web')->user();
        
        // Charger la relation role avec ses permissions
        $user->load('role.permissions');

        // Si l'utilisateur est un super admin, lui donner accès à tout
        if ($user->is_admin) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a un rôle avec la permission
        if ($user->role && $user->role->hasPermission($permission)) {
            return $next($request);
        }

        // Accès refusé
        abort(403, 'Vous n\'avez pas la permission d\'accéder à cette ressource.');
    }
}
