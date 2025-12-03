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
        });
    }
}
