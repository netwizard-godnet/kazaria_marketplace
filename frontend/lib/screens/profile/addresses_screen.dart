import 'package:flutter/material.dart';
import '../../utils/constants.dart';
import '../../services/address_service.dart';
import 'add_address_screen.dart';

class AddressesScreen extends StatefulWidget {
  const AddressesScreen({super.key});

  @override
  State<AddressesScreen> createState() => _AddressesScreenState();
}

class _AddressesScreenState extends State<AddressesScreen> {
  List<Map<String, dynamic>> _addresses = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadAddresses();
  }

  Future<void> _loadAddresses() async {
    setState(() {
      _isLoading = true;
    });

    // Initialiser les adresses par défaut si nécessaire
    await AddressService.initializeDefaultAddresses();
    
    // Charger les adresses
    final addresses = await AddressService.getAddresses();
    
    setState(() {
      _addresses = addresses;
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Mes adresses'),
        elevation: 0,
      ),
      body: _isLoading 
          ? const Center(child: CircularProgressIndicator())
          : _addresses.isEmpty 
              ? _buildEmptyState() 
              : _buildAddressesList(),
      floatingActionButton: _buildAddButton(),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(AppSizes.space8),
            decoration: BoxDecoration(
              gradient: AppColors.primaryGradient,
              shape: BoxShape.circle,
              boxShadow: AppShadows.shadowLG,
            ),
            child: const Icon(
              Icons.location_on_outlined,
              size: 64,
              color: AppColors.white,
            ),
          ),
          const SizedBox(height: AppSizes.space6),
          Text(
            'Aucune adresse',
            style: AppTextStyles.headlineMedium.copyWith(
              color: AppColors.textDark,
            ),
          ),
          const SizedBox(height: AppSizes.space2),
          Text(
            'Ajoutez une adresse de livraison',
            style: AppTextStyles.bodyMedium.copyWith(
              color: AppColors.textMuted,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAddressesList() {
    return ListView.builder(
      padding: const EdgeInsets.all(AppSizes.space4),
      itemCount: _addresses.length,
      itemBuilder: (context, index) {
        return _buildAddressCard(_addresses[index], index);
      },
    );
  }

  Widget _buildAddressCard(Map<String, dynamic> address, int index) {
    return Container(
      margin: const EdgeInsets.only(bottom: AppSizes.space4),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radiusXL),
        border: address['isDefault']
            ? Border.all(color: AppColors.primary, width: 2)
            : null,
        boxShadow: AppShadows.shadowMD,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Container(
            padding: const EdgeInsets.all(AppSizes.space4),
            decoration: BoxDecoration(
              gradient: address['isDefault']
                  ? AppColors.primaryGradient
                  : LinearGradient(
                      colors: [
                        AppColors.grey100,
                        AppColors.grey50,
                      ],
                    ),
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(AppSizes.radiusXL),
                topRight: Radius.circular(AppSizes.radiusXL),
              ),
            ),
            child: Row(
              children: [
                Icon(
                  address['label'] == 'Domicile'
                      ? Icons.home
                      : Icons.business,
                  color: address['isDefault']
                      ? AppColors.white
                      : AppColors.textMedium,
                  size: AppSizes.iconMD,
                ),
                const SizedBox(width: AppSizes.space2),
                Expanded(
                  child: Text(
                    address['label'],
                    style: AppTextStyles.titleLarge.copyWith(
                      color: address['isDefault']
                          ? AppColors.white
                          : AppColors.textDark,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                if (address['isDefault'])
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppSizes.space2,
                      vertical: AppSizes.space1,
                    ),
                    decoration: BoxDecoration(
                      color: AppColors.white.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                    ),
                    child: Text(
                      'Par défaut',
                      style: AppTextStyles.labelSmall.copyWith(
                        color: AppColors.white,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
              ],
            ),
          ),
          
          // Contenu
          Padding(
            padding: const EdgeInsets.all(AppSizes.space4),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildInfoRow(Icons.person, address['name']),
                const SizedBox(height: AppSizes.space2),
                _buildInfoRow(Icons.phone, address['phone']),
                const SizedBox(height: AppSizes.space2),
                _buildInfoRow(Icons.location_on, address['address']),
                const SizedBox(height: AppSizes.space2),
                _buildInfoRow(
                  Icons.location_city,
                  '${address['city']}, ${address['postalCode']}',
                ),
                const SizedBox(height: AppSizes.space2),
                _buildInfoRow(Icons.flag, address['country']),
              ],
            ),
          ),
          
          // Actions
          Padding(
            padding: const EdgeInsets.all(AppSizes.space4),
            child: Column(
              children: [
                // Bouton définir par défaut (si pas déjà par défaut)
                if (!address['isDefault']) ...[
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: () => _setAsDefault(index),
                      icon: const Icon(Icons.star, size: AppSizes.iconSM),
                      label: const Text('Définir comme adresse par défaut'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.warning,
                        foregroundColor: AppColors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: AppSizes.space2),
                ],
                // Boutons modifier et supprimer
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => _editAddress(index),
                        icon: const Icon(Icons.edit, size: AppSizes.iconSM),
                        label: const Text('Modifier'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.primary,
                          side: const BorderSide(color: AppColors.primary),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: AppSizes.space2),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => _deleteAddress(index),
                        icon: const Icon(Icons.delete, size: AppSizes.iconSM),
                        label: const Text('Supprimer'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.error,
                          side: const BorderSide(color: AppColors.error),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String text) {
    return Row(
      children: [
        Icon(icon, size: AppSizes.iconSM, color: AppColors.textMuted),
        const SizedBox(width: AppSizes.space2),
        Expanded(
          child: Text(
            text,
            style: AppTextStyles.bodyMedium,
          ),
        ),
      ],
    );
  }

  Widget _buildAddButton() {
    return Container(
      decoration: BoxDecoration(
        gradient: AppColors.primaryGradient,
        borderRadius: BorderRadius.circular(AppSizes.radiusXL),
        boxShadow: AppShadows.shadowLG,
      ),
      child: FloatingActionButton.extended(
        onPressed: _addAddress,
        backgroundColor: Colors.transparent,
        elevation: 0,
        icon: const Icon(Icons.add),
        label: const Text('Ajouter'),
      ),
    );
  }

  void _addAddress() async {
    final result = await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => const AddAddressScreen(),
      ),
    );
    
    if (result != null && result is Map<String, dynamic>) {
      await AddressService.addAddress(result);
      await _loadAddresses(); // Recharger la liste
      
      if (!mounted) return;
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Adresse ajoutée avec succès'),
          backgroundColor: AppColors.success,
        ),
      );
    }
  }

  void _editAddress(int index) async {
    final result = await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => AddAddressScreen(address: _addresses[index]),
      ),
    );
    
    if (result != null && result is Map<String, dynamic>) {
      final addressId = _addresses[index]['id'] as int;
      await AddressService.updateAddress(addressId, result);
      await _loadAddresses(); // Recharger la liste
      
      if (!mounted) return;
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Adresse modifiée avec succès'),
          backgroundColor: AppColors.success,
        ),
      );
    }
  }

  void _setAsDefault(int index) async {
    final addressId = _addresses[index]['id'] as int;
    await AddressService.setDefaultAddress(addressId);
    await _loadAddresses(); // Recharger la liste
    
    if (!mounted) return;
    
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('${_addresses[index]['label']} définie comme adresse par défaut'),
        backgroundColor: AppColors.success,
      ),
    );
  }

  void _deleteAddress(int index) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppSizes.radius2XL),
        ),
        title: const Text('Supprimer l\'adresse'),
        content: Text('Voulez-vous supprimer ${_addresses[index]['label']} ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          ElevatedButton(
            onPressed: () async {
              final addressId = _addresses[index]['id'] as int;
              await AddressService.deleteAddress(addressId);
              await _loadAddresses(); // Recharger la liste
              
              if (!mounted) return;
              Navigator.pop(context);
              
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: const Text('Adresse supprimée'),
                  backgroundColor: AppColors.success,
                  behavior: SnackBarBehavior.floating,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(AppSizes.radiusLG),
                  ),
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
            ),
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );
  }
}

