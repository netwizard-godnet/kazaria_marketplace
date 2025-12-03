import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/wishlist_provider.dart';
import '../../utils/constants.dart';
import 'wishlist_details_screen.dart';
import 'create_wishlist_dialog.dart';
import 'wishlist_alerts_screen.dart';

class WishlistsScreen extends StatefulWidget {
  const WishlistsScreen({super.key});

  @override
  State<WishlistsScreen> createState() => _WishlistsScreenState();
}

class _WishlistsScreenState extends State<WishlistsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        context.read<WishlistProvider>().loadWishlists();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Mes Listes de Souhaits'),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_active_outlined),
            tooltip: 'Alertes actives',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const WishlistAlertsScreen()),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => _showCreateWishlistDialog(),
          ),
        ],
      ),
      body: Consumer<WishlistProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading && provider.wishlists.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null) {
            return _buildErrorState(provider);
          }

          if (provider.wishlists.isEmpty) {
            return _buildEmptyState();
          }

          return RefreshIndicator(
            onRefresh: () => provider.loadWishlists(),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: provider.wishlists.length,
              itemBuilder: (context, index) {
                final wishlist = provider.wishlists[index];
                return _buildWishlistCard(wishlist, provider);
              },
            ),
          );
        },
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showCreateWishlistDialog(),
        backgroundColor: AppColors.primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text(
          'Nouvelle liste',
          style: TextStyle(color: Colors.white),
        ),
      ),
    );
  }

  Widget _buildWishlistCard(Map<String, dynamic> wishlist, WishlistProvider provider) {
    final icon = wishlist['icon'] as String? ?? '❤️';
    final name = wishlist['name'] as String? ?? 'Ma liste';
    final itemsCount = wishlist['items_count'] ?? 0;
    final isDefault = wishlist['is_default'] == true || wishlist['is_default'] == 1;
    final privacy = wishlist['privacy'] as String? ?? 'private';

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: InkWell(
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => WishlistDetailsScreen(
                wishlistId: wishlist['id'],
                wishlistName: name,
              ),
            ),
          );
        },
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              // Icône
              Container(
                width: 60,
                height: 60,
                decoration: BoxDecoration(
                  gradient: AppColors.primaryGradient,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Center(
                  child: Text(
                    icon,
                    style: const TextStyle(fontSize: 30),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              
              // Info
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            name,
                            style: AppTextStyles.h4,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (isDefault)
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: AppColors.primary.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Text(
                              'Par défaut',
                              style: TextStyle(
                                color: AppColors.primary,
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '$itemsCount produit${itemsCount > 1 ? 's' : ''}',
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.textLight,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Icon(
                          _getPrivacyIcon(privacy),
                          size: 14,
                          color: AppColors.textLight,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          _getPrivacyLabel(privacy),
                          style: AppTextStyles.caption.copyWith(
                            color: AppColors.textLight,
                            fontSize: 11,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              
              // Flèche
              const Icon(
                Icons.arrow_forward_ios,
                size: 18,
                color: AppColors.textLight,
              ),
            ],
          ),
        ),
      ),
    );
  }

  IconData _getPrivacyIcon(String privacy) {
    switch (privacy) {
      case 'public':
        return Icons.public;
      case 'friends':
        return Icons.people;
      default:
        return Icons.lock;
    }
  }

  String _getPrivacyLabel(String privacy) {
    switch (privacy) {
      case 'public':
        return 'Publique';
      case 'friends':
        return 'Amis';
      default:
        return 'Privée';
    }
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.favorite_border,
            size: 100,
            color: AppColors.textLight.withOpacity(0.3),
          ),
          const SizedBox(height: 24),
          const Text(
            'Aucune liste de souhaits',
            style: AppTextStyles.h3,
          ),
          const SizedBox(height: 8),
          Text(
            'Créez votre première liste\npour organiser vos produits favoris',
            style: AppTextStyles.body.copyWith(
              color: AppColors.textLight,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 32),
          ElevatedButton.icon(
            onPressed: () => _showCreateWishlistDialog(),
            icon: const Icon(Icons.add),
            label: const Text('Créer une liste'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorState(WishlistProvider provider) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, size: 64, color: AppColors.error),
          const SizedBox(height: 16),
          const Text('Erreur de chargement', style: AppTextStyles.h3),
          const SizedBox(height: 8),
          Text(
            provider.error!,
            style: AppTextStyles.body.copyWith(color: AppColors.textLight),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () => provider.loadWishlists(),
            child: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }

  void _showCreateWishlistDialog() {
    showDialog(
      context: context,
      builder: (context) => const CreateWishlistDialog(),
    ).then((result) {
      if (result == true) {
        // Recharger les listes
        context.read<WishlistProvider>().loadWishlists();
      }
    });
  }
}

