import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/payment_provider.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';
import '../payment/payment_history_screen.dart';

class PaymentsScreen extends StatefulWidget {
  const PaymentsScreen({super.key});

  @override
  State<PaymentsScreen> createState() => _PaymentsScreenState();
}

class _PaymentsScreenState extends State<PaymentsScreen> {
  String? _selectedPaymentMethod;

  @override
  void initState() {
    super.initState();
    // Charger l'historique des paiements au démarrage
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<PaymentProvider>().loadPaymentHistory();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Paiements'),
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16), // Réduit de paddingLarge
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Section des méthodes de paiement disponibles
            _buildPaymentMethodsSection(),

            const SizedBox(height: 16), // Réduit

            // Section de l'historique des paiements
            _buildPaymentHistorySection(),

            const SizedBox(height: 16), // Réduit

            // Section d'aide
            _buildHelpSection(),
            
            const SizedBox(height: 16), // Espace en bas
          ],
        ),
      ),
    );
  }

  Widget _buildPaymentMethodsSection() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16), // Réduit
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Méthodes de paiement',
            style: AppTextStyles.h3.copyWith(
              color: AppColors.primary,
              fontSize: 18, // Réduit
            ),
          ),
          const SizedBox(height: 12), // Réduit

          // Orange Money
          _buildPaymentMethodCard(
            id: 'orange_money',
            icon: Icons.phone_android,
            title: 'Orange Money',
            description: 'Paiement sécurisé',
            color: Colors.orange,
            isAvailable: true,
          ),

          const SizedBox(height: 10), // Réduit

          // MTN Money
          _buildPaymentMethodCard(
            id: 'mtn_money',
            icon: Icons.phone_android,
            title: 'MTN Money',
            description: 'Paiement sécurisé',
            color: Colors.yellow.shade700,
            isAvailable: true,
          ),

          const SizedBox(height: 10), // Réduit

          // Moov Money
          _buildPaymentMethodCard(
            id: 'moov_money',
            icon: Icons.phone_android,
            title: 'Moov Money',
            description: 'Paiement sécurisé',
            color: Colors.blue,
            isAvailable: true,
          ),

          const SizedBox(height: 10), // Réduit

          // Paiement à la livraison
          _buildPaymentMethodCard(
            id: 'cash_on_delivery',
            icon: Icons.money,
            title: 'Paiement à la livraison',
            description: 'Payez en espèces',
            color: AppColors.success,
            isAvailable: true,
          ),

          const SizedBox(height: 10), // Réduit

          // Carte bancaire
          _buildPaymentMethodCard(
            id: 'card',
            icon: Icons.credit_card,
            title: 'Carte bancaire',
            description: 'Bientôt disponible',
            color: AppColors.primary,
            isAvailable: false,
            comingSoon: true,
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentMethodCard({
    required String id,
    required IconData icon,
    required String title,
    required String description,
    required Color color,
    required bool isAvailable,
    bool comingSoon = false,
  }) {
    final isSelected = _selectedPaymentMethod == id;
    
    return InkWell(
      onTap: isAvailable ? () {
        setState(() {
          _selectedPaymentMethod = id;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('$title sélectionné'),
            duration: const Duration(seconds: 1),
            backgroundColor: color,
          ),
        );
      } : null,
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.all(12), // Réduit
      decoration: BoxDecoration(
          color: isSelected 
              ? color.withOpacity(0.15)
              : isAvailable 
                  ? color.withOpacity(0.08) 
                  : AppColors.textLight.withOpacity(0.05),
          borderRadius: BorderRadius.circular(10),
        border: Border.all(
            color: isSelected 
                ? color 
                : isAvailable 
                    ? color.withOpacity(0.2) 
                    : AppColors.textLight.withOpacity(0.2),
            width: isSelected ? 2 : 1,
        ),
      ),
      child: Row(
        children: [
          Container(
              width: 44,
              height: 44,
            decoration: BoxDecoration(
                color: isAvailable ? color.withOpacity(0.15) : AppColors.textLight.withOpacity(0.1),
                borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              icon,
              color: isAvailable ? color : AppColors.textLight,
                size: 22,
              ),
            ),
            const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: [
                      Flexible(
                        child: Text(
                      title,
                          style: AppTextStyles.bodyMedium.copyWith(
                        fontWeight: FontWeight.bold,
                        color: isAvailable ? AppColors.textDark : AppColors.textLight,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    if (comingSoon) ...[
                        const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 6,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: AppColors.warning,
                            borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          'Bientôt',
                          style: AppTextStyles.caption.copyWith(
                            color: AppColors.white,
                            fontWeight: FontWeight.bold,
                              fontSize: 10,
                            ),
                        ),
                      ),
                    ],
                  ],
                ),
                  const SizedBox(height: 2),
                Text(
                  description,
                    style: AppTextStyles.bodySmall.copyWith(
                    color: isAvailable ? AppColors.textMedium : AppColors.textLight.withOpacity(0.7),
                  ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
            const SizedBox(width: 8),
          Icon(
              isSelected 
                  ? Icons.radio_button_checked 
                  : isAvailable 
                      ? Icons.radio_button_unchecked 
                      : Icons.lock,
              color: isSelected 
                  ? color 
                  : isAvailable 
                      ? color.withOpacity(0.5) 
                      : AppColors.textLight,
              size: 22,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPaymentHistorySection() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16), // Réduit
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Historique',
                style: AppTextStyles.h3.copyWith(
                  color: AppColors.primary,
                  fontSize: 18, // Réduit
                ),
              ),
              TextButton(
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const PaymentHistoryScreen(),
                    ),
                  );
                },
                child: const Text('Voir tout'),
              ),
            ],
          ),
          const SizedBox(height: 12), // Réduit

          Consumer<PaymentProvider>(
            builder: (context, paymentProvider, _) {
              if (paymentProvider.isLoading) {
                return const Center(
                  child: Padding(
                    padding: EdgeInsets.all(16),
                    child: CircularProgressIndicator(),
                  ),
                );
              }

              if (paymentProvider.paymentHistory.isEmpty) {
                return Container(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      Icon(
                        Icons.payment_outlined,
                        size: 40,
                        color: AppColors.textLight,
                      ),
                      const SizedBox(height: 12),
                      Text(
                        'Aucun paiement récent',
                        style: AppTextStyles.bodyMedium.copyWith(
                          color: AppColors.textLight,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Vos paiements apparaîtront ici',
                        style: AppTextStyles.bodySmall.copyWith(
                          color: AppColors.textLight,
                        ),
                      ),
                    ],
                  ),
                );
              }

              // Afficher les 3 derniers paiements
              final recentPayments = paymentProvider.paymentHistory.take(3).toList();

              return Column(
                children: recentPayments.map((transaction) {
                  return Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.background,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(
                            color: _getStatusColor(transaction.status).withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Icon(
                            _getPaymentMethodIcon(transaction.paymentMethodId),
                            color: _getStatusColor(transaction.status),
                            size: 18,
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(
                                _getPaymentMethodName(transaction.paymentMethodId),
                                style: AppTextStyles.bodyMedium.copyWith(
                                  fontWeight: FontWeight.bold,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              Text(
                                Helpers.formatDateTime(transaction.createdAt),
                                style: AppTextStyles.bodySmall.copyWith(
                                  color: AppColors.textMedium,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 8),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              Helpers.formatPrice(transaction.amount),
                              style: AppTextStyles.bodyMedium.copyWith(
                                fontWeight: FontWeight.bold,
                                color: AppColors.primary,
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 6,
                                vertical: 2,
                              ),
                              decoration: BoxDecoration(
                                color: _getStatusColor(transaction.status).withOpacity(0.1),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                transaction.statusText,
                                style: AppTextStyles.caption.copyWith(
                                  color: _getStatusColor(transaction.status),
                                  fontWeight: FontWeight.bold,
                                  fontSize: 10,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  );
                }).toList(),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildHelpSection() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.info.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: AppColors.info.withOpacity(0.3),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            children: [
              Icon(
                Icons.help_outline,
                color: AppColors.info,
                size: 22,
              ),
              const SizedBox(width: 8),
              Text(
                'Besoin d\'aide ?',
                style: AppTextStyles.h4.copyWith(
                  color: AppColors.info,
                  fontSize: 16,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            'Si vous rencontrez des problèmes avec vos paiements, contactez notre support.',
            style: AppTextStyles.bodySmall.copyWith(
              color: AppColors.info,
            ),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            height: 40,
            child: ElevatedButton.icon(
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Support client bientôt disponible'),
                  backgroundColor: AppColors.info,
                ),
              );
            },
              icon: const Icon(Icons.support_agent, size: 18),
              label: const Text('Contacter le support', style: TextStyle(fontSize: 14)),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.info,
              foregroundColor: AppColors.white,
                padding: const EdgeInsets.symmetric(horizontal: 12),
              ),
            ),
          ),
        ],
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
}
