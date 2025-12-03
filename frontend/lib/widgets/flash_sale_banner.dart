import 'package:flutter/material.dart';
import '../utils/constants.dart';

class FlashSaleBanner extends StatefulWidget {
  final List<FlashSaleInfo> flashSales;
  final VoidCallback? onTap;

  const FlashSaleBanner({
    super.key,
    required this.flashSales,
    this.onTap,
  });

  @override
  State<FlashSaleBanner> createState() => _FlashSaleBannerState();
}

class _FlashSaleBannerState extends State<FlashSaleBanner>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _scaleAnimation;
  int _currentIndex = 0;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 2000),
      vsync: this,
    );

    _fadeAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeInOut,
    ));

    _scaleAnimation = Tween<double>(
      begin: 0.8,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.elasticOut,
    ));

    _startAnimation();
  }

  void _startAnimation() {
    if (widget.flashSales.isNotEmpty) {
      _animationController.forward();
      
      // Changer de bannière toutes les 4 secondes
      Future.delayed(const Duration(seconds: 4), () {
        if (mounted) {
          _animationController.reset();
          setState(() {
            _currentIndex = (_currentIndex + 1) % widget.flashSales.length;
          });
          _startAnimation();
        }
      });
    }
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (widget.flashSales.isEmpty) {
      return const SizedBox.shrink();
    }

    final currentSale = widget.flashSales[_currentIndex];

    return GestureDetector(
      onTap: widget.onTap,
      child: Container(
        height: 60,
        margin: const EdgeInsets.symmetric(
          horizontal: AppSizes.paddingMedium,
          vertical: AppSizes.space2,
        ),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(AppSizes.radiusXL),
          gradient: LinearGradient(
            begin: Alignment.centerLeft,
            end: Alignment.centerRight,
            colors: [
              AppColors.primary,
              AppColors.primaryLight,
              AppColors.accent,
            ],
          ),
          boxShadow: [
            BoxShadow(
              color: AppColors.primary.withOpacity(0.3),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: AnimatedBuilder(
          animation: _animationController,
          builder: (context, child) {
            return Transform.scale(
              scale: _scaleAnimation.value,
              child: Opacity(
                opacity: _fadeAnimation.value,
                child: Row(
                  children: [
                    // Icône animée
                    Container(
                      width: 50,
                      height: 50,
                      margin: const EdgeInsets.all(AppSizes.space2),
                      decoration: BoxDecoration(
                        color: AppColors.white.withOpacity(0.2),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.flash_on,
                        color: AppColors.white,
                        size: 24,
                      ),
                    ),
                    
                    // Contenu principal
                    Expanded(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            currentSale.title,
                            style: AppTextStyles.bodyMedium.copyWith(
                              color: AppColors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 2),
                          Text(
                            currentSale.subtitle,
                            style: AppTextStyles.caption.copyWith(
                              color: AppColors.white.withOpacity(0.9),
                              fontSize: 12,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                    
                    // Indicateur de temps ou prix
                    Container(
                      margin: const EdgeInsets.only(right: AppSizes.space3),
                      padding: const EdgeInsets.symmetric(
                        horizontal: AppSizes.space2,
                        vertical: AppSizes.space1,
                      ),
                      decoration: BoxDecoration(
                        color: AppColors.white.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                      ),
                      child: Text(
                        currentSale.actionText,
                        style: AppTextStyles.caption.copyWith(
                          color: AppColors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 11,
                        ),
                      ),
                    ),
                    
                    // Flèche
                    Container(
                      margin: const EdgeInsets.only(right: AppSizes.space3),
                      child: const Icon(
                        Icons.arrow_forward_ios,
                        color: AppColors.white,
                        size: 16,
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}

class FlashSaleInfo {
  final String title;
  final String subtitle;
  final String actionText;
  final String? imageUrl;
  final VoidCallback? onTap;

  FlashSaleInfo({
    required this.title,
    required this.subtitle,
    required this.actionText,
    this.imageUrl,
    this.onTap,
  });
}

// Données d'exemple pour les ventes flash
class FlashSaleData {
  static List<FlashSaleInfo> getFlashSales() {
    return [
      FlashSaleInfo(
        title: "🔥 VENTES FLASH",
        subtitle: "Jusqu'à -70% sur tous les produits",
        actionText: "MAINTENANT",
      ),
      FlashSaleInfo(
        title: "⚡ BLACK FRIDAY",
        subtitle: "Offres exceptionnelles jusqu'au 30 Nov",
        actionText: "DÉCOUVRIR",
      ),
      FlashSaleInfo(
        title: "🎉 NOUVEAUTÉS",
        subtitle: "Découvrez nos derniers produits",
        actionText: "VOIR",
      ),
      FlashSaleInfo(
        title: "💎 PREMIUM",
        subtitle: "Produits de luxe à prix réduits",
        actionText: "EXPLORER",
      ),
      FlashSaleInfo(
        title: "🚚 LIVRAISON GRATUITE",
        subtitle: "Commandez maintenant, livré demain",
        actionText: "COMMANDER",
      ),
    ];
  }
}
