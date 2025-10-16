<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ImageController;

// Routes principales
Route::get('/', [HomeController::class, 'index'])->name('accueil');

Route::get('/categorie/{slug}', [ProductController::class, 'category'])->name('categorie');

Route::get('/search', [ProductController::class, 'search'])->name('search_product');

Route::get('/boutique-officielle', [ProductController::class, 'boutique'])->name('boutique_officielle');

Route::get('/produit/{slug}', [ProductController::class, 'show'])->name('product-page');

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

Route::get('/panier', function () {
    return view('cart');
})->name('product-cart');

// Route d'authentification temporaire
Route::get('/authentification', function () {
    // Rediriger vers l'accueil si l'utilisateur est déjà connecté
    $token = request()->cookie('auth_token') ?? session('auth_token');
    if ($token) {
        return redirect()->route('accueil');
    }
    return view('auth.authentification');
})->name('login');

// Route pour les suggestions de recherche
Route::get('/api/search-suggestions', [App\Http\Controllers\SearchController::class, 'suggestions'])->name('search.suggestions');

// Route pour l'avatar des emails
Route::get('/avatar/kazaria', [App\Http\Controllers\AvatarController::class, 'kazariaAvatar'])->name('avatar.kazaria');
Route::get('/avatar/generate', [App\Http\Controllers\AvatarController::class, 'generateEmailAvatar'])->name('avatar.generate');

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

// Route profil utilisateur (protégée)
Route::get('/profil', [App\Http\Controllers\ProfileController::class, 'index'])
    ->middleware('client.auth')
    ->name('profil');

// Route panier
Route::get('/panier', [App\Http\Controllers\CartController::class, 'index'])->name('product-cart');

// Route favoris - Redirige vers l'onglet favoris du profil
Route::get('/favoris', function() {
    return redirect()->route('profil') . '#favorites';
})->name('favorites');

// Routes de commande
Route::get('/checkout', [App\Http\Controllers\OrderController::class, 'checkout'])->name('checkout');
Route::get('/shipping', [App\Http\Controllers\OrderController::class, 'shipping'])->name('shipping');
Route::get('/order/invoice/{orderNumber}', [App\Http\Controllers\OrderController::class, 'invoice'])->name('order-invoice');
Route::get('/order/download/{orderNumber}', [App\Http\Controllers\OrderController::class, 'downloadInvoice'])->name('order-download');
Route::get('/order/details/{orderNumber}', [App\Http\Controllers\OrderController::class, 'orderDetails'])->name('order-details');

// Route politique de confidentialité
Route::get('/politique-de-confidentialite', function() {
    return view('privacy-policy');
})->name('privacy-policy');

// Routes boutiques
Route::middleware('client.auth')->group(function () {
    Route::get('/store/create', [StoreController::class, 'create'])->name('store.create');
    Route::post('/store/create', [StoreController::class, 'store'])->name('store.store');
    Route::get('/store/pending', [StoreController::class, 'pending'])->name('store.pending');
    Route::get('/store/dashboard', [StoreController::class, 'dashboard'])->name('store.dashboard');
    Route::get('/store/edit', [StoreController::class, 'edit'])->name('store.edit');
    Route::post('/store/update', [StoreController::class, 'update'])->name('store.update');
    Route::get('/store/orders/{orderNumber}', function($orderNumber) {
        return view('seller.order-details', compact('orderNumber'));
    })->name('store.order-details');
});

// Route publique pour voir une boutique
Route::get('/boutique/{slug}', [StoreController::class, 'show'])->name('store.show');
