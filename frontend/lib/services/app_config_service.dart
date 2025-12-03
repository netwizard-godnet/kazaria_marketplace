import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import 'storage_service.dart';

class AppConfigService {
  static final AppConfigService _instance = AppConfigService._internal();
  factory AppConfigService() => _instance;
  AppConfigService._internal();

  final StorageService _storageService = StorageService();

  /// Headers avec token d'authentification
  Future<Map<String, String>> _getHeaders() async {
    final token = await _storageService.getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  /// Récupérer la configuration complète de l'application
  Future<Map<String, dynamic>> getAppConfig() async {
    try {
      final headers = await _getHeaders();
      final uri = Uri.parse(ApiConfig.appConfig);

      print('🔄 [APP_CONFIG] Récupération configuration app');

      final response = await http.get(uri, headers: headers);
      final data = json.decode(response.body);

      print('📥 [APP_CONFIG] Réponse: ${response.statusCode}');

      if (response.statusCode == 200) {
        return {
          'success': true,
          'data': data['data'] ?? {},
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Erreur lors de la récupération de la configuration',
        };
      }
    } catch (e) {
      print('💥 [APP_CONFIG] Exception: $e');
      return {
        'success': false,
        'message': e.toString(),
      };
    }
  }

  /// Récupérer uniquement le logo de l'application
  Future<Map<String, dynamic>> getAppLogo() async {
    try {
      final headers = await _getHeaders();
      final uri = Uri.parse(ApiConfig.appLogo);

      print('🔄 [APP_CONFIG] Récupération logo app');

      final response = await http.get(uri, headers: headers);
      final data = json.decode(response.body);

      print('📥 [APP_CONFIG] Réponse logo: ${response.statusCode}');

      if (response.statusCode == 200) {
        return {
          'success': true,
          'logo': data['logo'] ?? '',
          'logo_path': data['logo_path'] ?? '',
          'app_name': data['app_name'] ?? 'KAZARIA',
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Erreur lors de la récupération du logo',
        };
      }
    } catch (e) {
      print('💥 [APP_CONFIG] Exception logo: $e');
      return {
        'success': false,
        'message': e.toString(),
      };
    }
  }

  /// Récupérer les informations de contact
  Future<Map<String, dynamic>> getContactInfo() async {
    try {
      final headers = await _getHeaders();
      final uri = Uri.parse(ApiConfig.appContact);

      print('🔄 [APP_CONFIG] Récupération infos contact');

      final response = await http.get(uri, headers: headers);
      final data = json.decode(response.body);

      print('📥 [APP_CONFIG] Réponse contact: ${response.statusCode}');

      if (response.statusCode == 200) {
        return {
          'success': true,
          'contact': data['contact'] ?? {},
          'social': data['social'] ?? {},
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Erreur lors de la récupération des informations',
        };
      }
    } catch (e) {
      print('💥 [APP_CONFIG] Exception contact: $e');
      return {
        'success': false,
        'message': e.toString(),
      };
    }
  }
}

