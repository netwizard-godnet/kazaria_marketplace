class ApiConfig {
  // URL de base de votre API Laravel
  // Changez cette URL selon votre environnement
  static const String baseUrl = 'http://10.0.2.2:8000/api';
  // Pour Android Emulator utiliser: http://10.0.2.2:8000/api
  // Pour appareil physique utiliser: http://YOUR_LOCAL_IP:8000/api

  static const String imageBaseUrl = 'http://10.0.2.2:8000';

  // Assistant IA
  static const String aiQuery = '$baseUrl/ai/query';
  static const String aiSuggestions = '$baseUrl/ai/suggestions';
  static const String aiInteraction = '$baseUrl/ai/interaction';

  // Endpoints d'authentification
  static const String register = '$baseUrl/register';
  static const String login = '$baseUrl/login';
  static const String verifyLoginCode = '$baseUrl/verify-login-code';
  static const String forgotPassword = '$baseUrl/forgot-password';
  static const String resetPassword = '$baseUrl/reset-password';
  static const String resendVerificationCode =
      '$baseUrl/resend-verification-code';
  static const String logout = '$baseUrl/logout';
  static const String me = '$baseUrl/me';
  static String socialAuth(String provider) => '$baseUrl/auth/social/$provider';

  // Endpoints profil
  static const String profileUpdate = '$baseUrl/profile/update';
  static const String changePassword = '$baseUrl/profile/change-password';
  static const String updatePhoto = '$baseUrl/profile/update-photo';
  static const String recentActivity = '$baseUrl/activity/recent';
  static const String inbox = '$baseUrl/inbox';
  static const String checkSellerStatus = '$baseUrl/check-seller-status';

  // Endpoints panier
  static const String cart = '$baseUrl/cart';
  static const String cartAdd = '$baseUrl/cart/add';
  static const String cartUpdate = '$baseUrl/cart/update';
  static const String cartRemove = '$baseUrl/cart/remove';
  static const String cartClear = '$baseUrl/cart/clear';

  // Endpoints favoris
  static const String favorites = '$baseUrl/favorites';
  static const String toggleFavorite = '$baseUrl/favorites/toggle';

  // Endpoints commandes
  static const String createOrder = '$baseUrl/orders/create';
  static const String myOrders = '$baseUrl/orders/my-orders';
  static const String getUserOrders = '$baseUrl/orders/my-orders';
  static const String orderDetails = '$baseUrl/orders';
  static const String trackOrder = '$baseUrl/track-order';
  static const String downloadInvoice = '$baseUrl/orders';

  // Endpoints avis
  static const String reviews = '$baseUrl/reviews';
  static const String reviewVote = '$baseUrl/reviews';
  static const String myReviews = '$baseUrl/reviews/my-reviews';
  static const String myReviewsCount = '$baseUrl/reviews/my-reviews-count';

  // Endpoints coupons
  static const String applyCoupon = '$baseUrl/coupons/apply';

  // Endpoints boutiques (pour les clients)
  static const String stores = '$baseUrl/stores';
  static const String storeDetails = '$baseUrl/stores';
  static const String storeProducts = '$baseUrl/stores';
  static const String popularStores = '$baseUrl/stores/popular';
  static const String verifiedStores = '$baseUrl/stores/verified';
  static const String searchStores = '$baseUrl/stores/search';

  // Endpoints boutique vendeur
  static const String storeStats = '$baseUrl/store/stats';
  static const String storeRecentOrders = '$baseUrl/store/recent-orders';
  static const String storeProductsVendor = '$baseUrl/store/products';
  static const String storeOrders = '$baseUrl/store/orders';
  static const String storeUpdate = '$baseUrl/store/update';
  static const String storeUploadLogo = '$baseUrl/store/upload-logo';
  static const String storeUploadBanner = '$baseUrl/store/upload-banner';
  static const String storeUpdateSocial = '$baseUrl/store/update-social';
  static const String storeToggleStatus = '$baseUrl/store/toggle-status';
  static const String storeDelete = '$baseUrl/store/delete';

  // Endpoints catégories
  static const String categories = '$baseUrl/categories';

  // Endpoints contact
  static const String contact = '$baseUrl/contact';

  // Endpoints configuration de l'application
  static const String appConfig = '$baseUrl/app/config';
  static const String appLogo = '$baseUrl/app/logo';
  static const String appContact = '$baseUrl/app/contact';

  // Endpoints pour l'application mobile
  static const String mobileHomeData = '$baseUrl/mobile/home-data';
  static const String mobileCategories = '$baseUrl/mobile/categories';
  static const String mobileProducts = '$baseUrl/mobile/products';
  static const String mobileProductDetails = '$baseUrl/mobile/products';
  static const String mobileBanners = '$baseUrl/mobile/banners';
  static const String mobileStores = '$baseUrl/mobile/stores';
  static const String mobileStoreDetails = '$baseUrl/mobile/stores';
  static const String mobileFlashSales = '$baseUrl/mobile/flash-sales';
  static const String mobileBrands = '$baseUrl/mobile/brands';

  // Endpoints vendeur/boutique
  static const String sellerStats = '$baseUrl/store/stats';
  static const String sellerRecentOrders = '$baseUrl/store/recent-orders';
  static const String sellerProducts = '$baseUrl/store/products';
  static const String sellerCreateProduct = '$baseUrl/store/products';
  static const String sellerOrders = '$baseUrl/store/orders';
  static const String sellerStoreInfo = '$baseUrl/store/info';
  static const String sellerUpdateStore = '$baseUrl/store/update';

  // Endpoints wishlists
  static const String wishlists = '$baseUrl/wishlists';
  static const String wishlistsShared = '$baseUrl/wishlists/shared';

  // Endpoints comparaison
  static const String comparison = '$baseUrl/comparison';
  static const String comparisonCompare = '$baseUrl/comparison/compare';

  // Endpoints alertes de prix
  static const String priceAlerts = '$baseUrl/price-alerts';

  // Endpoints historique paiements et factures
  static const String paymentHistory = '$baseUrl/payments/history';
  static const String paymentDetails = '$baseUrl/payments';
  static const String invoiceHistory = '$baseUrl/invoices/history';
  static const String invoiceDownload = '$baseUrl/invoices';

  // Headers
  static Map<String, String> headers({String? token}) {
    final Map<String, String> headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (token != null) {
      headers['Authorization'] = 'Bearer $token';
    }

    return headers;
  }
}
