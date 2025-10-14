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
        'title' => 'KAZARIA - Marketplace #1 Côte d\'Ivoire | Meilleurs Prix Garantis | Livraison Gratuite Abidjan',
        'description' => 'Achetez téléphones, ordinateurs, électroménager à Abidjan. Prix 15% moins chers que Jumia. Livraison gratuite, paiement Mobile Money, garantie 100% authentique. Alternative ivoirienne de confiance.',
        'keywords' => 'marketplace côte d\'ivoire, alternative jumia ci, e-commerce abidjan, acheter téléphone abidjan, ordinateur portable ci, électroménager abidjan, livraison gratuite abidjan, paiement mobile money, achat en ligne côte d\'ivoire, marketplace abidjan, iphone abidjan, samsung ci, meilleur prix électronique ci, boutique en ligne cocody, vente en ligne plateau, marketplace fiable ci',
        'image' => 'images/KAZARIA.jpg',
        'author' => 'KAZARIA',
        'robots' => 'index,follow',
        'type' => 'website',
    ],

    'site' => [
        'name' => 'KAZARIA',
        'url' => env('APP_URL', 'https://kazaria-ci.com'),
        'logo' => 'images/KAZARIA.jpg',
        'description' => 'Marketplace en ligne #1 de Côte d\'Ivoire. Meilleurs prix garantis, livraison gratuite, service client 24/7. L\'alternative ivoirienne de confiance.',
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
