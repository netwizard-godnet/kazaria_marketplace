<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;

// Routes d'authentification publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-login-code', [AuthController::class, 'verifyLoginCodeApi']); // API pour Flutter/mobile
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode']);
// Route de vérification d'email supprimée - utilise la route web

// Route d'authentification sociale pour mobile
Route::post('/auth/social/{provider}', [SocialAuthController::class, 'mobileAuth'])->where('provider', 'google|facebook');

// Routes protégées par authentification
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all-devices', [AuthController::class, 'logoutAllDevices']);
    Route::get('/me', [AuthController::class, 'me']);
    // Routes API pour le profil (utilisent des tokens)
    Route::post('/profile/update', [App\Http\Controllers\ProfileController::class, 'updateApi']);
    Route::post('/profile/change-password', [App\Http\Controllers\ProfileController::class, 'changePasswordApi']);
    Route::post('/profile/request-email-verification', [App\Http\Controllers\ProfileController::class, 'requestEmailVerificationApi']);
});

// Routes protégées par authentification (suite)
Route::middleware('auth:sanctum')->group(function () {
    // Route pour la photo de profil (API - Tokens pour Flutter)
    Route::post('/profile/update-photo', [App\Http\Controllers\ProfileController::class, 'updatePhotoApi']);
    // Route pour l'activité récente (API - Tokens pour Flutter)
    Route::get('/activity/recent', [App\Http\Controllers\ProfileController::class, 'getRecentActivityApi']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Autres routes API avec tokens uniquement
});

// Route de déconnexion publique (pour les tokens stockés côté client)
Route::post('/logout-client', [AuthController::class, 'logoutClient']);

// Routes du panier (API - Tokens uniquement, sans CSRF)
Route::prefix('cart')->middleware('auth:sanctum')->group(function () {
    Route::post('/add', [App\Http\Controllers\CartController::class, 'addApi']);
    Route::get('/items', [App\Http\Controllers\CartController::class, 'getCartApi']);
    Route::put('/update/{id}', [App\Http\Controllers\CartController::class, 'update']);
    Route::delete('/remove/{id}', [App\Http\Controllers\CartController::class, 'remove']);
    Route::delete('/clear', [App\Http\Controllers\CartController::class, 'clear']);
});

// Routes des favoris (public - fonctionne avec session ou user)
Route::prefix('favorites')->group(function () {
    Route::get('/', [App\Http\Controllers\CartController::class, 'getFavorites']);
    Route::post('/toggle', [App\Http\Controllers\CartController::class, 'toggleFavorite']);
});

// Route de mise à jour produit (API - sans CSRF pour test)
Route::post('/store/api/products/{id}/edit', [App\Http\Controllers\Seller\ProductController::class, 'updateProduct'])->name('store.api.products.edit');

// Routes de commande (API - Tokens pour Flutter)
// Pour mobile : utiliser auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders/my-orders', [App\Http\Controllers\OrderController::class, 'myOrders']);
    Route::get('/orders/count', [App\Http\Controllers\OrderController::class, 'getOrdersCount']);
    Route::get('/orders/{orderNumber}', [App\Http\Controllers\OrderController::class, 'getOrderDetails']);
    Route::post('/orders/{orderNumber}/cancel', [App\Http\Controllers\OrderController::class, 'cancelOrder']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/orders/create', [App\Http\Controllers\OrderController::class, 'createOrder']);
});

// Route de suivi de commande (publique)
Route::post('/track-order', [App\Http\Controllers\OrderController::class, 'trackOrder']);

// Routes des avis
Route::get('/products/{productId}/reviews', [App\Http\Controllers\ReviewController::class, 'getProductReviews']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'store']);
    Route::get('/reviews/my-reviews', [App\Http\Controllers\ReviewController::class, 'getMyReviews']);
    Route::get('/reviews/my-reviews-count', [App\Http\Controllers\ReviewController::class, 'getMyReviewsCount']);
});
Route::post('/reviews/{reviewId}/vote', [App\Http\Controllers\ReviewController::class, 'vote']);

