import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/payment_provider.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';

class PaymentHistoryScreen extends StatefulWidget {
  const PaymentHistoryScreen({super.key});

  @override
  State<PaymentHistoryScreen> createState() => _PaymentHistoryScreenState();
}

class _PaymentHistoryScreenState extends State<PaymentHistoryScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<PaymentProvider>().loadPaymentHistory();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Historique des paiements'),
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.white,
      ),
      body: Consumer<PaymentProvider>(
        builder: (context, paymentProvider, _) {
          if (paymentProvider.isLoading && paymentProvider.paymentHistory.isEmpty) {
            return const Center(
              child: CircularProgressIndicator(),
            );
          }

          if (paymentProvider.error != null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.error_outline,
                    size: 64,
                    color: AppColors.error,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    paymentProvider.error!,
                    style: AppTextStyles.bodyLarge.copyWith(
                      color: AppColors.error,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () {
                      paymentProvider.clearError();
                      paymentProvider.loadPaymentHistory();
                    },
                    child: const Text('Réessayer'),
                  ),
                ],
              ),
            );
          }

          if (paymentProvider.paymentHistory.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.payment_outlined,
                    size: 64,
                    color: AppColors.textLight,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Aucun paiement trouvé',
                    style: AppTextStyles.h4.copyWith(
                      color: AppColors.textLight,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Vos paiements apparaîtront ici',
                    style: AppTextStyles.bodyLarge.copyWith(
                      color: AppColors.textLight,
                    ),
                  ),
                ],
              ),
            );
          }

          return RefreshIndicator(
            onRefresh: () => paymentProvider.loadPaymentHistory(),
            child: ListView.builder(
              padding: const EdgeInsets.all(AppSizes.paddingMedium),
              itemCount: paymentProvider.paymentHistory.length,
              itemBuilder: (context, index) {
                final transaction = paymentProvider.paymentHistory[index];
                return _buildPaymentCard(transaction);
              },
            ),
          );
        },
      ),
    );
  }

  Widget _buildPaymentCard(transaction) {
    return Card(
      margin: const EdgeInsets.only(bottom: AppSizes.paddingMedium),
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
      ),
      child: InkWell(
        onTap: () => _showPaymentDetails(transaction),
        borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
        child: Padding(
          padding: const EdgeInsets.all(AppSizes.paddingMedium),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // En-tête avec statut
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Transaction #${transaction.id.substring(0, 8)}...',
                    style: AppTextStyles.bodyLarge.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: _getStatusColor(transaction.status).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(AppSizes.radiusSmall),
                      border: Border.all(
                        color: _getStatusColor(transaction.status).withOpacity(0.3),
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          _getStatusIcon(transaction.status),
                          size: 16,
                          color: _getStatusColor(transaction.status),
                        ),
                        const SizedBox(width: 4),
                        Text(
                          transaction.statusText,
                          style: AppTextStyles.caption.copyWith(
                            color: _getStatusColor(transaction.status),
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 12),

              // Détails de la transaction
              Row(
                children: [
                  Icon(
                    _getPaymentMethodIcon(transaction.paymentMethodId),
                    color: AppColors.primary,
                    size: 20,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          _getPaymentMethodName(transaction.paymentMethodId),
                          style: AppTextStyles.bodyLarge,
                        ),
                        Text(
                          'Commande #${transaction.orderId}',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: AppColors.textLight,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        Helpers.formatPrice(transaction.amount),
                        style: AppTextStyles.h4.copyWith(
                          color: AppColors.primary,
                        ),
                      ),
                      Text(
                        transaction.currency,
                        style: AppTextStyles.caption.copyWith(
                          color: AppColors.textLight,
                        ),
                      ),
                    ],
                  ),
                ],
              ),

              const SizedBox(height: 12),

              // Date et heure
              Row(
                children: [
                  Icon(
                    Icons.schedule,
                    size: 16,
                    color: AppColors.textLight,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    Helpers.formatDateTime(transaction.createdAt),
                    style: AppTextStyles.caption.copyWith(
                      color: AppColors.textLight,
                    ),
                  ),
                  if (transaction.completedAt != null) ...[
                    const SizedBox(width: 16),
                    Icon(
                      Icons.check_circle,
                      size: 16,
                      color: AppColors.success,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      'Terminé ${Helpers.formatDateTime(transaction.completedAt!)}',
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.success,
                      ),
                    ),
                  ],
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'pending':
        return AppColors.warning;
      case 'processing':
        return AppColors.info;
      case 'completed':
        return AppColors.success;
      case 'failed':
        return AppColors.error;
      case 'cancelled':
        return AppColors.textLight;
      default:
        return AppColors.textLight;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'pending':
        return Icons.schedule;
      case 'processing':
        return Icons.sync;
      case 'completed':
        return Icons.check_circle;
      case 'failed':
        return Icons.error;
      case 'cancelled':
        return Icons.cancel;
      default:
        return Icons.help_outline;
    }
  }

  IconData _getPaymentMethodIcon(String methodId) {
    switch (methodId) {
      case 'orange_money':
      case 'mtn_money':
      case 'moov_money':
        return Icons.phone_android;
      case 'card':
        return Icons.credit_card;
      case 'cash_on_delivery':
        return Icons.money;
      default:
        return Icons.payment;
    }
  }

  String _getPaymentMethodName(String methodId) {
    switch (methodId) {
      case 'orange_money':
        return 'Orange Money';
      case 'mtn_money':
        return 'MTN Money';
      case 'moov_money':
        return 'Moov Money';
      case 'card':
        return 'Carte bancaire';
      case 'cash_on_delivery':
        return 'Paiement à la livraison';
      default:
        return 'Méthode inconnue';
    }
  }

  void _showPaymentDetails(transaction) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Détails du paiement'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildDetailRow('ID Transaction', transaction.id),
            _buildDetailRow('Commande', transaction.orderId),
            _buildDetailRow('Méthode', _getPaymentMethodName(transaction.paymentMethodId)),
            _buildDetailRow('Montant', '${Helpers.formatPrice(transaction.amount)} ${transaction.currency}'),
            _buildDetailRow('Statut', transaction.statusText),
            _buildDetailRow('Créé le', Helpers.formatDateTime(transaction.createdAt)),
            if (transaction.updatedAt != null)
              _buildDetailRow('Modifié le', Helpers.formatDateTime(transaction.updatedAt!)),
            if (transaction.completedAt != null)
              _buildDetailRow('Terminé le', Helpers.formatDateTime(transaction.completedAt!)),
            if (transaction.transactionReference != null)
              _buildDetailRow('Référence', transaction.transactionReference!),
            if (transaction.providerTransactionId != null)
              _buildDetailRow('ID Fournisseur', transaction.providerTransactionId!),
            if (transaction.errorMessage != null)
              _buildDetailRow('Erreur', transaction.errorMessage!, isError: true),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Fermer'),
          ),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value, {bool isError = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              '$label:',
              style: AppTextStyles.bodyMedium.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: AppTextStyles.bodyMedium.copyWith(
                color: isError ? AppColors.error : null,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
