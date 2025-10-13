<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour le référencement SEO du site KAZARIA
    |
    */

    'defaults' => [
        'title' => 'KAZARIA - Votre marketplace en ligne en Côte d\'Ivoire',
        'description' => 'Découvrez une large gamme de produits électroniques, électroménagers et accessoires sur KAZARIA. Livraison gratuite, paiement sécurisé et satisfaction garantie.',
        'keywords' => 'e-commerce, marketplace, Côte d\'Ivoire, Abidjan, téléphones, électronique, électroménager, ordinateurs, livraison gratuite',
        'image' => 'images/KAZARIA.jpg',
        'author' => 'KAZARIA',
        'robots' => 'index,follow',
        'type' => 'website',
    ],

    'site' => [
        'name' => 'KAZARIA',
        'url' => env('APP_URL', 'https://kazaria.ci'),
        'logo' => 'images/KAZARIA.jpg',
        'description' => 'Marketplace en ligne leader en Côte d\'Ivoire',
        'language' => 'fr',
        'country' => 'CI',
        'locale' => 'fr_CI',
    ],

    'social' => [
        'facebook' => null,
        'twitter' => '@kazaria_ci',
        'instagram' => null,
        'linkedin' => null,
        'youtube' => null,
        'whatsapp' => '+2250701234567',
    ],

    'contact' => [
        'email' => 'contact@kazaria.ci',
        'phone' => '+225 07 01 23 45 67',
        'address' => [
            'street' => 'Cocody, Angré 8ème Tranche',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
            'postal_code' => null,
            'coordinates' => [
                'latitude' => 5.316374996094937,
                'longitude' => -4.008675685237123,
            ],
        ],
    ],

    'business' => [
        'type' => 'Organization',
        'founded' => '2024',
        'slogan' => 'Votre marketplace en ligne en Côte d\'Ivoire',
        'services' => [
            'E-commerce',
            'Marketplace',
            'Livraison',
            'Support client',
        ],
        'payment_methods' => [
            'Paiement à la livraison',
            'Mobile Money',
            'Carte bancaire',
            'Wave',
        ],
    ],

    'sitemap' => [
        'cache_duration' => 3600, // 1 heure en secondes
        'max_urls_per_page' => 50000,
        'include_images' => true,
        'include_videos' => false,
    ],

    'robots' => [
        'user_agent' => '*',
        'crawl_delay' => 1,
        'disallow' => [
            '/admin/',
            '/api/',
            '/storage/',
            '/vendor/',
            '/database/',
            '/config/',
            '/bootstrap/',
            '/app/',
            '/resources/',
            '/tests/',
            '/node_modules/',
            '/.env',
            '/.git/',
            '/composer.json',
            '/composer.lock',
            '/package.json',
            '/package-lock.json',
            '/yarn.lock',
            '/webpack.mix.js',
            '/vite.config.js',
            '/phpunit.xml',
            '/.gitignore',
            '/.gitattributes',
            '/README.md',
            '/artisan',
            '/telescope/',
            '/horizon/',
            '/log-viewer/',
            '/_debugbar/',
            '/tinker',
            '/phpinfo',
        ],
        'allow' => [
            '/css/',
            '/js/',
            '/images/',
            '/favicon.ico',
            '/favicon.png',
        ],
    ],

    'analytics' => [
        'google_analytics' => env('GOOGLE_ANALYTICS_ID'),
        'google_tag_manager' => env('GOOGLE_TAG_MANAGER_ID'),
        'facebook_pixel' => env('FACEBOOK_PIXEL_ID'),
    ],

    'performance' => [
        'enable_compression' => true,
        'enable_caching' => true,
        'cache_static_pages' => 3600, // 1 heure
        'cache_dynamic_pages' => 1800, // 30 minutes
    ],
];
