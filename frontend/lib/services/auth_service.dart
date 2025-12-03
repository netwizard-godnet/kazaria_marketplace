import 'dart:io';
import '../config/api_config.dart';
import '../models/user_model.dart';
import 'api_service.dart';
import 'storage_service.dart';

class AuthService {
  final ApiService _apiService = ApiService();
  final StorageService _storageService = StorageService();

  Future<Map<String, dynamic>> register({
    required String nom,
    required String prenoms,
    required String email,
    required String password,
    required String telephone,
    bool newsletter = false,
  }) async {
    final response = await _apiService.post(ApiConfig.register, {
      'nom': nom,
      'prenoms': prenoms,
      'email': email,
      'password': password,
      'password_confirmation': password,
      'telephone': telephone,
      'newsletter': newsletter,
      'termes_condition': true,
    });

    if (response['success'] && response['token'] != null) {
      await _storageService.saveToken(response['token']);
      if (response['user'] != null) {
        await _storageService.saveUser(response['user']);
      }
    }

    return response;
  }

  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    final response = await _apiService.post(ApiConfig.login, {
      'email': email,
      'password': password,
    });

    // Le backend envoie un code de vérification, pas de token immédiatement
    return response;
  }

  Future<Map<String, dynamic>> verifyLoginCode({
    required String email,
    required String code,
  }) async {
    final response = await _apiService.post(ApiConfig.verifyLoginCode, {
      'email': email,
      'code': code,
    });

    if (response['success'] && response['token'] != null) {
      await _storageService.saveToken(response['token']);
      if (response['user'] != null) {
        await _storageService.saveUser(response['user']);
      }
    }

    return response;
  }

  Future<Map<String, dynamic>> forgotPassword({
    required String email,
  }) async {
    return await _apiService.post(ApiConfig.forgotPassword, {
      'email': email,
    });
  }

  Future<Map<String, dynamic>> resetPassword({
    required String token,
    required String email,
    required String password,
  }) async {
    return await _apiService.post(ApiConfig.resetPassword, {
      'token': token,
      'email': email,
      'password': password,
      'password_confirmation': password,
    });
  }

  Future<Map<String, dynamic>> resendVerificationCode({
    required String email,
  }) async {
    return await _apiService.post(ApiConfig.resendVerificationCode, {
      'email': email,
    });
  }

  Future<Map<String, dynamic>> getMe() async {
    final response = await _apiService.get(
      ApiConfig.me,
      requiresAuth: true,
    );

    if (response['success'] && response['user'] != null) {
      await _storageService.saveUser(response['user']);
    }

    return response;
  }

  Future<UserModel?> getCurrentUser() async {
    final userData = await _storageService.getUser();
    if (userData != null) {
      return UserModel.fromJson(userData);
    }
    return null;
  }

  Future<Map<String, dynamic>> updateProfile({
    required String nom,
    required String prenoms,
    required String email,
    required String telephone,
    String? adresse,
    String? ville,
    String? codePostal,
    String? pays,
    String? bio,
  }) async {
    print('📤 [AUTH_SERVICE] Envoi des données au serveur:');
    print('  - Endpoint: ${ApiConfig.profileUpdate}');
    print('  - Nom: $nom');
    print('  - Prénoms: $prenoms');
    print('  - Email: $email');
    print('  - Téléphone: $telephone');
    print('  - Adresse: $adresse');
    print('  - Ville: $ville');
    print('  - Code Postal: $codePostal');
    print('  - Pays: $pays');
    print('  - Bio: $bio');
    
    final requestData = {
      'nom': nom,
      'prenoms': prenoms,
      'email': email,
      'telephone': telephone,
      'adresse': adresse,
      'ville': ville,
      'code_postal': codePostal,
      'pays': pays,
      'bio': bio,
    };
    
    print('📦 [AUTH_SERVICE] Données JSON: $requestData');
    
    final response = await _apiService.post(
      ApiConfig.profileUpdate,
      requestData,
      requiresAuth: true,
    );
    
    print('📨 [AUTH_SERVICE] Réponse du serveur: $response');

    if (response['success'] && response['user'] != null) {
      print('✅ [AUTH_SERVICE] Sauvegarde de l\'utilisateur dans le storage');
      await _storageService.saveUser(response['user']);
    } else {
      print('❌ [AUTH_SERVICE] Échec de la mise à jour: ${response['message']}');
      if (response['errors'] != null) {
        print('🔴 [AUTH_SERVICE] Erreurs de validation: ${response['errors']}');
      }
    }

    return response;
  }

  Future<Map<String, dynamic>> changePassword({
    required String currentPassword,
    required String newPassword,
  }) async {
    return await _apiService.post(
      ApiConfig.changePassword,
      {
        'current_password': currentPassword,
        'new_password': newPassword,
        'new_password_confirmation': newPassword,
      },
      requiresAuth: true,
    );
  }

  Future<Map<String, dynamic>> updatePhoto(String filePath) async {
    return await _apiService.postMultipart(
      ApiConfig.updatePhoto,
      {},
      files: {'photo': filePath},
      requiresAuth: true,
    );
  }

  Future<Map<String, dynamic>> checkSellerStatus() async {
    return await _apiService.get(
      ApiConfig.checkSellerStatus,
      requiresAuth: true,
    );
  }

  Future<void> logout() async {
    try {
      await _apiService.post(ApiConfig.logout, {}, requiresAuth: true);
    } catch (e) {
      // Continue même si l'appel API échoue
    }
    await _storageService.clearAll();
  }

  Future<bool> isAuthenticated() async {
    return await _storageService.isAuthenticated();
  }

  /// Mettre à jour la photo de profil
  Future<Map<String, dynamic>> updateProfilePhoto(File photo) async {
    return await _apiService.postMultipart(
      ApiConfig.updatePhoto,
      {},
      files: {'photo': photo.path},
      requiresAuth: true,
    );
  }

  /// Obtenir les commandes de l'utilisateur
  Future<Map<String, dynamic>> getMyOrders() async {
    return await _apiService.get(
      ApiConfig.myOrders,
      requiresAuth: true,
    );
  }

  /// Obtenir les favoris de l'utilisateur
  Future<Map<String, dynamic>> getFavorites() async {
    return await _apiService.get(
      ApiConfig.favorites,
      requiresAuth: true,
    );
  }

  /// Ajouter/Retirer un favori
  Future<Map<String, dynamic>> toggleFavorite(int productId) async {
    return await _apiService.post(
      ApiConfig.toggleFavorite,
      {'product_id': productId},
      requiresAuth: true,
    );
  }
}

