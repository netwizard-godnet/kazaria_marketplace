import 'dart:io';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:image_picker/image_picker.dart';
import '../../providers/seller_provider.dart';
import '../../utils/constants.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/custom_button.dart';
import '../../config/api_config.dart';

class EditProductScreen extends StatefulWidget {
  final int productId;
  
  const EditProductScreen({
    super.key,
    required this.productId,
  });

  @override
  State<EditProductScreen> createState() => _EditProductScreenState();
}

class _EditProductScreenState extends State<EditProductScreen> {
  final _formKey = GlobalKey<FormState>();
  final ImagePicker _imagePicker = ImagePicker();
  
  // Contrôleurs
  final _nameController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _priceController = TextEditingController();
  final _promoPriceController = TextEditingController();
  final _stockController = TextEditingController();
  final _discountController = TextEditingController();
  final _brandController = TextEditingController();
  final _modelController = TextEditingController();
  final _warrantyController = TextEditingController();
  final _tagsController = TextEditingController();

  bool _isLoading = false;
  bool _isInitialized = false;
  String? _currentMainImage;
  File? _newMainImage;
  List<String> _currentImages = [];
  List<File> _newImages = [];

  @override
  void initState() {
    super.initState();
    _loadProductDetails();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _descriptionController.dispose();
    _priceController.dispose();
    _promoPriceController.dispose();
    _stockController.dispose();
    _discountController.dispose();
    _brandController.dispose();
    _modelController.dispose();
    _warrantyController.dispose();
    _tagsController.dispose();
    super.dispose();
  }

