<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Category;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Partager les catégories avec toutes les vues
        View::composer('*', function ($view) {
            $allCategories = Category::active()
                ->ordered()
                ->with('subcategories')
                ->get();
            
            $view->with('allCategories', $allCategories);
            
            // Charger l'utilisateur avec ses relations si authentifié
            try {
                if (auth()->check()) {
                    $userId = auth()->id();
                    if ($userId) {
                        // Recharger l'utilisateur depuis la base de données avec ses relations
                        // pour s'assurer que les données sont toujours à jour
                        $user = \App\Models\User::with('store')->find($userId);
                        if ($user) {
                            $view->with('currentUser', $user);
                        }
                    }
                }
            } catch (\Exception $e) {
                // En cas d'erreur, ne pas bloquer le rendu de la vue
                \Log::warning('Erreur lors du chargement de l\'utilisateur dans ViewServiceProvider: ' . $e->getMessage());
            }
        });
    }
}
