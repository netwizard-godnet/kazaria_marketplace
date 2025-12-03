import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../models/product_model.dart';
import '../utils/constants.dart';
import '../utils/helpers.dart';
import '../providers/favorites_provider.dart';
import '../screens/products/product_details_screen.dart'; // ✅ Import pour navigation

class ProductCard extends StatelessWidget {
  final ProductModel product;
  final VoidCallback? onTap;
  final VoidCallback? onFavorite;
  final bool? isFavorite; // Nullable pour auto-détection
  final String? heroTag;

  const ProductCard({
    super.key,
    required this.product,
    this.onTap,
    this.onFavorite,
    this.isFavorite, // Nullable
    this.heroTag,
  });

  /// Obtenir l'URL de l'image principale du produit
  String? _getProductImageUrl() {
    // ✅ Priorité 1 : imageUrl (URL complète de l'API)
    if (product.imageUrl != null && product.imageUrl!.isNotEmpty) {
      // Si imageUrl contient 127.0.0.1 ou localhost, utiliser le champ image (chemin relatif) à la place
      if (product.imageUrl!.contains('127.0.0.1') ||
          product.imageUrl!.contains('localhost')) {
        // Utiliser le champ image (chemin relatif) si disponible
        if (product.image != null && product.image!.isNotEmpty) {
          return _fixImageUrl(product.image!);
        }
      } else {
        // Sinon, utiliser imageUrl directement (mais corriger si nécessaire)
        return _fixImageUrl(product.imageUrl!);
      }
    }

    // ✅ Priorité 2 : champ image (chemin relatif)
    if (product.image != null && product.image!.isNotEmpty) {
      return _fixImageUrl(product.image!);
    }

    // ✅ Priorité 3 : première image du tableau images
    if (product.images != null && product.images!.isNotEmpty) {
      return _fixImageUrl(product.images!.first);
    }

    // Aucune image disponible
    return null;
  }

  /// Corriger et construire l'URL d'image
  String _fixImageUrl(String imagePath) {
    return ImageUrlHelper.fixImageUrl(imagePath);
  }

  Widget _buildImageContent() {
    return Container(
      height: 120,
      width: double.infinity,
      color: AppColors.white,
      padding: const EdgeInsets.all(6),
      child: _getProductImageUrl() != null
          ? CachedNetworkImage(
              imageUrl: _getProductImageUrl()!,
              fit: BoxFit.contain,
              alignment: Alignment.center,
              memCacheWidth: 400,
              memCacheHeight: 400,
              maxHeightDiskCache: 800,
              maxWidthDiskCache: 800,
              placeholder: (context, url) => Container(
                color: AppColors.background,
                child: const Center(
                  child: CircularProgressIndicator(strokeWidth: 2),
                ),
              ),
              errorWidget: (context, url, error) => _buildPlaceholder(),
            )
          : _buildPlaceholder(),
    );
  }

  Widget _buildHeroImage() {
    final imageContent = _buildImageContent();
    if (heroTag == null) {
      return imageContent;
    }
    return Hero(tag: heroTag!, child: imageContent);
  }

  Widget _buildPlaceholder() {
    return Container(
      color: AppColors.grey100,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.shopping_bag_outlined,
            size: 50,
            color: AppColors.primary.withOpacity(0.5),
          ),
          const SizedBox(height: 8),
          Text(
            'K',
            style: TextStyle(
              fontSize: 32,
              fontWeight: FontWeight.bold,
              color: AppColors.primary,
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<FavoritesProvider>(
      builder: (context, favoritesProvider, _) {
        // Auto-détection du statut favori si non spécifié
        final isProductFavorite =
            isFavorite ?? favoritesProvider.isFavorite(product.id);

        return GestureDetector(
          onTap:
              onTap ??
              () {
                HapticFeedback.lightImpact();
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => ProductDetailsScreen(
                      product: product,
                      heroTag: heroTag ?? 'product_${product.id}',
                    ),
                  ),
                );
              },
          child: Container(
            decoration: BoxDecoration(
              color: AppColors.white,
              borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.05),
                  blurRadius: 10,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Image
                Stack(
                  children: [
                    ClipRRect(
                      borderRadius: const BorderRadius.only(
                        topLeft: Radius.circular(AppSizes.radiusMedium),
                        topRight: Radius.circular(AppSizes.radiusMedium),
                      ),
                      child: _buildHeroImage(),
                    ),
                    // Badges
                    Positioned(
                      top: 8,
                      left: 8,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (product.isNew)
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: AppColors.info,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: const Text(
                                'Nouveau',
                                style: TextStyle(
                                  color: AppColors.white,
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          if (product.hasDiscount)
                            Container(
                              margin: const EdgeInsets.only(top: 4),
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: AppColors.error,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                '-${product.discountPercentage?.toInt()}%',
                                style: const TextStyle(
                                  color: AppColors.white,
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                    // Favorite button
                    Positioned(
                      top: 8,
                      right: 8,
                      child: GestureDetector(
                        onTap:
                            onFavorite ??
                            () async {
                              HapticFeedback.selectionClick();
                              final favoritesProvider =
                                  Provider.of<FavoritesProvider>(
                                    context,
                                    listen: false,
                                  );
                              await favoritesProvider.toggleFavorite(
                                product.id,
                              );
                            },
                        child: Container(
                          padding: const EdgeInsets.all(6),
                          decoration: BoxDecoration(
                            color: AppColors.white,
                            shape: BoxShape.circle,
                          ),
                          child: Icon(
                            isProductFavorite
                                ? Icons.favorite
                                : Icons.favorite_border,
                            color: isProductFavorite
                                ? AppColors.error
                                : AppColors.textLight,
                            size: 20,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                // Product info
                Padding(
                  padding: const EdgeInsets.fromLTRB(8, 6, 8, 8),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Name
                      SizedBox(
                        height: 34, // ✅ Hauteur réduite
                        child: Text(
                          product.name,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: AppTextStyles.body.copyWith(
                            fontWeight: FontWeight.w600,
                            fontSize: 12.5,
                            height: 1.2, // ✅ Interligne encore plus réduit
                          ),
                        ),
                      ),
                      const SizedBox(height: 3),
                      // Rating
                      Row(
                        children: [
                          const Icon(
                            Icons.star,
                            color: AppColors.warning,
                            size: 13,
                          ),
                          const SizedBox(width: 2),
                          Text(
                            '${product.rating} (${product.reviewsCount})',
                            style: AppTextStyles.caption.copyWith(fontSize: 10),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      // Price - Affichage vertical pour éviter la troncature
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            Helpers.formatPrice(product.price),
                            style: AppTextStyles.body.copyWith(
                              color: AppColors.primary,
                              fontWeight: FontWeight.bold,
                              fontSize: 13,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.visible,
                          ),
                          if (product.hasDiscount) ...[
                            const SizedBox(height: 1),
                            Text(
                              Helpers.formatPrice(product.oldPrice!),
                              style: AppTextStyles.caption.copyWith(
                                decoration: TextDecoration.lineThrough,
                                fontSize: 10,
                                color: AppColors.textLight,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ],
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
