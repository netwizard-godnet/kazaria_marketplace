<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as Middleware;

class ValidateCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'logout',
        '/logout',
        // Exclure toutes les routes API
        'api/*',
        '/api/*',
        // Le panier en web garde le CSRF pour le site web
        // Mais on peut ajouter une route API pour mobile
    ];
    
    /**
     * Déterminer si la requête devrait passer la vérification CSRF.
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
