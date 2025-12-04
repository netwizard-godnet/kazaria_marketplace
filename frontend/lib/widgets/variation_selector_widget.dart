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
    
    // Si une variation initiale est fournie, la sélectionner
    if (widget.initialVariation != null) {
      _currentVariation = widget.initialVariation;
      for (var attr in widget.initialVariation!.attributes) {
        _selectedAttributes[attr.attributeId] = attr.valueId;
      }
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
  }

  void _onAttributeValueSelected(int attributeId, int valueId) {
    setState(() {
      _selectedAttributes[attributeId] = valueId;
      _findMatchingVariation();
    });
  }

  void _findMatchingVariation() {
    if (widget.product.variations == null || widget.product.variations!.isEmpty) {
      _currentVariation = null;
      widget.onVariationChanged(null);
      return;
    }

    // Trouver la variation qui correspond à la sélection
    final matchingVariation = widget.product.variations!.firstWhere(
      (variation) => variation.matchesSelection(_selectedAttributes),
      orElse: () => widget.product.variations!.first,
    );

    if (_currentVariation?.id != matchingVariation.id) {
      _currentVariation = matchingVariation;
      widget.onVariationChanged(_currentVariation);
      print('✅ [VARIATION_SELECTOR] Variation sélectionnée: ${matchingVariation.id} - ${matchingVariation.attributesDescription}');
    }
  }

  /// Vérifie si une valeur d'attribut est disponible (a du stock dans au moins une variation)
  bool _isValueAvailable(int attributeId, int valueId) {
    if (widget.product.variations == null) return true;

    // Créer une sélection temporaire avec cette valeur
    final tempSelection = Map<int, int>.from(_selectedAttributes);
    tempSelection[attributeId] = valueId;

    // Vérifier s'il existe une variation en stock avec cette combinaison
    return widget.product.variations!.any((variation) {
      if (!variation.isInStock) return false;
      
      // La variation doit avoir cette valeur d'attribut
      final hasThisValue = variation.attributes.any(
        (attr) => attr.attributeId == attributeId && attr.valueId == valueId,
      );
      
      if (!hasThisValue) return false;

      // Vérifier que la variation correspond aussi aux autres sélections
      for (var entry in tempSelection.entries) {
        if (entry.key == attributeId) continue; // Skip l'attribut en cours
        
        final hasOtherAttr = variation.attributes.any(
          (attr) => attr.attributeId == entry.key && attr.valueId == entry.value,
        );
        
        if (!hasOtherAttr) return false;
      }

      return true;
    });
  }

  @override
  Widget build(BuildContext context) {
    // Si le produit n'a pas de variations, ne rien afficher
    if (!widget.product.hasVariations || 
        widget.product.productAttributes == null || 
        widget.product.productAttributes!.isEmpty) {
      return const SizedBox.shrink();
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
                  ? () => _onAttributeValueSelected(attribute.id, value.id)
                  : null,
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
    return GestureDetector(
      onTap: onTap,
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

