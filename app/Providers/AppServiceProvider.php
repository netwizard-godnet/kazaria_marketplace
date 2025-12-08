<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use App\Models\Product;
use App\Observers\ProductObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrer l'observer pour les produits (détection des changements de prix)
        Product::observe(ProductObserver::class);
        
        // Charger les routes admin
        $this->loadAdminRoutes();
        
        // Enregistrer les directives Blade personnalisées
        $this->registerBladeDirectives();
        
        // Enregistrer le listener pour les erreurs CSRF
        $this->app['events']->listen(
            \Illuminate\Foundation\Events\CSRFValidationError::class,
            \App\Listeners\CsrfErrorListener::class
        );
    }
    
    /**
     * Enregistrer les directives Blade personnalisées
     */
    private function registerBladeDirectives(): void
    {
        // Directive pour le statut des commandes
        Blade::directive('orderStatus', function ($expression) {
            return "<?php echo \App\Helpers\OrderHelper::getStatusLabel($expression); ?>";
        });
        
        // Directive pour la couleur du statut
        Blade::directive('orderStatusColor', function ($expression) {
            return "<?php echo \App\Helpers\OrderHelper::getStatusColor($expression); ?>";
        });
        
        // Directive pour le statut de paiement
        Blade::directive('paymentStatus', function ($expression) {
            return "<?php echo \App\Helpers\OrderHelper::getPaymentStatusLabel($expression); ?>";
        });
        
        // Directive pour la couleur du statut de paiement
        Blade::directive('paymentStatusColor', function ($expression) {
            return "<?php echo \App\Helpers\OrderHelper::getPaymentStatusColor($expression); ?>";
        });
    }
    
    /**
     * Load admin routes
     */
    private function loadAdminRoutes(): void
    {
        if (file_exists(base_path('routes/admin.php'))) {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(function () {
                    require base_path('routes/admin.php');
                });
        }
    }
}
