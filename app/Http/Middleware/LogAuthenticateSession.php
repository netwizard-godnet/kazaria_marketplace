<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de logging pour diagnostiquer les problèmes avec AuthenticateSession
 * À supprimer après résolution du problème
 */
class LogAuthenticateSession
{
    public function handle(Request $request, Closure $next): Response
    {
        // Log avant AuthenticateSession
        if ($request->hasSession() && Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $session = $request->session();
            $passwordHashKey = 'password_hash_web';
            
            Log::info('LogAuthenticateSession: Avant AuthenticateSession', [
                'user_id' => $user->id,
                'session_id' => $session->getId(),
                'has_password_hash' => $session->has($passwordHashKey),
                'password_hash_matches' => $session->has($passwordHashKey) && 
                    hash_equals($session->get($passwordHashKey), $user->getAuthPassword()),
                'user_password_hash' => substr($user->getAuthPassword(), 0, 20) . '...',
                'session_password_hash' => $session->has($passwordHashKey) ? 
                    substr($session->get($passwordHashKey), 0, 20) . '...' : 'ABSENT',
            ]);
        }

        $response = $next($request);

        // Log après AuthenticateSession
        if ($request->hasSession()) {
            $wasAuthenticated = Auth::guard('web')->check();
            Log::info('LogAuthenticateSession: Après AuthenticateSession', [
                'still_authenticated' => $wasAuthenticated,
                'session_id' => $request->session()->getId(),
            ]);
        }

        return $response;
    }
}

