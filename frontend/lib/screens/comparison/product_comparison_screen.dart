import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../providers/comparison_provider.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';
import '../../config/api_config.dart';
import 'comparison_history_screen.dart';

class ProductComparisonScreen extends StatefulWidget {
  const ProductComparisonScreen({super.key});

  @override
  State<ProductComparisonScreen> createState() => _ProductComparisonScreenState();
}

class _ProductComparisonScreenState extends State<ProductComparisonScreen> {
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final provider = context.read<ComparisonProvider>();
      if (provider.selectedProducts.isNotEmpty) {
        provider.compare();
      }
    });
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Comparateur de Produits'),
        actions: [
          IconButton(
            icon: const Icon(Icons.history),
            tooltip: 'Historique',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const ComparisonHistoryScreen()),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.clear_all),
            tooltip: 'Effacer la sélection',
            onPressed: () {
              context.read<ComparisonProvider>().clearSelection();
              Navigator.pop(context);
            },
          ),
        ],
      ),
      body: Consumer<ComparisonProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null) {
            return _buildErrorState(provider);
          }

          final comparison = provider.comparisonResult;
          if (comparison == null) {
            return const Center(child: Text('Aucune comparaison disponible'));
          }

          return SingleChildScrollView(
            controller: _scrollController,
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Meilleur choix
                _buildBestChoice(comparison),
                const SizedBox(height: 24),

                // Tableau de comparaison
                _buildComparisonTable(comparison),
                
                const SizedBox(height: 24),

                // Statistiques
                _buildStatistics(comparison),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildBestChoice(Map<String, dynamic> comparison) {
    final bestChoice = comparison['best_choice'] as Map<String, dynamic>?;
    if (bestChoice == null) return const SizedBox.shrink();

    final products = comparison['products'] as List;
    final bestProduct = products.firstWhere(
      (p) => p['id'] == bestChoice['product_id'],
      orElse: () => null,
    );

    if (bestProduct == null) return const SizedBox.shrink();

    final reasons = List<String>.from(bestChoice['reasons'] ?? []);

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF4CAF50), Color(0xFF45A049)],
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: AppShadows.shadowLG,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.emoji_events, color: Colors.white, size: 28),
              const SizedBox(width: 12),
              const Text(
                'Notre recommandation',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            bestProduct['name'],
            style: const TextStyle(
              color: Colors.white,
              fontSize: 16,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Score: ${bestChoice['score']}/100',
            style: const TextStyle(
              color: Colors.white70,
              fontSize: 14,
            ),
          ),
          if (reasons.isNotEmpty) ...[
            const SizedBox(height: 12),
            ...reasons.map((reason) => Padding(
              padding: const EdgeInsets.only(bottom: 4),
              child: Row(
                children: [
                  const Icon(Icons.check_circle, color: Colors.white, size: 16),
                  const SizedBox(width: 8),
                  Text(
                    reason,
                    style: const TextStyle(color: Colors.white, fontSize: 13),
                  ),
                ],
              ),
            )).toList(),
          ],
        ],
      ),
    );
  }

  Widget _buildComparisonTable(Map<String, dynamic> comparison) {
    final products = comparison['products'] as List;
    final commonAttributes = comparison['common_attributes'] as Map<String, dynamic>? ?? {};
    final bestChoice = comparison['best_choice'] as Map<String, dynamic>?;
    final bestProductId = bestChoice?['product_id'];

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Headers (Images + Noms)
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Colonne des labels
              SizedBox(
                width: 120,
                child: Column(
                  children: [
                    Container(height: 180), // Espace pour les images
                    const SizedBox(height: 8),
                    Container(height: 60), // Espace pour les noms
                  ],
                ),
              ),
              
              // Colonnes des produits
              ...products.map((product) {
                final isBest = product['id'] == bestProductId;
                return _buildProductColumn(product, isBest);
              }).toList(),
            ],
          ),

          const SizedBox(height: 16),

          // Lignes de comparaison
          _buildComparisonRow('Prix', products, (p) => Helpers.formatPrice(p['price'])),
          _buildComparisonRow('Note', products, (p) => '${p['rating']} ⭐'),
          _buildComparisonRow('Avis', products, (p) => '${p['reviews_count']} avis'),
          _buildComparisonRow('Stock', products, (p) => p['is_in_stock'] ? '✅ En stock' : '❌ Rupture'),
          if (products.every((p) => p['brand'] != null))
            _buildComparisonRow('Marque', products, (p) => p['brand'] ?? '-'),
          
          // Attributs communs
          ...commonAttributes.entries.map((entry) {
            return _buildComparisonRow(
              entry.key,
              products,
              (p) {
                final attrs = entry.value as Map<String, dynamic>;
                return attrs[p['id'].toString()]?.toString() ?? '-';
              },
            );
          }).toList(),
        ],
      ),
    );
  }

  Widget _buildProductColumn(Map<String, dynamic> product, bool isBest) {
    final imageUrl = _getProductImageUrl(product);

    return Container(
      width: 160,
      margin: const EdgeInsets.only(right: 8),
      child: Column(
        children: [
          // Badge "Meilleur choix"
          if (isBest)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: const Color(0xFF4CAF50),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Text(
                '🏆 Meilleur choix',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                ),
              ),
            )
          else
            const SizedBox(height: 24),
          
          const SizedBox(height: 8),

          // Image
          GestureDetector(
            onTap: () {
              // TODO: Navigator vers ProductDetailsScreen
            },
            child: Container(
              height: 140,
              decoration: BoxDecoration(
                color: AppColors.grey100,
                borderRadius: BorderRadius.circular(12),
                border: isBest
                    ? Border.all(color: const Color(0xFF4CAF50), width: 2)
                    : null,
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: imageUrl != null
                    ? CachedNetworkImage(
                        imageUrl: imageUrl,
                        fit: BoxFit.cover,
                        errorWidget: (_, __, ___) => const Icon(Icons.image, size: 40),
                      )
                    : const Icon(Icons.image, size: 40),
              ),
            ),
          ),

          const SizedBox(height: 8),

          // Nom
          SizedBox(
            height: 60,
            child: Text(
              product['name'],
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildComparisonRow(
    String label,
    List products,
    String Function(dynamic) getValue,
  ) {
    return Container(
      margin: const EdgeInsets.only(bottom: 1),
      decoration: BoxDecoration(
        color: AppColors.white,
        border: Border(
          bottom: BorderSide(color: AppColors.border.withOpacity(0.3)),
        ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Label
          Container(
            width: 120,
            padding: const EdgeInsets.all(12),
            child: Text(
              label,
              style: const TextStyle(
                fontWeight: FontWeight.w600,
                fontSize: 13,
              ),
            ),
          ),
          
          // Valeurs
          ...products.map((product) {
            final value = getValue(product);
            return Container(
              width: 160,
              padding: const EdgeInsets.all(12),
              margin: const EdgeInsets.only(right: 8),
              child: Text(
                value,
                style: const TextStyle(fontSize: 12),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            );
          }).toList(),
        ],
      ),
    );
  }

  Widget _buildStatistics(Map<String, dynamic> comparison) {
    final priceRange = comparison['price_range'] as Map<String, dynamic>?;
    final ratingRange = comparison['rating_range'] as Map<String, dynamic>?;

    if (priceRange == null || ratingRange == null) {
      return const SizedBox.shrink();
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.grey50,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Statistiques',
            style: AppTextStyles.h4,
          ),
          const SizedBox(height: 12),
          _buildStatRow('Prix moyen', Helpers.formatPrice(priceRange['average'])),
          _buildStatRow('Prix min', Helpers.formatPrice(priceRange['min'])),
          _buildStatRow('Prix max', Helpers.formatPrice(priceRange['max'])),
          const Divider(height: 24),
          _buildStatRow('Note moyenne', '${ratingRange['average'].toStringAsFixed(1)} ⭐'),
          _buildStatRow('Note min', '${ratingRange['min']} ⭐'),
          _buildStatRow('Note max', '${ratingRange['max']} ⭐'),
        ],
      ),
    );
  }

  Widget _buildStatRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: const TextStyle(color: AppColors.textLight),
          ),
          Text(
            value,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorState(ComparisonProvider provider) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, size: 64, color: AppColors.error),
          const SizedBox(height: 16),
          const Text('Erreur', style: AppTextStyles.h3),
          const SizedBox(height: 8),
          Text(
            provider.error!,
            style: AppTextStyles.body.copyWith(color: AppColors.textLight),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () => provider.compare(),
            child: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }

  String? _getProductImageUrl(Map<String, dynamic> product) {
    final image = product['image'] as String?;
    if (image == null || image.isEmpty) return null;

    if (image.startsWith('http')) {
      return _fixImageUrl(image);
    }

    return '${ApiConfig.imageBaseUrl}/$image';
  }

  String _fixImageUrl(String url) {
    if (url.startsWith('http:') && !url.startsWith('http://')) {
      return url.replaceFirst('http:', 'http://');
    }
    if (url.startsWith('https:') && !url.startsWith('https://')) {
      return url.replaceFirst('https:', 'https://');
    }
    return url;
  }
}

