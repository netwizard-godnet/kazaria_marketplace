import 'package:flutter/material.dart';
import '../../utils/constants.dart';
import '../../services/order_service.dart';
import '../../models/order_model.dart';
import 'order_details_screen.dart';

/// 🔍 Écran de suivi de commande public (numéro + email)
/// Permet de suivre une commande sans être connecté
class TrackOrderScreen extends StatefulWidget {
  const TrackOrderScreen({super.key});

  @override
  State<TrackOrderScreen> createState() => _TrackOrderScreenState();
}

class _TrackOrderScreenState extends State<TrackOrderScreen> {
  final _formKey = GlobalKey<FormState>();
  final _orderNumberController = TextEditingController();
  final _emailController = TextEditingController();
  final OrderService _orderService = OrderService();
  bool _isLoading = false;

  @override
  void dispose() {
    _orderNumberController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  Future<void> _trackOrder() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    try {
      // Appel API pour obtenir les détails de la commande
      final response = await _orderService.trackOrder(
        orderNumber: _orderNumberController.text.trim(),
        email: _emailController.text.trim(),
      );

      if (!mounted) return;

      setState(() {
        _isLoading = false;
      });

      if (response['success'] && response['order'] != null) {
        // Parser la commande
        final order = OrderModel.fromJson(response['order']);
        
        // Afficher les détails de la commande
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => OrderDetailsScreen(order: order),
          ),
        );
      } else {
        // Erreur
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.error_outline, color: Colors.white),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    response['message'] ?? 'Commande non trouvée',
                    style: const TextStyle(color: Colors.white),
                  ),
                ),
              ],
            ),
            backgroundColor: AppColors.error,
            duration: const Duration(seconds: 4),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;

      setState(() {
        _isLoading = false;
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.error_outline, color: Colors.white),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  'Erreur: $e',
                  style: const TextStyle(color: Colors.white),
                ),
              ),
            ],
          ),
          backgroundColor: AppColors.error,
          duration: const Duration(seconds: 4),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Suivre ma commande'),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSizes.paddingLarge),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: AppSizes.space4),
              
              // Illustration
              Container(
                padding: const EdgeInsets.all(32),
                decoration: BoxDecoration(
                  color: AppColors.primary.withOpacity(0.05),
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  Icons.local_shipping,
                  size: 80,
                  color: AppColors.primary,
                ),
              ),
              
              const SizedBox(height: AppSizes.space6),
              
              // Titre
              Text(
                'Suivre votre commande',
                style: AppTextStyles.h2.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppColors.textDark,
                ),
                textAlign: TextAlign.center,
              ),
              
              const SizedBox(height: AppSizes.space2),
              
              // Description
              Text(
                'Entrez votre numéro de commande et votre email pour voir le statut de votre livraison',
                style: AppTextStyles.bodyMedium.copyWith(
                  color: AppColors.textMedium,
                ),
                textAlign: TextAlign.center,
              ),
              
              const SizedBox(height: AppSizes.space6),
              
              // Numéro de commande
              TextFormField(
                controller: _orderNumberController,
                decoration: InputDecoration(
                  labelText: 'Numéro de commande',
                  hintText: 'Ex: KAZ-20250430-ABC123 ou KAZ-MOB-20250430-XYZ789',
                  prefixIcon: const Icon(Icons.receipt_long),
                  helperText: 'Formats acceptés: KAZ-AAAAMMJJ-XXXXXX ou KAZ-MOB-AAAAMMJJ-XXXXXX',
                  helperStyle: TextStyle(
                    fontSize: 11,
                    color: AppColors.textLight,
                  ),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                  ),
                  filled: true,
                  fillColor: AppColors.white,
                ),
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Veuillez entrer votre numéro de commande';
                  }
                  
                  // Validation du format KAZ(-XXX)-YYYYMMDD-XXXXXX
                  final pattern = RegExp(r'^KAZ(?:-[A-Z]+)?-\d{8}-[A-Z0-9]{6}$');
                  if (!pattern.hasMatch(value.trim().toUpperCase())) {
                    return 'Format invalide. Ex: KAZ-20250430-ABC123';
                  }
                  
                  return null;
                },
                textCapitalization: TextCapitalization.characters,
                onChanged: (value) {
                  // Auto-formater en majuscules
                  if (value != value.toUpperCase()) {
                    _orderNumberController.value = TextEditingValue(
                      text: value.toUpperCase(),
                      selection: _orderNumberController.selection,
                    );
                  }
                },
              ),
              
              const SizedBox(height: AppSizes.space4),
              
              // Email
              TextFormField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(
                  labelText: 'Email de commande',
                  hintText: 'votre@email.com',
                  prefixIcon: const Icon(Icons.email_outlined),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                  ),
                  filled: true,
                  fillColor: AppColors.white,
                ),
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Veuillez entrer votre email';
                  }
                  if (!value.contains('@')) {
                    return 'Email invalide';
                  }
                  return null;
                },
              ),
              
              const SizedBox(height: AppSizes.space6),
              
              // Bouton de recherche
              ElevatedButton(
                onPressed: _isLoading ? null : _trackOrder,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                  ),
                  elevation: 2,
                ),
                child: _isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                        ),
                      )
                    : Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.search, size: 20),
                          const SizedBox(width: 8),
                          Text(
                            'Suivre ma commande',
                            style: AppTextStyles.button.copyWith(
                              color: Colors.white,
                              fontSize: 16,
                            ),
                          ),
                        ],
                      ),
              ),
              
              const SizedBox(height: AppSizes.space6),
              
              // Information
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.info.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                  border: Border.all(
                    color: AppColors.info.withOpacity(0.3),
                    width: 1,
                  ),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.info_outline,
                      color: AppColors.info,
                      size: 20,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Besoin d\'aide ?',
                            style: AppTextStyles.bodySmall.copyWith(
                              fontWeight: FontWeight.w600,
                              color: AppColors.info,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Vous avez perdu votre numéro de commande ? Contactez notre support client.',
                            style: AppTextStyles.caption.copyWith(
                              color: AppColors.info,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

