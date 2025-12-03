<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    /**
     * Générer les métadonnées SEO pour la page d'accueil
     */
    public static function getHomeSeo()
    {
        return [
            'title' => 'KAZARIA - Votre marketplace en ligne en Côte d\'Ivoire',
            'description' => 'Découvrez une large gamme de produits électroniques, électroménagers et accessoires sur KAZARIA. Livraison gratuite, paiement sécurisé et satisfaction garantie.',
            'keywords' => 'e-commerce, marketplace, Côte d\'Ivoire, Abidjan, téléphones, électronique, électroménager, ordinateurs, livraison gratuite, KAZARIA',
            'image' => asset('images/KAZARIA.jpg'),
            'type' => 'website',
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => 'KAZARIA',
                'description' => 'Marketplace en ligne leader en Côte d\'Ivoire',
                'url' => config('app.url'),
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => config('app.url') . '/search?q={search_term_string}'
                    ],
                    'query-input' => 'required name=search_term_string'
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'KAZARIA',
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('images/KAZARIA.jpg')
                    ]
                ]
            ]
        ];
    }

    /**
     * Générer les métadonnées SEO pour une catégorie
     */
    public static function getCategorySeo(Category $category)
    {
        $productsCount = $category->products()->active()->count();
        
        return [
            'title' => $category->name . ' - KAZARIA',
            'description' => "Découvrez notre sélection de {$category->name} sur KAZARIA. {$productsCount} produits disponibles avec livraison gratuite en Côte d'Ivoire.",
            'keywords' => $category->name . ', ' . strtolower($category->name) . ', KAZARIA, Côte d\'Ivoire, Abidjan, livraison gratuite',
            'image' => $category->image ? asset('storage/' . $category->image) : asset('images/KAZARIA.jpg'),
            'type' => 'website',
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $category->name,
                'description' => "Page des produits {$category->name} sur KAZARIA",
                'url' => route('categorie', $category->slug),
                'mainEntity' => [
                    '@type' => 'ItemList',
                    'name' => $category->name,
                    'numberOfItems' => $productsCount
                ]
            ]
        ];
    }

    /**
     * Générer les métadonnées SEO pour un produit
     */
    public static function getProductSeo(Product $product)
    {
        $price = $product->old_price && $product->old_price > $product->price 
            ? $product->price 
            : $product->price;
        
        $availability = $product->stock > 0 ? 'InStock' : 'OutOfStock';
        
        $description = $product->description 
            ? substr(strip_tags($product->description), 0, 160) 
            : "Découvrez {$product->name} sur KAZARIA. Livraison gratuite en Côte d'Ivoire.";
        
        $image = $product->images && count($product->images) > 0 
            ? (filter_var($product->images[0], FILTER_VALIDATE_URL) 
                ? $product->images[0] 
                : asset('storage/' . $product->images[0]))
            : asset('images/produit.jpg');

        return [
            'title' => $product->name . ' - ' . number_format($price, 0, ',', ' ') . ' FCFA - KAZARIA',
            'description' => $description,
            'keywords' => $product->name . ', ' . $product->brand . ', ' . strtolower($product->name) . ', KAZARIA, Côte d\'Ivoire, livraison gratuite',
            'image' => $image,
            'type' => 'product',
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $product->name,
                'description' => $product->description,
                'image' => $image,
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $product->brand ?? 'KAZARIA'
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'price' => $price,
                    'priceCurrency' => 'XOF',
                    'availability' => 'https://schema.org/' . $availability,
                    'seller' => [
                        '@type' => 'Organization',
                        'name' => 'KAZARIA'
                    ],
                    'url' => route('product-page', $product->slug)
                ],
                'aggregateRating' => $product->reviews_count > 0 ? [
                    '@type' => 'AggregateRating',
                    'ratingValue' => $product->rating,
                    'reviewCount' => $product->reviews_count
                ] : null
            ]
        ];
    }

    /**
     * Générer les métadonnées SEO pour une boutique
     */
    public static function getStoreSeo(Store $store)
    {
        $productsCount = $store->products()->count();
        
        return [
            'title' => $store->name . ' - Boutique officielle KAZARIA',
            'description' => "Découvrez la boutique {$store->name} sur KAZARIA. {$productsCount} produits disponibles avec livraison gratuite.",
            'keywords' => $store->name . ', boutique KAZARIA, ' . strtolower($store->name) . ', Côte d\'Ivoire, livraison gratuite',
            'image' => $store->logo ? asset('storage/' . $store->logo) : asset('images/KAZARIA.jpg'),
            'type' => 'website',
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'Store',
                'name' => $store->name,
                'description' => $store->description,
                'url' => route('store.show', $store->slug),
                'telephone' => $store->phone,
                'email' => $store->email,
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $store->address,
                    'addressLocality' => $store->city,
                    'addressCountry' => 'CI'
                ],
                'aggregateRating' => $store->reviews_count > 0 ? [
                    '@type' => 'AggregateRating',
                    'ratingValue' => $store->rating,
                    'reviewCount' => $store->reviews_count
                ] : null
            ]
        ];
    }

    /**
     * Générer les métadonnées SEO pour les pages statiques
     */
    public static function getStaticPageSeo($page, $title, $description, $keywords = null)
    {
        return [
            'title' => $title . ' - KAZARIA',
            'description' => $description,
            'keywords' => $keywords ?? $title . ', KAZARIA, Côte d\'Ivoire',
            'image' => asset('images/KAZARIA.jpg'),
            'type' => 'website',
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $title,
                'description' => $description,
                'url' => config('app.url') . '/' . $page,
                'isPartOf' => [
                    '@type' => 'WebSite',
                    'name' => 'KAZARIA',
                    'url' => config('app.url')
                ]
            ]
        ];
    }
}