// Coupons (public, stateless)
Route::post('/coupons/apply', [CouponController::class, 'apply']);

// KAZAR I.A
Route::post('/ai/query', [AIController::class, 'query']);
Route::post('/ai/interaction', [AIController::class, 'logInteraction'])->name('ai.interaction');
Route::get('/ai/suggestions', [AIController::class, 'getSuggestions'])->middleware('web');

// Route pour vérifier le statut de vendeur
Route::get('/check-seller-status', [App\Http\Controllers\ProfileController::class, 'checkSellerStatus'])->middleware('auth:sanctum');

// Route pour le formulaire de contact
Route::post('/contact', [App\Http\Controllers\ContactController::class, 'sendMessage']);

// Routes API pour le dashboard vendeur
Route::middleware('auth:sanctum')->prefix('store')->group(function () {
    Route::get('/info', [App\Http\Controllers\StoreController::class, 'getStoreInfo']);
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
    
    // Gestion des commandes vendeur (noms retirés car dupliqués dans web.php)
    Route::get('/orders', [App\Http\Controllers\Seller\OrderController::class, 'getOrders']);
    Route::get('/orders/stats', [App\Http\Controllers\Seller\OrderController::class, 'getOrderStats']);
    Route::get('/orders/{orderNumber}', [App\Http\Controllers\Seller\OrderController::class, 'getOrderDetails']);
    Route::put('/orders/{orderNumber}/status', [App\Http\Controllers\Seller\OrderController::class, 'updateOrderStatus']);
    Route::post('/orders/{orderNumber}/ship', [App\Http\Controllers\Seller\OrderController::class, 'markAsShipped']);
    Route::post('/orders/{orderNumber}/deliver', [App\Http\Controllers\Seller\OrderController::class, 'markAsDelivered']);
    Route::post('/orders/{orderNumber}/cancel', [App\Http\Controllers\Seller\OrderController::class, 'cancelOrder']);
    Route::put('/orders/{orderNumber}/payment-status', [App\Http\Controllers\Seller\OrderController::class, 'changePaymentStatus']);
    
    // Paramètres de la boutique
    Route::post('/update', [App\Http\Controllers\StoreController::class, 'updateStore']);
    Route::post('/upload-logo', [App\Http\Controllers\StoreController::class, 'uploadLogo']);
    Route::post('/upload-banner', [App\Http\Controllers\StoreController::class, 'uploadBanner']);
    Route::post('/update-social', [App\Http\Controllers\StoreController::class, 'updateSocialLinks']);
    Route::post('/toggle-status', [App\Http\Controllers\StoreController::class, 'toggleStatus']);
    Route::delete('/delete', [App\Http\Controllers\StoreController::class, 'deleteStore']);
});

// Route pour récupérer les sous-catégories d'une catégorie
Route::get('/categories/{categoryId}/subcategories', [App\Http\Controllers\Admin\CategoryController::class, 'getSubcategories']);

// Routes API mobiles
Route::prefix('mobile')->group(function () {
    Route::get('/home-data', [App\Http\Controllers\MobileController::class, 'getHomeData']);
    Route::get('/categories', [App\Http\Controllers\MobileController::class, 'getCategories']);
    Route::get('/categories/{id}', [App\Http\Controllers\MobileController::class, 'getCategoryDetails']);
    Route::get('/products', [App\Http\Controllers\MobileController::class, 'getProducts']);
    Route::get('/products/{id}', [App\Http\Controllers\MobileController::class, 'getProductDetails']);
    Route::get('/banners', [App\Http\Controllers\MobileController::class, 'getBanners']);
    Route::get('/stores', [App\Http\Controllers\MobileController::class, 'getStores']);
    Route::get('/stores/verified', [App\Http\Controllers\MobileController::class, 'getVerifiedStores']);
    Route::get('/stores/popular', [App\Http\Controllers\MobileController::class, 'getPopularStores']);
    Route::get('/stores/best-offers', [App\Http\Controllers\MobileController::class, 'getBestOffersStores']);
    Route::get('/stores/new-products', [App\Http\Controllers\MobileController::class, 'getNewProductsStores']);
    Route::get('/stores/{id}', [App\Http\Controllers\MobileController::class, 'getStoreDetails']);
    Route::get('/stores/{id}/products', [App\Http\Controllers\MobileController::class, 'getStoreProducts']);
    Route::get('/stores/boutique-pub-banners', [App\Http\Controllers\MobileController::class, 'getBoutiquePubBanners']);
    Route::get('/flash-sales', [App\Http\Controllers\MobileController::class, 'getFlashSales']);
    Route::get('/brands', [App\Http\Controllers\MobileController::class, 'getBrands']);
});

