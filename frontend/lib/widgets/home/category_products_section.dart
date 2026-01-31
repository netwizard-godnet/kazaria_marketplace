import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../utils/constants.dart';
import '../../providers/product_provider.dart';
import '../../models/product_model.dart';
import '../../screens/products/products_list_screen.dart';
import '../../screens/products/product_details_screen.dart';
import '../modern_product_card.dart';

/// Section dynamique affichant des produits par catégorie/sous-catégorie
class CategoryProductsSection extends StatefulWidget {
  const CategoryProductsSection({super.key});

  @override
  State<CategoryProductsSection> createState() => _CategoryProductsSectionState();
}

class _CategoryProductsSectionState extends State<CategoryProductsSection> {
  @override
  void initState() {
    super.initState();
    // Charger les données home dans le provider si pas déjà fait
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final productProvider = Provider.of<ProductProvider>(context, listen: false);
      if (!productProvider.hasData) {
        productProvider.loadHomeData();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<ProductProvider>(
      builder: (context, productProvider, _) {
        final categoryProducts = productProvider.categoryProducts;
        
        // Ne rien afficher si pas de produits par catégorie
        if (categoryProducts.isEmpty) {
          return const SizedBox.shrink();
        }

        // Prendre jusqu'à 4 sections de catégories
        final sections = categoryProducts.entries.take(4).toList();

        return Column(
          children: sections.map((entry) {
            return _buildCategorySectionFromData(context, entry.key, entry.value);
          }).toList(),
        );
      },
    );
  }

  Widget _buildCategorySectionFromData(
    BuildContext context,
    String categoryKey,
    List<ProductModel> products,
  ) {
    // Ne pas afficher si pas de produits
    if (products.isEmpty) {
      return const SizedBox.shrink();
    }

    // Prendre jusqu'à 6 produits
    final displayProducts = products.take(6).toList();
    
    // Déterminer le nom de la catégorie et l'icône
    String categoryName;
    IconData categoryIcon;
    
    switch (categoryKey) {
      case 'phones':
        categoryName = 'Téléphones et Tablettes';
        categoryIcon = Icons.smartphone;
        break;
      case 'tv':
        categoryName = 'TV et Électronique';
        categoryIcon = Icons.tv;
        break;
      case 'electro':
        categoryName = 'Électroménager';
        categoryIcon = Icons.kitchen;
        break;
      case 'computers':
        categoryName = 'Ordinateurs et Accessoires';
        categoryIcon = Icons.computer;
        break;
      default:
        categoryName = 'Produits';
        categoryIcon = Icons.category;
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
          // En-tête de la section
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: AppSizes.space4),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                // Titre avec icône
                Expanded( // ✅ Utiliser Expanded pour éviter l'overflow
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(
                          categoryIcon,
                          color: AppColors.primary,
                          size: 20,
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded( // ✅ Utiliser Expanded pour le texte
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              categoryName,
                              style: AppTextStyles.sectionTitle,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis, // ✅ Tronquer si trop long
                            ),
                            Text(
                              '${products.length} produits',
                              style: AppTextStyles.sectionSubtitle,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                // Bouton "Voir tout"
                TextButton(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => ProductsListScreen(
                          title: categoryName,
                          category: categoryKey,
                          icon: categoryIcon,
                        ),
                      ),
                    );
                  },
                  child: Row(
                    children: [
                      Text(
                        'Voir tout',
                        style: TextStyle(
                          color: AppColors.primary,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(width: 4),
                      Icon(
                        Icons.arrow_forward_ios,
                        size: 14,
                        color: AppColors.primary,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: AppSizes.space3),

          // Liste horizontale de produits
          SizedBox(
            height: 280,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: AppSizes.space4),
              itemCount: displayProducts.length,
              itemBuilder: (context, index) {
                final product = displayProducts[index];
                return Container(
                  width: 160,
                  margin: EdgeInsets.only(
                    right: index < displayProducts.length - 1 ? AppSizes.space3 : 0,
                  ),
                  child: ModernProductCard(
                    product: product,
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => ProductDetailsScreen(
                            product: product,
                            heroTag: 'category_${categoryKey}_product_${product.id}_$index',
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

