import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../models/cart_model.dart';
import '../utils/constants.dart';
import '../utils/helpers.dart';
import '../config/api_config.dart';

class CartItemCard extends StatefulWidget {
  final CartItemModel item;
  final VoidCallback onTap;
  final Function(int) onQuantityChanged;
  final VoidCallback onRemove;

  const CartItemCard({
    super.key,
    required this.item,
    required this.onTap,
    required this.onQuantityChanged,
    required this.onRemove,
  });

  @override
  State<CartItemCard> createState() => _CartItemCardState();
}

class _CartItemCardState extends State<CartItemCard> {
  /// Obtenir l'URL de l'image principale du produit
  String _getProductImageUrl() {
    final product = widget.item.product;
    if (product == null) return '';
    
    // D'abord vérifier le champ image
    if (product.image != null && product.image!.isNotEmpty) {
      if (product.image!.startsWith('http')) {
        return product.image!;
      }
      return '${ApiConfig.imageBaseUrl}/${product.image}';
    }
    
    // Sinon, prendre la première image du tableau images
    if (product.images != null && product.images!.isNotEmpty) {
      final firstImage = product.images!.first;
      if (firstImage.startsWith('http')) {
        return firstImage;
      }
      return '${ApiConfig.imageBaseUrl}/$firstImage';
    }
    
    return '';
  }

