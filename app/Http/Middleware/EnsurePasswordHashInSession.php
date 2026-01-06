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
        $passwordHashKey = 'password_hash_web';
        $updatedBefore = false;
        $updatedAfter = false;
        
        // ⚠️ IMPORTANT : S'assurer que password_hash_web est présent AVANT la requête
        // pour éviter que AuthenticateSession ne déconnecte l'utilisateur
        // On utilise Auth::guard('web')->check() pour vérifier si l'utilisateur est connecté
        // car Laravel charge automatiquement l'utilisateur depuis la session si disponible
        if ($request->hasSession()) {
            $session = $request->session();
            
            // Vérifier si l'utilisateur est connecté via le guard web
            // Laravel charge automatiquement l'utilisateur depuis la session si disponible
            if (Auth::guard('web')->check()) {
                $user = Auth::guard('web')->user();
                
                if ($user && $user->getAuthPassword()) {
                    $hadHash = $session->has($passwordHashKey);
                    $hashMatches = $hadHash && hash_equals(
                        $session->get($passwordHashKey), 
                        $user->getAuthPassword()
                    );
                    
                    // Si password_hash_web n'existe pas ou ne correspond pas, le mettre à jour
                    if (!$hadHash || !$hashMatches) {
                        $session->put($passwordHashKey, $user->getAuthPassword());
                        $updatedBefore = true;
                        
                        \Log::warning('🔧 [PASSWORD HASH] Mis à jour AVANT la requête', [
                            'route' => $request->route()?->getName() ?? $request->path(),
                            'method' => $request->method(),
                            'user_id' => $user->id,
                            'session_id' => substr($session->getId(), 0, 15) . '...',
                            'had_hash' => $hadHash,
                            'hash_matched' => $hashMatches,
                            'session_started' => $session->isStarted(),
                            'timestamp' => now()->toDateTimeString(),
                        ]);
                    } else {
                        \Log::debug('✅ [PASSWORD HASH] OK AVANT la requête', [
                            'route' => $request->route()?->getName() ?? $request->path(),
                            'user_id' => $user->id,
                            'session_id' => substr($session->getId(), 0, 15) . '...',
                        ]);
                    }
                }
            }
        }
        
        $response = $next($request);

        // S'assurer aussi APRÈS la requête (au cas où l'utilisateur se connecte pendant la requête)
        // et sauvegarder la session pour garantir la persistance
        if ($request->hasSession() && Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            
            if ($user && $user->getAuthPassword()) {
                $session = $request->session();
                
                $hadHash = $session->has($passwordHashKey);
                $hashMatches = $hadHash && hash_equals(
                    $session->get($passwordHashKey), 
                    $user->getAuthPassword()
                );
                
                if (!$hadHash || !$hashMatches) {
                    $session->put($passwordHashKey, $user->getAuthPassword());
                    $updatedAfter = true;
                    
                    \Log::warning('🔧 [PASSWORD HASH] Mis à jour APRÈS la requête', [
                        'route' => $request->route()?->getName() ?? $request->path(),
                        'method' => $request->method(),
                        'user_id' => $user->id,
                        'session_id' => substr($session->getId(), 0, 15) . '...',
                        'had_hash' => $hadHash,
                        'hash_matched' => $hashMatches,
                        'session_started' => $session->isStarted(),
                        'timestamp' => now()->toDateTimeString(),
                    ]);
                }
                
                // S'assurer que la session est sauvegardée pour garantir la persistance
                // Cela évite que password_hash_web soit perdu entre les requêtes
                if ($session->isStarted()) {
                    try {
                        $session->save();
                        if ($updatedBefore || $updatedAfter) {
                            \Log::info('💾 [SESSION] Sauvegardée après mise à jour password_hash', [
                                'user_id' => $user->id,
                                'session_id' => substr($session->getId(), 0, 15) . '...',
                                'updated_before' => $updatedBefore,
                                'updated_after' => $updatedAfter,
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('❌ [SESSION] Erreur lors de la sauvegarde', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                            'session_id' => substr($session->getId(), 0, 15) . '...',
                        ]);
                    }
                }
            }
        }

        return $response;
    }
}

