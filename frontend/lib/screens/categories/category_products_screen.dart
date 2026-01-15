import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../models/category_model.dart';
import '../../models/product_model.dart';
import '../../providers/product_provider.dart';
import '../../providers/comparison_provider.dart';
import '../../utils/constants.dart';
import '../../widgets/product_card.dart';
import '../../widgets/modern_product_card.dart';
import '../../widgets/product_filters_bottom_sheet.dart';
import '../../widgets/active_filters_chips.dart';
import '../products/product_details_screen.dart';
import '../comparison/product_comparison_screen.dart';

class CategoryProductsScreen extends StatefulWidget {
  final CategoryModel category;

  const CategoryProductsScreen({
    super.key,
    required this.category,
  });

  @override
  State<CategoryProductsScreen> createState() => _CategoryProductsScreenState();
}

class _CategoryProductsScreenState extends State<CategoryProductsScreen> {
  int? _selectedSubcategoryId;
  bool _isLoading = false;
  List<ProductModel> _products = [];
  List<ProductModel> _allLoadedProducts = []; // ✅ Tous les produits chargés (avant filtrage local)
  int _currentPage = 1;
  bool _hasMore = true;
  final ScrollController _scrollController = ScrollController();
  bool _showBackToTop = false;
  
  // ✅ Filtres avancés
  Map<String, dynamic> _filters = {
    'sort_by': 'created_at',
    'min_price': null,
    'max_price': null,
    'min_rating': null,
    'in_stock': null,
    'brands': <String>[],
  };
  
  // ✅ Min/Max prix pour le slider
  double _minPrice = 0;
  double _maxPrice = 1000000;

  // ✅ Mode comparaison
  bool _isComparisonMode = false;
  final Set<int> _selectedProductIds = {};

