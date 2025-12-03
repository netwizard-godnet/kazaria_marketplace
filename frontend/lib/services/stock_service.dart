import 'package:http/http.dart' as http;
import 'dart:convert';
import '../config/api_config.dart';

class StockService {
  /// Obtenir le stock d'un produit en temps réel
  Future<Map<String, dynamic>> getProductStock(int productId) async {
    try {
      final url = '${ApiConfig.baseUrl}/products/$productId/stock';
      
      print('📦 [STOCK] Récupération du stock pour le produit $productId');
      
      final response = await http.get(
        Uri.parse(url),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        if (data['success'] == true) {
          print('✅ [STOCK] Stock récupéré: ${data['stock']} unités');
          return {
            'success': true,
            'stock': data['stock'],
            'stock_status': data['stock_status'],
            'is_available': data['is_available'],
            'low_stock_threshold': data['low_stock_threshold']
          };
        }
      }

      print('❌ [STOCK] Erreur lors de la récupération du stock');
      return {
        'success': false,
        'message': 'Erreur lors de la récupération du stock'
      };
    } catch (e) {
      print('❌ [STOCK] Exception: $e');
      return {
        'success': false,
        'message': 'Erreur de connexion'
      };
    }
  }

  /// Obtenir le stock de plusieurs produits en une seule requête
  Future<Map<String, dynamic>> getBatchStock(List<int> productIds) async {
    try {
      final url = '${ApiConfig.baseUrl}/products/stock/batch';
      
      print('📦 [STOCK_BATCH] Récupération du stock pour ${productIds.length} produits');
      
      final response = await http.post(
        Uri.parse(url),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'product_ids': productIds}),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        if (data['success'] == true) {
          print('✅ [STOCK_BATCH] Stock récupéré pour ${data['stocks'].length} produits');
          return {
            'success': true,
            'stocks': data['stocks']
          };
        }
      }

      print('❌ [STOCK_BATCH] Erreur lors de la récupération du stock');
      return {
        'success': false,
        'message': 'Erreur lors de la récupération du stock'
      };
    } catch (e) {
      print('❌ [STOCK_BATCH] Exception: $e');
      return {
        'success': false,
        'message': 'Erreur de connexion'
      };
    }
  }

  /// Vérifier si un produit est disponible
  Future<bool> isProductAvailable(int productId) async {
    final result = await getProductStock(productId);
    return result['success'] == true && result['is_available'] == true;
  }

  /// Obtenir le statut du stock (in_stock, low_stock, out_of_stock)
  Future<String> getStockStatus(int productId) async {
    final result = await getProductStock(productId);
    if (result['success'] == true) {
      return result['stock_status'] ?? 'unknown';
    }
    return 'unknown';
  }
}

