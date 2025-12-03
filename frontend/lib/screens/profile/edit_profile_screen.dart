import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../utils/constants.dart';
import '../../utils/helpers.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';

class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  final _cityController = TextEditingController();
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    if (user != null) {
      _nameController.text = user.fullName;
      _phoneController.text = user.telephone ?? '';
      _addressController.text = user.adresse ?? '';
      _cityController.text = user.ville ?? '';
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _cityController.dispose();
    super.dispose();
  }

  Future<void> _saveProfile() async {
    if (!_formKey.currentState!.validate()) {
      print('🚫 [PROFIL] Validation échouée');
      return;
    }

    setState(() {
      _isLoading = true;
    });

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    
    final profileData = {
      'name': _nameController.text.trim(),
      'telephone': _phoneController.text.trim(),
      'adresse': _addressController.text.trim(),
      'ville': _cityController.text.trim(),
    };
    
    print('📝 [PROFIL] Données envoyées:');
    print('  - Nom: ${profileData['name']}');
    print('  - Téléphone: ${profileData['telephone']}');
    print('  - Adresse: ${profileData['adresse']}');
    print('  - Ville: ${profileData['ville']}');
    
    final response = await authProvider.updateProfile(profileData);
    
    print('📥 [PROFIL] Réponse reçue:');
    print('  - Success: ${response['success']}');
    print('  - Message: ${response['message']}');
    if (response['errors'] != null) {
      print('  - Erreurs: ${response['errors']}');
    }

    setState(() {
      _isLoading = false;
    });

    if (!mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(response['message'] ?? 'Profil mis à jour'),
        backgroundColor: response['success'] ? AppColors.success : AppColors.error,
        duration: const Duration(seconds: 4),
      ),
    );

    if (response['success']) {
      Navigator.pop(context);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Modifier le profil'),
        actions: [
          if (_isLoading)
            const Padding(
              padding: EdgeInsets.all(16),
              child: SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(strokeWidth: 2),
              ),
            )
          else
            TextButton(
              onPressed: _saveProfile,
              child: const Text(
                'Sauvegarder',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
            ),
        ],
      ),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSizes.paddingLarge),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Informations personnelles
              const Text(
                'Informations personnelles',
                style: AppTextStyles.h3,
              ),
              const SizedBox(height: 16),
              
              CustomTextField(
                label: 'Nom complet',
                controller: _nameController,
                validator: (value) => Helpers.validateRequired(value, 'Le nom'),
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
                maxLines: 3,
                validator: (value) {
                  // Adresse optionnelle - aucune validation stricte
                  return null;
                },
              ),
              const SizedBox(height: 16),
              
              CustomTextField(
                label: 'Ville',
                controller: _cityController,
                validator: (value) {
                  // Ville optionnelle - aucune validation stricte
                  return null;
                },
              ),
              
              const SizedBox(height: 32),
              
              // Bouton de sauvegarde
              SizedBox(
                width: double.infinity,
                child: CustomButton(
                  text: 'Sauvegarder les modifications',
                  onPressed: _isLoading ? null : _saveProfile,
                  isLoading: _isLoading,
                ),
              ),
              
              const SizedBox(height: 16),
              
              // Information sur la validation
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.primary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                  border: Border.all(
                    color: AppColors.primary.withOpacity(0.3),
                  ),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.info_outline,
                      color: AppColors.primary,
                      size: 20,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'Les champs marqués d\'un astérisque (*) sont obligatoires. Les autres sont optionnels.',
                        style: AppTextStyles.caption.copyWith(
                          color: AppColors.primary,
                        ),
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