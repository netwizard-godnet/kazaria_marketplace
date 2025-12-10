import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../utils/constants.dart';
import '../../models/brand_model.dart';
import '../../services/brand_service.dart';
import '../../config/api_config.dart';
import '../../screens/products/brand_products_screen.dart';

/// Section Marques en Collaboration - Grid de logos
class BrandsSection extends StatefulWidget {
  const BrandsSection({super.key});

  @override
  State<BrandsSection> createState() => _BrandsSectionState();
}

class _BrandsSectionState extends State<BrandsSection> {
  final BrandService _brandService = BrandService();
  List<BrandModel> _brands = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadBrands();
  }

  Future<void> _loadBrands() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final response = await _brandService.getBrands();
      
      if (mounted && response['success'] == true) {
        final brandsData = response['data'] as List?;
        if (brandsData != null) {
          setState(() {
            _brands = brandsData
                .map((b) => BrandModel.fromJson(b as Map<String, dynamic>))
                .toList();
            _isLoading = false;
          });
        } else {
          setState(() {
            _brands = [];
            _isLoading = false;
          });
        }
      } else {
        if (mounted) {
          setState(() {
            _brands = [];
            _isLoading = false;
          });
        }
      }
    } catch (e) {
      print('❌ [BRANDS_SECTION] Erreur: $e');
      if (mounted) {
        setState(() {
          _brands = [];
          _isLoading = false;
    });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    // Ne rien afficher si aucune marque
    if (!_isLoading && _brands.isEmpty) {
          return const SizedBox.shrink();
        }

        return Container(
          margin: const EdgeInsets.only(bottom: AppSizes.space4),
          decoration: BoxDecoration(
            color: AppColors.white,
            borderRadius: BorderRadius.circular(0),
          ),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 16),
            child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // En-tête
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(
                    Icons.handshake,
                        color: AppColors.primary,
                        size: 20,
                      ),
                    ),
                    const SizedBox(width: 10),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                        'Marques en Collaboration',
                            style: AppTextStyles.sectionTitle,
                          ),
                          Text(
                        'Découvrez nos partenaires',
                            style: AppTextStyles.sectionSubtitle,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              
              const SizedBox(height: 16),
              
          // Grid de marques ou chargement
          _isLoading
                  ? const Center(
                      child: Padding(
                        padding: EdgeInsets.all(20),
                        child: CircularProgressIndicator(),
                      ),
                    )
                  : Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: GridView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 4,
                          crossAxisSpacing: 12,
                          mainAxisSpacing: 12,
                          childAspectRatio: 1,
                        ),
                    itemCount: _brands.length > 8 ? 8 : _brands.length,
                        itemBuilder: (context, index) {
                      final brand = _brands[index];
                      return _BrandCard(brand: brand);
                        },
                      ),
                    ),
            ],
          ),
          ),
        );
  }
}

/// Card individuelle de marque
class _BrandCard extends StatelessWidget {
  final BrandModel brand;

  const _BrandCard({
    required this.brand,
  });

  /// Obtenir l'URL complète de l'image de la marque
  String? _getImageUrl() {
    // Priorité 1: Utiliser l'image (URL complète depuis le backend)
    if (brand.image != null && brand.image!.isNotEmpty) {
      // Utiliser la fonction utilitaire qui gère localhost automatiquement
      return ImageUrlHelper.fixImageUrl(brand.image!);
    }
    
    // Priorité 2: Utiliser image_path pour construire l'URL
    if (brand.imagePath != null && brand.imagePath!.isNotEmpty) {
      if (brand.imagePath!.startsWith('http://') || brand.imagePath!.startsWith('https://')) {
        return brand.imagePath;
      }
      // Corriger les URLs mal formées
      if (brand.imagePath!.startsWith('http:') && !brand.imagePath!.startsWith('http://')) {
        return brand.imagePath!.replaceFirst('http:', 'http://');
      }
      // Construire l'URL complète
      if (brand.imagePath!.startsWith('storage/') || brand.imagePath!.startsWith('images/')) {
        return '${ApiConfig.imageBaseUrl}/${brand.imagePath}';
      }
      return '${ApiConfig.imageBaseUrl}/storage/${brand.imagePath}';
    }
    
    return null;
  }

  @override
  Widget build(BuildContext context) {
    final imageUrl = _getImageUrl();
    
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () {
          print('🖱️ [BRANDS] Clic détecté sur marque: ${brand.name}');
          _handleBrandTap(context, brand);
        },
        borderRadius: BorderRadius.circular(12),
        child: Container(
          decoration: BoxDecoration(
            color: AppColors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: AppColors.grey200,
              width: 1,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Logo de la marque
              imageUrl != null
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: CachedNetworkImage(
                      imageUrl: imageUrl,
                      width: 50,
                      height: 50,
                      fit: BoxFit.contain,
                      placeholder: (context, url) => Container(
                        width: 50,
                        height: 50,
                        decoration: BoxDecoration(
                          color: AppColors.grey100,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Center(
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(AppColors.primary),
                          ),
                        ),
                      ),
                      errorWidget: (context, url, error) {
                        print('❌ [BRANDS] Erreur chargement logo ${brand.name}: $imageUrl');
                        return _buildFallbackIcon();
                      },
                    ),
                  )
                : _buildFallbackIcon(),
              
              const SizedBox(height: 8),
              
              // Nom de la marque
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 4),
                child: Text(
                  brand.name,
                  style: const TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textDark,
                  ),
                  textAlign: TextAlign.center,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  /// Widget de fallback si pas de logo
  Widget _buildFallbackIcon() {
    return Container(
      width: 50,
      height: 50,
      decoration: BoxDecoration(
        color: AppColors.primary.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: const Icon(
        Icons.branding_watermark,
        color: AppColors.primary,
        size: 28,
      ),
    );
  }

  /// Gérer le clic sur une marque - Ouvrir la page dédiée aux produits de la marque
  void _handleBrandTap(BuildContext context, BrandModel brand) {
    print('🔍 [BRANDS] _handleBrandTap appelé pour: ${brand.name}');
    print('🔍 [BRANDS] linkUrl: ${brand.linkUrl}');
    print('🔍 [BRANDS] name: ${brand.name}');
    
    if (brand.name.isEmpty) {
      print('⚠️ [BRANDS] Marque sans nom, impossible d\'afficher les produits');
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Marque sans nom'),
          duration: Duration(seconds: 2),
        ),
      );
      return;
    }

    print('✅ [BRANDS] Navigation vers page produits pour marque: ${brand.name}');
    
    // Toujours naviguer vers la page dédiée aux produits de la marque
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => BrandProductsScreen(
          brandName: brand.name,
          brandImageUrl: brand.image ?? brand.imagePath,
        ),
      ),
    ).then((_) {
      print('✅ [BRANDS] Retour de la page produits');
    }).catchError((error) {
      print('❌ [BRANDS] Erreur navigation: $error');
    });
  }
}