  @override
  Widget build(BuildContext context) {
    final product = widget.item.product;
    if (product == null) return const SizedBox.shrink();
    
    final imageUrl = _getProductImageUrl();

    return Dismissible(
        key: Key('cart_${widget.item.id}'),
        direction: DismissDirection.endToStart,
        confirmDismiss: (direction) async {
          // Afficher une confirmation
          final result = await showDialog<bool>(
            context: context,
            builder: (context) => AlertDialog(
              title: const Text('Retirer du panier'),
              content: Text('Retirer "${product.name}" de votre panier ?'),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context, false),
                  child: const Text('Annuler'),
                ),
                TextButton(
                  onPressed: () => Navigator.pop(context, true),
                  style: TextButton.styleFrom(
                    foregroundColor: AppColors.error,
                  ),
                  child: const Text('Retirer'),
                ),
              ],
            ),
          );
          return result ?? false;
        },
        onDismissed: (direction) {
          widget.onRemove();
        },
        background: Container(
          alignment: Alignment.centerRight,
          padding: const EdgeInsets.only(right: 20),
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [
                AppColors.error.withOpacity(0.7),
                AppColors.error,
              ],
            ),
            borderRadius: BorderRadius.circular(12),
          ),
          child: const Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.delete_outline,
                color: Colors.white,
                size: 32,
              ),
              SizedBox(height: 4),
              Text(
                'Supprimer',
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
        ),
        child: Card(
          elevation: 2,
          margin: const EdgeInsets.symmetric(vertical: 6),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          child: InkWell(
            onTap: widget.onTap,
            borderRadius: BorderRadius.circular(12),
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Image avec hero animation
                  Hero(
                    tag: 'product_${product.id}',
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(10),
                      child: imageUrl.isNotEmpty
                          ? CachedNetworkImage(
                              imageUrl: imageUrl,
                              width: 90,
                              height: 90,
                              fit: BoxFit.cover,
                              placeholder: (context, url) => Container(
                                color: AppColors.background,
                                child: const Center(
                                  child: CircularProgressIndicator(strokeWidth: 2),
                                ),
                              ),
                              errorWidget: (context, url, error) {
                                print('❌ [CART IMAGE] Erreur chargement: $url');
                                return Container(
                                  width: 90,
                                  height: 90,
                                  color: AppColors.grey100,
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(
                                        Icons.shopping_bag_outlined,
                                        size: 35,
                                        color: AppColors.primary.withOpacity(0.5),
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        'K',
                                        style: TextStyle(
                                          fontSize: 16,
                                          fontWeight: FontWeight.bold,
                                          color: AppColors.primary,
                                        ),
                                      ),
                                    ],
                                  ),
                                );
                              },
                            )
                          : Container(
                              width: 90,
                              height: 90,
                              color: AppColors.grey100,
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(
                                    Icons.shopping_bag_outlined,
                                    size: 35,
                                    color: AppColors.primary.withOpacity(0.5),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    'K',
                                    style: TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold,
                                      color: AppColors.primary,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                    ),
                  ),
                  const SizedBox(width: 12),

                  // Détails du produit
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Nom
                        Text(
                          product.name,
                          style: AppTextStyles.body.copyWith(
                            fontWeight: FontWeight.w600,
                            fontSize: 15,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                // ✅ Afficher les attributs sélectionnés
                if (widget.item.attributes != null && widget.item.attributes!.isNotEmpty) ...[
                  const SizedBox(height: 6),
                  Wrap(
                    spacing: 6,
                    runSpacing: 4,
                    children: widget.item.attributes!.entries.where((entry) {
                      // Filtrer les attributs vides ou avec seulement des IDs numériques
                      final key = entry.key.toString();
                      final value = entry.value.toString();
                      // Ne pas afficher si la clé est juste un chiffre ET la valeur aussi
                      if (int.tryParse(key) != null && int.tryParse(value) != null) {
                        return false; // Anciens attributs stockés avec des IDs
                      }
                      return key.isNotEmpty && value.isNotEmpty;
                    }).map((entry) {
                      return Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [
                              AppColors.info.withOpacity(0.15),
                              AppColors.info.withOpacity(0.08),
                            ],
                          ),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(
                            color: AppColors.info.withOpacity(0.4),
                            width: 1,
                          ),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.check_circle_outline,
                              size: 12,
                              color: AppColors.info,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              '${entry.key}: ${entry.value}',
                              style: AppTextStyles.caption.copyWith(
                                color: AppColors.info,
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      );
                    }).toList(),
                  ),
                ],
                        const SizedBox(height: 6),

                        // Prix avec réduction si applicable
                        Row(
                          children: [
                            Text(
                              Helpers.formatPrice(widget.item.price),
                              style: AppTextStyles.body.copyWith(
                                color: AppColors.primary,
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                              ),
                            ),
                            if (product.hasDiscount) ...[
                              const SizedBox(width: 8),
                              Text(
                                Helpers.formatPrice(product.oldPrice!),
                                style: AppTextStyles.caption.copyWith(
                                  decoration: TextDecoration.lineThrough,
                                  color: AppColors.textLight,
                                ),
                              ),
                              const SizedBox(width: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 6,
                                  vertical: 2,
                                ),
                                decoration: BoxDecoration(
                                  color: AppColors.error,
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: Text(
                                  '-${product.discountPercentage?.toInt()}%',
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ],
                          ],
                        ),
                        const SizedBox(height: 8),

                        // Contrôles de quantité moderne
                        Row(
                          children: [
                            _buildQuantityControl(),
                            const Spacer(),
                            // Total pour cet article
                            Text(
                              Helpers.formatPrice(widget.item.total),
                              style: AppTextStyles.body.copyWith(
                                fontWeight: FontWeight.bold,
                                color: AppColors.textDark,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      );
  }

  Widget _buildQuantityControl() {
    final maxStock = widget.item.product?.stock ?? 99;

    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppColors.primary.withOpacity(0.1),
            AppColors.primary.withOpacity(0.05),
          ],
        ),
        borderRadius: BorderRadius.circular(25),
        border: Border.all(
          color: AppColors.primary.withOpacity(0.3),
          width: 1.5,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Bouton -
          _buildQuantityButton(
            icon: Icons.remove,
            onPressed: widget.item.quantity > 1
                ? () => widget.onQuantityChanged(widget.item.quantity - 1)
                : null,
          ),

          // Quantité avec animation
          AnimatedSwitcher(
            duration: const Duration(milliseconds: 200),
            transitionBuilder: (child, animation) {
              return ScaleTransition(
                scale: animation,
                child: child,
              );
            },
            child: Container(
              key: ValueKey(widget.item.quantity),
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Text(
                '${widget.item.quantity}',
                style: AppTextStyles.body.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppColors.primary,
                  fontSize: 16,
                ),
              ),
            ),
          ),

          // Bouton +
          _buildQuantityButton(
            icon: Icons.add,
            onPressed: widget.item.quantity < maxStock
                ? () => widget.onQuantityChanged(widget.item.quantity + 1)
                : null,
          ),
        ],
      ),
    );
  }

  Widget _buildQuantityButton({
    required IconData icon,
    required VoidCallback? onPressed,
  }) {
    final isEnabled = onPressed != null;

    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onPressed,
        borderRadius: BorderRadius.circular(20),
        child: Container(
          padding: const EdgeInsets.all(8),
          child: Icon(
            icon,
            size: 20,
            color: isEnabled
                ? AppColors.primary
                : AppColors.textLight.withOpacity(0.5),
          ),
        ),
      ),
    );
  }
}