// Routes wishlists (Listes de souhaits)
Route::middleware('auth:sanctum')->prefix('wishlists')->group(function () {
    Route::get('/', [App\Http\Controllers\WishlistController::class, 'index']);
    Route::post('/', [App\Http\Controllers\WishlistController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\WishlistController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\WishlistController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\WishlistController::class, 'destroy']);
    Route::post('/{id}/products', [App\Http\Controllers\WishlistController::class, 'addProduct']);
    Route::delete('/{id}/products/{productId}', [App\Http\Controllers\WishlistController::class, 'removeProduct']);
    Route::post('/{id}/share', [App\Http\Controllers\WishlistController::class, 'share']);
});

// Route wishlist partagée (publique)
Route::get('/wishlists/shared/{token}', [App\Http\Controllers\WishlistController::class, 'getSharedWishlist']);

// Routes comparaison de produits
Route::middleware('auth:sanctum')->prefix('comparison')->group(function () {
    Route::post('/compare', [App\Http\Controllers\ComparisonController::class, 'compare']);
    Route::get('/', [App\Http\Controllers\ComparisonController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\ComparisonController::class, 'show']);
    Route::delete('/{id}', [App\Http\Controllers\ComparisonController::class, 'destroy']);
});

// Routes alertes de prix
Route::middleware('auth:sanctum')->prefix('price-alerts')->group(function () {
    Route::post('/', [App\Http\Controllers\WishlistController::class, 'createPriceAlert']);
    Route::get('/', [App\Http\Controllers\WishlistController::class, 'getPriceAlerts']);
    Route::delete('/{id}', [App\Http\Controllers\WishlistController::class, 'deletePriceAlert']);
});

// Routes historique paiements et factures
Route::middleware('auth:sanctum')->prefix('payments')->group(function () {
    Route::get('/history', [App\Http\Controllers\PaymentController::class, 'getPaymentHistory']);
    Route::get('/{id}', [App\Http\Controllers\PaymentController::class, 'getPaymentDetails']);
});

Route::middleware('auth:sanctum')->prefix('invoices')->group(function () {
    Route::get('/history', [App\Http\Controllers\PaymentController::class, 'getInvoiceHistory']);
    Route::get('/{id}', [App\Http\Controllers\PaymentController::class, 'downloadInvoice']);
});

// Route inbox (messages/notifications)
Route::middleware('auth:sanctum')->get('/inbox', [App\Http\Controllers\ProfileController::class, 'getInboxApi']);

// Routes configuration application
Route::get('/app/config', [App\Http\Controllers\MobileController::class, 'getAppConfig']);
Route::get('/app/logo', [App\Http\Controllers\MobileController::class, 'getAppLogo']);
Route::get('/app/contact', [App\Http\Controllers\MobileController::class, 'getAppContact']);

// Routes de partage (share)
Route::middleware('auth:sanctum')->prefix('share')->group(function () {
    Route::get('/product/{productId}/link', [App\Http\Controllers\ShareController::class, 'getProductShareLink']);
    Route::get('/store/{storeId}/link', [App\Http\Controllers\ShareController::class, 'getStoreShareLink']);
});

