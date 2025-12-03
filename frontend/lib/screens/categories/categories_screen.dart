import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../utils/constants.dart';
import '../../models/category_model.dart';
import '../../providers/product_provider.dart';
import '../../config/api_config.dart';
import 'category_products_screen.dart';

class CategoriesScreen extends StatefulWidget {
  const CategoriesScreen({super.key});

  @override
  State<CategoriesScreen> createState() => _CategoriesScreenState();
}

class _CategoriesScreenState extends State<CategoriesScreen> with SingleTickerProviderStateMixin {
  String _searchQuery = '';
  bool _isGridView = false; // false = liste, true = grille
  
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<ProductProvider>(context, listen: false).loadCategories();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Catégories'),
        elevation: 0,
        backgroundColor: AppColors.white,
        actions: [
          // Bouton pour changer de vue
          IconButton(
            icon: Icon(_isGridView ? Icons.view_list : Icons.grid_view),
            onPressed: () {
              setState(() {
                _isGridView = !_isGridView;
              });
            },
            tooltip: _isGridView ? 'Vue liste' : 'Vue grille',
          ),
        ],
      ),
      body: Consumer<ProductProvider>(
        builder: (context, productProvider, _) {
          if (productProvider.isLoading && productProvider.categories.isEmpty) {
            return _buildLoadingState();
          }

          if (productProvider.categories.isEmpty) {
            return _buildEmptyState();
          }

          // Filtrer les catégories selon la recherche
          final filteredCategories = _searchQuery.isEmpty
              ? productProvider.categories
              : productProvider.categories.where((cat) {
                  return cat.name.toLowerCase().contains(_searchQuery.toLowerCase());
                }).toList();

          return Column(
            children: [
              // Barre de recherche
              _buildSearchBar(),
              
              // Liste ou Grille de catégories
              Expanded(
                child: filteredCategories.isEmpty
                    ? _buildNoResultsState()
                    : AnimatedSwitcher(
                        duration: const Duration(milliseconds: 300),
                        child: _isGridView
                            ? _buildGridView(filteredCategories)
                            : _buildListView(filteredCategories),
                      ),
              ),
            ],
          );
        },
      ),
    );
  }

  /// Barre de recherche style Jumia
  Widget _buildSearchBar() {
    return Container(
      padding: const EdgeInsets.all(AppSizes.paddingMedium),
      color: AppColors.white,
      child: TextField(
        onChanged: (value) {
          setState(() {
            _searchQuery = value;
          });
        },
        decoration: InputDecoration(
          hintText: 'Rechercher une catégorie...',
          hintStyle: const TextStyle(color: AppColors.textMuted),
          prefixIcon: const Icon(Icons.search, color: AppColors.textMedium),
          suffixIcon: _searchQuery.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear, color: AppColors.textMedium),
                  onPressed: () {
                    setState(() {
                      _searchQuery = '';
                    });
                  },
                )
              : null,
          filled: true,
          fillColor: AppColors.background,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(AppSizes.radiusLG),
            borderSide: BorderSide.none,
          ),
          contentPadding: const EdgeInsets.symmetric(
            horizontal: AppSizes.paddingMedium,
            vertical: AppSizes.paddingSmall,
          ),
        ),
      ),
    );
  }

  /// Vue en LISTE (style Jumia) - Design horizontal
  Widget _buildListView(List<CategoryModel> categories) {
    return ListView.separated(
      key: const ValueKey('list'),
      padding: const EdgeInsets.all(AppSizes.paddingMedium),
      itemCount: categories.length,
      separatorBuilder: (context, index) => const SizedBox(height: AppSizes.space3),
      itemBuilder: (context, index) {
        return _buildJumiaStyleCard(categories[index]);
      },
    );
  }

  /// Vue en GRILLE - Design compact
  Widget _buildGridView(List<CategoryModel> categories) {
    return GridView.builder(
      key: const ValueKey('grid'),
      padding: const EdgeInsets.all(AppSizes.paddingMedium),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: AppSizes.space3,
        mainAxisSpacing: AppSizes.space3,
        childAspectRatio: 1.0,
      ),
      itemCount: categories.length,
      itemBuilder: (context, index) {
        return _buildGridCard(categories[index]);
      },
    );
  }

  /// Carte style JUMIA (horizontale, épurée, professionnelle)
  Widget _buildJumiaStyleCard(CategoryModel category) {
    return InkWell(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => CategoryProductsScreen(category: category),
          ),
        );
      },
      borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
          border: Border.all(color: AppColors.grey200, width: 1),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // En-tête : Image + Nom
            Padding(
              padding: const EdgeInsets.all(AppSizes.paddingMedium),
              child: Row(
                children: [
                  // Image de la catégorie (carrée)
                  Container(
                    width: 70,
                    height: 70,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                      border: Border.all(color: AppColors.grey200, width: 1),
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                      child: category.image != null && category.image!.isNotEmpty
                          ? CachedNetworkImage(
                              imageUrl: _fixImageUrl(category.image!),
                              fit: BoxFit.cover,
                              placeholder: (context, url) => Container(
                                color: AppColors.background,
                                child: const Center(
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    color: AppColors.primary,
                                  ),
                                ),
                              ),
                              errorWidget: (context, url, error) => Container(
                                color: AppColors.primary.withOpacity(0.05),
                                child: const Icon(
                                  Icons.category,
                                  color: AppColors.primary,
                                  size: 32,
                                ),
                              ),
                            )
                          : Container(
                              color: AppColors.primary.withOpacity(0.05),
                              child: const Icon(
                                Icons.category,
                                color: AppColors.primary,
                                size: 32,
                              ),
                            ),
                    ),
                  ),
                  
                  const SizedBox(width: AppSizes.space3),
                  
                  // Nom + compteur
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          category.name,
                          style: AppTextStyles.h4.copyWith(
                            fontWeight: FontWeight.bold,
                            color: AppColors.textDark,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        if (category.subcategories != null && 
                            category.subcategories!.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Text(
                            '${category.subcategories!.length} sous-catégories',
                            style: AppTextStyles.caption.copyWith(
                              color: AppColors.textMuted,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  
                  // Flèche
                  const Icon(
                    Icons.chevron_right,
                    color: AppColors.textMuted,
                    size: 24,
                  ),
                ],
              ),
            ),
            
            // Sous-catégories cliquables (si disponibles)
            if (category.subcategories != null && 
                category.subcategories!.isNotEmpty) ...[
              const Divider(height: 1, color: AppColors.grey200),
              Padding(
                padding: const EdgeInsets.all(AppSizes.paddingMedium),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Sous-catégories populaires',
                      style: AppTextStyles.caption.copyWith(
                        fontWeight: FontWeight.w600,
                        color: AppColors.textMedium,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: category.subcategories!.take(6).map((sub) {
                        return InkWell(
                          onTap: () {
                            // Navigation vers la catégorie avec filtre sous-catégorie
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => CategoryProductsScreen(
                                  category: category,
                                ),
                              ),
                            );
                          },
                          borderRadius: BorderRadius.circular(AppSizes.radiusSM),
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 6,
                            ),
                            decoration: BoxDecoration(
                              color: AppColors.background,
                              borderRadius: BorderRadius.circular(AppSizes.radiusSM),
                              border: Border.all(
                                color: AppColors.grey200,
                                width: 1,
                              ),
                            ),
                            child: Text(
                              sub.name,
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w500,
                                color: AppColors.textDark,
                              ),
                            ),
                          ),
                        );
                      }).toList(),
                    ),
                    if (category.subcategories!.length > 6) ...[
                      const SizedBox(height: 8),
                      Text(
                        '+${category.subcategories!.length - 6} autres',
                        style: AppTextStyles.caption.copyWith(
                          color: AppColors.primary,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  /// Carte pour la vue GRILLE (compacte)
  Widget _buildGridCard(CategoryModel category) {
    return InkWell(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => CategoryProductsScreen(category: category),
          ),
        );
      },
      borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
          border: Border.all(color: AppColors.grey200, width: 1),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Image
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                border: Border.all(color: AppColors.grey200, width: 1),
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                child: category.image != null && category.image!.isNotEmpty
                    ? CachedNetworkImage(
                        imageUrl: _fixImageUrl(category.image!),
                        fit: BoxFit.cover,
                        placeholder: (context, url) => Container(
                          color: AppColors.background,
                          child: const Center(
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: AppColors.primary,
                            ),
                          ),
                        ),
                        errorWidget: (context, url, error) => Container(
                          color: AppColors.primary.withOpacity(0.05),
                          child: const Icon(
                            Icons.category,
                            color: AppColors.primary,
                            size: 40,
                          ),
                        ),
                      )
                    : Container(
                        color: AppColors.primary.withOpacity(0.05),
                        child: const Icon(
                          Icons.category,
                          color: AppColors.primary,
                          size: 40,
                        ),
                      ),
              ),
            ),
            
            const SizedBox(height: AppSizes.space2),
            
            // Nom
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: AppSizes.paddingSmall),
              child: Text(
                category.name,
                style: AppTextStyles.bodyMedium.copyWith(
                  fontWeight: FontWeight.w600,
                  color: AppColors.textDark,
                ),
                textAlign: TextAlign.center,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ),
            
            // Compteur sous-catégories
            if (category.subcategories != null && 
                category.subcategories!.isNotEmpty) ...[
              const SizedBox(height: 4),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 8,
                  vertical: 3,
                ),
                decoration: BoxDecoration(
                  color: AppColors.background,
                  borderRadius: BorderRadius.circular(AppSizes.radiusSM),
                ),
                child: Text(
                  '${category.subcategories!.length}',
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: AppColors.primary,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  /// Corriger et construire l'URL d'image
  String _fixImageUrl(String imagePath) {
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return imagePath;
    }
    
    if (imagePath.startsWith('http:') && !imagePath.startsWith('http://')) {
      return imagePath.replaceFirst('http:', 'http://');
    }
    
    if (imagePath.startsWith('https:') && !imagePath.startsWith('https://')) {
      return imagePath.replaceFirst('https:', 'https://');
    }
    
    return '${ApiConfig.imageBaseUrl}/$imagePath';
  }

  /// État de chargement
  Widget _buildLoadingState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const CircularProgressIndicator(color: AppColors.primary),
          const SizedBox(height: 16),
          Text(
            'Chargement des catégories...',
            style: AppTextStyles.body.copyWith(color: AppColors.textMedium),
          ),
        ],
      ),
    );
  }

  /// État vide
  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppSizes.paddingLarge),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: AppColors.primary.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.category_outlined,
                size: 64,
                color: AppColors.primary,
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'Aucune catégorie disponible',
              style: AppTextStyles.h3,
            ),
            const SizedBox(height: 8),
            Text(
              'Les catégories apparaîtront ici',
              style: AppTextStyles.body.copyWith(
                color: AppColors.textMedium,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  /// État "Aucun résultat"
  Widget _buildNoResultsState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppSizes.paddingLarge),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.search_off,
              size: 64,
              color: AppColors.textMuted,
            ),
            const SizedBox(height: 16),
            Text(
              'Aucune catégorie trouvée',
              style: AppTextStyles.h4,
            ),
            const SizedBox(height: 8),
            Text(
              'Essayez avec un autre mot-clé',
              style: AppTextStyles.body.copyWith(
                color: AppColors.textMedium,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
