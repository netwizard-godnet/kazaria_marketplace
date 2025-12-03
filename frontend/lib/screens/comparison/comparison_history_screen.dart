import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/comparison_provider.dart';
import '../../utils/constants.dart';
import 'product_comparison_screen.dart';

class ComparisonHistoryScreen extends StatefulWidget {
  const ComparisonHistoryScreen({super.key});

  @override
  State<ComparisonHistoryScreen> createState() => _ComparisonHistoryScreenState();
}

class _ComparisonHistoryScreenState extends State<ComparisonHistoryScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        context.read<ComparisonProvider>().loadHistory();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Historique des comparaisons'),
      ),
      body: Consumer<ComparisonProvider>(
        builder: (context, provider, _) {
          if (provider.historyLoading && provider.history.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.historyError != null && provider.history.isEmpty) {
            return _buildError(provider.historyError!);
          }

          if (provider.history.isEmpty) {
            return _buildEmptyState();
          }

          return RefreshIndicator(
            onRefresh: () => provider.loadHistory(),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: provider.history.length,
              itemBuilder: (context, index) {
                final entry = provider.history[index];
                return _buildHistoryCard(entry, provider);
              },
            ),
          );
        },
      ),
    );
  }

  Widget _buildHistoryCard(Map<String, dynamic> entry, ComparisonProvider provider) {
    final products = List<int>.from(entry['product_ids'] ?? []);
    final category = entry['category'] as String?;
    final lastViewed = entry['last_viewed_at'] as String?;
    final createdAt = entry['created_at'] as String?;

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.history, color: AppColors.primary),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Comparaison de ${products.length} produit${products.length > 1 ? 's' : ''}',
                        style: AppTextStyles.h4,
                      ),
                      if (category != null)
                        Text(
                          category,
                          style: AppTextStyles.caption.copyWith(color: AppColors.textLight),
                        ),
                    ],
                  ),
                ),
                ElevatedButton(
                  onPressed: () => _replayComparison(products, provider),
                  child: const Text('Rejouer'),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: products
                  .map(
                    (id) => Chip(
                      avatar: const CircleAvatar(
                        backgroundColor: AppColors.primary,
                        child: Icon(Icons.shopping_bag, size: 16, color: Colors.white),
                      ),
                      label: Text('Produit #$id'),
                    ),
                  )
                  .toList(),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                if (createdAt != null)
                  _buildMeta(Icons.event_available, 'Créée le ${_formatDate(createdAt)}'),
                if (lastViewed != null)
                  Padding(
                    padding: const EdgeInsets.only(left: 16),
                    child: _buildMeta(Icons.visibility, 'Vue le ${_formatDate(lastViewed)}'),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMeta(IconData icon, String label) {
    return Row(
      children: [
        Icon(icon, size: 16, color: AppColors.textLight),
        const SizedBox(width: 6),
        Text(
          label,
          style: AppTextStyles.caption.copyWith(color: AppColors.textLight),
        ),
      ],
    );
  }

  Future<void> _replayComparison(List<int> productIds, ComparisonProvider provider) async {
    if (productIds.length < 2) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Au moins deux produits sont nécessaires pour comparer.'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    final success = await provider.compareWithProductIds(productIds);
    if (!mounted) return;

    if (success) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const ProductComparisonScreen()),
      );
    } else if (provider.error != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(provider.error!),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  Widget _buildError(String message) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, size: 64, color: AppColors.error),
          const SizedBox(height: 16),
          const Text('Impossible de récupérer l\'historique', style: AppTextStyles.h3),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 24.0),
            child: Text(
              message,
              style: AppTextStyles.body.copyWith(color: AppColors.textLight),
              textAlign: TextAlign.center,
            ),
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () => context.read<ComparisonProvider>().loadHistory(),
            child: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.history_toggle_off,
              size: 96,
              color: AppColors.textLight.withOpacity(0.2),
            ),
            const SizedBox(height: 24),
            const Text('Aucune comparaison sauvegardée', style: AppTextStyles.h3),
            const SizedBox(height: 8),
            Text(
              'Comparez quelques produits pour voir votre historique apparaître ici.',
              style: AppTextStyles.body.copyWith(color: AppColors.textLight),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  String _formatDate(String isoDate) {
    try {
      final parsed = DateTime.parse(isoDate).toLocal();
      return '${parsed.day.toString().padLeft(2, '0')}/${parsed.month.toString().padLeft(2, '0')}/${parsed.year}';
    } catch (_) {
      return isoDate;
    }
  }
}


