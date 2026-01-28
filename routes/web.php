<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\NewsletterController;

// Route de landing page (doit être avant les autres routes pour être accessible)
Route::get('/landing', function () {
    $seoData = \App\Http\Controllers\SeoController::getStaticPageSeo(
        'landing',
        'KAZARIA - Bientôt disponible',
        'KAZARIA arrive bientôt ! Une nouvelle marketplace en ligne pour tous vos besoins en Côte d\'Ivoire.',
        'KAZARIA, marketplace, Côte d\'Ivoire, e-commerce, bientôt disponible'
    );
    foreach ($seoData as $key => $value) {
        $seoKey = 'seo' . ucfirst($key);
        view()->share($seoKey, $value);
    }
    
    // Récupérer les paramètres du site
    $settings = [];
    try {
        $settingsModel = \App\Models\Setting::where('is_public', true)->get();
        foreach ($settingsModel as $setting) {
            $settings[$setting->key] = $setting->value;
        }
    } catch (\Exception $e) {
        // Si la table n'existe pas encore, utiliser des valeurs par défaut
        $settings = [
            'site_name' => 'KAZARIA',
            'site_description' => 'Votre marketplace en ligne en Côte d\'Ivoire'
        ];
    }
    
    // Calculer la date de lancement pour le compte à rebours
    // Priorité : Setting admin > Config .env > Par défaut (24h fixe stocké en session)
    $launchDate = null;
    
    // Essayer d'abord depuis les settings admin
    $setting = \App\Models\Setting::where('key', 'landing_page_launch_date')->first();
    if ($setting && !empty(trim($setting->value))) {
        $launchDate = trim($setting->value);
    }
    
    // Fallback sur la config si le setting n'existe pas ou est vide
    if (!$launchDate || empty($launchDate)) {
        $configDate = config('app.landing_page_launch_date', null);
        if ($configDate && !empty(trim($configDate))) {
            $launchDate = trim($configDate);
        }
    }
    
    if ($launchDate && !empty($launchDate)) {
        try {
            // Utiliser la date configurée
            $targetTimestamp = \Carbon\Carbon::parse($launchDate)->timestamp * 1000;
        } catch (\Exception $e) {
            // Si la date est invalide, utiliser une date fixe par défaut (24h à partir de maintenant, stockée en session)
            if (!session()->has('landing_page_default_launch_date')) {
                session(['landing_page_default_launch_date' => now()->addHours(24)->format('Y-m-d H:i:s')]);
            }
            $defaultDate = session('landing_page_default_launch_date');
            $targetTimestamp = \Carbon\Carbon::parse($defaultDate)->timestamp * 1000;
        }
    } else {
        // Par défaut : utiliser une date fixe stockée en session (24h à partir de la première visite)
        // Cette date sera la même pour toutes les actualisations de la page
        if (!session()->has('landing_page_default_launch_date')) {
            session(['landing_page_default_launch_date' => now()->addHours(24)->format('Y-m-d H:i:s')]);
        }
        $defaultDate = session('landing_page_default_launch_date');
        $targetTimestamp = \Carbon\Carbon::parse($defaultDate)->timestamp * 1000;
    }
    
    return view('landing', compact('settings', 'targetTimestamp'));
})->name('landing');

// Routes principales (SESSION)
Route::get('/', [HomeController::class, 'index'])->name('accueil');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

Route::get('/categorie/{slug}', [ProductController::class, 'category'])->name('categorie');

Route::get('/search', [ProductController::class, 'search'])->name('search_product');

Route::get('/boutique-officielle', [ProductController::class, 'boutique'])->name('boutique_officielle');

Route::get('/produit/{slug}', [ProductController::class, 'show'])->name('product-page');

Route::get('/blackfriday', function () {
    $seoData = \App\Http\Controllers\SeoController::getStaticPageSeo(
        'black-friday',
        'Black Friday KAZARIA',
        'Profitez des meilleures offres Black Friday sur KAZARIA : réductions exceptionnelles sur l\'électronique, l\'électroménager et bien plus.',
        'black friday, promotions, soldes, KAZARIA, bons plans, réductions'
    );
    foreach ($seoData as $key => $value) {
        $seoKey = 'seo' . ucfirst($key);
        view()->share($seoKey, $value);
    }

    return view('blackfriday');
})->name('blackfriday');

