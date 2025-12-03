import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/product_provider.dart';
import '../../providers/cart_provider.dart';
import '../../widgets/modern_product_card.dart';
import '../../screens/products/products_list_screen.dart';
import '../../screens/products/product_details_screen.dart';
import '../../utils/constants.dart';

/// Section "Just For You" - Recommandations personnalisées
class JustForYouSection extends StatelessWidget {
  const JustForYouSection({super.key});

  @override
  Widget build(BuildContext context) {
    final productProvider = Provider.of<ProductProvider>(context);
    
    // Mélanger les produits pour simuler des recommandations
    final allProducts = [
      ...productProvider.featuredProducts,
      ...productProvider.trendingProducts,
      ...productProvider.newProducts,
    ];
    
    // Prendre aléatoirement 8 produits
    allProducts.shuffle();
    final recommendedProducts = allProducts.take(8).toList();

    if (recommendedProducts.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      margin: const EdgeInsets.symmetric(
        horizontal: AppSizes.space4,
        vertical: AppSizes.space4,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(AppSizes.space2),
                decoration: BoxDecoration(
                  gradient: AppColors.primaryGradient,
                  borderRadius: BorderRadius.circular(AppSizes.radiusMD),
                ),
                child: const Icon(
                  Icons.recommend,
                  color: AppColors.white,
                  size: 24,
                ),
              ),
              const SizedBox(width: AppSizes.space3),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Just For You',
                      style: AppTextStyles.h3.copyWith(
                        fontWeight: FontWeight.bold,
                        color: AppColors.textDark,
                      ),
                    ),
                    Text(
                      'Sélection personnalisée',
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.textMuted,
                      ),
                    ),
                  ],
                ),
              ),
              TextButton(
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => ProductsListScreen(
                        title: 'Recommandations',
                        category: 'all',
                        icon: Icons.recommend,
                      ),
                    ),
                  );
                },
                child: Row(
                  children: [
                    Text(
                      'Voir tout',
                      style: AppTextStyles.bodyMedium.copyWith(
                        color: AppColors.primary,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(width: 4),
                    const Icon(
                      Icons.arrow_forward_ios,
                      size: 14,
                      color: AppColors.primary,
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: AppSizes.space4),

          // Grille de produits
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: AppSizes.space3,
              mainAxisSpacing: AppSizes.space3,
              childAspectRatio: 0.75,
            ),
            itemCount: recommendedProducts.length,
            itemBuilder: (context, index) {
              final product = recommendedProducts[index];
              final cartProvider = Provider.of<CartProvider>(context, listen: false);
              
              return ModernProductCard(
                product: product,
                showQuickBuyButton: true, // ✅ Activer le bouton d'achat rapide
                onTap: () {
                  // ✅ Navigation vers les détails du produit
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => ProductDetailsScreen(product: product),
                    ),
                  );
                },
                onQuickBuy: () async {
                  // ✅ Ajouter au panier directement
                  if (product.stock > 0) {
                    try {
                      await cartProvider.addToCart(
                        product: product,
                        quantity: 1,
                      );
                      
                      // Afficher une confirmation
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Row(
                              children: [
                                const Icon(
                                  Icons.check_circle,
                                  color: Colors.white,
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Text(
                                    '${product.name} ajouté au panier',
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ],
                            ),
                            backgroundColor: AppColors.success,
                            duration: const Duration(seconds: 2),
                            behavior: SnackBarBehavior.floating,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            action: SnackBarAction(
                              label: 'Voir',
                              textColor: Colors.white,
                              onPressed: () {
                                // Optionnel : naviguer vers le panier
                              },
                            ),
                          ),
                        );
                      }
                    } catch (e) {
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('Erreur: ${e.toString()}'),
                            backgroundColor: AppColors.error,
                            duration: const Duration(seconds: 2),
                          ),
                        );
                      }
                    }
                  }
                },
              );
            },
          ),
        ],
      ),
    );
  }
}

