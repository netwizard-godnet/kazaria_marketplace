import 'package:flutter/material.dart';
import '../../models/product_model.dart';
import '../../services/recent_products_service.dart';
import '../../utils/constants.dart';
import '../../widgets/modern_product_card.dart';
import 'product_details_screen.dart';

class RecentProductsScreen extends StatefulWidget {
  const RecentProductsScreen({super.key});

  @override
  State<RecentProductsScreen> createState() => _RecentProductsScreenState();
}

class _RecentProductsScreenState extends State<RecentProductsScreen> {
  List<ProductModel> _recentProducts = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadRecentProducts();
  }

  Future<void> _loadRecentProducts() async {
    setState(() => _isLoading = true);
    try {
      final products = await RecentProductsService.getRecentProducts();
      if (mounted) {
        setState(() {
          _recentProducts = products;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
      print('Erreur chargement produits récents: $e');
    }
  }

  Future<void> _clearAll() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Effacer l\'historique'),
        content: const Text('Voulez-vous vraiment effacer tous les produits récemment vus ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Effacer'),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      await RecentProductsService.clearRecentProducts();
      _loadRecentProducts();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Historique effacé'),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Produits récemment vus'),
        actions: [
          if (_recentProducts.isNotEmpty)
            IconButton(
              icon: const Icon(Icons.delete_outline),
              onPressed: _clearAll,
              tooltip: 'Effacer l\'historique',
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _recentProducts.isEmpty
              ? _buildEmptyState()
              : RefreshIndicator(
                  onRefresh: _loadRecentProducts,
                  child: GridView.builder(
                    padding: const EdgeInsets.all(AppSizes.paddingMedium),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      childAspectRatio: 0.75,
                      crossAxisSpacing: AppSizes.space3,
                      mainAxisSpacing: AppSizes.space3,
                    ),
                    itemCount: _recentProducts.length,
                    itemBuilder: (context, index) {
                      final product = _recentProducts[index];
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
                ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.history,
            size: 80,
            color: AppColors.textLight.withOpacity(0.5),
          ),
          const SizedBox(height: AppSizes.space4),
          Text(
            'Aucun produit récemment vu',
            style: AppTextStyles.h3.copyWith(
              color: AppColors.textLight,
            ),
          ),
          const SizedBox(height: AppSizes.space2),
          Text(
            'Les produits que vous consultez apparaîtront ici',
            style: AppTextStyles.bodyMedium.copyWith(
              color: AppColors.textLight,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: AppSizes.space6),
          ElevatedButton.icon(
            onPressed: () => Navigator.pop(context),
            icon: const Icon(Icons.shopping_bag),
            label: const Text('Découvrir des produits'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(
                horizontal: AppSizes.space6,
                vertical: AppSizes.space3,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

