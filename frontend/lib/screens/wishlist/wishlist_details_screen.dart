import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:share_plus/share_plus.dart';
import '../../providers/wishlist_provider.dart';
import '../../utils/constants.dart';
import '../../config/api_config.dart';
import '../../widgets/product_card.dart';
import '../products/product_details_screen.dart';
import 'wishlist_share_management_screen.dart';

class WishlistDetailsScreen extends StatefulWidget {
  final int wishlistId;
  final String wishlistName;

  const WishlistDetailsScreen({
    super.key,
    required this.wishlistId,
    required this.wishlistName,
  });

  @override
  State<WishlistDetailsScreen> createState() => _WishlistDetailsScreenState();
}

class _WishlistDetailsScreenState extends State<WishlistDetailsScreen> {
  bool _isCreating = false; // ✅ Variable pour gérer le loading lors du partage

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        context.read<WishlistProvider>().loadWishlist(widget.wishlistId);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.wishlistName),
        actions: [
          IconButton(
            icon: const Icon(Icons.people_outline),
            tooltip: 'Gérer les partages',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => WishlistShareManagementScreen(
                    wishlistId: widget.wishlistId,
                    wishlistName: widget.wishlistName,
                  ),
                ),
              );
            },
          ),
          // Partager
          _isCreating
              ? const Padding(
                  padding: EdgeInsets.all(12.0),
                  child: SizedBox(
                    width: 24,
                    height: 24,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  ),
                )
              : IconButton(
                  icon: const Icon(Icons.share),
                  onPressed: _shareWishlist,
                ),
          // Menu (modifier/supprimer)
          PopupMenuButton(
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'edit',
                child: Row(
                  children: [
                    Icon(Icons.edit, size: 20),
                    SizedBox(width: 8),
                    Text('Modifier'),
                  ],
                ),
              ),
              const PopupMenuItem(
                value: 'delete',
                child: Row(
                  children: [
                    Icon(Icons.delete, color: AppColors.error, size: 20),
                    SizedBox(width: 8),
                    Text('Supprimer', style: TextStyle(color: AppColors.error)),
                  ],
                ),
              ),
            ],
            onSelected: (value) {
              if (value == 'delete') {
                _confirmDelete();
              }
            },
          ),
        ],
      ),
      body: Consumer<WishlistProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading && provider.currentWishlist == null) {
            return const Center(child: CircularProgressIndicator());
          }

          final wishlist = provider.currentWishlist;
          if (wishlist == null) {
            return const Center(child: Text('Liste non trouvée'));
          }

          final items = wishlist['items_with_products'] as List? ?? [];

          if (items.isEmpty) {
            return _buildEmptyState();
          }

          return RefreshIndicator(
            onRefresh: () => provider.loadWishlist(widget.wishlistId),
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                // Info de la liste
                _buildWishlistInfo(wishlist),
                const SizedBox(height: 24),

                // Produits
                GridView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    childAspectRatio: 0.7,
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                  ),
                  itemCount: items.length,
                  itemBuilder: (context, index) {
                    final item = items[index];
                    final product = item['product'];

                    if (product == null) return const SizedBox.shrink();

                    return _buildWishlistProductCard(item, product, provider);
                  },
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildWishlistInfo(Map<String, dynamic> wishlist) {
    final description = wishlist['description'] as String?;
    final itemsCount = wishlist['items_count'] ?? 0;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: AppColors.primaryGradient,
        borderRadius: BorderRadius.circular(16),
        boxShadow: AppShadows.shadowMD,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (description != null && description.isNotEmpty) ...[
            Text(
              description,
              style: const TextStyle(color: Colors.white, fontSize: 14),
            ),
            const SizedBox(height: 12),
          ],
          Row(
            children: [
              const Icon(Icons.favorite, color: Colors.white, size: 18),
              const SizedBox(width: 8),
              Text(
                '$itemsCount produit${itemsCount > 1 ? 's' : ''}',
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildWishlistProductCard(
    Map<String, dynamic> item,
    Map<String, dynamic> productData,
    WishlistProvider provider,
  ) {
    // Convertir le Map en ProductModel pour utiliser ProductCard
    final product = _parseProductModel(productData);
    final productId = productData['id'] ?? productData['product_id'];
    final heroTag = 'wishlist_${item['id']}_${productId ?? ''}';

    return Stack(
      children: [
        ProductCard(
          product: product,
          heroTag: heroTag,
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) =>
                    ProductDetailsScreen(product: product, heroTag: heroTag),
              ),
            );
          },
        ),
        // Bouton supprimer
        Positioned(
          top: 8,
          left: 8,
          child: GestureDetector(
            onTap: () => _removeProduct(item['id'], provider),
            child: Container(
              padding: const EdgeInsets.all(6),
              decoration: const BoxDecoration(
                color: AppColors.error,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.close, color: Colors.white, size: 16),
            ),
          ),
        ),
      ],
    );
  }

  // Helper pour convertir Map en ProductModel
  dynamic _parseProductModel(Map<String, dynamic> data) {
    // Importer et utiliser ProductModel.fromJson
    // Pour simplifier, on retourne le data tel quel
    // Le ProductCard devra être adapté ou on crée un wrapper
    return data;
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.shopping_bag_outlined,
            size: 80,
            color: AppColors.textLight.withOpacity(0.5),
          ),
          const SizedBox(height: 16),
          const Text('Cette liste est vide', style: AppTextStyles.h3),
          const SizedBox(height: 8),
          Text(
            'Parcourez les produits et ajoutez-les\nà cette liste',
            style: AppTextStyles.body.copyWith(color: AppColors.textLight),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Future<void> _shareWishlist() async {
    final provider = context.read<WishlistProvider>();

    // Afficher un dialogue pour choisir le mode de partage
    final result = await showDialog<Map<String, dynamic>>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Partager la liste'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.link),
              title: const Text('Créer un lien de partage'),
              onTap: () => Navigator.pop(context, {'type': 'link'}),
            ),
            ListTile(
              leading: const Icon(Icons.email),
              title: const Text('Partager par email'),
              onTap: () => Navigator.pop(context, {'type': 'email'}),
            ),
          ],
        ),
      ),
    );

    if (result == null) return;

    setState(() => _isCreating = true);

    final response = await provider.shareWishlist(widget.wishlistId);

    setState(() => _isCreating = false);

    if (response['success'] == true && mounted) {
      final wishlist = response['wishlist'] as Map<String, dynamic>?;
      final shareToken = wishlist?['share_token'] as String?;
      final shareUrl = shareToken != null
          ? '${ApiConfig.imageBaseUrl}/wishlists/shared/$shareToken'
          : null;

      if (shareUrl != null) {
        if (result['type'] == 'link') {
          // Copier le lien
          await Clipboard.setData(ClipboardData(text: shareUrl));
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('🔗 Lien copié dans le presse-papiers'),
                backgroundColor: AppColors.success,
              ),
            );
          }
        } else {
          // Partager via Share dialog
          Share.share(
            'Découvrez ma liste de souhaits "${widget.wishlistName}" : $shareUrl',
            subject: 'Ma liste de souhaits Kazaria',
          );
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Erreur lors de la génération du lien'),
              backgroundColor: AppColors.error,
            ),
          );
        }
      }
    }
  }

  Future<void> _removeProduct(int itemId, WishlistProvider provider) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Retirer le produit'),
        content: const Text('Voulez-vous retirer ce produit de la liste ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: AppColors.error),
            child: const Text('Retirer'),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      final response = await provider.removeProduct(widget.wishlistId, itemId);

      if (response['success'] == true && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('✅ Produit retiré'),
            backgroundColor: AppColors.success,
          ),
        );
      }
    }
  }

  Future<void> _confirmDelete() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Supprimer la liste'),
        content: const Text(
          'Êtes-vous sûr de vouloir supprimer cette liste ? '
          'Cette action est irréversible.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: AppColors.error),
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );

    if (confirmed == true && mounted) {
      final provider = context.read<WishlistProvider>();
      final response = await provider.deleteWishlist(widget.wishlistId);

      if (response['success'] == true && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('✅ Liste supprimée'),
            backgroundColor: AppColors.success,
          ),
        );
        Navigator.pop(context);
      }
    }
  }
}
