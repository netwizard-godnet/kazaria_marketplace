import 'dart:io';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:image_picker/image_picker.dart';
import '../../providers/seller_provider.dart';
import '../../utils/constants.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/custom_button.dart';
import '../../config/api_config.dart';

class SellerStoreSettingsCompleteScreen extends StatefulWidget {
  const SellerStoreSettingsCompleteScreen({super.key});

  @override
  State<SellerStoreSettingsCompleteScreen> createState() => _SellerStoreSettingsCompleteScreenState();
}

class _SellerStoreSettingsCompleteScreenState extends State<SellerStoreSettingsCompleteScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final ImagePicker _imagePicker = ImagePicker();
  
  // Formulaire - Informations générales
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  final _addressController = TextEditingController();
  final _cityController = TextEditingController();
  
  // Formulaire - Réseaux sociaux
  final _facebookController = TextEditingController();
  final _instagramController = TextEditingController();
  final _twitterController = TextEditingController();
  final _websiteController = TextEditingController();
  
  bool _isLoading = true;
  bool _isUploading = false;
  Map<String, dynamic>? _storeInfo;
  
  File? _logoFile;
  File? _bannerFile;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    // Éviter setState pendant build
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadStoreInfo();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    _nameController.dispose();
    _descriptionController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _addressController.dispose();
    _cityController.dispose();
    _facebookController.dispose();
    _instagramController.dispose();
    _twitterController.dispose();
    _websiteController.dispose();
    super.dispose();
  }

  Future<void> _loadStoreInfo() async {
    print('🔄 [STORE_SETTINGS] Début chargement des infos boutique');
    setState(() => _isLoading = true);
    
    final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
    
    // D'abord charger les données dans le provider
    print('🔄 [STORE_SETTINGS] Appel sellerProvider.loadStoreInfo()');
    await sellerProvider.loadStoreInfo();
    
    // Puis récupérer les données depuis le provider
    final store = sellerProvider.storeInfo;
    
    print('🔍 [STORE_SETTINGS] Store info du provider: $store');
    print('🔍 [STORE_SETTINGS] Store info type: ${store.runtimeType}');
    print('🔍 [STORE_SETTINGS] Store info est null: ${store == null}');
    
    if (store != null) {
      print('🔍 [STORE_SETTINGS] Store name: ${store['name']}');
      print('🔍 [STORE_SETTINGS] Store email: ${store['email']}');
      print('🔍 [STORE_SETTINGS] Store phone: ${store['phone']}');
      
      _nameController.text = store['name'] ?? '';
      _descriptionController.text = store['description'] ?? '';
      _phoneController.text = store['phone'] ?? '';
      _emailController.text = store['email'] ?? '';
      _addressController.text = store['address'] ?? '';
      _cityController.text = store['city'] ?? '';
      
      // Liens sociaux
      final socialLinks = store['social_links'];
      if (socialLinks != null && socialLinks is Map) {
        _facebookController.text = socialLinks['facebook'] ?? '';
        _instagramController.text = socialLinks['instagram'] ?? '';
        _twitterController.text = socialLinks['twitter'] ?? '';
        _websiteController.text = socialLinks['website'] ?? '';
      }
      
      setState(() {
        _storeInfo = store;
      });
      
      print('✅ [STORE_SETTINGS] Champs remplis avec succès');
      print('✅ [STORE_SETTINGS] Nom controller: ${_nameController.text}');
      print('✅ [STORE_SETTINGS] Email controller: ${_emailController.text}');
    } else {
      print('❌ [STORE_SETTINGS] Aucune donnée de boutique trouvée');
      print('❌ [STORE_SETTINGS] Erreur du provider: ${sellerProvider.error}');
    }
    
    setState(() => _isLoading = false);
  }

  Future<void> _updateStoreInfo() async {
    if (!_formKey.currentState!.validate()) return;
    
    setState(() => _isUploading = true);
    
    final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
    
    final result = await sellerProvider.updateStoreInfo(
      name: _nameController.text.trim(),
      description: _descriptionController.text.trim(),
      phone: _phoneController.text.trim(),
      email: _emailController.text.trim(),
      address: _addressController.text.trim(),
      city: _cityController.text.trim(),
      facebook: _facebookController.text.trim(),
      instagram: _instagramController.text.trim(),
      twitter: _twitterController.text.trim(),
      website: _websiteController.text.trim(),
    );
    
    setState(() => _isUploading = false);
    
    if (!mounted) return;
    
    if (result['success']) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Informations mises à jour avec succès'),
          backgroundColor: AppColors.success,
        ),
      );
      // Recharger les données depuis le provider
      await _loadStoreInfo();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Erreur lors de la mise à jour'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  Future<void> _pickAndUploadLogo() async {
    final XFile? image = await _imagePicker.pickImage(
      source: ImageSource.gallery,
      maxWidth: 500,
      maxHeight: 500,
      imageQuality: 85,
    );
    
    if (image == null) return;
    
    setState(() {
      _logoFile = File(image.path);
      _isUploading = true;
    });
    
    final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
    final result = await sellerProvider.uploadLogo(_logoFile!);
    
    setState(() => _isUploading = false);
    
    if (!mounted) return;
    
    if (result['success']) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Logo mis à jour avec succès'),
          backgroundColor: AppColors.success,
        ),
      );
      await _loadStoreInfo();
      setState(() => _logoFile = null);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Erreur lors de l\'upload'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  Future<void> _pickAndUploadBanner() async {
    final XFile? image = await _imagePicker.pickImage(
      source: ImageSource.gallery,
      maxWidth: 1200,
      maxHeight: 400,
      imageQuality: 85,
    );
    
    if (image == null) return;
    
    setState(() {
      _bannerFile = File(image.path);
      _isUploading = true;
    });
    
    final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
    final result = await sellerProvider.uploadBanner(_bannerFile!);
    
    setState(() => _isUploading = false);
    
    if (!mounted) return;
    
    if (result['success']) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Bannière mise à jour avec succès'),
          backgroundColor: AppColors.success,
        ),
      );
      await _loadStoreInfo();
      setState(() => _bannerFile = null);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Erreur lors de l\'upload'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  Future<void> _pickAndUploadDocument(String type) async {
    // Afficher un dialogue pour choisir entre caméra et galerie
    final source = await showDialog<ImageSource>(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Sélectionner un document'),
          content: const Text('Choisissez une image du document (JPG ou PNG)'),
          actions: [
            TextButton.icon(
              onPressed: () => Navigator.pop(context, ImageSource.camera),
              icon: const Icon(Icons.camera_alt),
              label: const Text('Caméra'),
            ),
            TextButton.icon(
              onPressed: () => Navigator.pop(context, ImageSource.gallery),
              icon: const Icon(Icons.photo_library),
              label: const Text('Galerie'),
            ),
          ],
        );
      },
    );
    
    if (source == null) return;
    
    final XFile? image = await _imagePicker.pickImage(
      source: source,
      imageQuality: 90,
    );
    
    if (image == null) return;
    
    final file = File(image.path);
    
    setState(() => _isUploading = true);
    
    final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
    final uploadResult = await sellerProvider.uploadDocument(file, type);
    
    setState(() => _isUploading = false);
    
    if (!mounted) return;
    
    if (uploadResult['success']) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Document ${type == 'dfe' ? 'DFE' : 'Registre de commerce'} mis à jour avec succès'),
          backgroundColor: AppColors.success,
        ),
      );
      await _loadStoreInfo();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(uploadResult['message'] ?? 'Erreur lors de l\'upload'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Paramètres de la boutique'),
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.white,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: AppColors.white,
          labelColor: AppColors.white,
          unselectedLabelColor: AppColors.white.withOpacity(0.7),
          tabs: const [
            Tab(icon: Icon(Icons.info_outline), text: 'Infos'),
            Tab(icon: Icon(Icons.image_outlined), text: 'Images'),
            Tab(icon: Icon(Icons.description_outlined), text: 'Documents'),
            Tab(icon: Icon(Icons.share_outlined), text: 'Réseaux'),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : TabBarView(
              controller: _tabController,
              children: [
                _buildInfoTab(),
                _buildImagesTab(),
                _buildDocumentsTab(),
                _buildSocialTab(),
              ],
            ),
    );
  }

  Widget _buildInfoTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSizes.paddingLarge),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Informations générales',
              style: AppTextStyles.h3.copyWith(
                color: AppColors.primary,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: AppSizes.space4),
            
            CustomTextField(
              controller: _nameController,
              label: 'Nom de la boutique *',
              hint: 'Entrez le nom de votre boutique',
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'Le nom est requis';
                }
                return null;
              },
            ),
            const SizedBox(height: AppSizes.space3),
            
            CustomTextField(
              controller: _descriptionController,
              label: 'Description *',
              hint: 'Décrivez votre boutique',
              maxLines: 4,
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'La description est requise';
                }
                if (value.trim().length < 50) {
                  return 'Minimum 50 caractères requis';
                }
                return null;
              },
            ),
            const SizedBox(height: AppSizes.space3),
            
            CustomTextField(
              controller: _phoneController,
              label: 'Téléphone *',
              hint: '+225 XX XX XX XX XX',
              keyboardType: TextInputType.phone,
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'Le téléphone est requis';
                }
                return null;
              },
            ),
            const SizedBox(height: AppSizes.space3),
            
            CustomTextField(
              controller: _emailController,
              label: 'Email *',
              hint: 'contact@maboutique.com',
              keyboardType: TextInputType.emailAddress,
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'L\'email est requis';
                }
                if (!value.contains('@')) {
                  return 'Email invalide';
                }
                return null;
              },
            ),
            const SizedBox(height: AppSizes.space3),
            
            CustomTextField(
              controller: _addressController,
              label: 'Adresse',
              hint: 'Adresse complète',
              maxLines: 2,
            ),
            const SizedBox(height: AppSizes.space3),
            
            CustomTextField(
              controller: _cityController,
              label: 'Ville',
              hint: 'Ex: Abidjan',
            ),
            const SizedBox(height: AppSizes.space6),
            
            CustomButton(
              text: 'Sauvegarder les informations',
              onPressed: _isUploading ? null : _updateStoreInfo,
              isLoading: _isUploading,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildImagesTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSizes.paddingLarge),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Logo
          _buildImageSection(
            title: 'Logo de la boutique',
            subtitle: 'Format: JPG, PNG (max 2MB)',
            currentImage: _storeInfo?['logo'],
            selectedFile: _logoFile,
            onPickImage: _pickAndUploadLogo,
            aspectRatio: 1.0,
          ),
          
          const SizedBox(height: AppSizes.space6),
          const Divider(),
          const SizedBox(height: AppSizes.space6),
          
          // Bannière
          _buildImageSection(
            title: 'Bannière de la boutique',
            subtitle: 'Format: JPG, PNG (max 5MB)\nDimension recommandée: 1200x400',
            currentImage: _storeInfo?['banner'],
            selectedFile: _bannerFile,
            onPickImage: _pickAndUploadBanner,
            aspectRatio: 3.0,
          ),
        ],
      ),
    );
  }

  Widget _buildImageSection({
    required String title,
    required String subtitle,
    String? currentImage,
    File? selectedFile,
    required VoidCallback onPickImage,
    required double aspectRatio,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: AppTextStyles.h4.copyWith(
            color: AppColors.primary,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: AppSizes.space2),
        Text(
          subtitle,
          style: AppTextStyles.caption.copyWith(
            color: AppColors.textMuted,
          ),
        ),
        const SizedBox(height: AppSizes.space4),
        
        Container(
          height: 200,
          width: double.infinity,
          decoration: BoxDecoration(
            color: AppColors.grey100,
            borderRadius: BorderRadius.circular(AppSizes.radiusLG),
            border: Border.all(color: AppColors.grey300),
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(AppSizes.radiusLG),
            child: selectedFile != null
                ? Image.file(selectedFile, fit: BoxFit.cover)
                : currentImage != null && currentImage.isNotEmpty
                    ? Image.network(
                        '${ApiConfig.imageBaseUrl}/$currentImage',
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) {
                          return const Center(
                            child: Icon(Icons.broken_image, size: 64, color: AppColors.grey400),
                          );
                        },
                      )
                    : Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.image_outlined,
                              size: 64,
                              color: AppColors.grey400,
                            ),
                            const SizedBox(height: AppSizes.space2),
                            Text(
                              'Aucune image',
                              style: AppTextStyles.bodyMedium.copyWith(
                                color: AppColors.textMuted,
                              ),
                            ),
                          ],
                        ),
                      ),
          ),
        ),
        
        const SizedBox(height: AppSizes.space4),
        
        SizedBox(
          width: double.infinity,
          child: ElevatedButton.icon(
            onPressed: _isUploading ? null : onPickImage,
            icon: _isUploading
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.white),
                  )
                : const Icon(Icons.upload),
            label: Text(_isUploading ? 'Upload en cours...' : 'Choisir et uploader'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: AppColors.white,
              padding: const EdgeInsets.symmetric(vertical: AppSizes.paddingMedium),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(AppSizes.radiusLG),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildDocumentsTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSizes.paddingLarge),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Documents administratifs',
            style: AppTextStyles.h3.copyWith(
              color: AppColors.primary,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: AppSizes.space2),
          Text(
            'Prenez une photo ou sélectionnez une image du document\nFormats: JPG, PNG (max 5MB)',
            style: AppTextStyles.caption.copyWith(
              color: AppColors.textMuted,
            ),
          ),
          const SizedBox(height: AppSizes.space6),
          
          // Document DFE
          _buildDocumentCard(
            title: 'Document DFE',
            description: 'Déclaration Fiscale d\'Établissement',
            currentDocument: _storeInfo?['dfe_document'],
            onUpload: () => _pickAndUploadDocument('dfe'),
          ),
          
          const SizedBox(height: AppSizes.space4),
          
          // Registre de commerce
          _buildDocumentCard(
            title: 'Registre de commerce',
            description: 'Certificat d\'immatriculation au registre du commerce',
            currentDocument: _storeInfo?['commerce_register'],
            onUpload: () => _pickAndUploadDocument('commerce_register'),
          ),
        ],
      ),
    );
  }

  Widget _buildDocumentCard({
    required String title,
    required String description,
    String? currentDocument,
    required VoidCallback onUpload,
  }) {
    final hasDocument = currentDocument != null && currentDocument.isNotEmpty;
    
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppSizes.radiusLG),
      ),
      child: Padding(
        padding: const EdgeInsets.all(AppSizes.paddingMedium),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(AppSizes.space3),
                  decoration: BoxDecoration(
                    color: hasDocument ? AppColors.success.withOpacity(0.1) : AppColors.grey100,
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    hasDocument ? Icons.check_circle : Icons.description_outlined,
                    color: hasDocument ? AppColors.success : AppColors.grey400,
                    size: 32,
                  ),
                ),
                const SizedBox(width: AppSizes.space3),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: AppTextStyles.bodyLarge.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Text(
                        description,
                        style: AppTextStyles.caption.copyWith(
                          color: AppColors.textMuted,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            
            if (hasDocument) ...[
              const SizedBox(height: AppSizes.space3),
              Container(
                padding: const EdgeInsets.all(AppSizes.space2),
                decoration: BoxDecoration(
                  color: AppColors.success.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(AppSizes.radiusMD),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.check_circle, color: AppColors.success, size: 16),
                    const SizedBox(width: AppSizes.space2),
                    Text(
                      'Document uploadé',
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.success,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ],
            
            const SizedBox(height: AppSizes.space4),
            
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _isUploading ? null : onUpload,
                icon: _isUploading
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : Icon(hasDocument ? Icons.refresh : Icons.upload),
                label: Text(hasDocument ? 'Remplacer le document' : 'Uploader le document'),
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: AppSizes.paddingMedium),
                  side: BorderSide(color: AppColors.primary),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSocialTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSizes.paddingLarge),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Réseaux sociaux',
            style: AppTextStyles.h3.copyWith(
              color: AppColors.primary,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: AppSizes.space2),
          Text(
            'Ajoutez vos liens de réseaux sociaux',
            style: AppTextStyles.caption.copyWith(
              color: AppColors.textMuted,
            ),
          ),
          const SizedBox(height: AppSizes.space6),
          
          CustomTextField(
            controller: _facebookController,
            label: 'Facebook',
            hint: 'https://facebook.com/maboutique',
            keyboardType: TextInputType.url,
            prefixIcon: Icons.facebook,
          ),
          const SizedBox(height: AppSizes.space3),
          
          CustomTextField(
            controller: _instagramController,
            label: 'Instagram',
            hint: 'https://instagram.com/maboutique',
            keyboardType: TextInputType.url,
            prefixIcon: Icons.camera_alt,
          ),
          const SizedBox(height: AppSizes.space3),
          
          CustomTextField(
            controller: _twitterController,
            label: 'Twitter (X)',
            hint: 'https://twitter.com/maboutique',
            keyboardType: TextInputType.url,
            prefixIcon: Icons.flutter_dash,
          ),
          const SizedBox(height: AppSizes.space3),
          
          CustomTextField(
            controller: _websiteController,
            label: 'Site web',
            hint: 'https://maboutique.com',
            keyboardType: TextInputType.url,
            prefixIcon: Icons.language,
          ),
          const SizedBox(height: AppSizes.space6),
          
          CustomButton(
            text: 'Sauvegarder les liens',
            onPressed: _isUploading ? null : _updateStoreInfo,
            isLoading: _isUploading,
          ),
        ],
      ),
    );
  }
}

