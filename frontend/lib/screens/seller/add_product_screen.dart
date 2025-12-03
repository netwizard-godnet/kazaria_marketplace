import 'dart:io';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:image_picker/image_picker.dart';
import '../../providers/seller_provider.dart';
import '../../services/product_service.dart';
import '../../utils/constants.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/custom_button.dart';

class AddProductScreen extends StatefulWidget {
  const AddProductScreen({super.key});

  @override
  State<AddProductScreen> createState() => _AddProductScreenState();
}

class _AddProductScreenState extends State<AddProductScreen> {
  final _formKey = GlobalKey<FormState>();
  final ImagePicker _imagePicker = ImagePicker();
  final ProductService _productService = ProductService();
  
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
  bool _isLoadingCategories = false;
  List<File> _selectedImages = [];
  List<Map<String, dynamic>> _categories = [];
  int? _selectedCategoryId;

  @override
  void initState() {
    super.initState();
    _loadCategories();
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

  Future<void> _loadCategories() async {
    setState(() {
      _isLoadingCategories = true;
    });

    try {
      final result = await _productService.getCategories();
      if (result['success'] == true) {
        setState(() {
          _categories = List<Map<String, dynamic>>.from(
            (result['categories'] ?? result['data'] ?? [])
                .map((category) => {
                      'id': category['id'],
                      'name': category['name'],
                    })
          );
        });
      }
    } catch (e) {
      print('Error loading categories: $e');
    } finally {
      setState(() {
        _isLoadingCategories = false;
      });
    }
  }

  Future<void> _pickImages() async {
    final List<XFile> images = await _imagePicker.pickMultiImage(
      imageQuality: 85,
    );
    
    if (images.isNotEmpty) {
      setState(() {
        _selectedImages.addAll(images.map((xfile) => File(xfile.path)));
      });
    }
  }

  void _removeImage(int index) {
    setState(() {
      _selectedImages.removeAt(index);
    });
  }

  Future<void> _submitProduct() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final sellerProvider = Provider.of<SellerProvider>(context, listen: false);
      
      // Vérifier si une catégorie est sélectionnée
      if (_selectedCategoryId == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Veuillez sélectionner une catégorie'),
            backgroundColor: AppColors.error,
          ),
        );
        setState(() {
          _isLoading = false;
        });
        return;
      }

      // Étape 1 : Créer le produit
      final result = await sellerProvider.createProduct(
        name: _nameController.text.trim(),
        description: _descriptionController.text.trim(),
        price: double.parse(_priceController.text),
        quantity: int.parse(_stockController.text),
        categoryId: _selectedCategoryId!,
        brand: _brandController.text.trim().isEmpty ? null : _brandController.text.trim(),
        model: _modelController.text.trim().isEmpty ? null : _modelController.text.trim(),
        warranty: _warrantyController.text.trim().isEmpty ? null : _warrantyController.text.trim(),
        tags: _tagsController.text.trim().isEmpty ? null : _tagsController.text.trim(),
        promoPrice: _promoPriceController.text.trim().isEmpty ? null : double.tryParse(_promoPriceController.text),
        discount: _discountController.text.trim().isEmpty ? null : double.tryParse(_discountController.text),
      );

      if (result['success']) {
        // Étape 2 : Upload des images si sélectionnées
        if (_selectedImages.isNotEmpty && result['product'] != null) {
          final productId = result['product']['id'];
          
          final uploadResult = await sellerProvider.uploadProductImages(
            productId,
            _selectedImages,
          );
          
          if (!uploadResult['success']) {
            if (!mounted) return;
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text('Produit créé mais erreur d\'upload des images: ${uploadResult['message']}'),
                backgroundColor: AppColors.warning,
              ),
            );
          }
        }
        
        if (!mounted) return;
        
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Produit ajouté avec succès !'),
            backgroundColor: AppColors.success,
          ),
        );
        
        Navigator.of(context).pop(true); // Retour avec succès
      } else {
        if (!mounted) return;
        
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message'] ?? 'Erreur lors de l\'ajout du produit'),
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
        title: const Text('Ajouter un produit'),
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSizes.paddingLarge),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Informations de base
              _buildSectionTitle('Informations de base'),
              const SizedBox(height: AppSizes.space4),
              
              // Sélecteur de catégorie
              Text(
                'Catégorie *',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: AppColors.textDark,
                ),
              ),
              const SizedBox(height: 8),
              _isLoadingCategories
                  ? Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        border: Border.all(color: AppColors.border),
                        borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                      ),
                      child: const Center(
                        child: CircularProgressIndicator(),
                      ),
                    )
                  : _categories.isEmpty
                      ? Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            border: Border.all(color: AppColors.border),
                            borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                          ),
                          child: const Text(
                            'Aucune catégorie disponible',
                            style: TextStyle(color: AppColors.textLight),
                          ),
                        )
                      : DropdownButtonFormField<int>(
                          value: _selectedCategoryId,
                          decoration: InputDecoration(
                            hintText: 'Sélectionnez une catégorie',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                            ),
                            contentPadding: const EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 12,
                            ),
                          ),
                          items: _categories.map((category) {
                            return DropdownMenuItem<int>(
                              value: category['id'],
                              child: Text(category['name']),
                            );
                          }).toList(),
                          onChanged: (value) {
                            setState(() {
                              _selectedCategoryId = value;
                            });
                          },
                          validator: (value) {
                            if (value == null) {
                              return 'Veuillez sélectionner une catégorie';
                            }
                            return null;
                          },
                        ),
              const SizedBox(height: AppSizes.space3),

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
              
              // Bouton pour ajouter des images
              OutlinedButton.icon(
                onPressed: _pickImages,
                icon: const Icon(Icons.add_photo_alternate),
                label: Text('Ajouter des images (${_selectedImages.length})'),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 48),
                  side: const BorderSide(color: AppColors.primary),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                  ),
                ),
              ),
              const SizedBox(height: AppSizes.space4),
              
              // Affichage des images sélectionnées
              if (_selectedImages.isNotEmpty)
                SizedBox(
                  height: 120,
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    itemCount: _selectedImages.length,
                    itemBuilder: (context, index) {
                      return Container(
                        margin: const EdgeInsets.only(right: AppSizes.space3),
                        child: Stack(
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(AppSizes.radiusMD),
                              child: Image.file(
                                _selectedImages[index],
                                width: 120,
                                height: 120,
                                fit: BoxFit.cover,
                              ),
                            ),
                            Positioned(
                              top: 4,
                              right: 4,
                              child: GestureDetector(
                                onTap: () => _removeImage(index),
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
                          ],
                        ),
                      );
                    },
                  ),
                ),
              
              const SizedBox(height: AppSizes.space6),
              
              // Bouton de soumission
              CustomButton(
                text: 'Ajouter le produit',
                onPressed: _isLoading ? null : _submitProduct,
                isLoading: _isLoading,
              ),
              const SizedBox(height: AppSizes.space4),
            ],
          ),
        ),
      ),
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
