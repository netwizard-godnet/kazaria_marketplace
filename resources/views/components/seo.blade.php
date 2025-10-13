@props([
    'title' => 'KAZARIA - Votre marketplace en ligne en Côte d\'Ivoire',
    'description' => 'Découvrez une large gamme de produits électroniques, électroménagers et accessoires sur KAZARIA. Livraison gratuite, paiement sécurisé et satisfaction garantie.',
    'keywords' => 'e-commerce, marketplace, Côte d\'Ivoire, Abidjan, téléphones, électronique, électroménager, ordinateurs, livraison gratuite',
    'image' => null,
    'url' => null,
    'type' => 'website',
    'author' => 'KAZARIA',
    'canonical' => null,
    'robots' => 'index,follow',
    'jsonLd' => null
])

@php
    // URL par défaut
    $url = $url ?? request()->url();
    $canonical = $canonical ?? $url;
    
    // Image par défaut
    $image = $image ?? asset('images/KAZARIA.jpg');
    
    // Assurer que l'image est une URL absolue
    if ($image && !filter_var($image, FILTER_VALIDATE_URL)) {
        $image = asset($image);
    }
    
    // Données JSON-LD par défaut (organisation)
    $defaultJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'KAZARIA',
        'description' => 'Marketplace en ligne leader en Côte d\'Ivoire',
        'url' => config('app.url'),
        'logo' => asset('images/KAZARIA.jpg'),
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => '+2250701234567',
            'contactType' => 'customer service',
            'areaServed' => 'CI',
            'availableLanguage' => 'French'
        ],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Cocody, Angré 8ème Tranche',
            'addressLocality' => 'Abidjan',
            'addressCountry' => 'CI'
        ],
        'sameAs' => [
            'https://wa.me/2250701234567'
        ]
    ];
    
    $jsonLdData = $jsonLd ?? $defaultJsonLd;
@endphp

{{-- Balises meta de base --}}
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">
<meta name="author" content="{{ $author }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

{{-- Open Graph (Facebook, LinkedIn, etc.) --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:site_name" content="KAZARIA">
<meta property="og:locale" content="fr_CI">

{{-- Twitter Cards --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">
<meta name="twitter:site" content="@kazaria_ci">

{{-- Balises meta supplémentaires --}}
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta charset="utf-8">

{{-- Favicon et icônes --}}
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
<link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

{{-- Données structurées JSON-LD --}}
@if($jsonLdData)
<script type="application/ld+json">
{!! json_encode($jsonLdData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endif

{{-- Balises meta spécifiques au site --}}
<meta name="application-name" content="KAZARIA">
<meta name="msapplication-TileColor" content="#f04e27">
<meta name="theme-color" content="#f04e27">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="KAZARIA">

{{-- Balises geo pour la Côte d'Ivoire --}}
<meta name="geo.region" content="CI">
<meta name="geo.placename" content="Abidjan">
<meta name="geo.position" content="5.316374996094937;-4.008675685237123">
<meta name="ICBM" content="5.316374996094937, -4.008675685237123">

{{-- Balises de langue --}}
<meta name="language" content="French">
<meta name="revisit-after" content="7 days">

{{-- Balises de sécurité --}}
<meta name="referrer" content="strict-origin-when-cross-origin">
<meta http-equiv="Permissions-Policy" content="camera=(), microphone=(), geolocation=()">

{{-- Balises de performance --}}
<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="dns-prefetch" href="//www.google-analytics.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