  Future<void> _loadProductDetails() async {
    final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
    
    setState(() {
      _isLoading = true;
    });

    try {
      final result = await sellerProvider.getProduct(widget.productId);
      
      if (result['success']) {
        final product = result['product'];
        
        _nameController.text = product['name'] ?? '';
        _descriptionController.text = product['description'] ?? '';
        _priceController.text = (product['price'] ?? 0).toString();
        _stockController.text = (product['stock'] ?? 0).toString();
        _brandController.text = product['brand'] ?? '';
        _modelController.text = product['model'] ?? '';
        _warrantyController.text = product['warranty'] ?? '';
        
        if (product['old_price'] != null && (double.tryParse(product['old_price'].toString()) ?? 0) > 0) {
          _promoPriceController.text = (product['price'] ?? 0).toString();
          _priceController.text = (product['old_price'] ?? 0).toString();
        }
        
        if (product['discount_percentage'] != null && (double.tryParse(product['discount_percentage'].toString()) ?? 0) > 0) {
          _discountController.text = (product['discount_percentage'] ?? 0).toString();
        }
        
        if (product['tags'] != null && product['tags'] is List) {
          _tagsController.text = (product['tags'] as List).join(', ');
        }
        
        // Charger l'image principale
        _currentMainImage = product['image'];
        
        // Charger les images supplémentaires existantes
        if (product['images'] != null && product['images'] is List) {
          _currentImages = List<String>.from(product['images']);
        }
        
        setState(() {
          _isInitialized = true;
        });
      } else {
        if (!mounted) return;
        
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message'] ?? 'Erreur lors du chargement du produit'),
            backgroundColor: AppColors.error,
          ),
        );
        
        Navigator.of(context).pop();
      }
    } catch (e) {
      if (!mounted) return;
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Erreur: $e'),
          backgroundColor: AppColors.error,
        ),
      );
      
      Navigator.of(context).pop();
    }

    setState(() {
      _isLoading = false;
    });
  }

  Future<void> _pickMainImage() async {
    final XFile? image = await _imagePicker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 85,
    );
    
    if (image != null) {
      setState(() {
        _newMainImage = File(image.path);
      });
    }
  }

  Future<void> _pickImages() async {
    final List<XFile> images = await _imagePicker.pickMultiImage(
      imageQuality: 85,
    );
    
    if (images.isNotEmpty) {
      setState(() {
        _newImages.addAll(images.map((xfile) => File(xfile.path)));
      });
    }
  }

  void _removeNewImage(int index) {
    setState(() {
      _newImages.removeAt(index);
    });
  }

  Future<void> _updateProduct() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
      
      // Étape 1 : Mettre à jour les informations du produit
      final result = await sellerProvider.updateProduct(
        productId: widget.productId,
        name: _nameController.text.trim(),
        description: _descriptionController.text.trim(),
        price: double.parse(_priceController.text),
        quantity: int.parse(_stockController.text),
        brand: _brandController.text.trim().isEmpty ? null : _brandController.text.trim(),
        model: _modelController.text.trim().isEmpty ? null : _modelController.text.trim(),
        warranty: _warrantyController.text.trim().isEmpty ? null : _warrantyController.text.trim(),
        tags: _tagsController.text.trim().isEmpty ? null : _tagsController.text.trim(),
        promoPrice: _promoPriceController.text.trim().isEmpty ? null : double.tryParse(_promoPriceController.text),
        discount: _discountController.text.trim().isEmpty ? null : double.tryParse(_discountController.text),
      );

      if (result['success']) {
        // Étape 2 : Upload de l'image principale si sélectionnée
        if (_newMainImage != null) {
          final mainImageResult = await sellerProvider.updateProductMainImage(
            widget.productId,
            _newMainImage!,
          );
          
          if (!mainImageResult['success']) {
            if (!mounted) return;
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text('Produit mis à jour mais erreur d\'upload de l\'image principale: ${mainImageResult['message']}'),
                backgroundColor: AppColors.warning,
              ),
            );
          }
        }
        
        // Étape 3 : Upload des images supplémentaires si sélectionnées
        if (_newImages.isNotEmpty) {
          final uploadResult = await sellerProvider.uploadProductImages(
            widget.productId,
            _newImages,
          );
          
          if (!uploadResult['success']) {
            if (!mounted) return;
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text('Produit mis à jour mais erreur d\'upload des images supplémentaires: ${uploadResult['message']}'),
                backgroundColor: AppColors.warning,
              ),
            );
          }
        }
        
        if (!mounted) return;
        
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Produit mis à jour avec succès !'),
            backgroundColor: AppColors.success,
          ),
        );
        
        Navigator.of(context).pop(true); // Retour avec succès
      } else {
        if (!mounted) return;
        
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message'] ?? 'Erreur lors de la mise à jour du produit'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Erreur: $e'),
          backgroundColor: AppColors.error,
        ),
      );
    }

    setState(() {
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Modifier le produit'),
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.white,
      ),
      body: _isLoading && !_isInitialized
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(AppSizes.paddingLarge),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Informations de base
                    _buildSectionTitle('Informations de base'),
                    const SizedBox(height: AppSizes.space4),
                    
                    CustomTextField(
                      controller: _nameController,
                      label: 'Nom du produit *',
                      hint: 'Entrez le nom du produit',
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Le nom du produit est requis';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: AppSizes.space3),
                    
                    CustomTextField(
                      controller: _descriptionController,
                      label: 'Description *',
                      hint: 'Décrivez votre produit (minimum 50 caractères)',
                      maxLines: 4,
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'La description est requise';
                        }
                        if (value.trim().length < 50) {
                          return 'La description doit contenir au moins 50 caractères';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: AppSizes.space3),
                    
                    // Prix et stock
                    _buildSectionTitle('Prix et stock'),
                    const SizedBox(height: AppSizes.space4),
                    
                    Row(
                      children: [
                        Expanded(
                          child: CustomTextField(
                            controller: _priceController,
                            label: 'Prix normal *',
                            hint: '0.00',
                            keyboardType: TextInputType.number,
                            validator: (value) {
                              if (value == null || value.trim().isEmpty) {
                                return 'Le prix est requis';
                              }
                              if (double.tryParse(value) == null) {
                                return 'Prix invalide';
                              }
                              if (double.parse(value) <= 0) {
                                return 'Le prix doit être positif';
                              }
                              return null;
                            },
                          ),
                        ),
                        const SizedBox(width: AppSizes.space3),
                        Expanded(
                          child: CustomTextField(
                            controller: _promoPriceController,
                            label: 'Prix promo',
                            hint: '0.00',
                            keyboardType: TextInputType.number,
                            validator: (value) {
                              if (value != null && value.trim().isNotEmpty) {
                                if (double.tryParse(value) == null) {
                                  return 'Prix invalide';
                                }
                                if (double.parse(value) <= 0) {
                                  return 'Le prix doit être positif';
                                }
                              }
                              return null;
                            },
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: AppSizes.space3),
                    
                    Row(
                      children: [
                        Expanded(
                          child: CustomTextField(
                            controller: _stockController,
                            label: 'Stock *',
                            hint: '0',
                            keyboardType: TextInputType.number,
                            validator: (value) {
                              if (value == null || value.trim().isEmpty) {
                                return 'Le stock est requis';
                              }
                              if (int.tryParse(value) == null) {
                                return 'Stock invalide';
                              }
                              if (int.parse(value) < 0) {
                                return 'Le stock ne peut pas être négatif';
                              }
                              return null;
                            },
                          ),
                        ),
                        const SizedBox(width: AppSizes.space3),
                        Expanded(
                          child: CustomTextField(
                            controller: _discountController,
                            label: 'Remise (%)',
                            hint: '0',
                            keyboardType: TextInputType.number,
                            validator: (value) {
                              if (value != null && value.trim().isNotEmpty) {
                                if (double.tryParse(value) == null) {
                                  return 'Pourcentage invalide';
                                }
                                if (double.parse(value) < 0 || double.parse(value) > 100) {
                                  return 'La remise doit être entre 0 et 100%';
                                }
                              }
                              return null;
                            },
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: AppSizes.space3),
                    
                    // Détails du produit
                    _buildSectionTitle('Détails du produit'),
                    const SizedBox(height: AppSizes.space4),
                    
                    CustomTextField(
                      controller: _brandController,
                      label: 'Marque',
                      hint: 'Entrez la marque du produit',
                    ),
                    const SizedBox(height: AppSizes.space3),
                    
                    CustomTextField(
                      controller: _modelController,
                      label: 'Modèle',
                      hint: 'Entrez le modèle du produit',
                    ),
                    const SizedBox(height: AppSizes.space3),
                    
                    CustomTextField(
                      controller: _warrantyController,
                      label: 'Garantie',
                      hint: 'Ex: 1 an, 2 ans, etc.',
                    ),
                    const SizedBox(height: AppSizes.space3),
                    
                    CustomTextField(
                      controller: _tagsController,
                      label: 'Tags',
                      hint: 'Séparés par des virgules',
                    ),
                    const SizedBox(height: AppSizes.space6),
                    
                    // Section images
                    _buildSectionTitle('Images du produit'),
                    const SizedBox(height: AppSizes.space4),
                    
                    // Image principale
                    _buildMainImageSection(),
                    const SizedBox(height: AppSizes.space6),
                    
                    // Images existantes
                    if (_currentImages.isNotEmpty) ...[
                      Text(
                        'Images actuelles',
                        style: AppTextStyles.bodyMedium.copyWith(
                          color: AppColors.textMuted,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: AppSizes.space3),
                      SizedBox(
                        height: 100,
                        child: ListView.builder(
                          scrollDirection: Axis.horizontal,
                          itemCount: _currentImages.length,
                          itemBuilder: (context, index) {
                            return Container(
                              margin: const EdgeInsets.only(right: AppSizes.space3),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(AppSizes.radiusMD),
                                child: Image.network(
                                  '${ApiConfig.imageBaseUrl}/${_currentImages[index]}',
                                  width: 100,
                                  height: 100,
                                  fit: BoxFit.cover,
                                  errorBuilder: (context, error, stackTrace) {
                                    return Container(
                                      width: 100,
                                      height: 100,
                                      color: AppColors.grey200,
                                      child: const Icon(Icons.broken_image, color: AppColors.grey400),
                                    );
                                  },
                                ),
                              ),
                            );
                          },
                        ),
                      ),
                      const SizedBox(height: AppSizes.space4),
                    ],
                    
                    // Bouton pour ajouter de nouvelles images
                    OutlinedButton.icon(
                      onPressed: _pickImages,
                      icon: const Icon(Icons.add_photo_alternate),
                      label: Text('Ajouter des images (${_newImages.length} nouvelles)'),
                      style: OutlinedButton.styleFrom(
                        minimumSize: const Size(double.infinity, 48),
                        side: const BorderSide(color: AppColors.primary),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                        ),
                      ),
                    ),
                    const SizedBox(height: AppSizes.space4),
                    
                    // Affichage des nouvelles images sélectionnées
                    if (_newImages.isNotEmpty)
                      SizedBox(
                        height: 120,
                        child: ListView.builder(
                          scrollDirection: Axis.horizontal,
                          itemCount: _newImages.length,
                          itemBuilder: (context, index) {
                            return Container(
                              margin: const EdgeInsets.only(right: AppSizes.space3),
                              child: Stack(
                                children: [
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(AppSizes.radiusMD),
                                    child: Image.file(
                                      _newImages[index],
                                      width: 120,
                                      height: 120,
                                      fit: BoxFit.cover,
                                    ),
                                  ),
                                  Positioned(
                                    top: 4,
                                    right: 4,
                                    child: GestureDetector(
                                      onTap: () => _removeNewImage(index),
                                      child: Container(
                                        padding: const EdgeInsets.all(4),
                                        decoration: const BoxDecoration(
                                          color: AppColors.error,
                                          shape: BoxShape.circle,
                                        ),
                                        child: const Icon(
                                          Icons.close,
                                          color: AppColors.white,
                                          size: 16,
                                        ),
                                      ),
                                    ),
                                  ),
                                  // Badge "NOUVEAU"
                                  Positioned(
                                    bottom: 4,
                                    left: 4,
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: AppSizes.space2,
                                        vertical: 2,
                                      ),
                                      decoration: BoxDecoration(
                                        color: AppColors.success,
                                        borderRadius: BorderRadius.circular(AppSizes.radiusSM),
                                      ),
                                      child: Text(
                                        'NOUVEAU',
                                        style: AppTextStyles.caption.copyWith(
                                          color: AppColors.white,
                                          fontSize: 10,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
                      ),
                    
                    const SizedBox(height: AppSizes.space6),
                    
                    // Bouton de soumission
                    CustomButton(
                      text: 'Mettre à jour le produit',
                      onPressed: _isLoading ? null : _updateProduct,
                      isLoading: _isLoading,
                    ),
                    const SizedBox(height: AppSizes.space4),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildMainImageSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Image principale',
          style: AppTextStyles.bodyMedium.copyWith(
            color: AppColors.textMuted,
            fontWeight: FontWeight.w500,
          ),
        ),
        const SizedBox(height: AppSizes.space3),
        
        // Image principale actuelle
        if (_currentMainImage != null && _currentMainImage!.isNotEmpty)
          Container(
            margin: const EdgeInsets.only(bottom: AppSizes.space3),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(AppSizes.radiusMD),
              child: Image.network(
                '${ApiConfig.imageBaseUrl}/${_currentMainImage}',
                width: 120,
                height: 120,
                fit: BoxFit.cover,
                errorBuilder: (context, error, stackTrace) {
                  return Container(
                    width: 120,
                    height: 120,
                    color: AppColors.grey200,
                    child: const Icon(Icons.broken_image, color: AppColors.grey400),
                  );
                },
              ),
            ),
          ),
        
        // Nouvelle image principale sélectionnée
        if (_newMainImage != null)
          Container(
            margin: const EdgeInsets.only(bottom: AppSizes.space3),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(AppSizes.radiusMD),
              child: Image.file(
                _newMainImage!,
                width: 120,
                height: 120,
                fit: BoxFit.cover,
              ),
            ),
          ),
        
        // Bouton pour sélectionner une nouvelle image principale
        CustomButton(
          text: 'Changer l\'image principale',
          onPressed: _pickMainImage,
          isOutlined: true,
          icon: Icons.camera_alt,
        ),
      ],
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: AppTextStyles.h4.copyWith(
        color: AppColors.primary,
        fontWeight: FontWeight.bold,
      ),
    );
  }
}
