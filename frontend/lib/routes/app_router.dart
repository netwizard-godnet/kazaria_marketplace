import 'package:flutter/material.dart';
import '../screens/splash_screen.dart';
import '../screens/main_screen.dart';
import '../screens/auth/welcome_screen.dart';
import '../screens/auth/login_screen.dart';
import '../screens/auth/register_screen.dart';
import '../screens/auth/forgot_password_screen.dart';
// import '../screens/auth/verify_code_screen.dart'; // Désactivé - code de vérification non obligatoire
import '../screens/auth/verify_code_screen.dart'; // Import minimal pour éviter erreurs de compilation
import '../models/category_model.dart';
import '../models/product_model.dart';
import '../models/store_model.dart';
import '../models/order_model.dart';
import '../models/payment_method.dart';

// Home & Main
import '../screens/home/home_screen.dart';
import '../screens/categories/categories_screen.dart';
import '../screens/categories/category_products_screen.dart';
import '../screens/stores/stores_screen.dart';
import '../screens/cart/cart_screen.dart';
import '../screens/profile/profile_screen.dart';

// Products
import '../screens/products/product_details_screen.dart';
import '../screens/products/products_list_screen.dart';
import '../screens/products/image_gallery_screen.dart';
import '../screens/products/recent_products_screen.dart';
import '../screens/products/best_offers_list_screen.dart';

// Store
import '../screens/store/store_details_screen.dart';

// Checkout & Orders
import '../screens/checkout/checkout_screen.dart';
import '../screens/profile/my_orders_screen.dart';
import '../screens/profile/order_details_screen.dart';
import '../screens/profile/track_order_screen.dart';

// Payment
import '../screens/payment/payment_method_selection_screen.dart';
import '../screens/payment/mobile_money_payment_screen.dart';
import '../screens/payment/payment_history_screen.dart';

// Profile & Settings
import '../screens/profile/edit_profile_screen.dart';
import '../screens/profile/change_password_screen.dart';
import '../screens/profile/addresses_screen.dart';
import '../screens/profile/add_address_screen.dart';
import '../screens/profile/favorites_screen.dart';
import '../screens/profile/payments_screen.dart';
import '../screens/profile/invoice_history_screen.dart';
import '../screens/profile/language_screen.dart';

// Seller
import '../screens/seller/seller_dashboard_screen.dart';
import '../screens/seller/seller_register_screen.dart';
import '../screens/seller/seller_login_screen.dart';
import '../screens/seller/seller_verify_code_screen.dart';
import '../screens/seller/seller_store_settings_screen.dart';
import '../screens/seller/seller_store_settings_complete_screen.dart';
import '../screens/seller/seller_products_screen.dart';
import '../screens/seller/seller_orders_screen.dart';
import '../screens/seller/add_product_screen.dart';
import '../screens/seller/edit_product_screen.dart';

// Wishlist
import '../screens/wishlist/wishlists_screen.dart';
import '../screens/wishlist/wishlist_details_screen.dart';
import '../screens/wishlist/wishlist_alerts_screen.dart';
import '../screens/wishlist/wishlist_notification_preferences_screen.dart';
import '../screens/wishlist/wishlist_share_management_screen.dart';

// Comparison
import '../screens/comparison/product_comparison_screen.dart';
import '../screens/comparison/comparison_history_screen.dart';

// Reviews
import '../screens/reviews/reviews_screen.dart';

// Search
import '../screens/search/search_screen.dart';

// AI & Chat
import '../screens/ai/ai_chat_screen.dart';

// Help & Support
import '../screens/help/help_screen.dart';
import '../screens/help/contact_screen.dart';

// Notifications
import '../screens/notifications/notifications_screen.dart';

// Promotions
import '../screens/promotions/flash_sales_screen.dart';
import '../screens/promotions/black_friday_screen.dart';

// WebView
import '../screens/webview/webview_screen.dart';

/// Classe pour gérer toutes les routes de l'application
class AppRouter {
  // Noms des routes
  static const String splash = '/';
  static const String welcome = '/welcome';
  static const String login = '/login';
  static const String register = '/register';
  static const String forgotPassword = '/forgot-password';
  static const String verifyCode = '/verify-code';
  
  // Main Navigation
  static const String main = '/main';
  static const String home = '/home';
  static const String categories = '/categories';
  static const String categoryProducts = '/category-products';
  static const String stores = '/stores';
  static const String cart = '/cart';
  static const String profile = '/profile';
  
  // Products
  static const String productDetails = '/product-details';
  static const String productsList = '/products-list';
  static const String imageGallery = '/image-gallery';
  static const String recentProducts = '/recent-products';
  static const String bestOffers = '/best-offers';
  
  // Store
  static const String storeDetails = '/store-details';
  
  // Checkout & Orders
  static const String checkout = '/checkout';
  static const String myOrders = '/my-orders';
  static const String orderDetails = '/order-details';
  static const String trackOrder = '/track-order';
  
