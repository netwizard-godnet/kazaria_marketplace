<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use App\Models\Category;
use Illuminate\Support\Facades\View;

class SettingServiceProvider extends ServiceProvider
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
        // Partager les paramètres publics avec toutes les vues
        View::composer('*', function ($view) {
            $settings = Setting::getPublicSettings();
            $view->with('settings', $settings);
        });

        // Partager les catégories avec toutes les vues
        View::composer('*', function ($view) {
            $allCategories = Category::active()->ordered()->with('subcategories')->get();
            $view->with('allCategories', $allCategories);
        });

        // Partager des paramètres spécifiques pour l'admin
        View::composer('admin.*', function ($view) {
            $adminSettings = Setting::all()->keyBy('key');
            $view->with('adminSettings', $adminSettings);
        });
    }
}
