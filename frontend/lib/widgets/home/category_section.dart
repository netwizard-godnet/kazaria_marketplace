import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../models/category_model.dart';
import '../../utils/constants.dart';
import '../../config/api_config.dart';
import '../../screens/categories/category_products_screen.dart';
import '../../screens/categories/categories_screen.dart';

/// Section des catégories pour la page d'accueil
class CategorySection extends StatelessWidget {
  final List<CategoryModel> categories;

  const CategorySection({
    super.key,
    required this.categories,
  });

  @override
  Widget build(BuildContext context) {
    if (categories.isEmpty) {
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
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded( // ✅ Utiliser Expanded pour éviter l'overflow
                  child: Text(
                    'Catégories',
                    style: AppTextStyles.sectionTitle,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis, // ✅ Tronquer si trop long
                  ),
                ),
                TextButton(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => const CategoriesScreen(),
                      ),
                    );
                  },
                  child: const Text('Voir tout'),
                ),
              ],
            ),
            const SizedBox(height: 16),
          SizedBox(
            height: 120,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: categories.length,
              itemBuilder: (context, index) {
                final category = categories[index];
                return _CategoryCard(category: category);
              },
            ),
          ),
        ],
      ),
      ),
    );
  }
}

/// Card individuelle de catégorie
class _CategoryCard extends StatelessWidget {
  final CategoryModel category;

  const _CategoryCard({required this.category});

  /// Corriger et construire l'URL d'image (même logique que CategoriesScreen)
  String _fixImageUrl(String imagePath) {
    // Si c'est déjà une URL complète, utiliser fixImageUrl pour remplacer localhost
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return ImageUrlHelper.fixImageUrl(imagePath);
    }
    
    // Corriger les URLs malformées
    if (imagePath.startsWith('http:') && !imagePath.startsWith('http://')) {
      imagePath = imagePath.replaceFirst('http:', 'http://');
    }
    
    if (imagePath.startsWith('https:') && !imagePath.startsWith('https://')) {
      imagePath = imagePath.replaceFirst('https:', 'https://');
    }
    
    // Si le chemin commence déjà par storage/ ou images/, l'utiliser tel quel
    if (imagePath.startsWith('storage/') || imagePath.startsWith('images/')) {
      return '${ApiConfig.imageBaseUrl}/$imagePath';
    }
    
    // Sinon, essayer avec storage/ par défaut
    return '${ApiConfig.imageBaseUrl}/storage/$imagePath';
  }

  /// Construire l'image de la catégorie (image ou icon)
  Widget _buildCategoryImage() {
    // Priorité 1: Utiliser l'image si disponible
    if (category.image != null && category.image!.isNotEmpty) {
      return CachedNetworkImage(
        imageUrl: _fixImageUrl(category.image!),
        height: 80,
        width: double.infinity,
        fit: BoxFit.contain,
        placeholder: (context, url) => Container(
          color: AppColors.background,
          child: const Center(
            child: CircularProgressIndicator(
              strokeWidth: 2,
              color: AppColors.primary,
            ),
          ),
        ),
        errorWidget: (context, url, error) {
          print('❌ [CATEGORY_SECTION] Erreur image: $url');
          // Si l'image échoue, essayer l'icône
          return _buildIconFallback();
        },
      );
    }
    
    // Priorité 2: Utiliser l'icône si disponible
    if (category.icon != null && category.icon!.isNotEmpty) {
      return CachedNetworkImage(
        imageUrl: _fixImageUrl(category.icon!),
        height: 80,
        width: double.infinity,
        fit: BoxFit.contain,
        placeholder: (context, url) => Container(
          color: AppColors.background,
          child: const Center(
            child: CircularProgressIndicator(
              strokeWidth: 2,
              color: AppColors.primary,
            ),
          ),
        ),
        errorWidget: (context, url, error) {
          print('❌ [CATEGORY_SECTION] Erreur icon: $url');
          return _buildIconFallback();
        },
      );
    }
    
    // Fallback: Icône par défaut
    return _buildIconFallback();
  }
  
  /// Widget de fallback avec icône par défaut
  Widget _buildIconFallback() {
    return Container(
      height: 80,
      width: double.infinity,
      color: AppColors.primary.withOpacity(0.1),
      child: const Icon(
        Icons.category,
        color: AppColors.primary,
        size: 40,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => CategoryProductsScreen(category: category),
          ),
        );
      },
      child: Container(
        width: 120,
        margin: const EdgeInsets.only(right: 12),
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
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(AppSizes.radiusMedium),
                topRight: Radius.circular(AppSizes.radiusMedium),
              ),
              child: _buildCategoryImage(),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(8.0),
                child: Text(
                  category.name,
                  style: AppTextStyles.caption.copyWith(
                    fontWeight: FontWeight.w600,
                    fontSize: 11,
                  ),
                  textAlign: TextAlign.center,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

