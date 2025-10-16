<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Routes d'authentification publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-login-code', [AuthController::class, 'verifyLoginCode']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('api.verify-email');

// Routes protégées par authentification
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all-devices', [AuthController::class, 'logoutAllDevices']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/profile/update', [App\Http\Controllers\ProfileController::class, 'update']);
    Route::post('/profile/change-password', [App\Http\Controllers\ProfileController::class, 'changePassword']);
    Route::post('/profile/update-photo', [App\Http\Controllers\ProfileController::class, 'updatePhoto']);
    
    // Activité récente
    Route::get('/activity/recent', [App\Http\Controllers\ProfileController::class, 'getRecentActivity']);
});

// Route de déconnexion publique (pour les tokens stockés côté client)
Route::post('/logout-client', [AuthController::class, 'logoutClient']);

// Routes du panier (public - fonctionne avec session ou user)
Route::prefix('cart')->group(function () {
    Route::get('/', [App\Http\Controllers\CartController::class, 'getCart']);
    Route::post('/add', [App\Http\Controllers\CartController::class, 'add']);
    Route::put('/update/{id}', [App\Http\Controllers\CartController::class, 'update']);
    Route::delete('/remove/{id}', [App\Http\Controllers\CartController::class, 'remove']);
    Route::delete('/clear', [App\Http\Controllers\CartController::class, 'clear']);
});

// Routes des favoris (public - fonctionne avec session ou user)
Route::prefix('favorites')->group(function () {
    Route::get('/', [App\Http\Controllers\CartController::class, 'getFavorites']);
    Route::post('/toggle', [App\Http\Controllers\CartController::class, 'toggleFavorite']);
});

// Routes de commande (protégées)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/orders/create', [App\Http\Controllers\OrderController::class, 'createOrder']);
    Route::get('/orders/my-orders', [App\Http\Controllers\OrderController::class, 'myOrders']);
    Route::get('/orders/{orderNumber}', [App\Http\Controllers\OrderController::class, 'getOrderDetails']);
});

// Routes des avis
Route::get('/products/{productId}/reviews', [App\Http\Controllers\ReviewController::class, 'getProductReviews']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'store']);
});
Route::post('/reviews/{reviewId}/vote', [App\Http\Controllers\ReviewController::class, 'vote']);

// Route pour vérifier le statut de vendeur
Route::get('/check-seller-status', [App\Http\Controllers\ProfileController::class, 'checkSellerStatus'])->middleware('auth:sanctum');

// Route pour le formulaire de contact
Route::post('/contact', [App\Http\Controllers\ContactController::class, 'sendMessage']);

// Routes API pour le dashboard vendeur
Route::middleware('auth:sanctum')->prefix('store')->group(function () {
    Route::get('/stats', [App\Http\Controllers\StoreController::class, 'getStats']);
    Route::get('/recent-orders', [App\Http\Controllers\StoreController::class, 'getRecentOrders']);
    Route::get('/products', [App\Http\Controllers\StoreController::class, 'getProducts']);
    Route::get('/orders', [App\Http\Controllers\StoreController::class, 'getOrders']);
    
    // Gestion des produits
    Route::post('/products', [App\Http\Controllers\Seller\ProductController::class, 'store']);
    Route::get('/products/{id}', [App\Http\Controllers\Seller\ProductController::class, 'show']);
    Route::put('/products/{id}', [App\Http\Controllers\Seller\ProductController::class, 'update']);
    Route::delete('/products/{id}', [App\Http\Controllers\Seller\ProductController::class, 'destroy']);
    Route::post('/products/{id}/images', [App\Http\Controllers\Seller\ProductController::class, 'uploadImages']);
    Route::delete('/products/{id}/images', [App\Http\Controllers\Seller\ProductController::class, 'deleteImage']);
    
    // Gestion des commandes vendeur
    Route::get('/orders/stats', [App\Http\Controllers\Seller\OrderController::class, 'getOrderStats']);
    Route::get('/orders/{orderNumber}', [App\Http\Controllers\Seller\OrderController::class, 'getOrderDetails']);
    Route::put('/orders/{orderNumber}/status', [App\Http\Controllers\Seller\OrderController::class, 'updateOrderStatus']);
    Route::post('/orders/{orderNumber}/ship', [App\Http\Controllers\Seller\OrderController::class, 'markAsShipped']);
    Route::post('/orders/{orderNumber}/cancel', [App\Http\Controllers\Seller\OrderController::class, 'cancelOrder']);
    
    // Paramètres de la boutique
    Route::post('/update', [App\Http\Controllers\StoreController::class, 'updateStore']);
    Route::post('/upload-logo', [App\Http\Controllers\StoreController::class, 'uploadLogo']);
    Route::post('/upload-banner', [App\Http\Controllers\StoreController::class, 'uploadBanner']);
    Route::post('/update-social', [App\Http\Controllers\StoreController::class, 'updateSocialLinks']);
    Route::post('/toggle-status', [App\Http\Controllers\StoreController::class, 'toggleStatus']);
    Route::delete('/delete', [App\Http\Controllers\StoreController::class, 'deleteStore']);
});