// Routes pour les attributs
Route::get('/attribut/{attributeSlug}', [ProductController::class, 'byAttribute'])->name('products.by-attribute');
Route::get('/attribut/{attributeSlug}/{valueSlug}', [ProductController::class, 'byAttribute'])->name('products.by-attribute-value');

// Routes d'aide et contact avec SEO
Route::get('/aide-faq', function () {
    $seoData = \App\Http\Controllers\SeoController::getStaticPageSeo(
        'aide-faq',
        'Aide & FAQ',
        'Trouvez rapidement les réponses à vos questions sur KAZARIA : commandes, livraison, paiement, retours.',
        'aide, FAQ, questions fréquentes, KAZARIA, support client, assistance'
    );
    foreach ($seoData as $key => $value) {
        $seoKey = 'seo' . ucfirst($key);
        view()->share($seoKey, $value);
    }
    return view('help-faq');
})->name('help-faq');

Route::get('/contact', function () {
    $seoData = \App\Http\Controllers\SeoController::getStaticPageSeo(
        'contact',
        'Contactez-nous',
        'Contactez l\'équipe KAZARIA pour toute question. WhatsApp, email, téléphone. Support client disponible.',
        'contact, support, KAZARIA, WhatsApp, email, téléphone, assistance client'
    );
    foreach ($seoData as $key => $value) {
        $seoKey = 'seo' . ucfirst($key);
        view()->share($seoKey, $value);
    }
    return view('contact');
})->name('contact');

// Routes liens utiles avec SEO
Route::get('/suivre-commande', function () {
    $seoData = \App\Http\Controllers\SeoController::getStaticPageSeo(
        'suivre-commande',
        'Suivre sa commande',
        'Suivez l\'état de votre commande KAZARIA en temps réel. Numéro de commande et email requis pour le suivi.',
        'suivi commande, KAZARIA, livraison, statut commande, Côte d\'Ivoire'
    );
    foreach ($seoData as $key => $value) {
        $seoKey = 'seo' . ucfirst($key);
        view()->share($seoKey, $value);
    }
    return view('suivre-commande');
})->name('suivre-commande');

// Route pour rechercher une commande (suivi public)
Route::post('/api/track-order', [App\Http\Controllers\OrderController::class, 'trackOrder'])->name('api.track-order');

Route::get('/expedition-livraison', function () {
    $seoData = \App\Http\Controllers\SeoController::getStaticPageSeo(
        'expedition-livraison',
        'Expédition & Livraison',
        'Découvrez nos options de livraison KAZARIA : standard gratuite, express, zones couvertes en Côte d\'Ivoire.',
        'livraison, expédition, KAZARIA, Côte d\'Ivoire, Abidjan, frais livraison'
    );
    foreach ($seoData as $key => $value) {
        $seoKey = 'seo' . ucfirst($key);
        view()->share($seoKey, $value);
    }
    return view('expedition-livraison');
})->name('expedition-livraison');

Route::get('/politique-retour', function () {
    $seoData = \App\Http\Controllers\SeoController::getStaticPageSeo(
        'politique-retour',
        'Politique de retour',
        'Retournez vos produits KAZARIA dans les 14 jours. Conditions, processus et remboursement expliqués.',
        'retour, échange, remboursement, KAZARIA, politique retour, 14 jours'
    );
    foreach ($seoData as $key => $value) {
        $seoKey = 'seo' . ucfirst($key);
        view()->share($seoKey, $value);
    }
    return view('politique-retour');
})->name('politique-retour');

Route::get('/comment-commander', function () {
    $seoData = \App\Http\Controllers\SeoController::getStaticPageSeo(
        'comment-commander',
        'Comment commander',
        'Guide complet pour commander sur KAZARIA : étapes, paiement, modes de livraison et conseils.',
        'commander, guide achat, KAZARIA, paiement, livraison, étapes commande'
    );
    foreach ($seoData as $key => $value) {
        $seoKey = 'seo' . ucfirst($key);
        view()->share($seoKey, $value);
    }
    return view('comment-commander');
})->name('comment-commander');

