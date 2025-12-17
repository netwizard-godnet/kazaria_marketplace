<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'api' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'csrf' => \App\Http\Middleware\ValidateCsrfToken::class,
            'client.auth' => \App\Http\Middleware\ClientAuthMiddleware::class,
            'seo' => \App\Http\Middleware\SeoMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'hybrid.auth' => \App\Http\Middleware\HybridAuthMiddleware::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'auth.redirect' => \App\Http\Middleware\RedirectIfNotAuthenticated::class,
            'seller' => \App\Http\Middleware\RedirectIfNotSeller::class,
            'admin.redirect' => \App\Http\Middleware\RedirectIfNotAdmin::class,
            'force.session' => \App\Http\Middleware\ForceSessionSave::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'landing.page' => \App\Http\Middleware\LandingPageMiddleware::class,
        ]);
        
        // Remplacer le middleware CSRF par défaut par notre version personnalisée
        $middleware->validateCsrfTokens(except: [
            'logout',
            '/logout',
            // Exclure toutes les routes API pour mobile
            'api/*',
        ]);
        
        // Appliquer les middlewares web globalement
        $middleware->web(append: [
            \App\Http\Middleware\SeoMiddleware::class,
            \App\Http\Middleware\LandingPageMiddleware::class,
            \App\Http\Middleware\TrackPageVisits::class,
        ]);
        
        // NE PAS appliquer ForceSessionSave car il modifie la session
        // et invalide le token CSRF
        
        // Ne pas appliquer auth globalement pour éviter les boucles
        // L'authentification sera appliquée via les routes spécifiques
        
        // Ne pas appliquer auth globalement pour éviter les boucles
        // L'authentification sera appliquée via les routes spécifiques
        
        // Configuration des middlewares d'authentification
        // Ne pas appliquer auth globalement pour éviter les boucles
        
        // Ne pas ajouter EnsureFrontendRequestsAreStateful aux routes API
        // Les applications mobiles utilisent uniquement des tokens Sanctum, pas des sessions
        // $middleware->api(append: [
        //     \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
