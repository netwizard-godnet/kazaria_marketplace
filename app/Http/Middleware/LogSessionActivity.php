<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de logging pour diagnostiquer les problèmes de session
 * Log toutes les activités importantes de session pour identifier les problèmes de déconnexion
 */
class LogSessionActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $sessionStarted = false;
        $sessionId = null;
        $cookiePresent = false;
        
        // Log AVANT le traitement de la requête
        if ($request->hasSession()) {
            $session = $request->session();
            $sessionId = $session->getId();
            $sessionStarted = $session->isStarted();
            $cookiePresent = $request->cookies->has($session->getName());
            
            Log::info('📋 [SESSION ACTIVITY] Début de requête', [
                'route' => $request->route()?->getName() ?? $request->path(),
                'method' => $request->method(),
                'session_id' => $sessionId ? substr($sessionId, 0, 15) . '...' : 'NULL',
                'session_started' => $sessionStarted,
                'has_session' => $request->hasSession(),
                'cookie_present' => $cookiePresent,
                'cookie_name' => $session->getName(),
                'is_authenticated' => Auth::guard('web')->check(),
                'user_id' => Auth::guard('web')->check() ? Auth::guard('web')->id() : null,
                'timestamp' => now()->toDateTimeString(),
            ]);
        } else {
            Log::warning('⚠️ [SESSION ACTIVITY] Pas de session disponible', [
                'route' => $request->route()?->getName() ?? $request->path(),
                'method' => $request->method(),
                'timestamp' => now()->toDateTimeString(),
            ]);
        }

        $response = $next($request);

        // Log APRÈS le traitement de la requête
        if ($request->hasSession()) {
            $session = $request->session();
            $finalSessionId = $session->getId();
            $sessionChanged = $sessionId !== $finalSessionId;
            $isAuthenticated = Auth::guard('web')->check();
            
            Log::info('📋 [SESSION ACTIVITY] Fin de requête', [
                'route' => $request->route()?->getName() ?? $request->path(),
                'method' => $request->method(),
                'session_id_before' => $sessionId ? substr($sessionId, 0, 15) . '...' : 'NULL',
                'session_id_after' => $finalSessionId ? substr($finalSessionId, 0, 15) . '...' : 'NULL',
                'session_changed' => $sessionChanged,
                'session_started' => $session->isStarted(),
                'is_authenticated' => $isAuthenticated,
                'user_id' => $isAuthenticated ? Auth::guard('web')->id() : null,
                'response_status' => $response->getStatusCode(),
                'timestamp' => now()->toDateTimeString(),
            ]);
            
            // Si la session a changé, c'est important à noter
            if ($sessionChanged) {
                Log::warning('🔄 [SESSION ACTIVITY] ID de session changé pendant la requête', [
                    'route' => $request->route()?->getName() ?? $request->path(),
                    'old_session_id' => substr($sessionId, 0, 15) . '...',
                    'new_session_id' => substr($finalSessionId, 0, 15) . '...',
                    'is_authenticated' => $isAuthenticated,
                ]);
            }
        }

        return $response;
    }
}

