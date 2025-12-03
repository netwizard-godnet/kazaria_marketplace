import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../providers/wishlist_provider.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';

class WishlistAlertHistoryScreen extends StatefulWidget {
  const WishlistAlertHistoryScreen({super.key});

  @override
  State<WishlistAlertHistoryScreen> createState() => _WishlistAlertHistoryScreenState();
}

class _WishlistAlertHistoryScreenState extends State<WishlistAlertHistoryScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      context.read<WishlistProvider>().loadAlertHistory();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Historique des alertes'),
      ),
      body: Consumer<WishlistProvider>(
        builder: (context, provider, _) {
          if (provider.alertHistoryLoading && provider.alertHistory.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.alertHistoryError != null && provider.alertHistory.isEmpty) {
            return _buildError(provider.alertHistoryError!, provider);
          }

          if (provider.alertHistory.isEmpty) {
            return _buildEmptyState();
          }

          return RefreshIndicator(
            onRefresh: () => provider.loadAlertHistory(),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: provider.alertHistory.length,
              itemBuilder: (context, index) {
                final log = provider.alertHistory[index];
                return _buildLogCard(log);
              },
            ),
          );
        },
      ),
    );
  }

  Widget _buildLogCard(Map<String, dynamic> log) {
    final type = log['type'] as String? ?? 'price_drop';
    final title = log['title'] as String? ?? 'Alerte';
    final message = log['message'] as String? ?? '';
    final notifiedAt = log['notified_at'] as String?;
    final metadata = (log['metadata'] as Map<String, dynamic>?) ?? {};

    final icon = type == 'stock_back'
        ? const Icon(Icons.inventory_2_outlined, color: AppColors.primary)
        : const Icon(Icons.trending_down, color: AppColors.primary);

    DateTime? notifiedDate;
    if (notifiedAt != null) {
      notifiedDate = DateTime.tryParse(notifiedAt)?.toLocal();
    }

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
                icon,
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(title, style: AppTextStyles.h4),
                      if (notifiedDate != null)
                        Text(
                          Helpers.formatDateTime(notifiedDate),
                          style: AppTextStyles.caption.copyWith(color: AppColors.textLight),
                        ),
                    ],
                  ),
                ),
              ],
            ),
            if (message.isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(message, style: AppTextStyles.body),
            ],
            if (metadata.isNotEmpty) ...[
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: metadata.entries
                    .map(
                      (entry) => _buildMetaChip(entry.key, entry.value),
                    )
                    .toList(),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildMetaChip(String key, dynamic value) {
    String label = key;
    switch (key) {
      case 'initial_price':
        label = 'Prix initial';
        break;
      case 'current_price':
        label = 'Prix actuel';
        break;
      case 'target_price':
        label = 'Prix cible';
        break;
    }

    final formattedValue = value is num ? Helpers.formatPrice(value) : value.toString();

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: AppColors.primary.withOpacity(0.08),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: AppTextStyles.caption.copyWith(color: AppColors.textLight),
          ),
          Text(
            formattedValue,
            style: const TextStyle(fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }

  Widget _buildError(String message, WishlistProvider provider) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, size: 64, color: AppColors.error),
          const SizedBox(height: 16),
          const Text('Impossible de charger l’historique', style: AppTextStyles.h3),
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
            onPressed: () => provider.loadAlertHistory(),
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
              size: 96,
              color: AppColors.textLight.withOpacity(0.2),
            ),
            const SizedBox(height: 24),
            const Text('Aucune alerte reçue pour le moment', style: AppTextStyles.h3),
            const SizedBox(height: 8),
            Text(
              'Dès qu’une baisse de prix ou un retour en stock sera détecté, il apparaîtra ici.',
              style: AppTextStyles.body.copyWith(color: AppColors.textLight),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}


