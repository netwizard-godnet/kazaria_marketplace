import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

class AddressService {
  static const String _addressesKey = 'user_addresses';

  // Sauvegarder les adresses
  static Future<void> saveAddresses(List<Map<String, dynamic>> addresses) async {
    final prefs = await SharedPreferences.getInstance();
    final addressesJson = addresses.map((addr) => jsonEncode(addr)).toList();
    await prefs.setStringList(_addressesKey, addressesJson);
  }

  // Récupérer les adresses
  static Future<List<Map<String, dynamic>>> getAddresses() async {
    final prefs = await SharedPreferences.getInstance();
    final addressesJson = prefs.getStringList(_addressesKey) ?? [];
    return addressesJson.map((addr) => jsonDecode(addr) as Map<String, dynamic>).toList();
  }

  // Récupérer l'adresse par défaut
  static Future<Map<String, dynamic>?> getDefaultAddress() async {
    final addresses = await getAddresses();
    try {
      return addresses.firstWhere(
        (addr) => addr['isDefault'] == true,
        orElse: () => addresses.isNotEmpty ? addresses[0] : {},
      );
    } catch (e) {
      return null;
    }
  }

  // Définir une adresse par défaut
  static Future<void> setDefaultAddress(int addressId) async {
    final addresses = await getAddresses();
    for (var addr in addresses) {
      addr['isDefault'] = addr['id'] == addressId;
    }
    await saveAddresses(addresses);
  }

  // Ajouter une adresse
  static Future<void> addAddress(Map<String, dynamic> address) async {
    final addresses = await getAddresses();
    
    // Si c'est la première adresse ou si elle est marquée par défaut, mettre les autres à false
    if (addresses.isEmpty || address['isDefault'] == true) {
      for (var addr in addresses) {
        addr['isDefault'] = false;
      }
    }
    
    // Générer un ID unique
    address['id'] = addresses.isEmpty ? 1 : (addresses.map((a) => a['id'] as int).reduce((a, b) => a > b ? a : b) + 1);
    
    addresses.add(address);
    await saveAddresses(addresses);
  }

  // Mettre à jour une adresse
  static Future<void> updateAddress(int addressId, Map<String, dynamic> updatedAddress) async {
    final addresses = await getAddresses();
    final index = addresses.indexWhere((addr) => addr['id'] == addressId);
    
    if (index != -1) {
      // Si elle est marquée par défaut, retirer le statut des autres
      if (updatedAddress['isDefault'] == true) {
        for (int i = 0; i < addresses.length; i++) {
          if (i != index) {
            addresses[i]['isDefault'] = false;
          }
        }
      }
      
      updatedAddress['id'] = addressId; // Conserver l'ID
      addresses[index] = updatedAddress;
      await saveAddresses(addresses);
    }
  }

  // Supprimer une adresse
  static Future<void> deleteAddress(int addressId) async {
    final addresses = await getAddresses();
    addresses.removeWhere((addr) => addr['id'] == addressId);
    
    // Si on a supprimé l'adresse par défaut, définir la première comme par défaut
    if (addresses.isNotEmpty && !addresses.any((addr) => addr['isDefault'] == true)) {
      addresses[0]['isDefault'] = true;
    }
    
    await saveAddresses(addresses);
  }

  // Initialiser avec des adresses par défaut (pour les tests)
  static Future<void> initializeDefaultAddresses() async {
    final addresses = await getAddresses();
    if (addresses.isEmpty) {
      await saveAddresses([
        {
          'id': 1,
          'label': 'Domicile',
          'name': 'John Doe',
          'phone': '+225 07 XX XX XX XX',
          'address': '123 Rue de la Paix',
          'city': 'Abidjan',
          'postalCode': '00225',
          'country': 'Côte d\'Ivoire',
          'isDefault': true,
        },
        {
          'id': 2,
          'label': 'Bureau',
          'name': 'John Doe',
          'phone': '+225 07 YY YY YY YY',
          'address': '456 Avenue Marchand',
          'city': 'Abidjan',
          'postalCode': '00225',
          'country': 'Côte d\'Ivoire',
          'isDefault': false,
        },
      ]);
    }
  }
}

