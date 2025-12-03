import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/product_provider.dart';
import '../../utils/constants.dart';
import '../../models/product_model.dart';
import '../../widgets/modern_product_card.dart';
import '../search/search_screen.dart';
import 'product_details_screen.dart';

class ProductsListScreen extends StatefulWidget {
  final String title;
  final String category;
  final IconData icon;

  const ProductsListScreen({
    super.key,
    required this.title,
    required this.category,
    required this.icon,
  });

  @override
  State<ProductsListScreen> createState() => _ProductsListScreenState();
}

class _ProductsListScreenState extends State<ProductsListScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  String _selectedSort = 'created_at';

  @override
  void initState() {
    super.initState();
    if (widget.category == 'deals' || widget.category == 'best_offers') {
      _selectedSort = 'discount';
    }
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadProducts();
    });
  }

  Future<void> _loadProducts() async {
    print('🔄 [PRODUCTS_LIST] Chargement pour catégorie: ${widget.category}');
    
    // ✅ Pour les catégories spéciales (best_offers, trending, etc), charger depuis l'API
    final provider = Provider.of<ProductProvider>(context, listen: false);
    
    if (widget.category == 'best_offers' || 
        widget.category == 'trending' || 
        widget.category == 'new' || 
        widget.category == 'featured' ||
        widget.category == 'deals') {
      // Charger tous les produits avec le filtre spécial
      await provider.loadProducts(
        categoryId: null,
        search: _searchQuery.isNotEmpty ? _searchQuery : null,
        sortBy: _selectedSort,
        specialCategory: widget.category, // ✅ Passer la catégorie spéciale
      );
    } else {
      // Catégorie normale
      await provider.loadProducts(
        categoryId: null,
        search: _searchQuery.isNotEmpty ? _searchQuery : null,
        sortBy: _selectedSort,
      );
    }
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(AppSizes.space2),
              decoration: BoxDecoration(
                color: AppColors.primary.withOpacity(0.1),
                borderRadius: BorderRadius.circular(AppSizes.radiusLG),
              ),
              child: Icon(
                widget.icon,
                color: AppColors.primary,
                size: 20,
              ),
            ),
            const SizedBox(width: AppSizes.space3),
            Expanded(
              child: Text(
                widget.title,
                style: AppTextStyles.h3,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => const SearchScreen(),
                ),
              );
            },
          ),
          PopupMenuButton<String>(
            icon: const Icon(Icons.sort),
            onSelected: (value) {
              setState(() {
                if (value.contains('_')) {
                  final parts = value.split('_');
                  _selectedSort = parts[0];
                } else {
                  _selectedSort = value;
                }
              });
              _loadProducts();
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'created_at_desc',
                child: Text('Plus récents'),
              ),
              const PopupMenuItem(
                value: 'created_at_asc',
                child: Text('Plus anciens'),
              ),
              const PopupMenuItem(
                value: 'price_asc',
                child: Text('Prix croissant'),
              ),
              const PopupMenuItem(
                value: 'price_desc',
                child: Text('Prix décroissant'),
              ),
              const PopupMenuItem(
                value: 'name_asc',
                child: Text('Nom A-Z'),
              ),
              const PopupMenuItem(
                value: 'name_desc',
                child: Text('Nom Z-A'),
              ),
            ],
          ),
        ],
      ),
      body: Column(
        children: [
          // Barre de recherche
          Padding(
            padding: const EdgeInsets.all(AppSizes.paddingMedium),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Rechercher dans ${widget.title.toLowerCase()}...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _searchController.clear();
                          setState(() {
                            _searchQuery = '';
                          });
                          _loadProducts();
                        },
                      )
                    : null,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                  borderSide: const BorderSide(color: AppColors.primary, width: 2),
                ),
                filled: true,
                fillColor: AppColors.background,
              ),
              onChanged: (value) {
                setState(() {
                  _searchQuery = value;
                });
                // Délai pour éviter trop de requêtes
                Future.delayed(const Duration(milliseconds: 500), () async {
                  if (_searchQuery == value) {
                    _loadProducts();
                  }
                });
              },
            ),
          ),
          
          // Liste des produits
          Expanded(
            child: Consumer<ProductProvider>(
              builder: (context, productProvider, _) {
                if (productProvider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (productProvider.error != null) {
                  return _buildErrorState(productProvider.error!);
                }

                final products = _getFilteredProducts(productProvider);

                if (products.isEmpty) {
                  return _buildEmptyState();
                }

                return RefreshIndicator(
                  onRefresh: _loadProducts,
                  child: GridView.builder(
                    padding: const EdgeInsets.all(AppSizes.paddingMedium),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      childAspectRatio: 0.75,
                      crossAxisSpacing: AppSizes.space3,
                      mainAxisSpacing: AppSizes.space3,
                    ),
                    itemCount: products.length,
                    itemBuilder: (context, index) {
                      final product = products[index];
                      return ModernProductCard(
                        product: product,
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => ProductDetailsScreen(product: product),
                            ),
                          );
                        },
                      );
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  List<ProductModel> _getFilteredProducts(ProductProvider productProvider) {
    // ✅ Pour les catégories spéciales, utiliser allProducts (chargés depuis l'API avec le filtre)
    if (widget.category == 'best_offers' || 
        widget.category == 'trending' || 
        widget.category == 'new' || 
        widget.category == 'featured') {
      print('📊 [PRODUCTS_LIST] Catégorie spéciale "${widget.category}": ${productProvider.allProducts.length} produits');
      return productProvider.allProducts;
    }
    
    // Pour les catégories normales
    return productProvider.allProducts;
  }

  Widget _buildErrorState(String error) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppSizes.paddingLarge),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(AppSizes.paddingLarge),
              decoration: BoxDecoration(
                color: AppColors.errorLight,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.error_outline,
                color: AppColors.error,
                size: 64,
              ),
            ),
            const SizedBox(height: AppSizes.space4),
            Text(
              'Erreur de chargement',
              style: AppTextStyles.h3.copyWith(
                color: AppColors.textDark,
              ),
            ),
            const SizedBox(height: AppSizes.space2),
            Text(
              error,
              style: AppTextStyles.bodyMedium.copyWith(
                color: AppColors.textMuted,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppSizes.space4),
            ElevatedButton(
              onPressed: _loadProducts,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: AppColors.white,
              ),
              child: const Text('Réessayer'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppSizes.paddingLarge),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(AppSizes.paddingLarge),
              decoration: BoxDecoration(
                color: AppColors.background,
                shape: BoxShape.circle,
              ),
              child: Icon(
                widget.icon,
                color: AppColors.textMuted,
                size: 64,
              ),
            ),
            const SizedBox(height: AppSizes.space4),
            Text(
              'Aucun produit trouvé',
              style: AppTextStyles.h3.copyWith(
                color: AppColors.textDark,
              ),
            ),
            const SizedBox(height: AppSizes.space2),
            Text(
              _searchQuery.isNotEmpty
                  ? 'Aucun résultat pour "$_searchQuery"'
                  : 'Il n\'y a pas encore de produits dans cette catégorie.',
              style: AppTextStyles.bodyMedium.copyWith(
                color: AppColors.textMuted,
              ),
              textAlign: TextAlign.center,
            ),
            if (_searchQuery.isNotEmpty) ...[
              const SizedBox(height: AppSizes.space4),
              ElevatedButton(
                onPressed: () {
                  _searchController.clear();
                  setState(() {
                    _searchQuery = '';
                  });
                  _loadProducts();
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: AppColors.white,
                ),
                child: const Text('Effacer la recherche'),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