  @override
  void initState() {
    super.initState();
    // ✅ Différer le chargement après le build
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        _loadProducts();
      }
    });
    _scrollController.addListener(() {
      final shouldShow = _scrollController.offset > 600;
      if (shouldShow != _showBackToTop) {
        setState(() {
          _showBackToTop = shouldShow;
        });
      }
    });
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _loadProducts({bool refresh = false}) async {
    if (_isLoading) return;

    setState(() {
      _isLoading = true;
      if (refresh) {
        _currentPage = 1;
        _allLoadedProducts = [];
        _products = [];
        _hasMore = true;
      }
    });

    try {
      final productProvider = Provider.of<ProductProvider>(context, listen: false);
      
      // Si une sous-catégorie est sélectionnée, on l'utilise, sinon on utilise la catégorie principale
      final categoryIdToUse = _selectedSubcategoryId ?? widget.category.id;
      
      await productProvider.loadProducts(
        categoryId: categoryIdToUse,
        sortBy: _filters['sort_by'] ?? 'created_at',
        minPrice: _filters['min_price'],
        maxPrice: _filters['max_price'],
        minRating: _filters['min_rating'],
        inStock: _filters['in_stock'],
        brands: _filters['brands'],
        page: _currentPage,
      );

      if (mounted) {
        setState(() {
          if (refresh) {
            _allLoadedProducts = productProvider.allProducts;
            _products = productProvider.allProducts; // ✅ Pas besoin de filtrage local
          } else {
            _allLoadedProducts.addAll(productProvider.allProducts);
            _products = _allLoadedProducts; // ✅ Pas besoin de filtrage local
          }
          
          // ✅ Calculer les min/max prix pour le slider (seulement pour l'UI)
          if (refresh && _allLoadedProducts.isNotEmpty) {
            _calculatePriceRange();
          }
          
          _hasMore = productProvider.allProducts.length >= 20;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: ${e.toString()}')),
        );
      }
    }
  }
  
  /// Calculer le range de prix min/max basé sur les produits chargés
  void _calculatePriceRange() {
    if (_allLoadedProducts.isEmpty) return;
    
    final prices = _allLoadedProducts.map((p) => p.price).toList();
    _minPrice = prices.reduce((a, b) => a < b ? a : b);
    _maxPrice = prices.reduce((a, b) => a > b ? a : b);
    
    // ✅ Arrondir pour un slider plus ergonomique
    _minPrice = (_minPrice / 1000).floor() * 1000;
    _maxPrice = (_maxPrice / 1000).ceil() * 1000;
    
    // Initialiser les filtres de prix si non définis
    if (_filters['min_price'] == null) {
      _filters['min_price'] = _minPrice;
    }
    if (_filters['max_price'] == null) {
      _filters['max_price'] = _maxPrice;
    }
  }
  
  
  /// Obtenir toutes les marques disponibles
  List<String> _getAvailableBrands() {
    final brands = <String>{};
    for (final product in _allLoadedProducts) {
      if (product.brand != null && product.brand!.isNotEmpty) {
        brands.add(product.brand!);
      }
    }
    return brands.toList()..sort();
  }

  void _loadMore() {
    if (!_isLoading && _hasMore) {
      setState(() {
        _currentPage++;
      });
      _loadProducts();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.category.name),
        backgroundColor: _isComparisonMode ? AppColors.primary.withOpacity(0.1) : null,
        actions: [
          // ✅ Bouton de comparaison
          IconButton(
            icon: Icon(
              _isComparisonMode ? Icons.close : Icons.compare,
              color: _isComparisonMode ? AppColors.primary : null,
            ),
            tooltip: _isComparisonMode ? 'Annuler' : 'Comparer',
            onPressed: () {
              setState(() {
                _isComparisonMode = !_isComparisonMode;
                if (!_isComparisonMode) {
                  _selectedProductIds.clear();
                }
              });
            },
          ),
          // ✅ Bouton de filtres et tri
          Stack(
            children: [
              IconButton(
                icon: const Icon(Icons.filter_list),
                onPressed: _openFiltersBottomSheet,
              ),
              // Badge pour indiquer le nombre de filtres actifs
              if (_countActiveFilters() > 0)
                Positioned(
                  right: 8,
                  top: 8,
                  child: Container(
                    padding: const EdgeInsets.all(4),
                    decoration: const BoxDecoration(
                      color: AppColors.error,
                      shape: BoxShape.circle,
                    ),
                    constraints: const BoxConstraints(
                      minWidth: 16,
                      minHeight: 16,
                    ),
                    child: Text(
                      '${_countActiveFilters()}',
                      style: const TextStyle(
                        color: AppColors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
      body: Column(
        children: [
          // Breadcrumb
          _buildBreadcrumb(),
          
          // ✅ Bannières personnalisées de la catégorie
          _buildCategoryBanners(),
          
          // ✅ Chips de filtres actifs
          ActiveFiltersChips(
            filters: _filters,
            onRemoveFilter: _removeFilter,
            onClearAll: _clearAllFilters,
          ),
          
          // Filtres de sous-catégories
          if (widget.category.subcategories != null &&
              widget.category.subcategories!.isNotEmpty)
            Container(
              height: 50,
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                itemCount: widget.category.subcategories!.length + 1,
                itemBuilder: (context, index) {
                  if (index == 0) {
                    return _buildSubcategoryChip(
                      'Tous',
                      null,
                      _selectedSubcategoryId == null,
                    );
                  }
                  final subcategory = widget.category.subcategories![index - 1];
                  return _buildSubcategoryChip(
                    subcategory.name,
                    subcategory.id,
                    _selectedSubcategoryId == subcategory.id,
                  );
                },
              ),
            ),

          // Nombre de résultats
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              children: [
                Text(
                  '${_products.length} produit${_products.length > 1 ? 's' : ''}',
                  style: AppTextStyles.body.copyWith(
                    color: AppColors.textLight,
                  ),
                ),
                const Spacer(),
                Text(
                  _getSortLabel(),
                  style: AppTextStyles.caption,
                ),
              ],
            ),
          ),

          // Liste des produits
          Expanded(
            child: _isLoading && _products.isEmpty
                ? const Center(child: CircularProgressIndicator())
                : _products.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.shopping_bag_outlined,
                              size: 80,
                              color: AppColors.textLight.withOpacity(0.5),
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'Aucun produit trouvé',
                              style: AppTextStyles.h3.copyWith(
                                color: AppColors.textLight,
                              ),
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: () => _loadProducts(refresh: true),
                        child: GridView.builder(
                          controller: _scrollController,
                          padding: const EdgeInsets.all(16),
                          gridDelegate:
                              const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            childAspectRatio: 0.7,
                            crossAxisSpacing: 12,
                            mainAxisSpacing: 12,
                          ),
                          itemCount: _products.length + (_hasMore ? 1 : 0),
                          itemBuilder: (context, index) {
                            if (index == _products.length) {
                              // Loader pour charger plus
                              if (!_isLoading) {
                                _loadMore();
                              }
                              return const Center(
                                child: Padding(
                                  padding: EdgeInsets.all(16),
                                  child: CircularProgressIndicator(),
                                ),
                              );
                            }

                            final product = _products[index];
                            
                            // ✅ En mode comparaison, utiliser ModernProductCard avec checkbox
                            if (_isComparisonMode) {
                              return ModernProductCard(
                                product: product,
                                enableComparison: true,
                                isSelected: _selectedProductIds.contains(product.id),
                                onComparisonToggle: () {
                                  setState(() {
                                    if (_selectedProductIds.contains(product.id)) {
                                      _selectedProductIds.remove(product.id);
                                    } else {
                                      if (_selectedProductIds.length >= 4) {
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          const SnackBar(
                                            content: Text('⚠️ Maximum 4 produits'),
                                            backgroundColor: AppColors.error,
                                            duration: Duration(seconds: 2),
                                          ),
                                        );
                                        return;
                                      }
                                      _selectedProductIds.add(product.id);
                                    }
                                  });
                                },
                                onTap: () {
                                  // En mode comparaison, tap = toggle selection
                                  setState(() {
                                    if (_selectedProductIds.contains(product.id)) {
                                      _selectedProductIds.remove(product.id);
                                    } else {
                                      if (_selectedProductIds.length >= 4) {
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          const SnackBar(
                                            content: Text('⚠️ Maximum 4 produits'),
                                            backgroundColor: AppColors.error,
                                            duration: Duration(seconds: 2),
                                          ),
                                        );
                                        return;
                                      }
                                      _selectedProductIds.add(product.id);
                                    }
                                  });
                                },
                              );
                            }
                            
                            // ✅ Mode normal, utiliser ProductCard classique
                            return ProductCard(
                              product: product,
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => ProductDetailsScreen(
                                      product: product,
                                    ),
                                  ),
                                );
                              },
                              // onFavorite sera géré automatiquement par ProductCard
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
      floatingActionButton: _isComparisonMode && _selectedProductIds.isNotEmpty
          ? FloatingActionButton.extended(
              onPressed: _selectedProductIds.length < 2
                  ? null
                  : () async {
                      // Récupérer les produits sélectionnés
                      final selectedProducts = _products
                          .where((p) => _selectedProductIds.contains(p.id))
                          .toList();

                      if (selectedProducts.length < 2) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('⚠️ Sélectionnez au moins 2 produits'),
                            backgroundColor: AppColors.error,
                          ),
                        );
                        return;
                      }

                      // Ajouter les produits au provider de comparaison
                      final comparisonProvider = context.read<ComparisonProvider>();
                      comparisonProvider.clearSelection(); // Réinitialiser
                      for (final product in selectedProducts) {
                        comparisonProvider.addProduct(product);
                      }

                      // Naviguer vers l'écran de comparaison
                      if (mounted) {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const ProductComparisonScreen(),
                          ),
                        ).then((_) {
                          // Réinitialiser le mode comparaison après retour
                          setState(() {
                            _isComparisonMode = false;
                            _selectedProductIds.clear();
                          });
                        });
                      }
                    },
              backgroundColor: _selectedProductIds.length < 2
                  ? AppColors.textLight
                  : AppColors.primary,
              icon: const Icon(Icons.compare),
              label: Text(
                'Comparer (${_selectedProductIds.length})',
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
            )
          : (_showBackToTop
              ? FloatingActionButton(
                  onPressed: () {
                    _scrollController.animateTo(
                      0,
                      duration: const Duration(milliseconds: 400),
                      curve: Curves.easeOut,
                    );
                  },
                  backgroundColor: AppColors.primary,
                  child: const Icon(Icons.arrow_upward, color: Colors.white),
                )
              : null),
    );
  }

  Widget _buildBreadcrumb() {
    String? subName;
    if (_selectedSubcategoryId != null &&
        widget.category.subcategories != null) {
      for (final s in widget.category.subcategories!) {
        if (s.id == _selectedSubcategoryId) {
          subName = s.name;
          break;
        }
      }
    }
    return Container(
      width: double.infinity,
      color: AppColors.background,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        children: [
          GestureDetector(
            onTap: () => Navigator.pop(context),
            child: const Text('Accueil', style: AppTextStyles.caption),
          ),
          const Text('  >  ', style: AppTextStyles.caption),
          const Text('Catégories', style: AppTextStyles.caption),
          const Text('  >  ', style: AppTextStyles.caption),
          Expanded(
            child: Text(
              subName ?? widget.category.name,
              style: AppTextStyles.caption.copyWith(
                color: AppColors.textDark,
                fontWeight: FontWeight.w600,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }

  /// Construire les bannières personnalisées de la catégorie
  Widget _buildCategoryBanners() {
    // Récupérer les bannières depuis le provider ou la catégorie
    final productProvider = Provider.of<ProductProvider>(context, listen: false);
    List<Map<String, dynamic>> banners = [];
    
    // Priorité 1: Bannières depuis le provider (chargées avec les produits)
    if (productProvider.categoryBanners.isNotEmpty) {
      banners = productProvider.categoryBanners;
    }
    // Priorité 2: Bannières depuis le modèle de catégorie
    else if (widget.category.customBanners != null && widget.category.customBanners!.isNotEmpty) {
      banners = widget.category.customBanners!.map((banner) => {
        'id': banner.id,
        'title': banner.title,
        'image': banner.image,
        'link_url': banner.linkUrl,
        'sort_order': banner.sortOrder,
      }).toList();
    }

    // Si aucune bannière, ne rien afficher
    if (banners.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      height: 180,
      margin: const EdgeInsets.only(bottom: 8),
      child: PageView.builder(
        itemCount: banners.length,
        itemBuilder: (context, index) {
          final banner = banners[index];
          final imageUrl = banner['image'] as String?;
          
          if (imageUrl == null || imageUrl.isEmpty) {
            return const SizedBox.shrink();
          }

          return GestureDetector(
            onTap: () {
              final linkUrl = banner['link_url'] as String?;
              if (linkUrl != null && linkUrl.isNotEmpty) {
                _handleBannerTap(linkUrl);
              }
            },
            child: Container(
              margin: const EdgeInsets.symmetric(horizontal: 16),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: CachedNetworkImage(
                  imageUrl: ImageUrlHelper.fixImageUrl(imageUrl),
                  fit: BoxFit.cover,
                  width: double.infinity,
                  placeholder: (context, url) => Container(
                    color: AppColors.grey100,
                    child: const Center(
                      child: CircularProgressIndicator(),
                    ),
                  ),
                  errorWidget: (context, url, error) => Container(
                    color: AppColors.grey100,
                    child: const Icon(
                      Icons.image_not_supported,
                      color: AppColors.textLight,
                    ),
                  ),
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  /// Gérer le clic sur une bannière
  void _handleBannerTap(String linkUrl) async {
    try {
      final uri = Uri.parse(linkUrl);
      
      // Si c'est une URL externe, l'ouvrir
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        print('❌ [CATEGORY_BANNERS] Impossible d\'ouvrir: $linkUrl');
      }
    } catch (e) {
      print('❌ [CATEGORY_BANNERS] Erreur: $e');
    }
  }

  Widget _buildSubcategoryChip(String label, int? subcategoryId, bool isSelected) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label),
        selected: isSelected,
        onSelected: (selected) {
          setState(() {
            _selectedSubcategoryId = selected ? subcategoryId : null;
          });
          // Recharger les produits avec le nouveau filtre
          _loadProducts(refresh: true);
        },
        selectedColor: AppColors.primary,
        backgroundColor: AppColors.background,
        labelStyle: TextStyle(
          color: isSelected ? AppColors.white : AppColors.textDark,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
        ),
      ),
    );
  }

  String _getSortLabel() {
    final sortBy = _filters['sort_by'] ?? 'created_at';
    switch (sortBy) {
      case 'price_asc':
        return 'Prix ↑';
      case 'price_desc':
        return 'Prix ↓';
      case 'rating':
        return 'Note ⭐';
      case 'popular':
        return 'Populaire 🔥';
      case 'discount':
        return 'Promos 🔥';
      default:
        return 'Récents 🆕';
    }
  }
  
  /// Compter le nombre de filtres actifs
  int _countActiveFilters() {
    int count = 0;
    
    // Prix (si différent du min/max global)
    if (_filters['min_price'] != null && _filters['min_price'] != _minPrice) count++;
    if (_filters['max_price'] != null && _filters['max_price'] != _maxPrice) count++;
    
    // Note
    if (_filters['min_rating'] != null) count++;
    
    // Disponibilité
    if (_filters['in_stock'] != null) count++;
    
    // Marques
    final brands = _filters['brands'] as List<String>?;
    if (brands != null && brands.isNotEmpty) count += brands.length;
    
    return count;
  }
  
  /// Ouvrir le bottom sheet des filtres
  void _openFiltersBottomSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => ProductFiltersBottomSheet(
        currentFilters: _filters,
        availableBrands: _getAvailableBrands(),
        minPrice: _minPrice,
        maxPrice: _maxPrice,
        onApplyFilters: (newFilters) {
          setState(() {
            _filters = newFilters;
          });
          // ✅ Toujours recharger depuis l'API avec les nouveaux filtres
          _loadProducts(refresh: true);
        },
      ),
    );
  }
  
  /// Supprimer un filtre spécifique
  void _removeFilter(String filterKey, {dynamic value}) {
    setState(() {
      switch (filterKey) {
        case 'price':
          _filters['min_price'] = _minPrice;
          _filters['max_price'] = _maxPrice;
          break;
        case 'min_rating':
          _filters['min_rating'] = null;
          break;
        case 'in_stock':
          _filters['in_stock'] = null;
          break;
        case 'brands':
          if (value != null) {
            final brands = List<String>.from(_filters['brands'] ?? []);
            brands.remove(value);
            _filters['brands'] = brands;
          }
          break;
      }
    });
    // ✅ Recharger depuis l'API avec les filtres mis à jour
    _loadProducts(refresh: true);
  }
  
  /// Effacer tous les filtres
  void _clearAllFilters() {
    setState(() {
      _filters = {
        'sort_by': _filters['sort_by'], // Garder le tri
        'min_price': _minPrice,
        'max_price': _maxPrice,
        'min_rating': null,
        'in_stock': null,
        'brands': <String>[],
      };
    });
    // ✅ Recharger depuis l'API avec les filtres réinitialisés
    _loadProducts(refresh: true);
  }
}

