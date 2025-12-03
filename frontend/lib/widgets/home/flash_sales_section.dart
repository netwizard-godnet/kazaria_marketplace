import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../utils/constants.dart';
import '../../providers/product_provider.dart';
import '../../screens/promotions/flash_sales_screen.dart';
import '../../screens/products/product_details_screen.dart';
import '../modern_product_card.dart';

/// Section Ventes Flash avec compteur temps réel
class FlashSalesSection extends StatefulWidget {
  const FlashSalesSection({super.key});

  @override
  State<FlashSalesSection> createState() => _FlashSalesSectionState();
}

class _FlashSalesSectionState extends State<FlashSalesSection> {
  Timer? _timer;
  Duration? _timeRemaining;
  DateTime? _endTime;

  @override
  void initState() {
    super.initState();
    _loadFlashSalesData();
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _loadFlashSalesData() async {
    // Définir la fin de la vente flash
    // TODO: Récupérer la vraie heure de fin depuis le backend
    _endTime = DateTime.now().add(const Duration(hours: 24));
    
    _startCountdown();
  }

  void _startCountdown() {
    if (_endTime == null) return;

    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) {
        timer.cancel();
        return;
      }

      final now = DateTime.now();
      final remaining = _endTime!.difference(now);

      if (remaining.isNegative) {
        timer.cancel();
        setState(() {
          _timeRemaining = Duration.zero;
        });
      } else {
        setState(() {
          _timeRemaining = remaining;
        });
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<ProductProvider>(
      builder: (context, productProvider, _) {
        // Filtrer les produits en flash sale (avec réduction > 30%)
        final flashProducts = productProvider.allProducts
            .where((product) => 
              product.discountPercentage != null && 
              product.discountPercentage! >= 30
            )
            .toList();

        if (flashProducts.isEmpty) {
          return const SizedBox.shrink();
        }
        
        // Trier par réduction décroissante
        flashProducts.sort((a, b) => 
          (b.discountPercentage ?? 0).compareTo(a.discountPercentage ?? 0)
        );

        return Container(
          margin: const EdgeInsets.only(bottom: AppSizes.space4),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [
                Color(0xFFFF6B6B),
                Color(0xFFFF8E53),
                Color(0xFFFFA06B),
              ],
            ),
            borderRadius: BorderRadius.circular(AppSizes.radiusXL),
          ),
          child: Column(
            children: [
              // En-tête avec compteur
              _buildHeader(context, flashProducts.length),
              
              // Liste de produits
              SizedBox(
                height: 280,
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.all(AppSizes.space4),
                  itemCount: flashProducts.length > 10 ? 10 : flashProducts.length,
                  itemBuilder: (context, index) {
                    final product = flashProducts[index];
                    return Container(
                      width: 160,
                      margin: EdgeInsets.only(
                        right: index < flashProducts.length - 1 ? AppSizes.space3 : 0,
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
            ],
          ),
        );
      },
    );
  }

  Widget _buildHeader(BuildContext context, int productsCount) {
    return Padding(
      padding: const EdgeInsets.all(AppSizes.space4),
      child: Row(
        children: [
          // Icône Flash
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              Icons.flash_on,
              color: Colors.white,
              size: 28,
            ),
          ),
          
          const SizedBox(width: 12),
          
          // Titre et nombre de produits
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  '⚡ VENTES FLASH',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  '$productsCount produits',
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.white.withOpacity(0.9),
                  ),
                ),
              ],
            ),
          ),
          
          // Compteur
          _buildCountdown(),
          
          const SizedBox(width: 8),
          
          // Bouton "Voir tout"
          IconButton(
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => const FlashSalesScreen(),
                ),
              );
            },
            icon: const Icon(
              Icons.arrow_forward_ios,
              color: Colors.white,
              size: 20,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCountdown() {
    if (_timeRemaining == null) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.2),
          borderRadius: BorderRadius.circular(8),
        ),
        child: const Text(
          'Chargement...',
          style: TextStyle(
            color: Colors.white,
            fontSize: 11,
            fontWeight: FontWeight.bold,
          ),
        ),
      );
    }

    if (_timeRemaining!.isNegative || _timeRemaining!.inSeconds == 0) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.2),
          borderRadius: BorderRadius.circular(8),
        ),
        child: const Text(
          'Terminé',
          style: TextStyle(
            color: Colors.white,
            fontSize: 11,
            fontWeight: FontWeight.bold,
          ),
        ),
      );
    }

    final hours = _timeRemaining!.inHours;
    final minutes = _timeRemaining!.inMinutes.remainder(60);
    final seconds = _timeRemaining!.inSeconds.remainder(60);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.2),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _buildTimeUnit(hours.toString().padLeft(2, '0')),
          const Text(':', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
          _buildTimeUnit(minutes.toString().padLeft(2, '0')),
          const Text(':', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
          _buildTimeUnit(seconds.toString().padLeft(2, '0')),
        ],
      ),
    );
  }

  Widget _buildTimeUnit(String value) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 2),
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.9),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(
        value,
        style: const TextStyle(
          color: Color(0xFFFF6B6B),
          fontSize: 14,
          fontWeight: FontWeight.bold,
          fontFeatures: [FontFeature.tabularFigures()],
        ),
      ),
    );
  }
}

