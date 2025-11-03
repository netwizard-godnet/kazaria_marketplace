<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
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
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
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
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
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
?>


<title><?php echo e($title); ?></title>
<meta name="description" content="<?php echo e($description); ?>">
<meta name="keywords" content="<?php echo e($keywords); ?>">
<meta name="author" content="<?php echo e($author); ?>">
<meta name="robots" content="<?php echo e($robots); ?>">
<link rel="canonical" href="<?php echo e($canonical); ?>">


<meta property="og:type" content="<?php echo e($type); ?>">
<meta property="og:title" content="<?php echo e($title); ?>">
<meta property="og:description" content="<?php echo e($description); ?>">
<meta property="og:url" content="<?php echo e($url); ?>">
<meta property="og:image" content="<?php echo e($image); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:site_name" content="KAZARIA">
<meta property="og:locale" content="fr_CI">


<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo e($title); ?>">
<meta name="twitter:description" content="<?php echo e($description); ?>">
<meta name="twitter:image" content="<?php echo e($image); ?>">
<meta name="twitter:site" content="@kazaria_ci">


<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta charset="utf-8">


<?php
    $siteFavicon = \App\Models\Setting::get('site_favicon');
    $faviconUrl = $siteFavicon ? asset('storage/' . ltrim($siteFavicon, '/')) : asset('favicon.png');
?>
<link rel="icon" href="<?php echo e($faviconUrl); ?>">
<link rel="apple-touch-icon" href="<?php echo e($faviconUrl); ?>">


<?php if($jsonLdData): ?>
<script type="application/ld+json">
<?php echo json_encode($jsonLdData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>

</script>
<?php endif; ?>


<meta name="application-name" content="KAZARIA">
<meta name="msapplication-TileColor" content="#f04e27">
<meta name="theme-color" content="#f04e27">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="KAZARIA">


<meta name="geo.region" content="CI">
<meta name="geo.placename" content="Abidjan">
<meta name="geo.position" content="5.316374996094937;-4.008675685237123">
<meta name="ICBM" content="5.316374996094937, -4.008675685237123">


<meta name="language" content="French">
<meta name="revisit-after" content="7 days">


<meta name="referrer" content="strict-origin-when-cross-origin">
<meta http-equiv="Permissions-Policy" content="camera=(), microphone=(), geolocation=()">


<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="dns-prefetch" href="//www.google-analytics.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/components/seo.blade.php ENDPATH**/ ?>