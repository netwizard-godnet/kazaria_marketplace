import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/wishlist_provider.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';

class WishlistAlertsScreen extends StatefulWidget {
  const WishlistAlertsScreen({super.key});

  @override
  State<WishlistAlertsScreen> createState() => _WishlistAlertsScreenState();
}

class _WishlistAlertsScreenState extends State<WishlistAlertsScreen> {
  final Set<int> _loadingItems = {};

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        context.read<WishlistProvider>().loadAlerts();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Alertes Wishlist'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => context.read<WishlistProvider>().loadAlerts(),
          ),
        ],
      ),
      body: Consumer<WishlistProvider>(
        builder: (context, provider, _) {
          if (provider.alertsLoading && provider.alertItems.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.alertsError != null && provider.alertItems.isEmpty) {
            return _buildError(provider.alertsError!);
          }

          if (provider.alertItems.isEmpty) {
            return _buildEmptyState();
          }

          return RefreshIndicator(
            onRefresh: () => provider.loadAlerts(),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: provider.alertItems.length,
              itemBuilder: (context, index) {
                final item = provider.alertItems[index];
                return _buildAlertCard(item);
              },
            ),
          );
        },
      ),
    );
  }

  Widget _buildAlertCard(Map<String, dynamic> item) {
    final product = item['product'] as Map<String, dynamic>? ?? {};
    final alertId = item['id'] as int;
    final targetPrice = item['target_price'];
    final currentPrice = product['price'];
    final isActive = item['is_active'] ?? true;

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
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        product['name'] ??
                            'Produit #${product['id'] ?? item['product_id']}',
                        style: AppTextStyles.h4,
                      ),
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: isActive
                              ? AppColors.success.withOpacity(0.1)
                              : AppColors.grey100,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          isActive ? 'Active' : 'Déclenchée',
                          style: AppTextStyles.caption.copyWith(
                            color: isActive
                                ? AppColors.success
                                : AppColors.textLight,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                if (_loadingItems.contains(item['id']))
                  const SizedBox(
                    width: 24,
                    height: 24,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                _buildStatChip(
                  'Prix actuel',
                  Helpers.formatPrice(currentPrice),
                ),
                const SizedBox(width: 8),
                _buildStatChip(
                  'Prix cible',
                  targetPrice != null
                      ? Helpers.formatPrice(targetPrice)
                      : 'Aucun',
                  color: targetPrice != null
                      ? AppColors.primary.withOpacity(0.1)
                      : AppColors.grey100,
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                TextButton.icon(
                  onPressed: () => _deleteAlert(alertId),
                  icon: const Icon(
                    Icons.delete_outline,
                    color: AppColors.error,
                  ),
                  label: const Text(
                    'Supprimer l\'alerte',
                    style: TextStyle(color: AppColors.error),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _deleteAlert(int alertId) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Supprimer l\'alerte'),
        content: const Text(
          'Voulez-vous vraiment supprimer cette alerte de prix ?',
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

    if (confirmed != true) return;

    final provider = context.read<WishlistProvider>();
    setState(() => _loadingItems.add(alertId));

    final response = await provider.deletePriceAlert(alertId);

    if (mounted) {
      setState(() => _loadingItems.remove(alertId));

      if (response['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Alerte supprimée'),
            backgroundColor: AppColors.success,
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              response['message'] ?? 'Erreur lors de la suppression',
            ),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Widget _buildStatChip(String label, String value, {Color? color}) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color ?? AppColors.primary.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            label,
            style: AppTextStyles.caption.copyWith(
              fontSize: 11,
              color: AppColors.textLight,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            value,
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
          ),
        ],
      ),
    );
  }

  Widget _buildError(String message) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.warning_amber, size: 64, color: AppColors.error),
          const SizedBox(height: 16),
          const Text(
            'Impossible de charger les alertes',
            style: AppTextStyles.h3,
          ),
          const SizedBox(height: 8),
          Text(
            message,
            style: AppTextStyles.body.copyWith(color: AppColors.textLight),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () => context.read<WishlistProvider>().loadAlerts(),
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
              Icons.notifications_none,
              size: 100,
              color: AppColors.textLight.withOpacity(0.2),
            ),
            const SizedBox(height: 24),
            const Text('Aucune alerte active', style: AppTextStyles.h3),
            const SizedBox(height: 8),
            Text(
              'Activez une alerte de prix ou de stock sur vos produits favoris pour les voir ici.',
              style: AppTextStyles.body.copyWith(color: AppColors.textLight),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
