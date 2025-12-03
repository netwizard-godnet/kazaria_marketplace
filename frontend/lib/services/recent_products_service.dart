import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/product_model.dart';

class RecentProductsService {
  static const String _recentProductsKey = 'recent_products';
  static const int _maxRecentProducts = 10;

  /// Ajouter un produit à la liste des produits récemment vus
  static Future<void> addRecentProduct(ProductModel product) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final recentProductsJson = prefs.getStringList(_recentProductsKey) ?? [];
      
      // Convertir en objets ProductModel
      List<ProductModel> recentProducts = recentProductsJson
          .map((json) => ProductModel.fromJson(jsonDecode(json)))
          .toList();

      // Supprimer le produit s'il existe déjà
      recentProducts.removeWhere((p) => p.id == product.id);
      
      // Ajouter le produit au début
      recentProducts.insert(0, product);
      
      // Limiter à _maxRecentProducts
      if (recentProducts.length > _maxRecentProducts) {
        recentProducts = recentProducts.take(_maxRecentProducts).toList();
      }
      
      // Sauvegarder
      final updatedJson = recentProducts
          .map((p) => jsonEncode(p.toJson()))
          .toList();
      
      await prefs.setStringList(_recentProductsKey, updatedJson);
    } catch (e) {
      print('Erreur lors de l\'ajout du produit récent: $e');
    }
  }

  /// Récupérer les produits récemment vus
  static Future<List<ProductModel>> getRecentProducts() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final recentProductsJson = prefs.getStringList(_recentProductsKey) ?? [];
      
      return recentProductsJson
          .map((json) => ProductModel.fromJson(jsonDecode(json)))
          .toList();
    } catch (e) {
      print('Erreur lors de la récupération des produits récents: $e');
      return [];
    }
  }

  /// Vider la liste des produits récemment vus
  static Future<void> clearRecentProducts() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_recentProductsKey);
    } catch (e) {
      print('Erreur lors du vidage des produits récents: $e');
    }
  }

  /// Supprimer un produit spécifique de la liste
  static Future<void> removeRecentProduct(int productId) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final recentProductsJson = prefs.getStringList(_recentProductsKey) ?? [];
      
      List<ProductModel> recentProducts = recentProductsJson
          .map((json) => ProductModel.fromJson(jsonDecode(json)))
          .toList();

      recentProducts.removeWhere((p) => p.id == productId);
      
      final updatedJson = recentProducts
          .map((p) => jsonEncode(p.toJson()))
          .toList();
      
      await prefs.setStringList(_recentProductsKey, updatedJson);
    } catch (e) {
      print('Erreur lors de la suppression du produit récent: $e');
    }
  }
}
