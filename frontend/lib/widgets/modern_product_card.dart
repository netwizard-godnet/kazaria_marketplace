import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../models/product_model.dart';
import '../utils/constants.dart';
import '../utils/helpers.dart';
import '../providers/favorites_provider.dart';
import '../config/api_config.dart';

class ModernProductCard extends StatefulWidget {
  final ProductModel product;
  final VoidCallback? onTap;
  final VoidCallback? onFavorite;
  final bool? isFavorite;
  final bool enableComparison; // ✅ Activer la sélection pour comparaison
  final bool? isSelected; // ✅ État de sélection
  final VoidCallback? onComparisonToggle; // ✅ Callback pour toggle
  final bool showQuickBuyButton; // ✅ Afficher le bouton d'achat rapide
  final VoidCallback? onQuickBuy; // ✅ Callback pour l'achat rapide

  const ModernProductCard({
    super.key,
    required this.product,
    this.onTap,
    this.onFavorite,
    this.isFavorite,
    this.enableComparison = false,
    this.isSelected,
    this.onComparisonToggle,
    this.showQuickBuyButton = false,
    this.onQuickBuy,
  });

  @override
  State<ModernProductCard> createState() => _ModernProductCardState();
}

class _ModernProductCardState extends State<ModernProductCard>
    with SingleTickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _scaleAnimation;
  late Animation<double> _favoriteAnimation;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: AppAnimations.normal,
      vsync: this,
    );
    _scaleAnimation = Tween<double>(begin: 1.0, end: 0.98).animate(
      CurvedAnimation(
        parent: _animationController,
        curve: AppAnimations.easeOut,
      ),
    );
    _favoriteAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _animationController,
        curve: AppAnimations.bounceOut,
      ),
    );
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Widget _buildPlaceholder() {
    return Container(
      color: AppColors.grey100,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.shopping_bag_outlined,
            size: 50,
            color: AppColors.primary.withOpacity(0.5),
          ),
          const SizedBox(height: 8),
          Text(
            'K',
            style: TextStyle(
              fontSize: 32,
              fontWeight: FontWeight.bold,
              color: AppColors.primary,
            ),
          ),
        ],
      ),
    );
  }

  void _onTapDown(TapDownDetails details) {
    _animationController.forward();
  }

  void _onTapUp(TapUpDetails details) {
    _animationController.reverse();
  }

  void _onTapCancel() {
    _animationController.reverse();
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<FavoritesProvider>(
      builder: (context, favoritesProvider, _) {
        final isProductFavorite =
            widget.isFavorite ??
            favoritesProvider.isFavorite(widget.product.id);

        return AnimatedBuilder(
          animation: _scaleAnimation,
          builder: (context, child) {
            return Transform.scale(
              scale: _scaleAnimation.value,
              child: GestureDetector(
                onTapDown: _onTapDown,
                onTapUp: _onTapUp,
                onTapCancel: _onTapCancel,
                onTap: widget.onTap,
                child: Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(AppSizes.radiusXL),
                    boxShadow: AppShadows.shadowLG,
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(AppSizes.radiusXL),
                    child: Container(
                      decoration: const BoxDecoration(
                        gradient: AppColors.cardGradient,
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Image avec overlay moderne
                          _buildImageSection(
                            isProductFavorite,
                            favoritesProvider,
                          ),

                          // Contenu de la carte
                          Flexible(
                            child: Padding(
                              padding: const EdgeInsets.fromLTRB(8, 6, 8, 8),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  // Nom du produit
                                  Flexible(child: _buildProductName()),

                                  const SizedBox(height: 2),

                                  // Rating avec style moderne
                                  _buildRating(),

                                  const SizedBox(height: 2),

                                  // Prix avec design moderne
                                  _buildPrice(),

                                  // ✅ Bouton d'achat rapide
                                  if (widget.showQuickBuyButton) ...[
                                    const SizedBox(height: 6),
                                    _buildQuickBuyButton(),
                                  ],
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }

  /// Obtenir l'URL de l'image principale du produit
  String? _getProductImageUrl() {
    // D'abord vérifier le champ image
    if (widget.product.image != null && widget.product.image!.isNotEmpty) {
      return _fixImageUrl(widget.product.image!);
    }

    // Sinon, prendre la première image du tableau images
    if (widget.product.images != null && widget.product.images!.isNotEmpty) {
      return _fixImageUrl(widget.product.images!.first);
    }

    // Aucune image disponible
    return null;
  }

  /// Corriger et construire l'URL d'image
  String _fixImageUrl(String imagePath) {
    // Si l'URL est déjà complète et correcte
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return imagePath;
    }

    // ✅ CORRECTION : Si c'est "http:" sans "//" (erreur commune)
    if (imagePath.startsWith('http:') && !imagePath.startsWith('http://')) {
      return imagePath.replaceFirst('http:', 'http://');
    }

    // ✅ CORRECTION : Si c'est "https:" sans "//" (erreur commune)
    if (imagePath.startsWith('https:') && !imagePath.startsWith('https://')) {
      return imagePath.replaceFirst('https:', 'https://');
    }

    // Sinon, construire l'URL complète
    return '${ApiConfig.imageBaseUrl}/$imagePath';
  }

  Widget _buildImageSection(
    bool isFavorite,
    FavoritesProvider favoritesProvider,
  ) {
    final imageUrl = _getProductImageUrl();

    return Stack(
      children: [
        // Image principale
        Container(
          height: 120,
          width: double.infinity,
          child: imageUrl != null
              ? CachedNetworkImage(
                  imageUrl: imageUrl,
                  fit: BoxFit.contain,
                  alignment: Alignment.center,
                  placeholder: (context, url) => Container(
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [AppColors.grey100, AppColors.grey200],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                    ),
                    child: const Center(
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation<Color>(
                          AppColors.primary,
                        ),
                      ),
                    ),
                  ),
                  errorWidget: (context, url, error) => _buildPlaceholder(),
                )
              : _buildPlaceholder(),
        ),

        // Overlay gradient moderne
        Positioned.fill(
          child: Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [Colors.transparent, Colors.black.withOpacity(0.1)],
                stops: const [0.6, 1.0],
              ),
            ),
          ),
        ),

        // Badge de réduction moderne
        if (widget.product.hasDiscount)
          Positioned(
            top: AppSizes.space3,
            left: AppSizes.space3,
            child: Container(
              padding: const EdgeInsets.symmetric(
                horizontal: AppSizes.space2,
                vertical: AppSizes.space1,
              ),
              decoration: BoxDecoration(
                gradient: AppColors.accentGradient,
                borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                boxShadow: AppShadows.shadowSM,
              ),
              child: Text(
                '-${widget.product.discountPercentage?.toInt()}%',
                style: AppTextStyles.labelSmall.copyWith(
                  color: AppColors.white,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ),

        // ✅ Badge de stock (stock faible / rupture)
        _buildStockBadge(),

        // ✅ Checkbox de sélection pour comparaison
        if (widget.enableComparison)
          Positioned(
            top: AppSizes.space3,
            right: AppSizes.space3,
            child: GestureDetector(
              onTap: widget.onComparisonToggle,
              child: Container(
                padding: const EdgeInsets.all(4),
                decoration: BoxDecoration(
                  color: (widget.isSelected ?? false)
                      ? AppColors.primary
                      : AppColors.white.withOpacity(0.9),
                  shape: BoxShape.circle,
                  boxShadow: AppShadows.shadowMD,
                ),
                child: Icon(
                  (widget.isSelected ?? false)
                      ? Icons.check_circle
                      : Icons.radio_button_unchecked,
                  color: (widget.isSelected ?? false)
                      ? AppColors.white
                      : AppColors.textLight,
                  size: 20,
                ),
              ),
            ),
          ),

        // Bouton favori moderne
        if (!widget.enableComparison)
          Positioned(
            top: AppSizes.space3,
            right: AppSizes.space3,
            child: GestureDetector(
              onTap: () async {
                // Animation du cœur
                _animationController.forward().then((_) {
                  _animationController.reverse();
                });

                // Toggle favori
                if (widget.onFavorite != null) {
                  widget.onFavorite!();
                } else {
                  final result = await favoritesProvider.toggleFavorite(
                    widget.product.id,
                  );

                  // Afficher un message de confirmation
                  if (context.mounted && result['success']) {
                    final isFav = result['is_favorite'] ?? false;
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Row(
                          children: [
                            Icon(
                              isFav ? Icons.favorite : Icons.favorite_border,
                              color: Colors.white,
                              size: 20,
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                isFav
                                    ? '${widget.product.name} ajouté aux favoris'
                                    : '${widget.product.name} retiré des favoris',
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                        backgroundColor: isFav
                            ? AppColors.success
                            : AppColors.error,
                        behavior: SnackBarBehavior.floating,
                        duration: const Duration(seconds: 2),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    );
                  }
                }
              },
              child: AnimatedBuilder(
                animation: _favoriteAnimation,
                builder: (context, child) {
                  return Transform.scale(
                    scale: 1.0 + (_favoriteAnimation.value * 0.2),
                    child: Container(
                      padding: const EdgeInsets.all(AppSizes.space2),
                      decoration: BoxDecoration(
                        color: AppColors.white.withOpacity(0.9),
                        shape: BoxShape.circle,
                        boxShadow: AppShadows.shadowMD,
                      ),
                      child: Icon(
                        isFavorite ? Icons.favorite : Icons.favorite_border,
                        size: AppSizes.iconSM,
                        color: isFavorite ? AppColors.error : AppColors.grey500,
                      ),
                    ),
                  );
                },
              ),
            ),
          ),

        // Overlay hover moderne
        Positioned.fill(
          child: Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(AppSizes.radiusXL),
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [
                  AppColors.primary.withOpacity(0.05),
                  Colors.transparent,
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildProductName() {
    return SizedBox(
      height: 34, // ✅ Hauteur fixe pour garantir 2 lignes
      child: Text(
        widget.product.name,
        style: AppTextStyles.titleMedium.copyWith(
          fontWeight: FontWeight.w600,
          fontSize: 12.5,
          height: 1.2,
        ),
        maxLines: 2,
        overflow: TextOverflow.ellipsis,
      ),
    );
  }

  Widget _buildRating() {
    return Row(
      children: [
        const Icon(Icons.star, color: AppColors.warning, size: 13),
        const SizedBox(width: 2),
        Text(
          '${widget.product.rating} (${widget.product.reviewsCount})',
          style: AppTextStyles.caption.copyWith(fontSize: 10),
        ),
      ],
    );
  }

  Widget _buildPrice() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        // ✅ Prix actuel (toujours visible)
        Text(
          Helpers.formatPrice(widget.product.price),
          style: AppTextStyles.body.copyWith(
            color: AppColors.primary,
            fontWeight: FontWeight.bold,
            fontSize: 13,
          ),
          maxLines: 1,
          overflow: TextOverflow.visible,
        ),
        // ✅ Ancien prix (si réduction)
        if (widget.product.hasDiscount) ...[
          const SizedBox(height: 2),
          Text(
            Helpers.formatPrice(widget.product.oldPrice!),
            style: AppTextStyles.caption.copyWith(
              decoration: TextDecoration.lineThrough,
              fontSize: 10,
              color: AppColors.textLight,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ],
    );
  }

  /// 🎯 Badge de stock (rupture / stock faible)
  Widget _buildStockBadge() {
    final stock = widget.product.stock;

    // Ne rien afficher si le stock est normal (> 5)
    if (stock > 5) {
      return const SizedBox.shrink();
    }

    String text;
    Color bgColor;
    IconData icon;

    if (stock == 0) {
      // 🔴 Rupture de stock
      text = 'Rupture';
      bgColor = AppColors.error;
      icon = Icons.block;
    } else if (stock == 1) {
      // 🟡 Dernière pièce
      text = 'Dernière';
      bgColor = Colors.orange;
      icon = Icons.warning_amber_rounded;
    } else {
      // 🟠 Stock limité (2-5)
      text = 'Stock limité';
      bgColor = Colors.deepOrange;
      icon = Icons.inventory_2_outlined;
    }

    return Positioned(
      bottom: AppSizes.space3,
      left: AppSizes.space3,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
        decoration: BoxDecoration(
          color: bgColor,
          borderRadius: BorderRadius.circular(AppSizes.radiusLG),
          boxShadow: AppShadows.shadowSM,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 10, color: AppColors.white),
            const SizedBox(width: 3),
            Text(
              text,
              style: AppTextStyles.labelSmall.copyWith(
                color: AppColors.white,
                fontWeight: FontWeight.w600,
                fontSize: 9,
              ),
            ),
          ],
        ),
      ),
    );
  }

  /// Bouton d'achat rapide
  Widget _buildQuickBuyButton() {
    // Vérifier si le produit est en stock
    final isInStock = widget.product.stock > 0;

    return SizedBox(
      width: double.infinity,
      height: 32,
      child: ElevatedButton.icon(
        onPressed: isInStock ? widget.onQuickBuy : null,
        style: ElevatedButton.styleFrom(
          backgroundColor: isInStock ? AppColors.primary : AppColors.grey300,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppSizes.radiusMD),
          ),
          elevation: isInStock ? 2 : 0,
        ),
        icon: Icon(isInStock ? Icons.shopping_cart : Icons.block, size: 16),
        label: Text(
          isInStock ? 'Acheter' : 'Rupture',
          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
        ),
      ),
    );
  }
}
