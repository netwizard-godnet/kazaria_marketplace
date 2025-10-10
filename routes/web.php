<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

//GET ROUTES
Route::get('/', function () {
    return view('accueil');
})->name('accueil');

Route::get('/categorie', function () {
    return view('categorie');
})->name('categorie');

Route::get('/Search-product', function () {
    return view('search_product');
})->name('search_product');

Route::get('/Boutique-Officielle', function () {
    return view('boutique_officielle');
})->name('boutique_officielle');

Route::get('/product-page', function () {
    return view('product');
})->name('product-page');

Route::get('/product-cart', function () {
    return view('cart');
})->name('product-cart');

// Routes d'authentification pour les invités (non connectés)
Route::middleware('guest')->group(function () {
    // Connexion
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Vérification du code d'authentification
    Route::get('/verify-code', [AuthController::class, 'showVerifyCodeForm'])->name('verify-code.show');
    Route::post('/verify-code', [AuthController::class, 'verifyCode'])->name('verify-code.verify');
    Route::post('/verify-code/resend', [AuthController::class, 'resendAuthCode'])->name('verify-code.resend');
    
    // Inscription
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    // Vérification d'email
    Route::get('/email/verify', [AuthController::class, 'verificationNotice'])->name('verification.notice');
    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])->name('verification.resend');
});

// Route de vérification d'email (accessible même connecté)
// Note: 'signed' temporairement retiré pour faciliter les tests en développement
// TODO: Remettre 'signed' avant la production pour la sécurité !
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['throttle:6,1'])
    ->name('verification.verify');

Route::middleware('guest')->group(function () {
    
    // Réinitialisation de mot de passe
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Routes pour les utilisateurs authentifiés
Route::middleware('auth')->group(function () {
    Route::get('/mon-profil', [ProfileController::class, 'index'])->name('profil');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});