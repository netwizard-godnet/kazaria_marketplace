import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../models/product_model.dart';
import '../utils/constants.dart';
import '../utils/helpers.dart';
import '../config/api_config.dart';
import '../screens/products/product_details_screen.dart';

class RecentProductsSection extends StatelessWidget {
  final List<ProductModel> products;
  final VoidCallback? onViewAll;

  const RecentProductsSection({
    super.key,
    required this.products,
    this.onViewAll,
  });

  @override
  Widget build(BuildContext context) {
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
        padding: const EdgeInsets.all(AppSizes.paddingMedium),
        child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // En-tête
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded( // ✅ Utiliser Expanded pour éviter l'overflow
                child: Row(
                  children: [
                    Icon(Icons.history, color: AppColors.primary, size: 20),
                    const SizedBox(width: 8),
                    Expanded( // ✅ Utiliser Expanded pour le texte
                      child: Text(
                        'Produits récemment vus',
                        style: AppTextStyles.sectionTitle,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis, // ✅ Tronquer si trop long
                      ),
                    ),
                  ],
                ),
              ),
              if (onViewAll != null)
                TextButton(
                  onPressed: onViewAll,
                  child: const Text('Voir tout'),
                ),
            ],
          ),
          const SizedBox(height: 16),

          // Liste des produits
          SizedBox(
            height: 200,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: products.length,
              itemBuilder: (context, index) {
                final product = products[index];
                return _buildProductCard(context, product);
              },
            ),
          ),
        ],
      ),
      ),
    );
  }

  /// Obtenir l'URL de l'image principale du produit
  String _getProductImageUrl(ProductModel product) {
    // D'abord vérifier le champ image
    if (product.image != null && product.image!.isNotEmpty) {
      return _fixImageUrl(product.image!);
    }

    // Sinon, prendre la première image du tableau images
    if (product.images != null && product.images!.isNotEmpty) {
      return _fixImageUrl(product.images!.first);
    }

    // Aucune image disponible - retourner une URL vide (le errorWidget s'affichera)
    return '';
  }

  /// Corriger et construire l'URL d'image
  String _fixImageUrl(String imagePath) {
    // Si l'URL est déjà complète et correcte
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return imagePath;
    }

    // ✅ CORRECTION : Si c'est "http:" sans "//" (erreur commune)
    if (imagePath.startsWith('http:') && !imagePath.startsWith('http://')) {
      return imagePath.replaceFirst('http:', 'http://');
    }

    // ✅ CORRECTION : Si c'est "https:" sans "//"
    if (imagePath.startsWith('https:') && !imagePath.startsWith('https://')) {
      return imagePath.replaceFirst('https:', 'https://');
    }

    // Sinon, construire l'URL complète
    return '${ApiConfig.imageBaseUrl}/$imagePath';
  }

  Widget _buildProductCard(BuildContext context, ProductModel product) {
    final imageUrl = _getProductImageUrl(product);

    return Container(
      width: 160,
      margin: const EdgeInsets.only(right: 12),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: InkWell(
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => ProductDetailsScreen(product: product),
            ),
          );
        },
        borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image
            ClipRRect(
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(AppSizes.radiusMedium),
                topRight: Radius.circular(AppSizes.radiusMedium),
              ),
              child: imageUrl.isNotEmpty
                  ? CachedNetworkImage(
                      imageUrl: imageUrl,
                      height: 100,
                      width: double.infinity,
                      fit: BoxFit.contain,
                      alignment: Alignment.center,
                      placeholder: (context, url) => Container(
                        color: AppColors.background,
                        child: const Center(
                          child: CircularProgressIndicator(strokeWidth: 2),
                        ),
                      ),
                      errorWidget: (context, url, error) => Container(
                        color: AppColors.grey100,
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.shopping_bag_outlined,
                              size: 40,
                              color: AppColors.primary.withOpacity(0.5),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'K',
                              style: TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                                color: AppColors.primary,
                              ),
                            ),
                          ],
                        ),
                      ),
                    )
                  : Container(
                      height: 100,
                      color: AppColors.grey100,
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.shopping_bag_outlined,
                            size: 40,
                            color: AppColors.primary.withOpacity(0.5),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'K',
                            style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: AppColors.primary,
                            ),
                          ),
                        ],
                      ),
                    ),
            ),

            // Détails
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(8.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Nom
                    Text(
                      product.name,
                      style: AppTextStyles.caption.copyWith(
                        fontWeight: FontWeight.w600,
                        fontSize: 12,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),

                    // Prix
                    Row(
                      children: [
                        Flexible(
                          child: Text(
                            Helpers.formatPrice(product.price),
                            style: AppTextStyles.caption.copyWith(
                              color: AppColors.primary,
                              fontWeight: FontWeight.bold,
                              fontSize: 13,
                            ),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (product.hasDiscount) ...[
                          const SizedBox(width: 4),
                          Flexible(
                            child: Text(
                              Helpers.formatPrice(product.oldPrice!),
                              style: AppTextStyles.caption.copyWith(
                                decoration: TextDecoration.lineThrough,
                                fontSize: 10,
                                color: AppColors.textLight,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ],
                    ),
                    const SizedBox(height: 4),

                    // Rating
                    Row(
                      children: [
                        const Icon(
                          Icons.star,
                          color: AppColors.warning,
                          size: 12,
                        ),
                        const SizedBox(width: 2),
                        Text(
                          '${product.rating}',
                          style: AppTextStyles.caption.copyWith(fontSize: 10),
                        ),
                        const SizedBox(width: 4),
                        Text(
                          '(${product.reviewsCount})',
                          style: AppTextStyles.caption.copyWith(
                            fontSize: 9,
                            color: AppColors.textLight,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
