import 'package:flutter/material.dart';
import '../models/product_model.dart';
import '../models/product_variation_model.dart';
import '../utils/constants.dart';
import '../utils/helpers.dart';

/// Widget pour sélectionner les variations d'un produit
class VariationSelectorWidget extends StatefulWidget {
  final ProductModel product;
  final Function(ProductVariation?) onVariationChanged;
  final ProductVariation? initialVariation;

  const VariationSelectorWidget({
    super.key,
    required this.product,
    required this.onVariationChanged,
    this.initialVariation,
  });

  @override
  State<VariationSelectorWidget> createState() => _VariationSelectorWidgetState();
}

class _VariationSelectorWidgetState extends State<VariationSelectorWidget> {
  // Map<attributeId, valueId>
  Map<int, int> _selectedAttributes = {};
  ProductVariation? _currentVariation;

  @override
  void initState() {
    super.initState();
    
    // ✅ Différer l'appel de onVariationChanged après la phase de build
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      
    // Si une variation initiale est fournie, la sélectionner
    if (widget.initialVariation != null) {
      _currentVariation = widget.initialVariation;
      for (var attr in widget.initialVariation!.attributes) {
        _selectedAttributes[attr.attributeId] = attr.valueId;
      }
        widget.onVariationChanged(_currentVariation);
    } 
    // Sinon, sélectionner la variation par défaut si elle existe
    else if (widget.product.defaultVariationId != null && widget.product.variations != null) {
      final defaultVar = widget.product.variations!.firstWhere(
        (v) => v.id == widget.product.defaultVariationId,
        orElse: () => widget.product.variations!.first,
      );
      _currentVariation = defaultVar;
      for (var attr in defaultVar.attributes) {
        _selectedAttributes[attr.attributeId] = attr.valueId;
      }
      widget.onVariationChanged(_currentVariation);
    }
    });
  }

  void _onAttributeValueSelected(int attributeId, int valueId) {
    print('🖱️ [VARIATION_SELECTOR] Clic sur attribut $attributeId, valeur $valueId');
    setState(() {
      _selectedAttributes[attributeId] = valueId;
      print('   📊 Attributs sélectionnés après clic: $_selectedAttributes');
      _findMatchingVariation();
    });
  }

  void _findMatchingVariation() {
    if (widget.product.variations == null || widget.product.variations!.isEmpty) {
      _currentVariation = null;
      widget.onVariationChanged(null);
      return;
    }

    // ✅ NOUVELLE LOGIQUE : Trouver la meilleure variation correspondante
    // 1. Si tous les attributs sont sélectionnés, chercher une correspondance exacte
    // 2. Sinon, chercher une variation qui correspond aux attributs sélectionnés
    //    (même si elle n'a pas tous les attributs)
    
    final allAttributesSelected = widget.product.productAttributes != null &&
        _selectedAttributes.length == widget.product.productAttributes!.length;
    
    ProductVariation? matchingVariation;
    
    if (allAttributesSelected) {
      // Tous les attributs sont sélectionnés, trouver la variation exacte
      try {
        matchingVariation = widget.product.variations!.firstWhere(
      (variation) => variation.matchesSelection(_selectedAttributes),
        );
      } catch (e) {
        // Aucune variation exacte trouvée, chercher la meilleure correspondance partielle
        matchingVariation = _findBestPartialMatch();
      }
    } else {
      // Pas tous les attributs sont sélectionnés, trouver la meilleure correspondance
      matchingVariation = _findBestPartialMatch();
    }

    if (matchingVariation != null && _currentVariation?.id != matchingVariation.id) {
      _currentVariation = matchingVariation;
      widget.onVariationChanged(_currentVariation);
      print('✅ [VARIATION_SELECTOR] Variation sélectionnée: ${matchingVariation.id} - Prix: ${matchingVariation.price} - Stock: ${matchingVariation.stock}');
      print('   📊 Attributs sélectionnés: $_selectedAttributes');
      print('   📊 Attributs de la variation: ${matchingVariation.attributes.map((a) => '${a.attributeName}=${a.value}').join(', ')}');
    }
  }
  
  /// Trouve la meilleure variation correspondant aux attributs sélectionnés
  ProductVariation? _findBestPartialMatch() {
    if (widget.product.variations == null || widget.product.variations!.isEmpty) {
      return null;
    }
    
    // Si aucun attribut n'est sélectionné, retourner la variation par défaut ou la première
    if (_selectedAttributes.isEmpty) {
      return widget.product.variations!.firstWhere(
        (v) => v.isDefault,
        orElse: () => widget.product.variations!.first,
      );
    }
    
    // Trouver la variation qui correspond au maximum d'attributs sélectionnés
    ProductVariation? bestMatch;
    int maxMatches = 0;
    
    for (var variation in widget.product.variations!) {
      int matchCount = 0;
      bool allSelectedMatch = true;

      // Compter combien d'attributs sélectionnés correspondent à cette variation
      for (var entry in _selectedAttributes.entries) {
        final hasMatch = variation.attributes.any(
          (attr) => attr.attributeId == entry.key && attr.valueId == entry.value,
        );
        
        if (hasMatch) {
          matchCount++;
        } else {
          // Si la variation a cet attribut mais avec une valeur différente, ce n'est pas un match
          final hasThisAttribute = variation.attributes.any(
            (attr) => attr.attributeId == entry.key,
          );
          if (hasThisAttribute) {
            allSelectedMatch = false;
            break;
          }
          // Si la variation n'a pas cet attribut, on considère que c'est OK (attribut optionnel)
        }
      }
      
      // Si tous les attributs sélectionnés correspondent (ou la variation n'a pas ces attributs)
      if (allSelectedMatch && matchCount >= maxMatches) {
        maxMatches = matchCount;
        bestMatch = variation;
      }
    }
    
    // Si on a trouvé un match, le retourner
    if (bestMatch != null) {
      return bestMatch;
    }
    
    // Sinon, retourner la première variation disponible
    return widget.product.variations!.firstWhere(
      (variation) => variation.isInStock,
      orElse: () => widget.product.variations!.first,
    );
  }

  /// Vérifie si une valeur d'attribut est disponible (a du stock dans au moins une variation)
  bool _isValueAvailable(int attributeId, int valueId) {
    if (widget.product.variations == null || widget.product.variations!.isEmpty) {
      return true; // Si pas de variations, toutes les valeurs sont disponibles
    }

    // ✅ NOUVELLE LOGIQUE : Permettre la sélection si :
    // 1. La valeur existe dans au moins une variation (même partielle)
    // 2. OU si aucune variation n'a cet attribut, permettre quand même la sélection
    //    (pour les produits où certaines variations n'ont pas tous les attributs)
    
    // Vérifier d'abord si au moins une variation a cette valeur
    final hasExactMatch = widget.product.variations!.any((variation) {
      return variation.attributes.any(
        (attr) => attr.attributeId == attributeId && attr.valueId == valueId,
      );
    });
    
    if (hasExactMatch) {
      return true;
    }
    
    // Si aucune variation n'a cette valeur exacte, vérifier si au moins une variation
    // n'a pas cet attribut du tout (ce qui signifie que l'attribut est optionnel pour cette variation)
    final hasVariationWithoutThisAttribute = widget.product.variations!.any((variation) {
      // La variation n'a pas cet attribut du tout
      return !variation.attributes.any((attr) => attr.attributeId == attributeId);
    });
    
    // Si au moins une variation n'a pas cet attribut, permettre la sélection
    // (cela permet de gérer les cas où certaines variations n'ont pas tous les attributs)
    if (hasVariationWithoutThisAttribute) {
      print('   ⚠️ Aucune variation n\'a l\'attribut $attributeId, mais certaines variations n\'ont pas cet attribut - Permettre la sélection');
      return true;
    }
    
    // Sinon, la valeur n'est pas disponible
    print('   ❌ Aucune variation trouvée pour attribut $attributeId=$valueId');
    return false;
  }

  @override
  Widget build(BuildContext context) {
    // Si le produit n'a pas de variations, ne rien afficher
    if (!widget.product.hasVariations || 
        widget.product.productAttributes == null || 
        widget.product.productAttributes!.isEmpty) {
      print('⚠️ [VARIATION_SELECTOR] Pas de variations ou attributs: hasVariations=${widget.product.hasVariations}, productAttributes=${widget.product.productAttributes?.length ?? 0}');
      return const SizedBox.shrink();
    }
    
    // Log pour déboguer
    print('✅ [VARIATION_SELECTOR] Build - Attributs: ${widget.product.productAttributes!.length}, Variations: ${widget.product.variations?.length ?? 0}');
    for (var attr in widget.product.productAttributes!) {
      print('   📊 Attribut ${attr.id} (${attr.name}): ${attr.values.length} valeurs');
      for (var val in attr.values) {
        final isAvailable = _isValueAvailable(attr.id, val.id);
        print('      - ${val.value} (ID: ${val.id}) - Disponible: $isAvailable');
      }
    }

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppSizes.radiusLG),
      ),
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Titre
            Row(
              children: [
                Icon(Icons.tune, color: AppColors.primary, size: 20),
                const SizedBox(width: 8),
                Text(
                  'Options disponibles',
                  style: AppTextStyles.h3.copyWith(
                    color: AppColors.textDark,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Liste des attributs
            ...widget.product.productAttributes!.map((attribute) {
              return Padding(
                padding: const EdgeInsets.only(bottom: 16),
                child: _buildAttributeSelector(attribute),
              );
            }),

            // Affichage de la variation sélectionnée
            if (_currentVariation != null) ...[
              const Divider(height: 24),
              _buildSelectedVariationInfo(_currentVariation!),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildAttributeSelector(ProductAttribute attribute) {
    final selectedValueId = _selectedAttributes[attribute.id];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Nom de l'attribut
        Text(
          attribute.name,
          style: AppTextStyles.body.copyWith(
            fontWeight: FontWeight.w600,
            color: AppColors.textDark,
          ),
        ),
        const SizedBox(height: 8),

        // Valeurs disponibles
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: attribute.values.map((value) {
            final isSelected = selectedValueId == value.id;
            final isAvailable = _isValueAvailable(attribute.id, value.id);

            return _buildValueChip(
              value: value.value,
              isSelected: isSelected,
              isAvailable: isAvailable,
              onTap: isAvailable
                  ? () {
                      print('🖱️ [VARIATION_SELECTOR] Clic détecté sur ${attribute.name}: ${value.value}');
                      _onAttributeValueSelected(attribute.id, value.id);
                    }
                  : () {
                      print('⚠️ [VARIATION_SELECTOR] Valeur non disponible: ${attribute.name}: ${value.value}');
                    },
            );
          }).toList(),
        ),
      ],
    );
  }

  Widget _buildValueChip({
    required String value,
    required bool isSelected,
    required bool isAvailable,
    VoidCallback? onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
      onTap: onTap,
        borderRadius: BorderRadius.circular(AppSizes.radiusMD),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: isSelected
              ? AppColors.primary
              : isAvailable
                  ? AppColors.white
                  : AppColors.grey100,
          border: Border.all(
            color: isSelected
                ? AppColors.primary
                : isAvailable
                    ? AppColors.border
                    : AppColors.grey300,
            width: isSelected ? 2 : 1,
          ),
          borderRadius: BorderRadius.circular(AppSizes.radiusMD),
        ),
        child: Text(
          value,
          style: AppTextStyles.body.copyWith(
            color: isSelected
                ? AppColors.white
                : isAvailable
                    ? AppColors.textDark
                    : AppColors.textLight,
            fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSelectedVariationInfo(ProductVariation variation) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: variation.isInStock 
            ? AppColors.success.withOpacity(0.1) 
            : AppColors.error.withOpacity(0.1),
        borderRadius: BorderRadius.circular(AppSizes.radiusMD),
        border: Border.all(
          color: variation.isInStock 
              ? AppColors.success.withOpacity(0.3) 
              : AppColors.error.withOpacity(0.3),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Prix
          Row(
            children: [
              Text(
                'Prix: ',
                style: AppTextStyles.bodySmall.copyWith(
                  color: AppColors.textLight,
                ),
              ),
              Text(
                Helpers.formatPrice(variation.price),
                style: AppTextStyles.h3.copyWith(
                  color: AppColors.primary,
                  fontWeight: FontWeight.bold,
                ),
              ),
              if (variation.hasDiscount) ...[
                const SizedBox(width: 8),
                Text(
                  Helpers.formatPrice(variation.oldPrice!),
                  style: AppTextStyles.bodySmall.copyWith(
                    decoration: TextDecoration.lineThrough,
                    color: AppColors.textLight,
                  ),
                ),
              ],
            ],
          ),
          const SizedBox(height: 4),

          // Stock
          Row(
            children: [
              Icon(
                variation.isInStock ? Icons.check_circle : Icons.cancel,
                size: 16,
                color: variation.isInStock ? AppColors.success : AppColors.error,
              ),
              const SizedBox(width: 4),
              Text(
                variation.isInStock
                    ? 'En stock (${variation.stock})'
                    : 'Rupture de stock',
                style: AppTextStyles.bodySmall.copyWith(
                  color: variation.isInStock ? AppColors.success : AppColors.error,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),

          // SKU
          if (variation.sku.isNotEmpty) ...[
                const SizedBox(height: 4),
            Text(
              'Réf: ${variation.sku}',
              style: AppTextStyles.bodySmall.copyWith(
                color: AppColors.textMuted,
              ),
            ),
          ],
        ],
      ),
    );
  }
}

