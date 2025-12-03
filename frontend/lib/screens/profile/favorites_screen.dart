import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/favorites_provider.dart';
import '../../utils/constants.dart';
import '../../widgets/modern_product_card.dart';
import '../products/product_details_screen.dart';

class FavoritesScreen extends StatefulWidget {
  const FavoritesScreen({super.key});

  @override
  State<FavoritesScreen> createState() => _FavoritesScreenState();
}

class _FavoritesScreenState extends State<FavoritesScreen> {
  @override
  void initState() {
    super.initState();
    // Charger les favoris au démarrage
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<FavoritesProvider>(context, listen: false).loadFavorites();
    });
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final favoritesProvider = Provider.of<FavoritesProvider>(context);

    if (!authProvider.isAuthenticated) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Mes favoris'),
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.favorite_border,
                size: 100,
                color: AppColors.textLight.withOpacity(0.5),
              ),
              const SizedBox(height: 24),
              Text(
                'Connectez-vous pour voir vos favoris',
                style: AppTextStyles.h3.copyWith(
                  color: AppColors.textLight,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () {
                  Navigator.pushNamed(context, '/login');
                },
                child: const Text('Se connecter'),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text('Mes favoris (${favoritesProvider.favoritesCount})'),
        actions: [
          if (favoritesProvider.favorites.isNotEmpty)
            IconButton(
              icon: const Icon(Icons.refresh),
              onPressed: () {
                favoritesProvider.loadFavorites();
              },
            ),
        ],
      ),
      body: favoritesProvider.isLoading
          ? const Center(child: CircularProgressIndicator())
          : favoritesProvider.error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.error_outline,
                        size: 100,
                        color: AppColors.error.withOpacity(0.5),
                      ),
                      const SizedBox(height: 24),
                      Text(
                        'Erreur lors du chargement',
                        style: AppTextStyles.h3.copyWith(
                          color: AppColors.textLight,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Text(
                        favoritesProvider.error!,
                        style: AppTextStyles.body.copyWith(
                          color: AppColors.textLight,
                        ),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 24),
                      ElevatedButton(
                        onPressed: () {
                          favoritesProvider.clearError();
                          favoritesProvider.loadFavorites();
                        },
                        child: const Text('Réessayer'),
                      ),
                    ],
                  ),
                )
              : favoritesProvider.favorites.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.favorite_border,
                            size: 100,
                            color: AppColors.textLight.withOpacity(0.5),
                          ),
                          const SizedBox(height: 24),
                          Text(
                            'Aucun favori pour le moment',
                            style: AppTextStyles.h3.copyWith(
                              color: AppColors.textLight,
                            ),
                          ),
                          const SizedBox(height: 12),
                          Text(
                            'Ajoutez des produits à vos favoris\nen cliquant sur l\'icône cœur',
                            style: AppTextStyles.body.copyWith(
                              color: AppColors.textLight,
                            ),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 24),
                          ElevatedButton.icon(
                            onPressed: () {
                              Navigator.pop(context);
                            },
                            icon: const Icon(Icons.shopping_bag),
                            label: const Text('Découvrir des produits'),
                          ),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: () => favoritesProvider.loadFavorites(),
                      child: GridView.builder(
                        padding: const EdgeInsets.all(AppSizes.space4),
                        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 2,
                          childAspectRatio: 0.75,
                          crossAxisSpacing: AppSizes.space3,
                          mainAxisSpacing: AppSizes.space3,
                        ),
                        itemCount: favoritesProvider.favorites.length,
                        itemBuilder: (context, index) {
                          final product = favoritesProvider.favorites[index];
                          return Dismissible(
                            key: Key('favorite_${product.id}'),
                            direction: DismissDirection.endToStart,
                            confirmDismiss: (direction) async {
                              // Afficher une confirmation
                              final result = await showDialog<bool>(
                                context: context,
                                builder: (context) => AlertDialog(
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(AppSizes.radius2XL),
                                  ),
                                  title: Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.all(AppSizes.space2),
                                        decoration: BoxDecoration(
                                          color: AppColors.errorLight,
                                          borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                                        ),
                                        child: const Icon(Icons.favorite_border, color: AppColors.error),
                                      ),
                                      const SizedBox(width: AppSizes.space3),
                                      const Text('Retirer des favoris'),
                                    ],
                                  ),
                                  content: Text('Retirer "${product.name}" de vos favoris ?'),
                                  actions: [
                                    TextButton(
                                      onPressed: () => Navigator.pop(context, false),
                                      child: const Text('Annuler'),
                                    ),
                                    Container(
                                      decoration: BoxDecoration(
                                        gradient: const LinearGradient(
                                          colors: [AppColors.error, Color(0xFFDC2626)],
                                        ),
                                        borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                                      ),
                                      child: ElevatedButton(
                                        onPressed: () => Navigator.pop(context, true),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: Colors.transparent,
                                          shadowColor: Colors.transparent,
                                        ),
                                        child: const Text('Retirer'),
                                      ),
                                    ),
                                  ],
                                ),
                              );
                              return result ?? false;
                            },
                            onDismissed: (direction) async {
                              // Retirer des favoris
                              await favoritesProvider.toggleFavorite(product.id);
                              
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Row(
                                      children: [
                                        const Icon(Icons.favorite_border, color: Colors.white),
                                        const SizedBox(width: 12),
                                        Text('${product.name} retiré des favoris'),
                                      ],
                                    ),
                                    backgroundColor: AppColors.error,
                                    behavior: SnackBarBehavior.floating,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                                    ),
                                  ),
                                );
                              }
                            },
                            background: Container(
                              alignment: Alignment.centerRight,
                              padding: const EdgeInsets.only(right: 20),
                              decoration: BoxDecoration(
                                gradient: const LinearGradient(
                                  colors: [AppColors.error, Color(0xFFDC2626)],
                                ),
                                borderRadius: BorderRadius.circular(AppSizes.radiusXL),
                              ),
                              child: const Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(
                                    Icons.favorite_border,
                                    color: Colors.white,
                                    size: 32,
                                  ),
                                  SizedBox(height: 4),
                                  Text(
                                    'Retirer',
                                    style: TextStyle(
                                      color: Colors.white,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            child: ModernProductCard(
                              product: product,
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => ProductDetailsScreen(product: product),
                                  ),
                                );
                              },
                            ),
                          );
                        },
                      ),
                    ),
    );
  }
}