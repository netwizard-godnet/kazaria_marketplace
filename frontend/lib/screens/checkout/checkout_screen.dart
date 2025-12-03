import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/cart_provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/order_service.dart';
import '../../services/address_service.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../payment/payment_method_selection_screen.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  final _cityController = TextEditingController();
  final _notesController = TextEditingController();
  final OrderService _orderService = OrderService();
  String _paymentMethod = 'cash_on_delivery';
  bool _isProcessing = false;

  @override
  void initState() {
    super.initState();
    _loadDefaultAddress();
  }

  Future<void> _loadDefaultAddress() async {
    // Charger l'adresse par défaut
    final defaultAddress = await AddressService.getDefaultAddress();

    if (defaultAddress != null && defaultAddress.isNotEmpty) {
      // Utiliser l'adresse par défaut
      _nameController.text = defaultAddress['name'] ?? '';
      _phoneController.text = defaultAddress['phone'] ?? '';
      _addressController.text = defaultAddress['address'] ?? '';
      _cityController.text = defaultAddress['city'] ?? '';
    } else {
      // Si pas d'adresse par défaut, utiliser les infos du user
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user != null) {
        _nameController.text = user.fullName;
        _phoneController.text = user.telephone ?? '';
        _addressController.text = user.adresse ?? '';
        _cityController.text = user.ville ?? '';
      }
    }

    setState(() {});
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _cityController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _placeOrder() async {
    if (!_formKey.currentState!.validate()) return;

    if (_isProcessing) return;

    // Si c'est un paiement Mobile Money, rediriger vers l'écran de paiement
    if (_paymentMethod == 'mobile_money') {
      _handleMobileMoneyPayment();
      return;
    }

    setState(() {
      _isProcessing = true;
    });

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const AlertDialog(
        title: Text('Commande en cours'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: 16),
            Text('Création de votre commande...'),
          ],
        ),
      ),
    );

    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final cartProvider = Provider.of<CartProvider>(context, listen: false);
      final response = await _orderService.createOrder(
        shippingName: _nameController.text.trim(),
        shippingEmail: user?.email ?? '',
        shippingPhone: _phoneController.text.trim(),
        shippingAddress: _addressController.text.trim(),
        shippingCity: _cityController.text.trim(),
        shippingCountry: 'CI', // Côte d'Ivoire par défaut
        paymentMethod: _paymentMethod,
        customerNotes: _notesController.text.trim().isNotEmpty
            ? _notesController.text.trim()
            : null,
        promoCode: cartProvider.promoCode,
      );

      if (!mounted) return;

      Navigator.of(context).pop(); // Fermer le dialog de chargement

      if (response['success']) {
        // Vider le panier
        await cartProvider.clearCart();

        // 🔔 Annuler les rappels de panier après validation de commande
        cartProvider.cancelCartReminder();

        if (!mounted) return;

        // Afficher le succès
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => AlertDialog(
            title: const Row(
              children: [
                Icon(Icons.check_circle, color: AppColors.success, size: 28),
                SizedBox(width: 12),
                Text('Commande créée !'),
              ],
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Numéro de commande : ${response['order_number'] ?? 'N/A'}',
                ),
                const SizedBox(height: 8),
                const Text('Votre commande a été créée avec succès !'),
                const SizedBox(height: 8),
                const Text('Vous recevrez une confirmation par email.'),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.of(context).popUntil((route) => route.isFirst);
                },
                child: const Text('Retour à l\'accueil'),
              ),
            ],
          ),
        );
      } else {
        // Afficher l'erreur
        showDialog(
          context: context,
          builder: (context) => AlertDialog(
            title: const Row(
              children: [
                Icon(Icons.error, color: AppColors.error, size: 28),
                SizedBox(width: 12),
                Text('Erreur'),
              ],
            ),
            content: Text(
              response['message'] ?? 'Impossible de créer la commande',
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
    } catch (e) {
      if (!mounted) return;

      Navigator.of(context).pop(); // Fermer le dialog de chargement

      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: const Row(
            children: [
              Icon(Icons.error, color: AppColors.error, size: 28),
              SizedBox(width: 12),
              Text('Erreur'),
            ],
          ),
          content: Text('Une erreur est survenue : $e'),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('Fermer'),
            ),
          ],
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isProcessing = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Finaliser la commande')),
      body: Column(
        children: [
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(AppSizes.paddingLarge),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Shipping information
                    const Text(
                      'Informations de livraison',
                      style: AppTextStyles.h3,
                    ),
                    const SizedBox(height: 16),
                    CustomTextField(
                      label: 'Nom complet',
                      controller: _nameController,
                      validator: (value) =>
                          Helpers.validateRequired(value, 'Le nom'),
                    ),
                    const SizedBox(height: 16),
                    CustomTextField(
                      label: 'Téléphone',
                      controller: _phoneController,
                      keyboardType: TextInputType.phone,
                      validator: Helpers.validatePhone,
                    ),
                    const SizedBox(height: 16),
                    CustomTextField(
                      label: 'Adresse',
                      controller: _addressController,
                      maxLines: 2,
                      validator: (value) =>
                          Helpers.validateRequired(value, 'L\'adresse'),
                    ),
                    const SizedBox(height: 16),
                    CustomTextField(
                      label: 'Ville',
                      controller: _cityController,
                      validator: (value) =>
                          Helpers.validateRequired(value, 'La ville'),
                    ),
                    const SizedBox(height: 24),
                    // Payment method
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Mode de paiement', style: AppTextStyles.h3),
                        TextButton(
                          onPressed: _selectPaymentMethod,
                          child: const Text('Choisir'),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Container(
                      padding: const EdgeInsets.all(AppSizes.paddingMedium),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(
                          AppSizes.radiusMedium,
                        ),
                        border: Border.all(
                          color: AppColors.primary.withOpacity(0.3),
                        ),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            _getPaymentMethodIcon(_paymentMethod),
                            color: AppColors.primary,
                            size: 24,
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  _getPaymentMethodName(_paymentMethod),
                                  style: AppTextStyles.bodyLarge.copyWith(
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                Text(
                                  _getPaymentMethodDescription(_paymentMethod),
                                  style: AppTextStyles.bodyMedium.copyWith(
                                    color: AppColors.textMedium,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Icon(
                            Icons.arrow_forward_ios,
                            color: AppColors.primary,
                            size: 16,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),
                    // Notes du client
                    const Text('Notes (optionnel)', style: AppTextStyles.h4),
                    const SizedBox(height: 8),
                    CustomTextField(
                      label: 'Commentaires pour votre commande',
                      controller: _notesController,
                      maxLines: 3,
                      validator: (value) => null, // Optionnel
                    ),
                  ],
                ),
              ),
            ),
          ),
          // Summary
          Consumer<CartProvider>(
            builder: (context, cartProvider, _) {
              return Container(
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
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Total à payer', style: AppTextStyles.h3),
                          Text(
                            Helpers.formatPrice(cartProvider.total),
                            style: AppTextStyles.h3.copyWith(
                              color: AppColors.primary,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      CustomButton(
                        text: 'Confirmer la commande',
                        onPressed: _placeOrder,
                        width: double.infinity,
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  // Méthodes helper pour l'affichage des méthodes de paiement
  IconData _getPaymentMethodIcon(String method) {
    switch (method) {
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

  String _getPaymentMethodName(String method) {
    switch (method) {
      case 'mobile_money':
        return 'Mobile Money';
      case 'card':
        return 'Carte bancaire';
      case 'cash_on_delivery':
        return 'Paiement à la livraison';
      default:
        return 'Méthode inconnue';
    }
  }

  String _getPaymentMethodDescription(String method) {
    switch (method) {
      case 'mobile_money':
        return 'Paiement sécurisé avec Orange Money, MTN Money ou Moov Money';
      case 'card':
        return 'Paiement sécurisé par carte bancaire';
      case 'cash_on_delivery':
        return 'Payez en espèces lors de la livraison';
      default:
        return 'Description non disponible';
    }
  }

  // Gestion de la sélection de méthode de paiement
  Future<void> _selectPaymentMethod() async {
    final cartProvider = Provider.of<CartProvider>(context, listen: false);

    final result = await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => PaymentMethodSelectionScreen(
          orderId: 'ORDER_${DateTime.now().millisecondsSinceEpoch}',
          amount: cartProvider.total,
          currency: 'XOF',
          selectionOnly: true,
        ),
      ),
    );

    if (result != null) {
      // Mettre à jour la méthode de paiement sélectionnée
      setState(() {
        _paymentMethod = result;
      });
    }
  }

  // Gestion du paiement Mobile Money
  Future<void> _handleMobileMoneyPayment() async {
    final cartProvider = Provider.of<CartProvider>(context, listen: false);

    // Créer la commande d'abord
    setState(() {
      _isProcessing = true;
    });

    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final response = await _orderService.createOrder(
        shippingName: _nameController.text.trim(),
        shippingEmail: user?.email ?? '',
        shippingPhone: _phoneController.text.trim(),
        shippingAddress: _addressController.text.trim(),
        shippingCity: _cityController.text.trim(),
        shippingCountry: 'CI',
        paymentMethod: _paymentMethod,
        customerNotes: _notesController.text.trim().isNotEmpty
            ? _notesController.text.trim()
            : null,
        promoCode: cartProvider.promoCode,
      );

      if (response['success'] && response['order_number'] != null) {
        // Rediriger vers l'écran de paiement Mobile Money
        final paymentResult = await Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => PaymentMethodSelectionScreen(
              orderId: response['order_number'],
              amount: cartProvider.total,
              currency: 'XOF',
            ),
          ),
        );

        if (paymentResult == true) {
          // Paiement réussi, vider le panier
          await cartProvider.clearCart();

          // 🔔 Annuler les rappels de panier après validation de commande
          cartProvider.cancelCartReminder();

          if (mounted) {
            Navigator.of(context).popUntil((route) => route.isFirst);
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Paiement réussi ! Commande créée avec succès.'),
                backgroundColor: AppColors.success,
              ),
            );
          }
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                response['message'] ??
                    'Erreur lors de la création de la commande',
              ),
              backgroundColor: AppColors.error,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isProcessing = false;
        });
      }
    }
  }
}