Route::get('/agences-points-relais', function () {
    $seoData = \App\Http\Controllers\SeoController::getStaticPageSeo(
        'agences-points-relais',
        'Agences & Points de relais KAZARIA',
        'Trouvez nos agences et points de relais KAZARIA à Abidjan et en Côte d\'Ivoire. Horaires et services.',
        'agences KAZARIA, points relais, Abidjan, Plateau, Cocody, Yopougon, Marcory'
    );
    foreach ($seoData as $key => $value) {
        $seoKey = 'seo' . ucfirst($key);
        view()->share($seoKey, $value);
    }
    return view('agences-points-relais');
})->name('agences-points-relais');

// Routes SEO
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Routes pour servir les images
Route::get('/images/storage/{path}', [App\Http\Controllers\ImageController::class, 'serve'])->where('path', '.*');
Route::get('/images/stores/{storeId}/logo/{filename}', [App\Http\Controllers\ImageController::class, 'storeLogo']);
Route::get('/images/stores/{storeId}/banner/{filename}', [App\Http\Controllers\ImageController::class, 'storeBanner']);
Route::get('/images/products/{productId}/{filename}', [App\Http\Controllers\ImageController::class, 'productImage']);

// Route panier supprimée - doublon avec la ligne 176

// Route d'authentification (rediriger si déjà connecté)
Route::get('/authentification', function () {
    return view('auth.authentification');
})->middleware('guest')->name('login');

// Route pour vérifier le code de connexion (utilise la session)
Route::post('/verify-login-code', [App\Http\Controllers\AuthController::class, 'verifyLoginCode'])->name('verify-login-code');

Route::middleware('guest')->group(function () {
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->name('social.redirect')
        ->whereIn('provider', ['google', 'facebook']);

    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->name('social.callback')
        ->whereIn('provider', ['google', 'facebook']);
});

