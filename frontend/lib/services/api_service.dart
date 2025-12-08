import 'dart:convert';
import 'dart:io';
import 'dart:async';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import 'storage_service.dart';

class ApiService {
  final StorageService _storageService = StorageService();
  
  // Timeout pour toutes les requêtes (30 secondes)
  static const Duration _requestTimeout = Duration(seconds: 30);

  Future<String?> _getToken() async {
    final token = await _storageService.getToken();
    // Nettoyer le token (enlever les espaces et "Bearer " si présent)
    if (token != null) {
      return token.trim().replaceFirst(RegExp(r'^Bearer\s+'), '');
    }
    return null;
  }

  Future<Map<String, dynamic>> get(String endpoint,
      {bool requiresAuth = false}) async {
    try {
      String? token;
      if (requiresAuth) {
        token = await _getToken();
        print('🔐 [API_SERVICE] Token récupéré: ${token != null ? "OUI (${token.substring(0, 10)}...)" : "NON"}');
        if (token == null) {
          return {'success': false, 'message': 'Non authentifié'};
        }
      }

      print('📡 [API_SERVICE] GET request à: $endpoint');
      print('📡 [API_SERVICE] Headers: ${ApiConfig.headers(token: token)}');

      final response = await http.get(
        Uri.parse(endpoint),
        headers: ApiConfig.headers(token: token),
      ).timeout(
        _requestTimeout,
        onTimeout: () {
          throw TimeoutException('La requête a expiré. Vérifiez votre connexion internet.');
        },
      );

      print('📡 [API_SERVICE] Status code: ${response.statusCode}');
      print('📡 [API_SERVICE] Response body: ${response.body.substring(0, response.body.length > 200 ? 200 : response.body.length)}...');

      return _handleResponse(response);
    } on TimeoutException catch (e) {
      return {'success': false, 'message': e.message ?? 'Délai d\'attente dépassé'};
    } on SocketException {
      return {'success': false, 'message': 'Pas de connexion internet'};
    } catch (e) {
      return {'success': false, 'message': 'Erreur de connexion: $e'};
    }
  }

  Future<Map<String, dynamic>> post(String endpoint, Map<String, dynamic> body,
      {bool requiresAuth = false}) async {
    try {
      String? token;
      if (requiresAuth) {
        token = await _getToken();
        if (token == null) {
          return {'success': false, 'message': 'Non authentifié'};
        }
      }

      final response = await http.post(
        Uri.parse(endpoint),
        headers: ApiConfig.headers(token: token),
        body: jsonEncode(body),
      ).timeout(
        _requestTimeout,
        onTimeout: () {
          throw TimeoutException('La requête a expiré. Vérifiez votre connexion internet.');
        },
      );

      return _handleResponse(response);
    } on TimeoutException catch (e) {
      return {'success': false, 'message': e.message ?? 'Délai d\'attente dépassé'};
    } on SocketException {
      return {'success': false, 'message': 'Pas de connexion internet'};
    } catch (e) {
      return {'success': false, 'message': 'Erreur de connexion: $e'};
    }
  }

  Future<Map<String, dynamic>> put(String endpoint, Map<String, dynamic> body,
      {bool requiresAuth = false}) async {
    try {
      String? token;
      if (requiresAuth) {
        token = await _getToken();
        if (token == null) {
          return {'success': false, 'message': 'Non authentifié'};
        }
      }

      final response = await http.put(
        Uri.parse(endpoint),
        headers: ApiConfig.headers(token: token),
        body: jsonEncode(body),
      ).timeout(
        _requestTimeout,
        onTimeout: () {
          throw TimeoutException('La requête a expiré. Vérifiez votre connexion internet.');
        },
      );

      return _handleResponse(response);
    } on TimeoutException catch (e) {
      return {'success': false, 'message': e.message ?? 'Délai d\'attente dépassé'};
    } on SocketException {
      return {'success': false, 'message': 'Pas de connexion internet'};
    } catch (e) {
      return {'success': false, 'message': 'Erreur de connexion: $e'};
    }
  }

