<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Démarrer la session si elle n'est pas démarrée
        if (!session()->isStarted()) {
            session()->start();
        }
        
        $response = $next($request);
        
        // Forcer la sauvegarde de la session après chaque requête
        if (session()->isStarted()) {
            // Vérifier si l'utilisateur est connecté et mettre à jour la session
            if (\Illuminate\Support\Facades\Auth::check()) {
                $user = \Illuminate\Support\Facades\Auth::user();
                // Mettre à jour le user_id dans la session
                session()->put('login_web_' . sha1('App\Models\User'), $user->id);
            }
            
            // Forcer la sauvegarde
            session()->save();
        }
        
        return $response;
    }
}
