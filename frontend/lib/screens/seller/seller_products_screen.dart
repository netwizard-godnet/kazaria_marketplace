import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/seller_provider.dart';
import '../../utils/constants.dart';
import '../../config/api_config.dart';
import '../../models/product_model.dart';
import '../../widgets/skeletons/seller_product_card_skeleton.dart';
import 'add_product_screen.dart';
import 'edit_product_screen.dart';

class SellerProductsScreen extends StatefulWidget {
  const SellerProductsScreen({super.key});

  @override
  State<SellerProductsScreen> createState() => _SellerProductsScreenState();
}

class _SellerProductsScreenState extends State<SellerProductsScreen> {
  final _scrollController = ScrollController();
  final _searchController = TextEditingController();
  String? _selectedStatus;
  Timer? _searchTimer;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadProducts();
    });
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    _searchTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadProducts() async {
    final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
    await sellerProvider.loadProducts(refresh: true);
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent * 0.9) {
      final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
      if (!sellerProvider.isLoading) {
        sellerProvider.loadMoreProducts();
      }
    }
  }

  Future<void> _refresh() async {
    await _loadProducts();
  }

  void _search(String query) {
    // Annuler le timer précédent s'il existe
    _searchTimer?.cancel();
    
    print('🔍 [PRODUCTS_SEARCH] Recherche: "$query", Statut: $_selectedStatus');
    
    // Créer un nouveau timer avec un délai de 500ms
    _searchTimer = Timer(const Duration(milliseconds: 500), () {
      final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
      sellerProvider.loadProducts(
        refresh: true,
        search: query.isEmpty ? null : query,
        status: _selectedStatus,
      );
    });
  }

  void _filterByStatus(String? status) {
    setState(() {
      _selectedStatus = status;
    });
    
    print('🏷️ [PRODUCTS_FILTER] Nouveau statut: "$status", Recherche: "${_searchController.text}"');
    
    final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
    sellerProvider.loadProducts(
      refresh: true,
      search: _searchController.text.isEmpty ? null : _searchController.text,
      status: status,
    );
  }

  String _buildFilterText() {
    List<String> filters = [];
    
    if (_searchController.text.isNotEmpty) {
      filters.add('Recherche: "${_searchController.text}"');
    }
    
    if (_selectedStatus != null) {
      String statusLabel = '';
      switch (_selectedStatus) {
        case 'active':
          statusLabel = 'Actif';
          break;
        case 'inactive':
          statusLabel = 'Inactif';
          break;
        case 'out_of_stock':
          statusLabel = 'En rupture';
          break;
        default:
          statusLabel = _selectedStatus!;
      }
      filters.add('Statut: $statusLabel');
    }
    
    return filters.join(' • ');
  }

  void _clearFilters() {
    setState(() {
      _selectedStatus = null;
      _searchController.clear();
    });
    
    final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
    sellerProvider.loadProducts(refresh: true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text(
          'Mes Produits',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w600,
            color: Colors.black,
          ),
        ),
        centerTitle: true,
        elevation: 0,
        backgroundColor: Colors.white,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.black),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: Column(
        children: [
          // Barre de recherche et filtres
          Container(
            padding: const EdgeInsets.all(AppSizes.paddingMedium),
            color: AppColors.white,
            child: Column(
              children: [
                // Recherche
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Rechercher un produit...',
                    prefixIcon: const Icon(Icons.search, color: AppColors.primary),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, color: AppColors.grey600),
                            onPressed: () {
                              _searchController.clear();
                              _search('');
                            },
                          )
                        : null,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                      borderSide: const BorderSide(color: AppColors.grey300),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                      borderSide: const BorderSide(color: AppColors.grey300),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                      borderSide: const BorderSide(color: AppColors.primary, width: 2),
                    ),
                    filled: true,
                    fillColor: AppColors.white,
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: AppSizes.paddingMedium,
                      vertical: AppSizes.space3,
                    ),
                  ),
                  onChanged: (value) => _search(value),
                ),
                
                const SizedBox(height: AppSizes.space3),
                
                // Indicateur de recherche/filtres actifs
                Consumer<SellerProvider>(
                  builder: (context, sellerProvider, child) {
                    if (_searchController.text.isNotEmpty || _selectedStatus != null) {
                      return Container(
                        margin: const EdgeInsets.only(bottom: AppSizes.space3),
                        padding: const EdgeInsets.all(AppSizes.paddingMedium),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                          border: Border.all(color: AppColors.primary.withOpacity(0.3)),
                        ),
                        child: Row(
                          children: [
                            Icon(Icons.filter_list, color: AppColors.primary, size: 20),
                            const SizedBox(width: AppSizes.space2),
                            Expanded(
                              child: Text(
                                _buildFilterText(),
                                style: AppTextStyles.bodySmall.copyWith(
                                  color: AppColors.primary,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ),
                            GestureDetector(
                              onTap: _clearFilters,
                              child: Container(
                                padding: const EdgeInsets.all(4),
                                decoration: BoxDecoration(
                                  color: AppColors.primary.withOpacity(0.2),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Icon(
                                  Icons.clear,
                                  color: AppColors.primary,
                                  size: 16,
                                ),
                              ),
                            ),
                          ],
                        ),
                      );
                    }
                    return const SizedBox.shrink();
                  },
                ),
                
                // Filtres de statut
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildFilterChip('Tous', null),
                      const SizedBox(width: AppSizes.space2),
                      _buildFilterChip('Actif', 'active'),
                      const SizedBox(width: AppSizes.space2),
                      _buildFilterChip('Inactif', 'inactive'),
                      const SizedBox(width: AppSizes.space2),
                      _buildFilterChip('En rupture', 'out_of_stock'),
                    ],
                  ),
                ),
              ],
            ),
          ),
          
          // Liste des produits
          Expanded(
            child: Consumer<SellerProvider>(
              builder: (context, sellerProvider, child) {
                if (sellerProvider.isLoading &&
                    sellerProvider.products.isEmpty) {
                  return GridView.builder(
                    controller: _scrollController,
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(AppSizes.paddingMedium),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      crossAxisSpacing: AppSizes.space3,
                      mainAxisSpacing: AppSizes.space3,
                      childAspectRatio: 0.8,
                    ),
                    itemCount: 6,
                    itemBuilder: (context, index) =>
                        const SellerProductCardSkeleton(),
                  );
                }

                if (sellerProvider.products.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.inventory_2_outlined,
                          size: 80,
                          color: AppColors.grey400,
                        ),
                        const SizedBox(height: AppSizes.space4),
                        Text(
                          'Aucun produit',
                          style: AppTextStyles.h3.copyWith(
                            color: AppColors.textMuted,
                          ),
                        ),
                        const SizedBox(height: AppSizes.space2),
                        Text(
                          'Ajoutez votre premier produit',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: AppColors.textMuted,
                          ),
                        ),
                      ],
                    ),
                  );
                }

                return RefreshIndicator(
                  onRefresh: _refresh,
                  child: GridView.builder(
                    controller: _scrollController,
                    padding: const EdgeInsets.all(AppSizes.paddingMedium),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      crossAxisSpacing: AppSizes.space3,
                      mainAxisSpacing: AppSizes.space3,
                      childAspectRatio: 0.8, // Augmenté de 0.75 à 0.8 pour éviter l'overflow
                    ),
                    itemCount: sellerProvider.products.length + (sellerProvider.hasMoreProducts ? 1 : 0),
                    itemBuilder: (context, index) {
                      if (index == sellerProvider.products.length) {
                        return const Center(
                          child: Padding(
                            padding: EdgeInsets.all(AppSizes.paddingMedium),
                            child: CircularProgressIndicator(),
                          ),
                        );
                      }

                      final product = sellerProvider.products[index];
                      return _buildProductCard(product, sellerProvider);
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const AddProductScreen()),
          );
          
          if (result == true) {
            // Recharger les produits si un produit a été ajouté
            final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
            await sellerProvider.loadProducts(refresh: true);
          }
        },
        backgroundColor: AppColors.primary,
        child: const Icon(Icons.add, color: AppColors.white),
      ),
    );
  }

  Widget _buildFilterChip(String label, String? value) {
    final isSelected = _selectedStatus == value;
    
    return GestureDetector(
      onTap: () => _filterByStatus(value),
      child: Container(
        padding: const EdgeInsets.symmetric(
          horizontal: AppSizes.paddingMedium,
          vertical: AppSizes.space2,
        ),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.primary : AppColors.grey200,
          borderRadius: BorderRadius.circular(AppSizes.radiusLG),
        ),
        child: Text(
          label,
          style: AppTextStyles.bodyMedium.copyWith(
            color: isSelected ? AppColors.white : AppColors.textDark,
            fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
          ),
        ),
      ),
    );
  }

  Widget _buildProductCard(ProductModel product, SellerProvider sellerProvider) {
    return Container(
      margin: const EdgeInsets.only(bottom: AppSizes.space3),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radiusLG),
        boxShadow: AppShadows.shadowSM,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          // Image du produit
          ClipRRect(
            borderRadius: const BorderRadius.vertical(top: Radius.circular(AppSizes.radiusLG)),
            child: Stack(
              children: [
                Container(
                  height: 100,
                  width: double.infinity,
                  color: AppColors.grey100,
                  child: _buildProductImage(product),
                ),
                // Bouton de suppression
                Positioned(
                  top: 6, // Réduit de 8 à 6
                  right: 6, // Réduit de 8 à 6
                  child: GestureDetector(
                    onTap: () => _showDeleteDialog(product, sellerProvider),
                    child: Container(
                      padding: const EdgeInsets.all(4), // Réduit de 6 à 4
                      decoration: BoxDecoration(
                        color: AppColors.error.withOpacity(0.9),
                        borderRadius: BorderRadius.circular(16), // Réduit de 20 à 16
                      ),
                      child: const Icon(
                        Icons.delete_outline,
                        color: AppColors.white,
                        size: 16, // Réduit de 18 à 16
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          
          // Informations du produit
          Flexible(
            child: Padding(
              padding: const EdgeInsets.all(8), // Padding fixe réduit
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    product.name,
                    style: AppTextStyles.bodyMedium.copyWith(
                      fontWeight: FontWeight.w600,
                      color: AppColors.textDark,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4), // Réduit encore plus
                  Text(
                    '${product.price.toStringAsFixed(0)} FCFA',
                    style: AppTextStyles.bodySmall.copyWith(
                      fontWeight: FontWeight.bold,
                      color: AppColors.primary,
                    ),
                  ),
                  const SizedBox(height: 4), // Réduit encore plus
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: product.stock > 0 ? AppColors.success.withOpacity(0.1) : AppColors.error.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          product.stock > 0 ? 'En stock' : 'Rupture',
                          style: AppTextStyles.caption.copyWith(
                            color: product.stock > 0 ? AppColors.success : AppColors.error,
                            fontWeight: FontWeight.w500,
                            fontSize: 10,
                          ),
                        ),
                      ),
                      const Spacer(),
                      Text(
                        'Stock: ${product.stock}',
                        style: AppTextStyles.caption.copyWith(
                          color: AppColors.textLight,
                          fontSize: 10,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6), // Réduit encore plus
                  
                  // Bouton d'action
                  SizedBox(
                    width: double.infinity,
                    height: 28, // Hauteur fixe réduite
                    child: OutlinedButton.icon(
                      onPressed: () async {
                        final result = await Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => EditProductScreen(productId: product.id),
                          ),
                        );
                        
                        if (result == true) {
                          await sellerProvider.loadProducts(refresh: true);
                        }
                      },
                      icon: const Icon(Icons.edit_outlined, size: 14),
                      label: const Text('Modifier', style: TextStyle(fontSize: 11)),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.primary,
                        side: const BorderSide(color: AppColors.primary),
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        minimumSize: const Size(0, 28),
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildProductImage(ProductModel product) {
    final candidateUrls = <String?>[
      product.imageUrl,
      product.image,
      if (product.images != null && product.images!.isNotEmpty) product.images!.first,
    ];

    String? resolved;
    for (final url in candidateUrls) {
      if (url == null || url.isEmpty) continue;
      if (url.startsWith('http://') || url.startsWith('https://')) {
        resolved = url;
        break;
      }

      final sanitized = url.startsWith('/') ? url.substring(1) : url;
      if (sanitized.startsWith('storage/')) {
        resolved = '${ApiConfig.imageBaseUrl}/$sanitized';
      } else if (sanitized.startsWith('images/')) {
        resolved = '${ApiConfig.imageBaseUrl}/$sanitized';
      } else if (sanitized.startsWith('products/')) {
        resolved = '${ApiConfig.imageBaseUrl}/storage/$sanitized';
      } else {
        resolved = '${ApiConfig.imageBaseUrl}/$sanitized';
      }
      break;
    }

    if (resolved == null) {
      return const Icon(
        Icons.image_not_supported,
        size: 32,
        color: AppColors.grey400,
      );
    }

    return Image.network(
      resolved,
      fit: BoxFit.cover,
      errorBuilder: (context, error, stackTrace) {
        return const Icon(
          Icons.image_not_supported,
          size: 32,
          color: AppColors.grey400,
        );
      },
    );
  }

  void _showDeleteDialog(ProductModel product, SellerProvider sellerProvider) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Supprimer le produit'),
        content: Text('Êtes-vous sûr de vouloir supprimer "${product.name}" ? Cette action est irréversible.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Annuler'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.of(context).pop();
              await _deleteProduct(product.id, sellerProvider);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
              foregroundColor: AppColors.white,
            ),
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );
  }

  Future<void> _deleteProduct(int productId, SellerProvider sellerProvider) async {
    try {
      final result = await sellerProvider.deleteProduct(productId);
      
      if (result['success']) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text('Produit supprimé avec succès'),
            backgroundColor: AppColors.success,
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message'] ?? 'Erreur lors de la suppression'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Erreur: $e'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }
}