  // Payment
  static const String paymentMethodSelection = '/payment-method-selection';
  static const String mobileMoneyPayment = '/mobile-money-payment';
  static const String paymentHistory = '/payment-history';
  
  // Profile & Settings
  static const String editProfile = '/edit-profile';
  static const String changePassword = '/change-password';
  static const String addresses = '/addresses';
  static const String addAddress = '/add-address';
  static const String favorites = '/favorites';
  static const String payments = '/payments';
  static const String invoiceHistory = '/invoice-history';
  static const String notifications = '/notifications';
  static const String language = '/language';
  
  // Seller
  static const String sellerDashboard = '/seller/dashboard';
  static const String sellerRegister = '/seller/register';
  static const String sellerLogin = '/seller/login';
  static const String sellerVerifyCode = '/seller/verify-code';
  static const String sellerStoreSettings = '/seller/store-settings';
  static const String sellerStoreSettingsComplete = '/seller/store-settings-complete';
  static const String sellerProducts = '/seller/products';
  static const String sellerOrders = '/seller/orders';
  static const String addProduct = '/seller/add-product';
  static const String editProduct = '/seller/edit-product';
  
  // Wishlist
  static const String wishlists = '/wishlists';
  static const String wishlistDetails = '/wishlist-details';
  static const String wishlistAlerts = '/wishlist-alerts';
  static const String wishlistNotificationPreferences = '/wishlist-notification-preferences';
  static const String wishlistShareManagement = '/wishlist-share-management';
  
  // Comparison
  static const String productComparison = '/product-comparison';
  static const String comparisonHistory = '/comparison-history';
  
  // Reviews
  static const String reviews = '/reviews';
  
  // Search
  static const String search = '/search';
  
  // AI & Chat
  static const String aiChat = '/ai-chat';
  
  // Help & Support
  static const String help = '/help';
  static const String contact = '/contact';
  
  // Promotions
  static const String flashSales = '/flash-sales';
  static const String blackFriday = '/black-friday';
  
  // WebView
  static const String webview = '/webview';
  
