<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;

// Routes principales
Route::get('/', [HomeController::class, 'index'])->name('accueil');

Route::get('/categorie/{slug}', [ProductController::class, 'category'])->name('categorie');

Route::get('/search', [ProductController::class, 'search'])->name('search_product');

Route::get('/boutique-officielle', [ProductController::class, 'boutique'])->name('boutique_officielle');

Route::get('/produit/{slug}', [ProductController::class, 'show'])->name('product-page');

// Routes d'aide et contact
Route::get('/aide-faq', function () {
    return view('help-faq');
})->name('help-faq');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

// Routes liens utiles
Route::get('/suivre-commande', function () {
    return view('suivre-commande');
})->name('suivre-commande');

Route::get('/expedition-livraison', function () {
    return view('expedition-livraison');
})->name('expedition-livraison');

Route::get('/politique-retour', function () {
    return view('politique-retour');
})->name('politique-retour');

Route::get('/comment-commander', function () {
    return view('comment-commander');
})->name('comment-commander');

Route::get('/agences-points-relais', function () {
    return view('agences-points-relais');
})->name('agences-points-relais');

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
    Route::get('/store/create', [App\Http\Controllers\StoreController::class, 'create'])->name('store.create');
    Route::post('/store/create', [App\Http\Controllers\StoreController::class, 'store'])->name('store.store');
    Route::get('/store/pending', [App\Http\Controllers\StoreController::class, 'pending'])->name('store.pending');
    Route::get('/store/dashboard', [App\Http\Controllers\StoreController::class, 'dashboard'])->name('store.dashboard');
    Route::get('/store/edit', [App\Http\Controllers\StoreController::class, 'edit'])->name('store.edit');
    Route::post('/store/update', [App\Http\Controllers\StoreController::class, 'update'])->name('store.update');
});

// Route publique pour voir une boutique
Route::get('/boutique/{slug}', [App\Http\Controllers\StoreController::class, 'show'])->name('store.show');
