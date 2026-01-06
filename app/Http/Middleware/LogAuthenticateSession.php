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
        $sessionId = null;
        $userIdBefore = null;
        $wasAuthenticatedBefore = false;
        $hasPasswordHashBefore = false;
        $passwordHashMatchesBefore = false;
        
        // Log AVANT AuthenticateSession
        if ($request->hasSession()) {
            $session = $request->session();
            $sessionId = $session->getId();
            $wasAuthenticatedBefore = Auth::guard('web')->check();
            
            if ($wasAuthenticatedBefore) {
                $user = Auth::guard('web')->user();
                $userIdBefore = $user->id;
                $passwordHashKey = 'password_hash_web';
                $hasPasswordHashBefore = $session->has($passwordHashKey);
                
                if ($hasPasswordHashBefore) {
                    $passwordHashMatchesBefore = hash_equals(
                        $session->get($passwordHashKey), 
                        $user->getAuthPassword()
                    );
                }
                
                Log::info('🔍 [AUTH SESSION] AVANT AuthenticateSession', [
                    'route' => $request->route()?->getName() ?? $request->path(),
                    'method' => $request->method(),
                    'user_id' => $userIdBefore,
                    'session_id' => substr($sessionId, 0, 15) . '...',
                    'is_authenticated' => $wasAuthenticatedBefore,
                    'has_password_hash' => $hasPasswordHashBefore,
                    'password_hash_matches' => $passwordHashMatchesBefore,
                    'session_started' => $session->isStarted(),
                    'session_name' => $session->getName(),
                    'cookie_present' => $request->cookies->has($session->getName()),
                    'user_password_hash_preview' => substr($user->getAuthPassword(), 0, 20) . '...',
                    'session_password_hash_preview' => $hasPasswordHashBefore ? 
                        substr($session->get($passwordHashKey), 0, 20) . '...' : 'ABSENT',
                    'timestamp' => now()->toDateTimeString(),
                ]);
            } else {
                Log::info('🔍 [AUTH SESSION] AVANT AuthenticateSession - Non authentifié', [
                    'route' => $request->route()?->getName() ?? $request->path(),
                    'method' => $request->method(),
                    'session_id' => substr($sessionId, 0, 15) . '...',
                    'session_started' => $session->isStarted(),
                    'timestamp' => now()->toDateTimeString(),
                ]);
            }
        }

        $response = $next($request);

        // Log APRÈS AuthenticateSession
        if ($request->hasSession()) {
            $session = $request->session();
            $wasAuthenticatedAfter = Auth::guard('web')->check();
            $userIdAfter = $wasAuthenticatedAfter ? Auth::guard('web')->user()->id : null;
            $wasDisconnected = $wasAuthenticatedBefore && !$wasAuthenticatedAfter;
            
            if ($wasDisconnected) {
                Log::warning('⚠️ [AUTH SESSION] DÉCONNEXION DÉTECTÉE par AuthenticateSession', [
                    'route' => $request->route()?->getName() ?? $request->path(),
                    'method' => $request->method(),
                    'user_id_before' => $userIdBefore,
                    'user_id_after' => $userIdAfter,
                    'session_id' => substr($session->getId(), 0, 15) . '...',
                    'was_authenticated_before' => $wasAuthenticatedBefore,
                    'is_authenticated_after' => $wasAuthenticatedAfter,
                    'had_password_hash' => $hasPasswordHashBefore,
                    'password_hash_matched' => $passwordHashMatchesBefore,
                    'timestamp' => now()->toDateTimeString(),
                ]);
            } else {
                Log::info('✅ [AUTH SESSION] APRÈS AuthenticateSession - OK', [
                    'route' => $request->route()?->getName() ?? $request->path(),
                    'method' => $request->method(),
                    'user_id' => $userIdAfter,
                    'session_id' => substr($session->getId(), 0, 15) . '...',
                    'is_authenticated' => $wasAuthenticatedAfter,
                    'timestamp' => now()->toDateTimeString(),
                ]);
            }
        }

        return $response;
    }
}