  /// Génère les routes de l'application
  static Map<String, WidgetBuilder> getRoutes() {
    return {
      // Auth
      splash: (context) => const SplashScreen(),
      welcome: (context) => const WelcomeScreen(),
      login: (context) => const LoginScreen(),
      register: (context) => const RegisterScreen(),
      forgotPassword: (context) => const ForgotPasswordScreen(),
      // ⚠️ Écran de vérification désactivé - voir verify_code_screen.dart
      verifyCode: (context) {
        // Rediriger vers l'écran principal si quelqu'un essaie d'accéder à cet écran
        WidgetsBinding.instance.addPostFrameCallback((_) {
          Navigator.of(context).pushReplacementNamed('/');
        });
        return const Scaffold(
          body: Center(child: CircularProgressIndicator()),
        );
      },
      
      // Main Navigation
      main: (context) => const MainScreen(),
      home: (context) => const HomeScreen(),
      categories: (context) => const CategoriesScreen(),
      categoryProducts: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        final category = args?['category'] as CategoryModel?;
        if (category == null) {
          return const Scaffold(
            body: Center(child: Text('Catégorie introuvable')),
          );
        }
        return CategoryProductsScreen(category: category);
      },
      stores: (context) => const StoresScreen(),
      cart: (context) => const CartScreen(),
      profile: (context) => const ProfileScreen(),
      
      // Products
      productDetails: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        final product = args?['product'] as ProductModel?;
        if (product == null) {
          return const Scaffold(
            body: Center(child: Text('Produit introuvable')),
          );
        }
        return ProductDetailsScreen(
          product: product,
          selectedAttributes: args?['selectedAttributes'] as Map<String, String>?,
          heroTag: args?['heroTag'] as String?,
        );
      },
      productsList: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        return ProductsListScreen(
          title: args?['title'] as String? ?? 'Produits',
          category: args?['category'] as String? ?? '',
          icon: args?['icon'] as IconData? ?? Icons.shopping_bag,
        );
      },
      imageGallery: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        return ImageGalleryScreen(
          images: args?['images'] as List<String>? ?? [],
          initialIndex: args?['initialIndex'] as int? ?? 0,
        );
      },
      recentProducts: (context) => const RecentProductsScreen(),
      bestOffers: (context) => const BestOffersListScreen(),
      
      // Store
      storeDetails: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        final store = args?['store'] as StoreModel?;
        if (store == null) {
          return const Scaffold(
            body: Center(child: Text('Boutique introuvable')),
          );
        }
        return StoreDetailsScreen(store: store);
      },
      
      // Checkout & Orders
      checkout: (context) => const CheckoutScreen(),
      myOrders: (context) => const MyOrdersScreen(),
      orderDetails: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        final order = args?['order'] as OrderModel?;
        if (order == null) {
          return const Scaffold(
            body: Center(child: Text('Commande introuvable')),
          );
        }
        return OrderDetailsScreen(order: order);
      },
      trackOrder: (context) => const TrackOrderScreen(),
      
      // Payment
      paymentMethodSelection: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        return PaymentMethodSelectionScreen(
          orderId: args?['orderId'] as String? ?? '',
          amount: args?['amount'] as double? ?? 0.0,
          currency: args?['currency'] as String? ?? 'XOF',
          selectionOnly: args?['selectionOnly'] as bool? ?? false,
        );
      },
      mobileMoneyPayment: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        final paymentMethod = args?['paymentMethod'] as PaymentMethod?;
        if (paymentMethod == null) {
          return const Scaffold(
            body: Center(child: Text('Méthode de paiement introuvable')),
          );
        }
        return MobileMoneyPaymentScreen(
          orderId: args?['orderId'] as String? ?? '',
          amount: args?['amount'] as double? ?? 0.0,
          currency: args?['currency'] as String? ?? 'XOF',
          paymentMethod: paymentMethod,
        );
      },
      paymentHistory: (context) => const PaymentHistoryScreen(),
      
      // Profile & Settings
      editProfile: (context) => const EditProfileScreen(),
      changePassword: (context) => const ChangePasswordScreen(),
      addresses: (context) => const AddressesScreen(),
      addAddress: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        return AddAddressScreen(
          address: args?['address'] as Map<String, dynamic>?,
        );
      },
      favorites: (context) => const FavoritesScreen(),
      payments: (context) => const PaymentsScreen(),
      invoiceHistory: (context) => const InvoiceHistoryScreen(),
      notifications: (context) {
        // Utiliser l'écran de notifications principal (pas celui du profil)
        return const NotificationsScreen();
      },
      language: (context) => const LanguageScreen(),
      
      // Seller
      sellerDashboard: (context) => const SellerDashboardScreen(),
      sellerRegister: (context) => const SellerRegisterScreen(),
      sellerLogin: (context) => const SellerLoginScreen(),
      sellerVerifyCode: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        return SellerVerifyCodeScreen(
          email: args?['email'] ?? '',
        );
      },
      sellerStoreSettings: (context) => const SellerStoreSettingsScreen(),
      sellerStoreSettingsComplete: (context) => const SellerStoreSettingsCompleteScreen(),
      sellerProducts: (context) => const SellerProductsScreen(),
      sellerOrders: (context) => const SellerOrdersScreen(),
      addProduct: (context) => const AddProductScreen(),
      editProduct: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        return EditProductScreen(
          productId: args?['productId'] as int? ?? 0,
        );
      },
      
      // Wishlist
      wishlists: (context) => const WishlistsScreen(),
      wishlistDetails: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        final wishlistId = args?['wishlistId'] as int?;
        final wishlistName = args?['wishlistName'] as String?;
        if (wishlistId == null || wishlistName == null) {
          return const Scaffold(
            body: Center(child: Text('Wishlist introuvable')),
          );
        }
        return WishlistDetailsScreen(
          wishlistId: wishlistId,
          wishlistName: wishlistName,
        );
      },
      wishlistAlerts: (context) => const WishlistAlertsScreen(),
      wishlistNotificationPreferences: (context) => const WishlistNotificationPreferencesScreen(),
      wishlistShareManagement: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        final wishlistId = args?['wishlistId'] as int?;
        final wishlistName = args?['wishlistName'] as String?;
        if (wishlistId == null || wishlistName == null) {
          return const Scaffold(
            body: Center(child: Text('Wishlist introuvable')),
          );
        }
        return WishlistShareManagementScreen(
          wishlistId: wishlistId,
          wishlistName: wishlistName,
        );
      },
      
      // Comparison
      productComparison: (context) => const ProductComparisonScreen(),
      comparisonHistory: (context) => const ComparisonHistoryScreen(),
      
      // Reviews
      reviews: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        return ReviewsScreen(
          productId: args?['productId'] as int? ?? 0,
        );
      },
      
      // Search
      search: (context) => const SearchScreen(),
      
      // AI & Chat
      aiChat: (context) => const AiChatScreen(),
      
      // Help & Support
      help: (context) => const HelpScreen(),
      contact: (context) => const ContactScreen(),
      
      // Promotions
      flashSales: (context) => const FlashSalesScreen(),
      blackFriday: (context) => const BlackFridayScreen(),
      
      // WebView
      webview: (context) {
        final args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>?;
        return WebViewScreen(
          url: args?['url'] as String? ?? '',
          title: args?['title'] as String? ?? '',
        );
      },
    };
  }
  
  /// Configuration de la page de route inconnue
  static Widget unknownRoute(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Page introuvable'),
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.error_outline,
              size: 64,
              color: Colors.grey,
            ),
            const SizedBox(height: 16),
            const Text(
              'Page introuvable',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'La page que vous recherchez n\'existe pas.',
              style: TextStyle(color: Colors.grey),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: () {
                Navigator.of(context).pushReplacementNamed(main);
              },
              child: const Text('Retour à l\'accueil'),
            ),
          ],
        ),
      ),
    );
  }
}
