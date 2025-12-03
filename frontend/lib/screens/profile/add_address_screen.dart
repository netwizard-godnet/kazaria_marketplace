import 'package:flutter/material.dart';
import '../../utils/constants.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../services/address_service.dart';

class AddAddressScreen extends StatefulWidget {
  final Map<String, dynamic>? address;

  const AddAddressScreen({
    super.key,
    this.address,
  });

  @override
  State<AddAddressScreen> createState() => _AddAddressScreenState();
}

class _AddAddressScreenState extends State<AddAddressScreen> {
  final _formKey = GlobalKey<FormState>();
  final _labelController = TextEditingController();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  final _cityController = TextEditingController();
  final _postalCodeController = TextEditingController();
  final _countryController = TextEditingController();
  bool _isDefault = false;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    if (widget.address != null) {
      _labelController.text = widget.address!['label'] ?? '';
      _nameController.text = widget.address!['name'] ?? '';
      _phoneController.text = widget.address!['phone'] ?? '';
      _addressController.text = widget.address!['address'] ?? '';
      _cityController.text = widget.address!['city'] ?? '';
      _postalCodeController.text = widget.address!['postalCode'] ?? '';
      _countryController.text = widget.address!['country'] ?? '';
      _isDefault = widget.address!['isDefault'] ?? false;
    } else {
      _countryController.text = 'Côte d\'Ivoire';
    }
  }

  @override
  void dispose() {
    _labelController.dispose();
    _nameController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _cityController.dispose();
    _postalCodeController.dispose();
    _countryController.dispose();
    super.dispose();
  }

  Future<void> _saveAddress() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    try {
      // Préparer les données de l'adresse
      final addressData = {
        'label': _labelController.text.trim(),
        'name': _nameController.text.trim(),
        'phone': _phoneController.text.trim(),
        'address': _addressController.text.trim(),
        'city': _cityController.text.trim(),
        'postalCode': _postalCodeController.text.trim(),
        'country': _countryController.text.trim(),
        'isDefault': _isDefault,
      };

      // Sauvegarder via AddressService
      if (widget.address != null && widget.address!['id'] != null) {
        // Modification d'une adresse existante
        await AddressService.updateAddress(
          widget.address!['id'] as int,
          addressData,
        );
      } else {
        // Ajout d'une nouvelle adresse
        await AddressService.addAddress(addressData);
      }

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.check_circle, color: Colors.white),
              const SizedBox(width: 12),
              Text(
                widget.address != null 
                    ? 'Adresse modifiée avec succès'
                    : 'Adresse ajoutée avec succès',
              ),
            ],
          ),
          backgroundColor: AppColors.success,
          behavior: SnackBarBehavior.floating,
        ),
      );

      // Retourner les données de l'adresse avec succès
      Navigator.pop(context, true);
      
    } catch (e) {
      if (!mounted) return;
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Erreur: ${e.toString()}'),
          backgroundColor: AppColors.error,
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(
          widget.address != null ? 'Modifier l\'adresse' : 'Nouvelle adresse',
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w600,
            color: Colors.black,
          ),
        ),
        centerTitle: true,
        elevation: 0,
        backgroundColor: Colors.white,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.black),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSizes.paddingMedium),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // En-tête
              Container(
                padding: const EdgeInsets.all(AppSizes.paddingMedium),
                decoration: BoxDecoration(
                  color: AppColors.white,
                  borderRadius: BorderRadius.circular(AppSizes.radiusXL),
                  boxShadow: AppShadows.shadowSM,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(AppSizes.space3),
                          decoration: BoxDecoration(
                            color: AppColors.primary.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                          ),
                          child: const Icon(
                            Icons.location_on_outlined,
                            color: AppColors.primary,
                            size: 24,
                          ),
                        ),
                        const SizedBox(width: AppSizes.space3),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                widget.address != null 
                                    ? 'Modifier l\'adresse'
                                    : 'Nouvelle adresse',
                                style: AppTextStyles.h3.copyWith(
                                  color: AppColors.textDark,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Ajoutez ou modifiez une adresse de livraison',
                                style: AppTextStyles.bodyMedium.copyWith(
                                  color: AppColors.textMuted,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              const SizedBox(height: AppSizes.space4),

              // Formulaire
              Container(
                padding: const EdgeInsets.all(AppSizes.paddingMedium),
                decoration: BoxDecoration(
                  color: AppColors.white,
                  borderRadius: BorderRadius.circular(AppSizes.radiusXL),
                  boxShadow: AppShadows.shadowSM,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Informations de livraison',
                      style: AppTextStyles.h3.copyWith(
                        color: AppColors.textDark,
                      ),
                    ),
                    const SizedBox(height: AppSizes.space4),

                    // Label de l'adresse
                    CustomTextField(
                      controller: _labelController,
                      label: 'Label de l\'adresse',
                      hint: 'Ex: Domicile, Bureau, etc.',
                      prefixIcon: Icons.label_outline,
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Veuillez entrer un label';
                        }
                        return null;
                      },
                    ),

                    const SizedBox(height: AppSizes.space4),

                    // Nom complet
                    CustomTextField(
                      controller: _nameController,
                      label: 'Nom complet',
                      hint: 'Entrez votre nom complet',
                      prefixIcon: Icons.person_outline,
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Veuillez entrer votre nom';
                        }
                        return null;
                      },
                    ),

                    const SizedBox(height: AppSizes.space4),

                    // Téléphone
                    CustomTextField(
                      controller: _phoneController,
                      label: 'Téléphone',
                      hint: 'Ex: +225 07 XX XX XX XX',
                      prefixIcon: Icons.phone_outlined,
                      keyboardType: TextInputType.phone,
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Veuillez entrer votre téléphone';
                        }
                        return null;
                      },
                    ),

                    const SizedBox(height: AppSizes.space4),

                    // Adresse
                    CustomTextField(
                      controller: _addressController,
                      label: 'Adresse',
                      hint: 'Rue, avenue, quartier...',
                      prefixIcon: Icons.home_outlined,
                      maxLines: 2,
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Veuillez entrer votre adresse';
                        }
                        return null;
                      },
                    ),

                    const SizedBox(height: AppSizes.space4),

                    // Ville et Code postal
                    Row(
                      children: [
                        Expanded(
                          flex: 2,
                          child: CustomTextField(
                            controller: _cityController,
                            label: 'Ville',
                            hint: 'Ex: Abidjan',
                            prefixIcon: Icons.location_city_outlined,
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return 'Veuillez entrer la ville';
                              }
                              return null;
                            },
                          ),
                        ),
                        const SizedBox(width: AppSizes.space3),
                        Expanded(
                          child: CustomTextField(
                            controller: _postalCodeController,
                            label: 'Code postal',
                            hint: '00225',
                            prefixIcon: Icons.mail_outline,
                            keyboardType: TextInputType.number,
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: AppSizes.space4),

                    // Pays
                    CustomTextField(
                      controller: _countryController,
                      label: 'Pays',
                      hint: 'Côte d\'Ivoire',
                      prefixIcon: Icons.public_outlined,
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Veuillez entrer le pays';
                        }
                        return null;
                      },
                    ),

                    const SizedBox(height: AppSizes.space4),

                    // Adresse par défaut
                    Container(
                      padding: const EdgeInsets.all(AppSizes.space3),
                      decoration: BoxDecoration(
                        color: AppColors.background,
                        borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                        border: Border.all(
                          color: AppColors.border,
                          width: 1,
                        ),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            Icons.star_outline,
                            color: AppColors.warning,
                            size: 20,
                          ),
                          const SizedBox(width: AppSizes.space2),
                          Expanded(
                            child: Text(
                              'Définir comme adresse par défaut',
                              style: AppTextStyles.bodyMedium.copyWith(
                                color: AppColors.textDark,
                              ),
                            ),
                          ),
                          Switch(
                            value: _isDefault,
                            onChanged: (value) {
                              setState(() {
                                _isDefault = value;
                              });
                            },
                            activeColor: AppColors.primary,
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: AppSizes.space6),

                    // Bouton de validation
                    SizedBox(
                      width: double.infinity,
                      child: CustomButton(
                        text: _isLoading 
                            ? 'Sauvegarde...' 
                            : (widget.address != null ? 'Modifier l\'adresse' : 'Ajouter l\'adresse'),
                        onPressed: _isLoading ? null : _saveAddress,
                        isLoading: _isLoading,
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
