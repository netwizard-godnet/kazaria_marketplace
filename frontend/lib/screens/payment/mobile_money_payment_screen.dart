import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../../providers/payment_provider.dart';
import '../../models/payment_method.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';

class MobileMoneyPaymentScreen extends StatefulWidget {
  final String orderId;
  final double amount;
  final String currency;
  final PaymentMethod paymentMethod;

  const MobileMoneyPaymentScreen({
    super.key,
    required this.orderId,
    required this.amount,
    required this.currency,
    required this.paymentMethod,
  });

  @override
  State<MobileMoneyPaymentScreen> createState() => _MobileMoneyPaymentScreenState();
}

class _MobileMoneyPaymentScreenState extends State<MobileMoneyPaymentScreen> {
  final _formKey = GlobalKey<FormState>();
  final _phoneController = TextEditingController();
  final _verificationController = TextEditingController();
  bool _isProcessing = false;
  bool _paymentInitiated = false;
  String? _transactionId;

  @override
  void dispose() {
    _phoneController.dispose();
    _verificationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Paiement ${widget.paymentMethod.name}'),
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.white,
      ),
      body: Consumer<PaymentProvider>(
        builder: (context, paymentProvider, _) {
          return SingleChildScrollView(
            padding: const EdgeInsets.all(AppSizes.paddingLarge),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Informations du paiement
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(AppSizes.paddingLarge),
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
                        Row(
                          children: [
                            Icon(
                              Icons.phone_android,
                              color: AppColors.primary,
                              size: 24,
                            ),
                            const SizedBox(width: 8),
                            Text(
                              widget.paymentMethod.name,
                              style: AppTextStyles.h4.copyWith(
                                color: AppColors.primary,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'Commande: #${widget.orderId}',
                          style: AppTextStyles.bodyLarge,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Montant: ${Helpers.formatPrice(widget.amount)} ${widget.currency}',
                          style: AppTextStyles.h3.copyWith(
                            color: AppColors.primary,
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: AppSizes.paddingLarge),

                  if (!_paymentInitiated) ...[
                    // Formulaire de numéro de téléphone
                    Text(
                      'Numéro de téléphone',
                      style: AppTextStyles.h4,
                    ),
                    const SizedBox(height: 8),
                    Consumer<PaymentProvider>(
                      builder: (context, paymentProvider, _) {
                        return CustomTextField(
                          controller: _phoneController,
                          label: 'Entrez votre numéro',
                          hint: _getPhoneHint(),
                          keyboardType: TextInputType.phone,
                          prefixIcon: Icons.phone,
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'Veuillez entrer votre numéro de téléphone';
                            }
                            if (!paymentProvider.validatePhoneNumber(value)) {
                              return 'Format de numéro invalide pour ${widget.paymentMethod.name}';
                            }
                            return null;
                          },
                        );
                      },
                    ),

                    const SizedBox(height: AppSizes.paddingLarge),

                    // Instructions
                    Container(
                      padding: const EdgeInsets.all(AppSizes.paddingMedium),
                      decoration: BoxDecoration(
                        color: AppColors.info.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(AppSizes.radiusSmall),
                        border: Border.all(
                          color: AppColors.info.withOpacity(0.3),
                        ),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Icon(
                                Icons.info_outline,
                                color: AppColors.info,
                                size: 20,
                              ),
                              const SizedBox(width: 8),
                              Text(
                                'Instructions',
                                style: AppTextStyles.bodyLarge.copyWith(
                                  color: AppColors.info,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(
                            _getPaymentInstructions(),
                            style: AppTextStyles.bodyMedium.copyWith(
                              color: AppColors.info,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ] else ...[
                    // Écran de confirmation
                    Container(
                      padding: const EdgeInsets.all(AppSizes.paddingLarge),
                      decoration: BoxDecoration(
                        color: AppColors.success.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                        border: Border.all(
                          color: AppColors.success.withOpacity(0.3),
                        ),
                      ),
                      child: Column(
                        children: [
                          Icon(
                            Icons.check_circle_outline,
                            color: AppColors.success,
                            size: 64,
                          ),
                          const SizedBox(height: 16),
                          Text(
                            'Paiement initié',
                            style: AppTextStyles.h3.copyWith(
                              color: AppColors.success,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Veuillez confirmer le paiement sur votre téléphone',
                            style: AppTextStyles.bodyLarge,
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 16),
                          Text(
                            'Numéro: ${paymentProvider.formatPhoneNumber(_phoneController.text)}',
                            style: AppTextStyles.bodyMedium.copyWith(
                              color: AppColors.textLight,
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: AppSizes.paddingLarge),

                    // Code de vérification (optionnel)
                    Text(
                      'Code de vérification (optionnel)',
                      style: AppTextStyles.h4,
                    ),
                    const SizedBox(height: 8),
                    CustomTextField(
                      controller: _verificationController,
                      label: 'Code de vérification',
                      hint: 'Entrez le code reçu par SMS',
                      keyboardType: TextInputType.number,
                      prefixIcon: Icons.security,
                    ),
                  ],

                  const SizedBox(height: AppSizes.paddingLarge),

                  // Boutons d'action
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () => Navigator.of(context).pop(),
                          child: const Text('Annuler'),
                        ),
                      ),
                      const SizedBox(width: AppSizes.paddingMedium),
                      Expanded(
                        flex: 2,
                        child: CustomButton(
                          text: _paymentInitiated ? 'Confirmer le paiement' : 'Initier le paiement',
                          onPressed: _isProcessing ? null : _handlePayment,
                          isLoading: _isProcessing,
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: AppSizes.paddingMedium),

                  // Statut du paiement
                  if (paymentProvider.currentTransaction != null) ...[
                    Container(
                      padding: const EdgeInsets.all(AppSizes.paddingMedium),
                      decoration: BoxDecoration(
                        color: _getStatusColor().withOpacity(0.1),
                        borderRadius: BorderRadius.circular(AppSizes.radiusSmall),
                        border: Border.all(
                          color: _getStatusColor().withOpacity(0.3),
                        ),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            _getStatusIcon(),
                            color: _getStatusColor(),
                            size: 20,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              paymentProvider.currentTransaction!.statusText,
                              style: AppTextStyles.bodyMedium.copyWith(
                                color: _getStatusColor(),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  String _getPhoneHint() {
    switch (widget.paymentMethod.config?['provider']) {
      case 'orange':
        return 'Ex: 07 12 34 56 78';
      case 'mtn':
        return 'Ex: 05 12 34 56 78';
      case 'moov':
        return 'Ex: 01 12 34 56 78';
      default:
        return 'Ex: 07 12 34 56 78';
    }
  }

  String _getPaymentInstructions() {
    switch (widget.paymentMethod.config?['provider']) {
      case 'orange':
        return '1. Composez *144#\n2. Sélectionnez "Paiement de facture"\n3. Entrez le code: ${_generatePaymentCode()}\n4. Confirmez le paiement';
      case 'mtn':
        return '1. Composez *126#\n2. Sélectionnez "Paiement de facture"\n3. Entrez le code: ${_generatePaymentCode()}\n4. Confirmez le paiement';
      case 'moov':
        return '1. Composez *144#\n2. Sélectionnez "Paiement de facture"\n3. Entrez le code: ${_generatePaymentCode()}\n4. Confirmez le paiement';
      default:
        return 'Suivez les instructions sur votre téléphone pour confirmer le paiement';
    }
  }

  String _generatePaymentCode() {
    // Générer un code de paiement basé sur l'ID de commande
    return widget.orderId.substring(0, 6).toUpperCase();
  }

  Color _getStatusColor() {
    final paymentProvider = Provider.of<PaymentProvider>(context, listen: false);
    if (paymentProvider.currentTransaction == null) {
      return AppColors.textLight;
    }

    switch (paymentProvider.currentTransaction!.status) {
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

  IconData _getStatusIcon() {
    final paymentProvider = Provider.of<PaymentProvider>(context, listen: false);
    if (paymentProvider.currentTransaction == null) {
      return Icons.help_outline;
    }

    switch (paymentProvider.currentTransaction!.status) {
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

  Future<void> _handlePayment() async {
    if (!_formKey.currentState!.validate()) return;

    final paymentProvider = context.read<PaymentProvider>();

    if (!_paymentInitiated) {
      // Initier le paiement
      setState(() {
        _isProcessing = true;
      });

      try {
        final result = await paymentProvider.initiateMobileMoneyPayment(
          orderId: widget.orderId,
          phoneNumber: _phoneController.text,
          amount: widget.amount,
          description: 'Paiement commande #${widget.orderId}',
        );

        if (result.success) {
          setState(() {
            _paymentInitiated = true;
            _transactionId = result.transactionId;
          });

          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(result.message ?? 'Paiement initié avec succès'),
              backgroundColor: AppColors.success,
            ),
          );
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(result.message ?? 'Erreur lors de l\'initiation du paiement'),
              backgroundColor: AppColors.error,
            ),
          );
        }
      } catch (e) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      } finally {
        setState(() {
          _isProcessing = false;
        });
      }
    } else {
      // Confirmer le paiement
      setState(() {
        _isProcessing = true;
      });

      try {
        final result = await paymentProvider.confirmPayment(
          transactionId: _transactionId!,
          verificationCode: _verificationController.text.isNotEmpty 
              ? _verificationController.text 
              : null,
        );

        if (result.success) {
          Navigator.of(context).pop(true); // Retourner true pour indiquer le succès
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(result.message ?? 'Erreur lors de la confirmation du paiement'),
              backgroundColor: AppColors.error,
            ),
          );
        }
      } catch (e) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      } finally {
        setState(() {
          _isProcessing = false;
        });
      }
    }
  }
}