// Routes d'authentification admin
Route::prefix('admin')->group(function () {
    Route::get('/login', [App\Http\Controllers\Admin\AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [App\Http\Controllers\Admin\AuthController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('admin.logout');
    Route::get('/check-auth', [App\Http\Controllers\Admin\AuthController::class, 'checkAuth'])->name('admin.check-auth');
});

// Route pour les suggestions de recherche
Route::get('/api/search-suggestions', [App\Http\Controllers\SearchController::class, 'suggestions'])->name('search.suggestions');

// Route pour l'avatar des emails
Route::get('/avatar/kazaria', [App\Http\Controllers\AvatarController::class, 'kazariaAvatar'])->name('avatar.kazaria');
Route::get('/avatar/generate', [App\Http\Controllers\AvatarController::class, 'generateEmailAvatar'])->name('avatar.generate');

// A PROPOS
Route::get('/qui-nous-sommes', function() { return view('qui-nous-sommes'); })->name('qui-nous-sommes');

// Routes d'authentification
Route::get('/verify-email/{token}', [App\Http\Controllers\AuthController::class, 'verifyEmail'])->name('verify-email');
Route::get('/forgot-password', function() {
    return view('auth.forgot-password');
})->name('forgot-password');
Route::get('/forgot-password-sent', function() {
    return view('auth.forgot-password-sent');
})->name('forgot-password-sent');
Route::get('/reset-password/{token}', function($token) {
    return view('auth.reset-password', compact('token'));
})->name('reset-password');

// Route profil utilisateur (authentification requise)
Route::get('/profil', [App\Http\Controllers\ProfileController::class, 'index'])
    ->middleware('auth')
    ->name('profil');

// Routes pour le profil utilisateur (authentification par session)
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/profile/change-password', [App\Http\Controllers\ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::post('/profile/update-two-factor', [App\Http\Controllers\ProfileController::class, 'updateTwoFactor'])->name('profile.update-two-factor');
    Route::post('/profile/logout-all-devices', [App\Http\Controllers\ProfileController::class, 'logoutAllDevices'])->name('profile.logout-all-devices');
    Route::post('/profile/request-email-verification', [App\Http\Controllers\ProfileController::class, 'requestEmailVerification'])->name('profile.request-email-verification');
});

// Route panier
Route::get('/panier', [App\Http\Controllers\CartController::class, 'index'])
    ->middleware(['web', \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('product-cart');

// Routes du panier (WEB - Sessions avec CSRF)
Route::middleware(['web', \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])->group(function () {
    Route::post('/cart/add', [App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
    Route::put('/cart/update', [App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove', [App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
    Route::get('/cart/get', [App\Http\Controllers\CartController::class, 'getCart'])->name('cart.get');
    Route::delete('/cart/clear', [App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');
    Route::post('/favorites/toggle', [App\Http\Controllers\CartController::class, 'toggleFavorite'])->name('favorites.toggle');
    Route::get('/favorites/', [App\Http\Controllers\CartController::class, 'getFavorites'])->name('favorites.get');
});

// Route favoris - Redirige vers l'onglet favoris du profil
Route::get('/favoris', function() {
    return redirect()->route('profil') . '#favorites';
})->name('favorites');

// Routes avis (WEB - Sessions + API - Tokens)
Route::middleware('hybrid.auth')->group(function () {
    Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'storeWeb'])->name('reviews.store');
});

// Routes de commande (WEB - Sessions + API - Tokens)
// Checkout et shipping accessibles sans authentification
Route::middleware('web')->group(function () {
    Route::get('/checkout', [App\Http\Controllers\OrderController::class, 'checkout'])->name('checkout');
    Route::get('/shipping', [App\Http\Controllers\OrderController::class, 'shipping'])->name('shipping');
});

Route::middleware('hybrid.auth')->group(function () {
    Route::get('/order/invoice/{orderNumber}', [App\Http\Controllers\OrderController::class, 'invoice'])->name('order-invoice');
    Route::get('/order/download/{orderNumber}', [App\Http\Controllers\OrderController::class, 'downloadInvoice'])->name('order-download');
    Route::get('/order/details/{orderNumber}', [App\Http\Controllers\OrderController::class, 'orderDetails'])->name('order-details');
});

// Routes pour la commande (utilise la session, auth optionnel)
Route::middleware(['web'])->group(function () {
    Route::post('/orders/verify-email', [App\Http\Controllers\OrderController::class, 'verifyEmailAndAuthenticate'])->name('orders.verify-email');
    Route::post('/orders/authenticate', [App\Http\Controllers\OrderController::class, 'authenticateForOrder'])->name('orders.authenticate');
    Route::post('/orders/setup-password', [App\Http\Controllers\OrderController::class, 'setupPassword'])->name('orders.setup-password');
    Route::post('/orders/create', [App\Http\Controllers\OrderController::class, 'createOrder'])->name('orders.create');
});

// Route politique de confidentialité
Route::get('/politique-de-confidentialite', function() {
    return view('privacy-policy');
})->name('privacy-policy');

// Routes utilisateur (authentification par session) - Route /profile supprimée (doublon avec /profil)

// Route de déconnexion GET et POST (sans CSRF)
Route::get('/logout', function() {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout.get');

Route::post('/logout', function() {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Création de boutique (utilisateur connecté requis)
Route::middleware('auth')->group(function () {
    Route::get('/store/create', [StoreController::class, 'create'])->name('store.create');
    Route::post('/store/create', [StoreController::class, 'store'])->name('store.store');
});

// Routes boutiques (authentification vendeur requise)
Route::middleware('seller')->group(function () {
    Route::get('/store/pending', [StoreController::class, 'pending'])->name('store.pending');
    Route::get('/store/rejected', [StoreController::class, 'rejected'])->name('store.rejected');
    Route::get('/store/dashboard', [StoreController::class, 'dashboard'])->name('store.dashboard');
    Route::get('/store/edit', [StoreController::class, 'edit'])->name('store.edit');
    Route::post('/store/update', [StoreController::class, 'update'])->name('store.update');
    Route::get('/store/orders/{orderNumber}', function($orderNumber) {
        return view('seller.order-details', compact('orderNumber'));
    })->name('store.order-details');
    
    // Routes pour les produits vendeur (Sessions avec CSRF)
    Route::middleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)->group(function () {
        Route::get('/store/api/products', [App\Http\Controllers\Seller\ProductController::class, 'getProducts'])->name('store.api.products');
        Route::post('/store/api/products', [App\Http\Controllers\Seller\ProductController::class, 'store'])->name('store.api.products.create');
        Route::get('/store/api/products/{id}', [App\Http\Controllers\Seller\ProductController::class, 'getProduct'])->name('store.api.products.show');
        Route::put('/store/api/products/{id}', [App\Http\Controllers\Seller\ProductController::class, 'updateProduct'])->name('store.api.products.update');
        Route::delete('/store/api/products/{id}', [App\Http\Controllers\Seller\ProductController::class, 'deleteProduct'])->name('store.api.products.delete');
    });
    
    
    // Routes pour les statistiques et paramètres de la boutique (Sessions avec CSRF)
    Route::middleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)->group(function () {
        Route::get('/store/api/stats', [App\Http\Controllers\StoreController::class, 'getStats'])->name('store.api.stats');
        Route::post('/store/api/update', [App\Http\Controllers\StoreController::class, 'updateApi'])->name('store.api.update');
        Route::post('/store/api/upload-logo', [App\Http\Controllers\StoreController::class, 'uploadLogo'])->name('store.api.upload-logo');
        Route::post('/store/api/upload-banner', [App\Http\Controllers\StoreController::class, 'uploadBanner'])->name('store.api.upload-banner');
        Route::post('/store/api/update-social', [App\Http\Controllers\StoreController::class, 'updateSocial'])->name('store.api.update-social');
        Route::post('/store/api/toggle-status', [App\Http\Controllers\StoreController::class, 'toggleStatus'])->name('store.api.toggle-status');
        Route::delete('/store/api/delete', [App\Http\Controllers\StoreController::class, 'deleteStore'])->name('store.api.delete');
    });
    
    // Routes pour les commandes vendeur (Hybride - Sessions + Tokens)
    Route::middleware('hybrid.auth')->group(function () {
        Route::get('/store/api/orders', [App\Http\Controllers\Seller\OrderController::class, 'getOrders'])->name('store.api.orders');
        Route::get('/store/api/orders/stats', [App\Http\Controllers\Seller\OrderController::class, 'getOrderStats'])->name('store.api.orders.stats');
        Route::get('/store/api/recent-orders', [App\Http\Controllers\Seller\OrderController::class, 'getRecentOrders'])->name('store.api.recent-orders');
        Route::get('/store/api/orders/{orderNumber}', [App\Http\Controllers\Seller\OrderController::class, 'getOrderDetails'])->name('store.api.order-details');
        Route::put('/store/api/orders/{orderNumber}/status', [App\Http\Controllers\Seller\OrderController::class, 'updateOrderStatus'])->name('store.api.order-status');
        Route::post('/store/api/orders/{orderNumber}/ship', [App\Http\Controllers\Seller\OrderController::class, 'markAsShipped'])->name('store.api.order-ship');
        Route::post('/store/api/orders/{orderNumber}/deliver', [App\Http\Controllers\Seller\OrderController::class, 'markAsDelivered'])->name('store.api.order-deliver');
        Route::post('/store/api/orders/{orderNumber}/cancel', [App\Http\Controllers\Seller\OrderController::class, 'cancelOrder'])->name('store.api.order-cancel');
        Route::put('/store/api/orders/{orderNumber}/payment-status', [App\Http\Controllers\Seller\OrderController::class, 'changePaymentStatus'])->name('store.api.order-payment-status');
    });
});

// Route publique pour voir une boutique
Route::get('/boutique/{slug}', [StoreController::class, 'show'])->name('store.show');

// Routes admin
require __DIR__.'/admin.php';
