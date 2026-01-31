import 'package:flutter/material.dart';
import '../../models/product_model.dart';
import '../../utils/constants.dart';
import '../../widgets/product_card.dart';
import '../../screens/products/product_details_screen.dart';
import '../../screens/products/products_list_screen.dart';
import '../skeletons/product_card_skeleton.dart';

/// Section de produits horizontale pour la page d'accueil
class ProductSection extends StatelessWidget {
  final String title;
  final List<ProductModel> products;
  final IconData icon;
  final String? category;
  final bool isLoading;
  final int skeletonCount;

  const ProductSection({
    super.key,
    required this.title,
    required this.products,
    required this.icon,
    this.category,
    this.isLoading = false,
    this.skeletonCount = 4,
  });

  @override
  Widget build(BuildContext context) {
    if (isLoading && products.isEmpty) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: AppSizes.paddingMedium,
              ),
              child: Row(
                children: [
                  Icon(icon, color: AppColors.primary),
                  const SizedBox(width: 8),
                  Text(
                    title,
                    style: AppTextStyles.sectionTitle,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              height: 280,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(
                  horizontal: AppSizes.paddingMedium,
                ),
                itemCount: skeletonCount,
                itemBuilder: (context, index) {
                  return const ProductCardSkeleton();
                },
              ),
            ),
          ],
        ),
      );
    }

    if (products.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      margin: const EdgeInsets.only(bottom: AppSizes.space4),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(0),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // En-tête avec titre et bouton "Voir tout"
            Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: AppSizes.paddingMedium,
              ),
              child: Row(
              children: [
                Icon(icon, color: AppColors.primary, size: 20), // ✅ Taille fixe pour l'icône
                const SizedBox(width: 8),
                Expanded( // ✅ Utiliser Expanded pour éviter l'overflow
                  child: Text(
                    title,
                    style: AppTextStyles.h3,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis, // ✅ Tronquer si trop long
                  ),
                ),
                TextButton(
                  onPressed: () {
                    if (category != null) {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => ProductsListScreen(
                            title: title,
                            category: category!,
                            icon: icon,
                          ),
                        ),
                      );
                    }
                  },
                  child: const Text('Voir tout'),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          
          // Liste horizontale de produits
          SizedBox(
            height: 280,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(
                horizontal: AppSizes.paddingMedium,
              ),
              itemCount: products.length,
              itemBuilder: (context, index) {
                final product = products[index];
                return Container(
                  width: 180,
                  margin: const EdgeInsets.only(right: 12),
                  child: ProductCard(
                    product: product,
                    heroTag: 'section_${title.hashCode}_${product.id}_$index',
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => ProductDetailsScreen(
                            product: product,
                            heroTag:
                                'section_${title.hashCode}_${product.id}_$index',
                          ),
                        ),
                      );
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
      ),
    );
  }
}

