<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bannières pour la sidebar (à côté du carousel)
        Banner::create([
            'title' => 'Nouveautés',
            'subtitle' => 'Découvrez nos derniers produits',
            'image_path' => 'banners/sidebar-1.jpg',
            'link_url' => '/search?q=nouveauté',
            'placement' => 'sidebar',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Banner::create([
            'title' => 'Promotions',
            'subtitle' => 'Jusqu\'à -50% sur une sélection',
            'image_path' => 'banners/sidebar-2.jpg',
            'link_url' => '/search?q=promotion',
            'placement' => 'sidebar',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Bannières pour le header
        Banner::create([
            'title' => 'Livraison Gratuite',
            'subtitle' => 'Pour toute commande de 100.000F et plus',
            'image_path' => 'banners/header-1.jpg',
            'link_url' => '/livraison-gratuite',
            'placement' => 'header',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Banner::create([
            'title' => 'Garantie 90 Jours',
            'subtitle' => 'Satisfaction garantie ou remboursé',
            'image_path' => 'banners/header-2.jpg',
            'link_url' => '/garantie',
            'placement' => 'header',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Bannières pour le footer
        Banner::create([
            'title' => 'Téléchargez notre app',
            'subtitle' => 'Disponible sur iOS et Android',
            'image_path' => 'banners/footer-1.jpg',
            'link_url' => '/app',
            'placement' => 'footer',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Bannières pour les catégories
        Banner::create([
            'title' => 'Électronique',
            'subtitle' => 'Les meilleures marques à prix imbattables',
            'image_path' => 'banners/category-1.jpg',
            'link_url' => '/categorie/electronique',
            'placement' => 'category',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Bannières pour les produits
        Banner::create([
            'title' => 'Produits similaires',
            'subtitle' => 'Découvrez d\'autres articles',
            'image_path' => 'banners/product-1.jpg',
            'link_url' => '/search',
            'placement' => 'product',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Bannières génériques
        Banner::create([
            'title' => 'KAZARIA Express',
            'subtitle' => 'Livraison en 24h',
            'image_path' => 'banners/generic-1.jpg',
            'link_url' => '/kazaria-express',
            'placement' => 'sidebar',
            'sort_order' => 3,
            'is_active' => true,
        ]);
    }
}
