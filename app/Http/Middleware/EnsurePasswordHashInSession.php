<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour s'assurer que password_hash_web est toujours stocké dans la session
 * si l'utilisateur est connecté. Cela évite les déconnexions intempestives.
 * 
 * Ce middleware doit être appliqué APRÈS StartSession et AuthenticateSession
 * pour garantir que password_hash_web est toujours présent.
 */
class EnsurePasswordHashInSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Si l'utilisateur est connecté et que la session existe
        if ($request->hasSession() && Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            
            // S'assurer que password_hash_web est présent dans la session
            // Cela évite que AuthenticateSession déconnecte l'utilisateur
            if ($user && $user->getAuthPassword()) {
                $session = $request->session();
                $passwordHashKey = 'password_hash_web';
                
                // Si password_hash_web n'existe pas ou ne correspond pas, le mettre à jour
                if (!$session->has($passwordHashKey) || 
                    !hash_equals($session->get($passwordHashKey), $user->getAuthPassword())) {
                    $session->put($passwordHashKey, $user->getAuthPassword());
                }
            }
        }

        return $response;
    }
}

