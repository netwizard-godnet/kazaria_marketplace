import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/product_provider.dart';
import '../../widgets/modern_product_card.dart';
import '../../widgets/skeletons/seller_product_card_skeleton.dart';
import '../../screens/products/products_list_screen.dart';
import '../../screens/products/product_details_screen.dart';
import '../../utils/constants.dart';

/// Section "Deals du Jour" avec compte à rebours
class DealsOfDaySection extends StatefulWidget {
  const DealsOfDaySection({super.key});

  @override
  State<DealsOfDaySection> createState() => _DealsOfDaySectionState();
}

class _DealsOfDaySectionState extends State<DealsOfDaySection> {
  late Timer _timer;
  Duration _timeRemaining = const Duration(hours: 24);
  DateTime? _countdownEnd;

  @override
  void initState() {
    super.initState();
    _startTimer();
  }

  @override
  void dispose() {
    _timer.cancel();
    super.dispose();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();

    final provider = Provider.of<ProductProvider>(context);
    final newEnd = provider.dealsCountdownEnd;

    if (newEnd != null) {
      if (_countdownEnd == null || _countdownEnd!.millisecondsSinceEpoch != newEnd.millisecondsSinceEpoch) {
        setState(() {
          _countdownEnd = newEnd;
        });
      }
    } else if (_countdownEnd != null) {
      setState(() {
        _countdownEnd = null;
      });
    }
  }

  String _formatTime(int value) {
    return value.toString().padLeft(2, '0');
  }

  void _startTimer() {
    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (!mounted) return;

      setState(() {
        if (_countdownEnd != null) {
          final diff = _countdownEnd!.difference(DateTime.now());
          _timeRemaining = diff.isNegative ? Duration.zero : diff;
        } else {
          final now = DateTime.now();
          final midnight = DateTime(now.year, now.month, now.day + 1);
          _timeRemaining = midnight.difference(now);
        }
      });
    });
  }

  Widget _buildLoadingState() {
    return Container(
      margin: const EdgeInsets.only(bottom: AppSizes.space4),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(0),
        gradient: LinearGradient(
          colors: [
            AppColors.primary.withOpacity(0.05),
            AppColors.accent.withOpacity(0.02),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(AppSizes.space4),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  AppColors.primary,
                  AppColors.accent,
                ],
              ),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(AppSizes.space2),
                  decoration: BoxDecoration(
                    color: AppColors.white.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(AppSizes.radiusMD),
                  ),
                  child: const Icon(
                    Icons.local_fire_department,
                    color: AppColors.white,
                    size: 28,
                  ),
                ),
                const SizedBox(width: AppSizes.space3),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Deals du Jour',
                        style: AppTextStyles.sectionTitle.copyWith(
                          color: AppColors.white,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Offres limitées !',
                        style: AppTextStyles.caption.copyWith(
                          color: AppColors.white.withOpacity(0.9),
                        ),
                      ),
                    ],
                  ),
                ),
                _buildCountdownTimer(),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.all(AppSizes.space4),
            child: GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisSpacing: AppSizes.space3,
              mainAxisSpacing: AppSizes.space3,
              childAspectRatio: 0.75,
              children: List.generate(
                4,
                (_) => const SellerProductCardSkeleton(),
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final productProvider = Provider.of<ProductProvider>(context);

    final dealsProducts = productProvider.dealsProducts.isNotEmpty
        ? productProvider.dealsProducts
        : productProvider.bestOffers;

    final displayedProducts = dealsProducts.take(6).toList();

    if (displayedProducts.isEmpty && productProvider.isLoading) {
      return _buildLoadingState();
    }

    if (displayedProducts.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      margin: const EdgeInsets.only(bottom: AppSizes.space4),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(0),
        gradient: LinearGradient(
          colors: [
            AppColors.primary.withOpacity(0.05),
            AppColors.accent.withOpacity(0.02),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Column(
        children: [
          // Header avec compte à rebours
          Container(
            padding: const EdgeInsets.all(AppSizes.space4),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  AppColors.primary,
                  AppColors.accent,
                ],
              ),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(AppSizes.space2),
                  decoration: BoxDecoration(
                    color: AppColors.white.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(AppSizes.radiusMD),
                  ),
                  child: const Icon(
                    Icons.local_fire_department,
                    color: AppColors.white,
                    size: 28,
                  ),
                ),
                const SizedBox(width: AppSizes.space3),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Deals du Jour',
                        style: AppTextStyles.sectionTitle.copyWith(
                          color: AppColors.white,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Offres limitées !',
                        style: AppTextStyles.caption.copyWith(
                          color: AppColors.white.withOpacity(0.9),
                        ),
                      ),
                    ],
                  ),
                ),
                // Compte à rebours
                _buildCountdownTimer(),
              ],
            ),
          ),

          // Liste des produits en deals
          Container(
            padding: const EdgeInsets.all(AppSizes.space4),
            child: Column(
              children: [
                GridView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2, // ✅ 2 colonnes au lieu de 3 pour plus d'espace
                    crossAxisSpacing: AppSizes.space3,
                    mainAxisSpacing: AppSizes.space3,
                    childAspectRatio: 0.75, // ✅ Ajusté pour éviter l'overflow
                  ),
                  itemCount: displayedProducts.length > 4 ? 4 : displayedProducts.length, // ✅ Max 4 produits (2x2)
                  itemBuilder: (context, index) {
                    final product = displayedProducts[index];
                    return ModernProductCard(
                      product: product,
                      onTap: () {
                        // ✅ Navigation vers les détails du produit
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
                
                const SizedBox(height: AppSizes.space4),
                
                // Bouton "Voir tout"
                OutlinedButton.icon(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => ProductsListScreen(
                          title: 'Deals du Jour',
                          category: 'deals',
                          icon: Icons.local_fire_department,
                        ),
                      ),
                    );
                  },
                  icon: const Icon(Icons.arrow_forward),
                  label: const Text('Voir tous les deals'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.primary,
                    side: const BorderSide(color: AppColors.primary, width: 2),
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppSizes.space6,
                      vertical: AppSizes.space3,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCountdownTimer() {
    final hours = _formatTime(_timeRemaining.inHours);
    final minutes = _formatTime(_timeRemaining.inMinutes.remainder(60));
    final seconds = _formatTime(_timeRemaining.inSeconds.remainder(60));

    return Container(
      padding: const EdgeInsets.symmetric(
        horizontal: AppSizes.space3,
        vertical: AppSizes.space2,
      ),
      decoration: BoxDecoration(
        color: AppColors.white.withOpacity(0.2),
        borderRadius: BorderRadius.circular(AppSizes.radiusMD),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _buildTimeUnit(hours, 'H'),
          const SizedBox(width: 4),
          Text(
            ':',
            style: AppTextStyles.h4.copyWith(
              color: AppColors.white,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(width: 4),
          _buildTimeUnit(minutes, 'M'),
          const SizedBox(width: 4),
          Text(
            ':',
            style: AppTextStyles.h4.copyWith(
              color: AppColors.white,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(width: 4),
          _buildTimeUnit(seconds, 'S'),
        ],
      ),
    );
  }

  Widget _buildTimeUnit(String value, String label) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          value,
          style: AppTextStyles.h4.copyWith(
            color: AppColors.white,
            fontWeight: FontWeight.bold,
          ),
        ),
        Text(
          label,
          style: AppTextStyles.caption.copyWith(
            color: AppColors.white.withOpacity(0.8),
            fontSize: 10,
          ),
        ),
      ],
    );
  }
}


