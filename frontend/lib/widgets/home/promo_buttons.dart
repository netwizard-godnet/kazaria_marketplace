import 'package:flutter/material.dart';
import '../../utils/constants.dart';
import '../../screens/promotions/black_friday_screen.dart';
import '../../screens/promotions/flash_sales_screen.dart';

/// Boutons d'accès rapide aux promotions (Black Friday et Ventes Flash)
class PromoButtons extends StatelessWidget {
  final Map<String, dynamic> promotions;

  const PromoButtons({
    super.key,
    required this.promotions,
  });

  Map<String, dynamic> _extractPromo(String key) {
    final value = promotions[key];
    if (value is Map<String, dynamic>) return value;
    if (value is Map) return Map<String, dynamic>.from(value);
    return const {};
  }

  @override
  Widget build(BuildContext context) {
    final blackFridayData = _extractPromo('black_friday');
    final flashSalesData = _extractPromo('flash_sales');

    final bool showBlackFriday = blackFridayData['enabled'] == true;
    final bool showFlashSales = flashSalesData['enabled'] == true;

    if (!showBlackFriday && !showFlashSales) {
      return const SizedBox.shrink();
    }

    final List<Widget> buttons = [];

    if (showBlackFriday) {
      buttons.add(
        Expanded(
          child: _PromoButton(
            height: 100,
            gradient: const LinearGradient(
              colors: [Color(0xFF000000), Color(0xFF1A1A1A)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            shadowColor: AppColors.warning.withOpacity(0.3),
            decorativeIcons: [
              Positioned(
                top: 10,
                right: 10,
                child: Icon(
                  Icons.star,
                  color: AppColors.warning.withOpacity(0.3),
                  size: 30,
                ),
              ),
              Positioned(
                bottom: 15,
                left: 15,
                child: Icon(
                  Icons.star,
                  color: AppColors.warning.withOpacity(0.2),
                  size: 20,
                ),
              ),
            ],
            content: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  (blackFridayData['title'] ?? 'Black Friday').toString().toUpperCase(),
                  style: const TextStyle(
                    color: AppColors.warning,
                    fontSize: 16,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 1.2,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  (blackFridayData['subtitle'] ?? 'Jusqu\'à -80 %').toString(),
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.85),
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 6,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.redAccent,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    (blackFridayData['badge'] ?? '-80 %').toString(),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const BlackFridayScreen()),
              );
            },
          ),
        ),
      );
    }

    if (showFlashSales) {
      if (buttons.isNotEmpty) {
        buttons.add(const SizedBox(width: AppSizes.space3));
      }

      buttons.add(
        Expanded(
          child: _PromoButton(
            height: 100,
            gradient: const LinearGradient(
              colors: [AppColors.error, Color(0xFFDC2626)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            shadowColor: AppColors.error.withOpacity(0.3),
            decorativeIcons: [
              Positioned(
                top: 5,
                right: 5,
                child: Icon(
                  Icons.flash_on,
                  color: Colors.white.withOpacity(0.2),
                  size: 40,
                ),
              ),
            ],
            content: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Row(
                  children: [
                    const Icon(
                      Icons.flash_on,
                      color: Colors.white,
                      size: 20,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      (flashSalesData['title'] ?? 'Ventes Flash').toString(),
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 14,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  (flashSalesData['subtitle'] ?? 'Offres limitées 24h').toString(),
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.85),
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 6,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    (flashSalesData['badge'] ?? '24H').toString(),
                    style: const TextStyle(
                      color: AppColors.error,
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const FlashSalesScreen()),
              );
            },
          ),
        ),
      );
    }

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: AppSizes.space4),
      child: Row(children: buttons),
    );
  }
}

/// Widget de bouton promotionnel avec gradient et effets
class _PromoButton extends StatelessWidget {
  final double height;
  final Gradient gradient;
  final Color shadowColor;
  final List<Widget> decorativeIcons;
  final Widget content;
  final VoidCallback onTap;

  const _PromoButton({
    required this.height,
    required this.gradient,
    required this.shadowColor,
    required this.decorativeIcons,
    required this.content,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        height: height,
        decoration: BoxDecoration(
          gradient: gradient,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: shadowColor,
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Stack(
          children: [
            // Icônes décoratives
            ...decorativeIcons,
            // Contenu
            Padding(
              padding: const EdgeInsets.all(12),
              child: content,
            ),
          ],
        ),
      ),
    );
  }
}

