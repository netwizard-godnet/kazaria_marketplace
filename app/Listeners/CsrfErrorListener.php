<?php

namespace App\Listeners;

use Illuminate\Foundation\Events\CSRFValidationError;
use Illuminate\Support\Facades\Log;

class CsrfErrorListener
{
    /**
     * Enregistrer les erreurs CSRF
     */
    public function handle(CSRFValidationError $event)
    {
        $request = $event->request;
        
        // Informations de debug
        $debugInfo = [
            'timestamp' => now()->toDateTimeString(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
            'has_csrf_token' => $request->has('_token'),
            'csrf_token_in_header' => $request->header('X-CSRF-TOKEN'),
            'session_expired' => !session()->isStarted(),
            'session_lifetime' => config('session.lifetime'),
        ];
        
        // Log l'erreur avec toutes les informations
        Log::warning('CSRF Token Mismatch', $debugInfo);
    }
}
