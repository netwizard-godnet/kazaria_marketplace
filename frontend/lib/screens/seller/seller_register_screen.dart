import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:file_picker/file_picker.dart';
import '../../services/seller_service.dart';
import '../../services/category_service.dart';
import '../../models/category_model.dart';

class SellerRegisterScreen extends StatefulWidget {
  const SellerRegisterScreen({Key? key}) : super(key: key);

  @override
  State<SellerRegisterScreen> createState() => _SellerRegisterScreenState();
}

class _SellerRegisterScreenState extends State<SellerRegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _sellerService = SellerService();
  final _categoryService = CategoryService();
  final _imagePicker = ImagePicker();

  // Controllers
  final _nameController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  final _addressController = TextEditingController();
  final _cityController = TextEditingController();
  final _facebookController = TextEditingController();
  final _instagramController = TextEditingController();
  final _twitterController = TextEditingController();
  final _websiteController = TextEditingController();

  // State
  bool _isLoading = false;
  bool _acceptTerms = false;
  int _currentStep = 0;
  List<CategoryModel> _categories = [];
  int? _selectedCategoryId;
  int? _selectedSubcategoryId;

  // Files
  File? _logoFile;
  File? _bannerFile;
  File? _dfeDocument;
  File? _commerceRegister;

  @override
  void initState() {
    super.initState();
    _loadCategories();
  }

  Future<void> _loadCategories() async {
    try {
      final response = await _categoryService.getAllCategories();

      if (response['success'] == true) {
        final List<dynamic> categoriesData =
            response['categories'] ?? response['data'] ?? [];
        setState(() {
          _categories = categoriesData
              .map((json) => CategoryModel.fromJson(json))
              .toList();
        });
      } else {
        _showError(
          response['message'] ?? 'Erreur lors du chargement des catégories',
        );
      }
    } catch (e) {
      _showError('Erreur lors du chargement des catégories: $e');
    }
  }

  /// Charger les sous-catégories d'une catégorie
  Future<List<SubcategoryModel>> _loadSubcategories(int categoryId) async {
    try {
      final response = await _categoryService.getSubcategories(categoryId);

      if (response['success'] == true) {
        final List<dynamic> subcategoriesData =
            response['subcategories'] ?? response['data'] ?? [];
        return subcategoriesData
            .map((json) => SubcategoryModel.fromJson(json))
            .toList();
      }
      return [];
    } catch (e) {
      print('❌ Erreur chargement sous-catégories: $e');
      return [];
    }
  }

  Future<void> _pickImage(String type) async {
    try {
      final XFile? image = await _imagePicker.pickImage(
        source: ImageSource.gallery,
        maxWidth: type == 'logo' ? 500 : 1920,
        maxHeight: type == 'logo' ? 500 : 1080,
        imageQuality: 85,
      );

      if (image != null) {
        setState(() {
          if (type == 'logo') {
            _logoFile = File(image.path);
          } else if (type == 'banner') {
            _bannerFile = File(image.path);
          }
        });
      }
    } catch (e) {
      _showError('Erreur lors de la sélection de l\'image');
    }
  }

  Future<void> _pickDocument(String type) async {
    try {
      FilePickerResult? result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
      );

      if (result != null && result.files.single.path != null) {
        setState(() {
          if (type == 'dfe') {
            _dfeDocument = File(result.files.single.path!);
          } else if (type == 'commerce') {
            _commerceRegister = File(result.files.single.path!);
          }
        });
      }
    } catch (e) {
      _showError('Erreur lors de la sélection du document');
    }
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (!_acceptTerms) {
      _showError('Vous devez accepter les conditions générales');
      return;
    }

    if (_dfeDocument == null || _commerceRegister == null) {
      _showError('Les documents légaux sont obligatoires');
      return;
    }

    setState(() => _isLoading = true);

    try {
      final result = await _sellerService.createStore(
        name: _nameController.text,
        description: _descriptionController.text,
        categoryId: _selectedCategoryId!,
        subcategoryId: _selectedSubcategoryId,
        phone: _phoneController.text,
        email: _emailController.text,
        address: _addressController.text.isNotEmpty
            ? _addressController.text
            : null,
        city: _cityController.text.isNotEmpty ? _cityController.text : null,
        logoFile: _logoFile,
        bannerFile: _bannerFile,
        dfeDocument: _dfeDocument!,
        commerceRegister: _commerceRegister!,
        facebook: _facebookController.text.isNotEmpty
            ? _facebookController.text
            : null,
        instagram: _instagramController.text.isNotEmpty
            ? _instagramController.text
            : null,
        twitter: _twitterController.text.isNotEmpty
            ? _twitterController.text
            : null,
        website: _websiteController.text.isNotEmpty
            ? _websiteController.text
            : null,
      );

      if (result['success'] == true) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(result['message'] ?? 'Boutique créée avec succès'),
              backgroundColor: Colors.green,
            ),
          );

          // Rediriger vers le dashboard vendeur
          Navigator.pushReplacementNamed(context, '/seller-dashboard');
        }
      } else {
        _showError(
          result['message'] ?? 'Erreur lors de la création de la boutique',
        );
      }
    } catch (e) {
      _showError('Erreur: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _showError(String message) {
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(message), backgroundColor: Colors.red),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Devenir Vendeur',
          style: TextStyle(color: Colors.white),
        ),
        backgroundColor: const Color(0xFFf04e26),
        foregroundColor: Colors.white,
      ),
      body: Form(
        key: _formKey,
        child: Stepper(
          currentStep: _currentStep,
          onStepContinue: () {
            if (_currentStep < 4) {
              setState(() => _currentStep++);
            } else {
              _submitForm();
            }
          },
          onStepCancel: () {
            if (_currentStep > 0) {
              setState(() => _currentStep--);
            }
          },
          controlsBuilder: (context, details) {
            return Padding(
              padding: const EdgeInsets.only(top: 16),
              child: Row(
                children: [
                  if (_isLoading)
                    const CircularProgressIndicator()
                  else
                    ElevatedButton(
                      onPressed: details.onStepContinue,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFFF6B00),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(
                          horizontal: 32,
                          vertical: 12,
                        ),
                      ),
                      child: Text(
                        _currentStep == 4 ? 'Créer ma boutique' : 'Suivant',
                      ),
                    ),
                  const SizedBox(width: 12),
                  if (_currentStep > 0)
                    TextButton(
                      onPressed: details.onStepCancel,
                      child: const Text('Précédent'),
                    ),
                ],
              ),
            );
          },
          steps: [
            // Étape 1: Informations générales
            Step(
              title: const Text('Informations générales'),
              isActive: _currentStep >= 0,
              state: _currentStep > 0 ? StepState.complete : StepState.indexed,
              content: Column(
                children: [
                  TextFormField(
                    controller: _nameController,
                    decoration: const InputDecoration(
                      labelText: 'Nom de la boutique *',
                      border: OutlineInputBorder(),
                      hintText: 'Ex: Tech Store',
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Le nom est obligatoire';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<int>(
                    decoration: const InputDecoration(
                      labelText: 'Catégorie principale *',
                      border: OutlineInputBorder(),
                    ),
                    value: _selectedCategoryId,
                    items: _categories.map((category) {
                      return DropdownMenuItem(
                        value: category.id,
                        child: Text(category.name),
                      );
                    }).toList(),
                    onChanged: (value) {
                      setState(() {
                        _selectedCategoryId = value;
                        _selectedSubcategoryId = null;
                      });
                    },
                    validator: (value) {
                      if (value == null) {
                        return 'La catégorie est obligatoire';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                  if (_selectedCategoryId != null)
                    FutureBuilder<List<SubcategoryModel>>(
                      future: _loadSubcategories(_selectedCategoryId!),
                      builder: (context, snapshot) {
                        if (snapshot.connectionState ==
                            ConnectionState.waiting) {
                          return const CircularProgressIndicator();
                        }

                        final subcategories = snapshot.data ?? [];

                        return DropdownButtonFormField<int>(
                          decoration: const InputDecoration(
                            labelText: 'Sous-catégorie (optionnel)',
                            border: OutlineInputBorder(),
                          ),
                          value: _selectedSubcategoryId,
                          items: subcategories.map((subcategory) {
                            return DropdownMenuItem(
                              value: subcategory.id,
                              child: Text(subcategory.name),
                            );
                          }).toList(),
                          onChanged: (value) {
                            setState(() => _selectedSubcategoryId = value);
                          },
                        );
                      },
                    ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _descriptionController,
                    decoration: const InputDecoration(
                      labelText: 'Description *',
                      border: OutlineInputBorder(),
                      hintText:
                          'Décrivez votre activité (minimum 50 caractères)',
                    ),
                    maxLines: 4,
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'La description est obligatoire';
                      }
                      if (value.length < 50) {
                        return 'La description doit contenir au moins 50 caractères';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '${_descriptionController.text.length} / 50 caractères minimum',
                    style: TextStyle(
                      color: _descriptionController.text.length >= 50
                          ? Colors.green
                          : Colors.red,
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ),

            // Étape 2: Coordonnées
            Step(
              title: const Text('Coordonnées'),
              isActive: _currentStep >= 1,
              state: _currentStep > 1 ? StepState.complete : StepState.indexed,
              content: Column(
                children: [
                  TextFormField(
                    controller: _phoneController,
                    decoration: const InputDecoration(
                      labelText: 'Téléphone *',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.phone),
                    ),
                    keyboardType: TextInputType.phone,
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Le téléphone est obligatoire';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _emailController,
                    decoration: const InputDecoration(
                      labelText: 'Email *',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.email),
                    ),
                    keyboardType: TextInputType.emailAddress,
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'L\'email est obligatoire';
                      }
                      if (!value.contains('@')) {
                        return 'Email invalide';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _addressController,
                    decoration: const InputDecoration(
                      labelText: 'Adresse physique',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.location_on),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _cityController,
                    decoration: const InputDecoration(
                      labelText: 'Ville',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.location_city),
                    ),
                  ),
                ],
              ),
            ),

            // Étape 3: Visuels
            Step(
              title: const Text('Visuels'),
              isActive: _currentStep >= 2,
              state: _currentStep > 2 ? StepState.complete : StepState.indexed,
              content: Column(
                children: [
                  // Logo
                  Card(
                    child: ListTile(
                      leading: const Icon(
                        Icons.store,
                        color: Color(0xFFFF6B00),
                      ),
                      title: const Text('Logo de la boutique'),
                      subtitle: _logoFile != null
                          ? const Text('✓ Image sélectionnée')
                          : const Text('Cliquez pour ajouter'),
                      trailing: _logoFile != null
                          ? ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: Image.file(
                                _logoFile!,
                                width: 60,
                                height: 60,
                                fit: BoxFit.cover,
                              ),
                            )
                          : const Icon(Icons.add_photo_alternate),
                      onTap: () => _pickImage('logo'),
                    ),
                  ),
                  const SizedBox(height: 16),
                  // Bannière
                  Card(
                    child: ListTile(
                      leading: const Icon(
                        Icons.image,
                        color: Color(0xFFFF6B00),
                      ),
                      title: const Text('Bannière de la boutique'),
                      subtitle: _bannerFile != null
                          ? const Text('✓ Image sélectionnée')
                          : const Text('Cliquez pour ajouter'),
                      trailing: _bannerFile != null
                          ? ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: Image.file(
                                _bannerFile!,
                                width: 100,
                                height: 60,
                                fit: BoxFit.cover,
                              ),
                            )
                          : const Icon(Icons.add_photo_alternate),
                      onTap: () => _pickImage('banner'),
                    ),
                  ),
                ],
              ),
            ),

            // Étape 4: Documents légaux
            Step(
              title: const Text('Documents légaux'),
              isActive: _currentStep >= 3,
              state: _currentStep > 3 ? StepState.complete : StepState.indexed,
              content: Column(
                children: [
                  const Card(
                    color: Color(0xFFE3F2FD),
                    child: Padding(
                      padding: EdgeInsets.all(12),
                      child: Row(
                        children: [
                          Icon(Icons.info, color: Color(0xFF2B5BA5)),
                          SizedBox(width: 12),
                          Expanded(
                            child: Text(
                              'Ces documents sont nécessaires pour la validation de votre boutique',
                              style: TextStyle(fontSize: 13),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  // DFE
                  Card(
                    child: ListTile(
                      leading: Icon(
                        Icons.description,
                        color: _dfeDocument != null
                            ? Colors.green
                            : Colors.grey,
                      ),
                      title: const Text('DFE - Déclaration Fiscale *'),
                      subtitle: _dfeDocument != null
                          ? Text('✓ ${_dfeDocument!.path.split('/').last}')
                          : const Text('PDF, JPG ou PNG (Max 5MB)'),
                      trailing: _dfeDocument != null
                          ? IconButton(
                              icon: const Icon(Icons.delete, color: Colors.red),
                              onPressed: () {
                                setState(() => _dfeDocument = null);
                              },
                            )
                          : const Icon(Icons.upload_file),
                      onTap: () => _pickDocument('dfe'),
                    ),
                  ),
                  const SizedBox(height: 16),
                  // Registre de commerce
                  Card(
                    child: ListTile(
                      leading: Icon(
                        Icons.description,
                        color: _commerceRegister != null
                            ? Colors.green
                            : Colors.grey,
                      ),
                      title: const Text('Registre de Commerce *'),
                      subtitle: _commerceRegister != null
                          ? Text('✓ ${_commerceRegister!.path.split('/').last}')
                          : const Text('PDF, JPG ou PNG (Max 5MB)'),
                      trailing: _commerceRegister != null
                          ? IconButton(
                              icon: const Icon(Icons.delete, color: Colors.red),
                              onPressed: () {
                                setState(() => _commerceRegister = null);
                              },
                            )
                          : const Icon(Icons.upload_file),
                      onTap: () => _pickDocument('commerce'),
                    ),
                  ),
                ],
              ),
            ),

            // Étape 5: Réseaux sociaux et confirmation
            Step(
              title: const Text('Réseaux sociaux'),
              isActive: _currentStep >= 4,
              state: StepState.indexed,
              content: Column(
                children: [
                  TextFormField(
                    controller: _facebookController,
                    decoration: const InputDecoration(
                      labelText: 'Facebook',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.facebook, color: Colors.blue),
                      hintText: 'https://facebook.com/...',
                    ),
                    keyboardType: TextInputType.url,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _instagramController,
                    decoration: const InputDecoration(
                      labelText: 'Instagram',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.camera_alt, color: Colors.pink),
                      hintText: 'https://instagram.com/...',
                    ),
                    keyboardType: TextInputType.url,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _twitterController,
                    decoration: const InputDecoration(
                      labelText: 'Twitter / X',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.textsms, color: Colors.lightBlue),
                      hintText: 'https://twitter.com/...',
                    ),
                    keyboardType: TextInputType.url,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _websiteController,
                    decoration: const InputDecoration(
                      labelText: 'Site Web',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.language),
                      hintText: 'https://...',
                    ),
                    keyboardType: TextInputType.url,
                  ),
                  const SizedBox(height: 24),
                  CheckboxListTile(
                    value: _acceptTerms,
                    onChanged: (value) {
                      setState(() => _acceptTerms = value ?? false);
                    },
                    title: const Text(
                      'J\'accepte les conditions générales de vente et la politique de confidentialité',
                      style: TextStyle(fontSize: 14),
                    ),
                    activeColor: const Color(0xFFFF6B00),
                    controlAffinity: ListTileControlAffinity.leading,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
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
}
