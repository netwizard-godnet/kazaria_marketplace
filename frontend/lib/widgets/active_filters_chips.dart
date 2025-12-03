import 'package:flutter/material.dart';
import '../utils/constants.dart';

class ActiveFiltersChips extends StatelessWidget {
  final Map<String, dynamic> filters;
  final Function(String filterKey, {dynamic value}) onRemoveFilter;
  final VoidCallback onClearAll;

  const ActiveFiltersChips({
    super.key,
    required this.filters,
    required this.onRemoveFilter,
    required this.onClearAll,
  });

  int _countActiveFilters() {
    int count = 0;
    
    // Prix
    if (filters['min_price'] != null || filters['max_price'] != null) {
      count++;
    }
    
    // Note
    if (filters['min_rating'] != null) {
      count++;
    }
    
    // Disponibilité
    if (filters['in_stock'] != null) {
      count++;
    }
    
    // Marques
    final brands = filters['brands'] as List<String>?;
    if (brands != null && brands.isNotEmpty) {
      count += brands.length;
    }
    
    return count;
  }

  @override
  Widget build(BuildContext context) {
    final activeFiltersCount = _countActiveFilters();
    
    if (activeFiltersCount == 0) {
      return const SizedBox.shrink();
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: AppColors.grey50,
        border: Border(
          bottom: BorderSide(color: AppColors.border.withOpacity(0.3)),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(
                'Filtres actifs ($activeFiltersCount)',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textLight,
                ),
              ),
              const Spacer(),
              TextButton.icon(
                onPressed: onClearAll,
                icon: const Icon(Icons.clear_all, size: 16),
                label: const Text('Tout effacer'),
                style: TextButton.styleFrom(
                  foregroundColor: AppColors.error,
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  minimumSize: Size.zero,
                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              // Filtre de prix
              if (filters['min_price'] != null || filters['max_price'] != null)
                _buildFilterChip(
                  label: '${filters['min_price']?.toInt() ?? 0} - ${filters['max_price']?.toInt() ?? 0} FCFA',
                  icon: Icons.attach_money,
                  onRemove: () => onRemoveFilter('price'),
                ),

              // Filtre de note
              if (filters['min_rating'] != null)
                _buildFilterChip(
                  label: '${filters['min_rating']}★ et +',
                  icon: Icons.star,
                  color: AppColors.warning,
                  onRemove: () => onRemoveFilter('min_rating'),
                ),

              // Filtre de disponibilité
              if (filters['in_stock'] != null)
                _buildFilterChip(
                  label: filters['in_stock'] == true ? 'En stock' : 'Rupture',
                  icon: filters['in_stock'] == true ? Icons.check_circle : Icons.cancel,
                  color: filters['in_stock'] == true ? AppColors.success : AppColors.error,
                  onRemove: () => onRemoveFilter('in_stock'),
                ),

              // Filtres de marques
              ...(() {
                final brands = filters['brands'] as List<String>?;
                if (brands == null || brands.isEmpty) return <Widget>[];
                return brands.map((brand) => _buildFilterChip(
                  label: brand,
                  icon: Icons.branding_watermark,
                  onRemove: () => onRemoveFilter('brands', value: brand),
                )).toList();
              })(),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip({
    required String label,
    required IconData icon,
    Color? color,
    required VoidCallback onRemove,
  }) {
    return Chip(
      avatar: Icon(
        icon,
        size: 16,
        color: color ?? AppColors.primary,
      ),
      label: Text(
        label,
        style: const TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.w500,
        ),
      ),
      deleteIcon: const Icon(Icons.close, size: 16),
      onDeleted: onRemove,
      backgroundColor: AppColors.white,
      side: BorderSide(color: color ?? AppColors.primary),
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
    );
  }
}

