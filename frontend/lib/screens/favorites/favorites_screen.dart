import 'package:flutter/material.dart';
import '../../utils/constants.dart';
import '../../models/product_model.dart';
import '../../widgets/product_card.dart';

class FavoritesScreen extends StatefulWidget {
  const FavoritesScreen({super.key});

  @override
  State<FavoritesScreen> createState() => _FavoritesScreenState();
}

class _FavoritesScreenState extends State<FavoritesScreen> {
  final List<ProductModel> _favorites = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadFavorites();
  }

  Future<void> _loadFavorites() async {
    setState(() {
      _isLoading = true;
    });

    // TODO: Charger les vrais favoris depuis l'API
    await Future.delayed(const Duration(seconds: 1));

    setState(() {
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Mes favoris'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadFavorites,
              child: _favorites.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.favorite_border,
                            size: 100,
                            color: AppColors.textLight,
                          ),
                          const SizedBox(height: 16),
                          const Text(
                            'Aucun favori',
                            style: AppTextStyles.h3,
                          ),
                          const SizedBox(height: 8),
                          const Text(
                            'Ajoutez des produits à vos favoris',
                            style: AppTextStyles.bodySmall,
                          ),
                        ],
                      ),
                    )
                  : GridView.builder(
                      padding: const EdgeInsets.all(AppSizes.paddingMedium),
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 2,
                        crossAxisSpacing: 12,
                        mainAxisSpacing: 12,
                        childAspectRatio: 0.7,
                      ),
                      itemCount: _favorites.length,
                      itemBuilder: (context, index) {
                        return ProductCard(
                          product: _favorites[index],
                          isFavorite: true,
                          onTap: () {
                            // TODO: Navigate to product details
                          },
                          onFavorite: () {
                            // TODO: Remove from favorites
                          },
                        );
                      },
                    ),
            ),
    );
  }
}