  Future<Map<String, dynamic>> delete(String endpoint,
      {bool requiresAuth = false}) async {
    try {
      String? token;
      if (requiresAuth) {
        token = await _getToken();
        if (token == null) {
          return {'success': false, 'message': 'Non authentifié'};
        }
      }

      final response = await http.delete(
        Uri.parse(endpoint),
        headers: ApiConfig.headers(token: token),
      ).timeout(
        _requestTimeout,
        onTimeout: () {
          throw TimeoutException('La requête a expiré. Vérifiez votre connexion internet.');
        },
      );

      return _handleResponse(response);
    } on TimeoutException catch (e) {
      return {'success': false, 'message': e.message ?? 'Délai d\'attente dépassé'};
    } on SocketException {
      return {'success': false, 'message': 'Pas de connexion internet'};
    } catch (e) {
      return {'success': false, 'message': 'Erreur de connexion: $e'};
    }
  }

  Future<Map<String, dynamic>> postMultipart(
      String endpoint, Map<String, String> fields,
      {Map<String, String>? files, bool requiresAuth = false}) async {
    try {
      String? token;
      if (requiresAuth) {
        token = await _getToken();
        print('🔐 [API_SERVICE] Token récupéré pour multipart: ${token != null ? "OUI (${token.substring(0, 10)}...)" : "NON"}');
        if (token == null) {
          return {'success': false, 'message': 'Non authentifié'};
        }
      }

      var request = http.MultipartRequest('POST', Uri.parse(endpoint));
      
      // Ajouter les headers manuellement (sans Content-Type pour laisser http gérer le boundary)
      request.headers['Accept'] = 'application/json';
      if (token != null) {
        request.headers['Authorization'] = 'Bearer $token';
        print('🔐 [API_SERVICE] Authorization header ajouté: Bearer ${token.substring(0, 10)}...');
      }
      
      // Ajouter les champs
      request.fields.addAll(fields);
      
      // Ajouter les fichiers
      if (files != null) {
        for (var entry in files.entries) {
          final file = File(entry.value);
          if (await file.exists()) {
          request.files.add(await http.MultipartFile.fromPath(
            entry.key,
            entry.value,
          ));
            print('📎 [API_SERVICE] Fichier ajouté: ${entry.key} = ${entry.value}');
          } else {
            print('❌ [API_SERVICE] Fichier introuvable: ${entry.value}');
            return {'success': false, 'message': 'Fichier introuvable: ${entry.value}'};
          }
        }
      }

      print('📡 [API_SERVICE] POST Multipart à: $endpoint');
      print('📡 [API_SERVICE] Headers: ${request.headers}');
      print('📡 [API_SERVICE] Fields: ${request.fields}');
      print('📡 [API_SERVICE] Files: ${request.files.length} fichier(s)');

      final streamedResponse = await request.send().timeout(
        _requestTimeout,
        onTimeout: () {
          throw TimeoutException('La requête a expiré. Vérifiez votre connexion internet.');
        },
      );
      final response = await http.Response.fromStream(streamedResponse);
      
      print('📡 [API_SERVICE] Status code: ${response.statusCode}');
      print('📡 [API_SERVICE] Response body: ${response.body.substring(0, response.body.length > 200 ? 200 : response.body.length)}...');
      
      return _handleResponse(response);
    } on TimeoutException catch (e) {
      return {'success': false, 'message': e.message ?? 'Délai d\'attente dépassé'};
    } on SocketException {
      return {'success': false, 'message': 'Pas de connexion internet'};
    } catch (e) {
      print('❌ [API_SERVICE] Erreur postMultipart: $e');
      return {'success': false, 'message': 'Erreur de connexion: $e'};
    }
  }

