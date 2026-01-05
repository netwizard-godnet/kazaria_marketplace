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
        // ⚠️ IMPORTANT : S'assurer que password_hash_web est présent AVANT la requête
        // pour éviter que AuthenticateSession ne déconnecte l'utilisateur
        if ($request->hasSession()) {
            $session = $request->session();
            
            // Vérifier si la session contient un user_id
            $userId = $session->get('login_web_59ba36addc2b2f9401580f014c7f58ea4');
            
            if ($userId) {
                // Charger l'utilisateur depuis la base de données
                $user = \App\Models\User::find($userId);
                
                if ($user && $user->getAuthPassword()) {
                    $passwordHashKey = 'password_hash_web';
                    
                    // Si password_hash_web n'existe pas ou ne correspond pas, le mettre à jour
                    if (!$session->has($passwordHashKey) || 
                        !hash_equals($session->get($passwordHashKey), $user->getAuthPassword())) {
                        $session->put($passwordHashKey, $user->getAuthPassword());
                        \Log::info('EnsurePasswordHashInSession: password_hash_web mis à jour AVANT la requête', [
                            'user_id' => $user->id,
                            'session_id' => $session->getId(),
                            'had_hash' => $session->has($passwordHashKey),
                        ]);
                    }
                }
            }
        }
        
        $response = $next($request);

        // S'assurer aussi APRÈS la requête (au cas où l'utilisateur se connecte pendant la requête)
        if ($request->hasSession() && Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            
            if ($user && $user->getAuthPassword()) {
                $session = $request->session();
                $passwordHashKey = 'password_hash_web';
                
                if (!$session->has($passwordHashKey) || 
                    !hash_equals($session->get($passwordHashKey), $user->getAuthPassword())) {
                    $session->put($passwordHashKey, $user->getAuthPassword());
                    \Log::info('EnsurePasswordHashInSession: password_hash_web mis à jour APRÈS la requête', [
                        'user_id' => $user->id,
                        'session_id' => $session->getId(),
                    ]);
                }
            }
        }

        return $response;
    }
}

