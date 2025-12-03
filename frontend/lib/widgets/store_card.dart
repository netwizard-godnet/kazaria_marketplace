import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../models/store_model.dart';
import '../utils/constants.dart';
import '../config/api_config.dart';

class StoreCard extends StatelessWidget {
  final StoreModel store;
  final VoidCallback? onTap;

  const StoreCard({
    super.key,
    required this.store,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 160,
      margin: const EdgeInsets.only(right: AppSizes.space3),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppSizes.radiusXL),
        boxShadow: [
          BoxShadow(
            color: AppColors.shadow.withOpacity(0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(AppSizes.radiusXL),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(AppSizes.paddingMedium),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Logo de la boutique
                Container(
                  width: double.infinity,
                  height: 80,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                    color: AppColors.background,
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                    child: _buildStoreLogo(),
                  ),
                ),
                const SizedBox(height: AppSizes.space3),

                // Nom de la boutique
                Text(
                  store.name,
                  style: AppTextStyles.bodyMedium.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: AppSizes.space2),

                // Badges
                Row(
                  children: [
                    if (store.isVerified) ...[
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: AppSizes.space1,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: AppColors.success,
                          borderRadius: BorderRadius.circular(AppSizes.radiusSM),
                        ),
                        child: const Icon(
                          Icons.verified,
                          color: AppColors.white,
                          size: 10,
                        ),
                      ),
                      const SizedBox(width: AppSizes.space1),
                    ],
                    if (store.isOfficial) ...[
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: AppSizes.space1,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: AppColors.primary,
                          borderRadius: BorderRadius.circular(AppSizes.radiusSM),
                        ),
                        child: const Icon(
                          Icons.star,
                          color: AppColors.white,
                          size: 10,
                        ),
                      ),
                      const SizedBox(width: AppSizes.space1),
                    ],
                    Expanded(
                      child: Text(
                        '${store.totalProducts} produits',
                        style: AppTextStyles.caption.copyWith(
                          color: AppColors.textMuted,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppSizes.space2),

                // Note
                Row(
                  children: [
                    const Icon(
                      Icons.star,
                      color: AppColors.warning,
                      size: 12,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      store.rating.toStringAsFixed(1),
                      style: AppTextStyles.caption.copyWith(
                        fontWeight: FontWeight.w500,
                        color: AppColors.textMuted,
                      ),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      '(${store.reviewsCount})',
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.textMuted,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildStoreLogo() {
    // Construction de l'URL du logo avec fallback
    String logoUrl;
    if (store.logoUrl?.isNotEmpty == true) {
      logoUrl = _fixImageUrl(store.logoUrl!);
    } else if (store.logo?.isNotEmpty == true) {
      logoUrl = _fixImageUrl(store.logo!);
    } else {
      logoUrl = '${ApiConfig.imageBaseUrl}/images/LOGO.jpg';
    }

    return CachedNetworkImage(
      imageUrl: logoUrl + '?v=${DateTime.now().millisecondsSinceEpoch}',
      fit: BoxFit.cover,
      placeholder: (context, url) => Container(
        color: AppColors.background,
        child: const Icon(
          Icons.store,
          color: AppColors.textMuted,
          size: 32,
        ),
      ),
      errorWidget: (context, url, error) => Container(
        color: AppColors.background,
        child: const Icon(
          Icons.store,
          color: AppColors.textMuted,
          size: 32,
        ),
      ),
    );
  }
  
  /// Corriger et construire l'URL d'image
  String _fixImageUrl(String imagePath) {
    // Si l'URL est déjà complète et correcte
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return imagePath;
    }
    
    // ✅ CORRECTION : Si c'est "http:" sans "//"
    if (imagePath.startsWith('http:') && !imagePath.startsWith('http://')) {
      return imagePath.replaceFirst('http:', 'http://');
    }
    
    // ✅ CORRECTION : Si c'est "https:" sans "//"
    if (imagePath.startsWith('https:') && !imagePath.startsWith('https://')) {
      return imagePath.replaceFirst('https:', 'https://');
    }
    
    // Sinon, construire l'URL complète
    if (imagePath.startsWith('storage/')) {
      return '${ApiConfig.imageBaseUrl}/$imagePath';
    }
    return '${ApiConfig.imageBaseUrl}/storage/$imagePath';
  }
}
