<?php

namespace App\Http\View\Composers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $categories = Category::active()
            ->ordered()
            ->limit(4) // Limiter à 4 catégories pour le footer
            ->get();

        $view->with('footerCategories', $categories);
    }
}
