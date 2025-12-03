import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/payment_provider.dart';
import '../../models/payment_method.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';
import '../../widgets/custom_button.dart';
import 'mobile_money_payment_screen.dart';

class PaymentMethodSelectionScreen extends StatefulWidget {
  final String orderId;
  final double amount;
  final String currency;
  final bool selectionOnly;

  const PaymentMethodSelectionScreen({
    super.key,
    required this.orderId,
    required this.amount,
    required this.currency,
    this.selectionOnly = false,
  });

  @override
  State<PaymentMethodSelectionScreen> createState() => _PaymentMethodSelectionScreenState();
}

class _PaymentMethodSelectionScreenState extends State<PaymentMethodSelectionScreen> {
  PaymentMethod? _selectedMethod;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<PaymentProvider>().loadPaymentMethods();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Choisir le mode de paiement'),
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.white,
      ),
      body: Consumer<PaymentProvider>(
        builder: (context, paymentProvider, _) {
          if (paymentProvider.isLoading) {
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
                      paymentProvider.loadPaymentMethods();
                    },
                    child: const Text('Réessayer'),
                  ),
                ],
              ),
            );
          }

          return Column(
            children: [
              // Résumé de la commande
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(AppSizes.paddingLarge),
                margin: const EdgeInsets.all(AppSizes.paddingMedium),
                decoration: BoxDecoration(
                  color: AppColors.primary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                  border: Border.all(
                    color: AppColors.primary.withOpacity(0.3),
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Résumé de la commande',
                      style: AppTextStyles.h4.copyWith(
                        color: AppColors.primary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Commande #${widget.orderId}',
                      style: AppTextStyles.bodyLarge,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Montant à payer: ${Helpers.formatPrice(widget.amount)} ${widget.currency}',
                      style: AppTextStyles.h3.copyWith(
                        color: AppColors.primary,
                      ),
                    ),
                  ],
                ),
              ),

              // Liste des méthodes de paiement
              Expanded(
                child: ListView.builder(
                  padding: const EdgeInsets.symmetric(
                    horizontal: AppSizes.paddingMedium,
                  ),
                  itemCount: paymentProvider.availableMethods.length,
                  itemBuilder: (context, index) {
                    final method = paymentProvider.availableMethods[index];
                    final isSelected = _selectedMethod?.id == method.id;

                    return Card(
                      margin: const EdgeInsets.only(bottom: AppSizes.paddingMedium),
                      elevation: isSelected ? 4 : 1,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                        side: BorderSide(
                          color: isSelected ? AppColors.primary : Colors.transparent,
                          width: 2,
                        ),
                      ),
                      child: InkWell(
                        onTap: () {
                          setState(() {
                            _selectedMethod = method;
                          });
                          paymentProvider.selectPaymentMethod(method);
                        },
                        borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                        child: Padding(
                          padding: const EdgeInsets.all(AppSizes.paddingMedium),
                          child: Row(
                            children: [
                              // Icône de la méthode
                              Container(
                                width: 48,
                                height: 48,
                                decoration: BoxDecoration(
                                  color: AppColors.primary.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(AppSizes.radiusSmall),
                                ),
                                child: Icon(
                                  _getPaymentMethodIcon(method.type),
                                  color: AppColors.primary,
                                  size: 24,
                                ),
                              ),
                              const SizedBox(width: AppSizes.paddingMedium),

                              // Informations de la méthode
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      method.name,
                                      style: AppTextStyles.h4,
                                    ),
                                    if (method.description != null) ...[
                                      const SizedBox(height: 4),
                                      Text(
                                        method.description!,
                                        style: AppTextStyles.bodyMedium.copyWith(
                                          color: AppColors.textLight,
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                              ),

                              // Indicateur de sélection
                              Radio<PaymentMethod>(
                                value: method,
                                groupValue: _selectedMethod,
                                onChanged: (value) {
                                  setState(() {
                                    _selectedMethod = value;
                                  });
                                  paymentProvider.selectPaymentMethod(method);
                                },
                                activeColor: AppColors.primary,
                              ),
                            ],
                          ),
                        ),
                      ),
                    );
                  },
                ),
              ),

              // Bouton de confirmation
              Container(
                padding: const EdgeInsets.all(AppSizes.paddingLarge),
                decoration: BoxDecoration(
                  color: AppColors.white,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.1),
                      blurRadius: 10,
                      offset: const Offset(0, -2),
                    ),
                  ],
                ),
                child: SafeArea(
                  child: CustomButton(
                    text: 'Continuer',
                    onPressed: _selectedMethod != null ? _proceedToPayment : null,
                    width: double.infinity,
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  IconData _getPaymentMethodIcon(String type) {
    switch (type) {
      case 'mobile_money':
        return Icons.phone_android;
      case 'card':
        return Icons.credit_card;
      case 'cash_on_delivery':
        return Icons.money;
      default:
        return Icons.payment;
    }
  }

  void _proceedToPayment() {
    if (_selectedMethod == null) return;

    if (widget.selectionOnly) {
      Navigator.pop(context, _selectedMethod!.type);
      return;
    }

    switch (_selectedMethod!.type) {
      case 'mobile_money':
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => MobileMoneyPaymentScreen(
              orderId: widget.orderId,
              amount: widget.amount,
              currency: widget.currency,
              paymentMethod: _selectedMethod!,
            ),
          ),
        );
        break;
      case 'cash_on_delivery':
        _handleCashOnDelivery();
        break;
      case 'card':
        _handleCardPayment();
        break;
      default:
        _showUnsupportedPaymentMethod();
    }
  }

  void _handleCashOnDelivery() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.money, color: AppColors.primary),
            SizedBox(width: 12),
            Text('Paiement à la livraison'),
          ],
        ),
        content: const Text(
          'Vous paierez en espèces lors de la livraison de votre commande. '
          'Veuillez préparer le montant exact.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Annuler'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop();
              _confirmCashOnDeliveryPayment();
            },
            child: const Text('Confirmer'),
          ),
        ],
      ),
    );
  }

  void _handleCardPayment() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Paiement par carte bancaire bientôt disponible'),
        backgroundColor: AppColors.warning,
      ),
    );
  }

  void _showUnsupportedPaymentMethod() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Mode de paiement non supporté'),
        backgroundColor: AppColors.error,
      ),
    );
  }

  void _confirmCashOnDeliveryPayment() {
    // Ici, vous pouvez ajouter la logique pour confirmer le paiement à la livraison
    Navigator.of(context).pop(true); // Retourner true pour indiquer le succès
  }
}
