import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/cart_provider.dart';
import '../../providers/auth_provider.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';
import '../../widgets/cart_item_card.dart';
import '../../widgets/recent_products_section.dart';
import '../../services/recent_products_service.dart';
import '../../models/product_model.dart';
import '../products/product_details_screen.dart';
import '../products/recent_products_screen.dart';
import '../checkout/checkout_screen.dart';
import '../auth/login_screen.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  final TextEditingController _promoController = TextEditingController();
  List<ProductModel> _recentProducts = [];
  bool _isApplyingPromo = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      if (authProvider.isAuthenticated) {
        Provider.of<CartProvider>(context, listen: false).loadCart();
      }
      _loadRecentProducts();
    });
  }

  Future<void> _loadRecentProducts() async {
    final recentProducts = await RecentProductsService.getRecentProducts();
    if (mounted) {
      setState(() {
        _recentProducts = recentProducts;
      });
    }
  }

  @override
  void dispose() {
    _promoController.dispose();
    super.dispose();
  }

  Future<void> _applyPromoCode() async {
    if (_promoController.text.trim().isEmpty) {
      _showSnackBar('Veuillez entrer un code promo', isError: true);
      return;
    }

    setState(() {
      _isApplyingPromo = true;
    });

    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    final response = await cartProvider.applyPromoCode(_promoController.text.trim());

    setState(() {
      _isApplyingPromo = false;
    });

    if (response['success']) {
      _showSnackBar(response['message'] ?? 'Code promo appliqué');
      _promoController.clear();
    } else {
      _showSnackBar(response['message'] ?? 'Code promo invalide', isError: true);
    }
  }

  Future<void> _removePromoCode() async {
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    final response = await cartProvider.removePromoCode();

    if (response['success']) {
      _showSnackBar(response['message'] ?? 'Code promo retiré');
    }
  }

  void _showSnackBar(String message, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? AppColors.error : AppColors.success,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final cartProvider = Provider.of<CartProvider>(context);

    if (!authProvider.isAuthenticated) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Mon panier'),
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.shopping_cart_outlined,
                size: 100,
                color: AppColors.textLight.withOpacity(0.5),
              ),
              const SizedBox(height: 24),
              Text(
                'Connectez-vous pour voir votre panier',
                style: AppTextStyles.h3.copyWith(
                  color: AppColors.textLight,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                  );
                },
                child: const Text('Se connecter'),
              ),
            ],
          ),
        ),
      );
    }

    if (cartProvider.isLoading && cartProvider.items.isEmpty) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Mon panier'),
        ),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    if (cartProvider.items.isEmpty) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Mon panier'),
        ),
        body: SingleChildScrollView(
          child: Column(
            children: [
              // Section panier vide
              Container(
                height: MediaQuery.of(context).size.height * 0.4,
                child: Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.shopping_cart_outlined,
                        size: 100,
                        color: AppColors.textLight.withOpacity(0.5),
                      ),
                      const SizedBox(height: 24),
                      Text(
                        'Votre panier est vide',
                        style: AppTextStyles.h3.copyWith(
                          color: AppColors.textLight,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Text(
                        'Ajoutez des produits pour commencer',
                        style: AppTextStyles.body.copyWith(
                          color: AppColors.textLight,
                        ),
                      ),
                      const SizedBox(height: 24),
                      ElevatedButton.icon(
                        onPressed: () {
                          Navigator.pop(context);
                        },
                        icon: const Icon(Icons.shopping_bag),
                        label: const Text('Continuer mes achats'),
                      ),
                    ],
                  ),
                ),
              ),
              
              // Section produits récemment vus
              if (_recentProducts.isNotEmpty)
                RecentProductsSection(
                  products: _recentProducts,
                  onViewAll: () {
                    // Naviguer vers l'écran des produits récemment vus
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => const RecentProductsScreen(),
                      ),
                    );
                  },
                ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text('Mon panier (${cartProvider.itemCount})'),
        actions: [
          if (cartProvider.items.isNotEmpty)
            IconButton(
              icon: const Icon(Icons.delete_outline),
              onPressed: () async {
                final confirm = await showDialog<bool>(
                  context: context,
                  builder: (context) => AlertDialog(
                    title: const Text('Vider le panier'),
                    content: const Text('Voulez-vous vraiment vider votre panier ?'),
                    actions: [
                      TextButton(
                        onPressed: () => Navigator.pop(context, false),
                        child: const Text('Annuler'),
                      ),
                      TextButton(
                        onPressed: () => Navigator.pop(context, true),
                        style: TextButton.styleFrom(
                          foregroundColor: AppColors.error,
                        ),
                        child: const Text('Vider'),
                      ),
                    ],
                  ),
                );

                if (confirm == true && mounted) {
                  final response = await cartProvider.clearCart();
                  if (mounted) {
                    _showSnackBar(
                      response['message'] ?? 'Panier vidé',
                      isError: !response['success'],
                    );
                  }
                }
              },
            ),
        ],
      ),
      body: Column(
        children: [
          // Liste des produits avec animations
          Expanded(
            child: RefreshIndicator(
              onRefresh: cartProvider.loadCart,
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                physics: const BouncingScrollPhysics(),
                itemCount: cartProvider.items.length,
                itemBuilder: (context, index) {
                  final item = cartProvider.items[index];
                  return CartItemCard(
                    item: item,
                    onTap: () {
                      if (item.product != null) {
                        // ✅ Passer les attributs sélectionnés au ProductDetailsScreen
                        final selectedAttrs = item.attributes != null 
                            ? Map<String, String>.from(
                                item.attributes!.map((k, v) => MapEntry(k.toString(), v.toString()))
                              )
                            : null;
                        
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => ProductDetailsScreen(
                              product: item.product!,
                              selectedAttributes: selectedAttrs, // ✅ Pré-sélectionner les options
                            ),
                          ),
                        );
                      }
                    },
                    onQuantityChanged: (newQuantity) async {
                      final response = await cartProvider.updateQuantity(
                        item.id,
                        newQuantity,
                      );
                      if (!context.mounted) return;
                      if (!response['success']) {
                        _showSnackBar(
                          response['message'] ?? 'Erreur',
                          isError: true,
                        );
                      }
                    },
                    onRemove: () async {
                      final response = await cartProvider.removeFromCart(item.id);
                      if (!context.mounted) return;
                      _showSnackBar(
                        response['message'] ?? 'Produit retiré',
                        isError: !response['success'],
                      );
                    },
                  );
                },
              ),
            ),
          ),

          // Code promo
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: AppColors.background,
              border: Border(
                top: BorderSide(color: AppColors.border),
              ),
            ),
            child: cartProvider.promoCode != null
                ? Row(
                    children: [
                      const Icon(Icons.local_offer, color: AppColors.success),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          'Code promo: ${cartProvider.promoCode}',
                          style: AppTextStyles.body.copyWith(
                            fontWeight: FontWeight.bold,
                            color: AppColors.success,
                          ),
                        ),
                      ),
                      TextButton(
                        onPressed: _removePromoCode,
                        child: const Text('Retirer'),
                      ),
                    ],
                  )
                : Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: _promoController,
                          decoration: const InputDecoration(
                            hintText: 'Code promo',
                            prefixIcon: Icon(Icons.local_offer),
                            border: OutlineInputBorder(),
                            contentPadding: EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 12,
                            ),
                          ),
                          textInputAction: TextInputAction.done,
                          onSubmitted: (_) => _applyPromoCode(),
                        ),
                      ),
                      const SizedBox(width: 12),
                      ElevatedButton(
                        onPressed: _isApplyingPromo ? null : _applyPromoCode,
                        child: _isApplyingPromo
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(strokeWidth: 2),
                              )
                            : const Text('Appliquer'),
                      ),
                    ],
                  ),
          ),

          // Résumé
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.1),
                  blurRadius: 10,
                  offset: const Offset(0, -2),
                ),
              ],
            ),
            child: SafeArea(
              child: Column(
                children: [
                  _buildSummaryRow('Sous-total', cartProvider.subtotal),
                  if (cartProvider.shippingCost > 0)
                    _buildSummaryRow('Livraison', cartProvider.shippingCost),
                  if (cartProvider.discount > 0)
                    _buildSummaryRow(
                      'Réduction',
                      -cartProvider.discount,
                      color: AppColors.success,
                    ),
                  const Divider(height: 24),
                  _buildSummaryRow(
                    'Total',
                    cartProvider.total,
                    isBold: true,
                    isLarge: true,
                  ),
                  const SizedBox(height: 16),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const CheckoutScreen(),
                          ),
                        );
                      },
                      style: ElevatedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 16),
                      ),
                      child: const Text(
                        'Passer la commande',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }


  Widget _buildSummaryRow(
    String label,
    double amount, {
    bool isBold = false,
    bool isLarge = false,
    Color? color,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: isLarge ? 18 : 14,
              fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
              color: color,
            ),
          ),
          Text(
            Helpers.formatPrice(amount.abs()),
            style: TextStyle(
              fontSize: isLarge ? 20 : 16,
              fontWeight: isBold ? FontWeight.bold : FontWeight.w600,
              color: color ?? AppColors.textDark,
            ),
          ),
        ],
      ),
    );
  }
}