  Map<String, dynamic> _handleResponse(http.Response response) {
    try {
      // Décoder avec support UTF-8 explicite pour gérer les caractères spéciaux
      final responseBody = utf8.decode(response.bodyBytes);
      final data = jsonDecode(responseBody);
      
      if (response.statusCode >= 200 && response.statusCode < 300) {
        return {'success': true, ...data};
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Une erreur est survenue',
          ...data
        };
      }
    } catch (e) {
      print('❌ [API_SERVICE] Erreur de parsing: $e');
      print('❌ [API_SERVICE] Response snippet: ${response.body.substring(0, response.body.length > 200 ? 200 : response.body.length)}');
      return {
        'success': false,
        'message': 'Erreur de traitement de la réponse: $e'
      };
    }
  }

  /// Upload d'un fichier
  Future<Map<String, dynamic>> uploadFile(
    String endpoint,
    String fieldName,
    File file, {
    bool requiresAuth = false,
    Map<String, String>? additionalFields,
  }) async {
    try {
      String? token;
      if (requiresAuth) {
        token = await _getToken();
        if (token == null) {
          return {'success': false, 'message': 'Non authentifié'};
        }
      }

      var request = http.MultipartRequest('POST', Uri.parse(endpoint));

      // Ajouter le token si nécessaire
      if (token != null) {
        request.headers['Authorization'] = 'Bearer $token';
      }
      request.headers['Accept'] = 'application/json';

      // Ajouter le fichier
      request.files.add(
        await http.MultipartFile.fromPath(
          fieldName,
          file.path,
        ),
      );

      // Ajouter des champs additionnels si fournis
      if (additionalFields != null) {
        request.fields.addAll(additionalFields);
      }

      final streamedResponse = await request.send().timeout(
        _requestTimeout,
        onTimeout: () {
          throw TimeoutException('L\'upload a expiré. Vérifiez votre connexion internet.');
        },
      );
      final response = await http.Response.fromStream(streamedResponse);

      return _handleResponse(response);
    } on TimeoutException catch (e) {
      return {'success': false, 'message': e.message ?? 'Délai d\'attente dépassé'};
    } on SocketException {
      return {'success': false, 'message': 'Pas de connexion internet'};
    } catch (e) {
      return {'success': false, 'message': 'Erreur d\'upload: $e'};
    }
  }

  /// Upload de plusieurs fichiers
  Future<Map<String, dynamic>> uploadMultipleFiles(
    String endpoint,
    String fieldName,
    List<File> files, {
    bool requiresAuth = false,
    Map<String, String>? additionalFields,
  }) async {
    try {
      String? token;
      if (requiresAuth) {
        token = await _getToken();
        if (token == null) {
          return {'success': false, 'message': 'Non authentifié'};
        }
      }

      var request = http.MultipartRequest('POST', Uri.parse(endpoint));

      // Ajouter le token si nécessaire
      if (token != null) {
        request.headers['Authorization'] = 'Bearer $token';
      }
      request.headers['Accept'] = 'application/json';

      // Ajouter tous les fichiers
      for (var file in files) {
        request.files.add(
          await http.MultipartFile.fromPath(
            '${fieldName}[]',
            file.path,
          ),
        );
      }

      // Ajouter des champs additionnels si fournis
      if (additionalFields != null) {
        request.fields.addAll(additionalFields);
      }

      final streamedResponse = await request.send().timeout(
        _requestTimeout,
        onTimeout: () {
          throw TimeoutException('L\'upload a expiré. Vérifiez votre connexion internet.');
        },
      );
      final response = await http.Response.fromStream(streamedResponse);

      return _handleResponse(response);
    } on TimeoutException catch (e) {
      return {'success': false, 'message': e.message ?? 'Délai d\'attente dépassé'};
    } on SocketException {
      return {'success': false, 'message': 'Pas de connexion internet'};
    } catch (e) {
      return {'success': false, 'message': 'Erreur d\'upload: $e'};
    }
  }
}

