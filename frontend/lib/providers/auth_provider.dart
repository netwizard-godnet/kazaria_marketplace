import 'package:flutter/foundation.dart';
import 'dart:io';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user_model.dart';
import '../models/order_model.dart';
import '../services/auth_service.dart';
import '../services/social_auth_service.dart';
import '../services/order_service.dart';
import '../services/api_service.dart';
import '../config/api_config.dart';

class AuthProvider with ChangeNotifier {
  final AuthService _authService = AuthService();
  final SocialAuthService _socialAuthService = SocialAuthService();

  UserModel? _user;
  bool _isAuthenticated = false;
  bool _isLoading = false;
  String? _error;

  UserModel? get user => _user;
  bool get isAuthenticated => _isAuthenticated;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isSeller => _user?.isSeller ?? false;

  Future<void> checkAuthStatus() async {
    _isLoading = true;
    notifyListeners();

    try {
      _isAuthenticated = await _authService.isAuthenticated();
      if (_isAuthenticated) {
        _user = await _authService.getCurrentUser();

        // Rafraîchir les données utilisateur depuis le serveur
        final response = await _authService.getMe();
        if (response['success'] && response['user'] != null) {
          _user = UserModel.fromJson(response['user']);
        }
      }
    } catch (e) {
      _error = e.toString();
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<Map<String, dynamic>> register({
    required String nom,
    required String prenoms,
    required String email,
    required String password,
    required String telephone,
    bool newsletter = false,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _authService.register(
        nom: nom,
        prenoms: prenoms,
        email: email,
        password: password,
        telephone: telephone,
        newsletter: newsletter,
      );

      if (response['success']) {
        if (response['user'] != null) {
          _user = UserModel.fromJson(response['user']);
        }
        _isAuthenticated = true;
      } else {
        _error = response['message'];
      }

      _isLoading = false;
      notifyListeners();
      return response;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    print('🔵 [AUTH_PROVIDER] Début login pour: $email');
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      print('🔄 [AUTH_PROVIDER] Appel authService.login()');
      final response = await _authService.login(
        email: email,
        password: password,
      );

      print('📊 [AUTH_PROVIDER] Réponse API: ${response['success']}');
      print('📊 [AUTH_PROVIDER] Message: ${response['message']}');

      if (response['success']) {
        print('✅ [AUTH_PROVIDER] Login API réussi');
        print('📊 [AUTH_PROVIDER] Requires code: ${response['requires_code'] ?? false}');
        
        // Si pas de code requis, traiter la connexion comme complète
        if (response['requires_code'] != true) {
          print('✅ [AUTH_PROVIDER] Connexion directe sans code');
          
          if (response['user'] != null) {
            _user = UserModel.fromJson(response['user']);
            print('✅ [AUTH_PROVIDER] User sauvegardé: ${_user!.email}');
          }
          
          if (response['token'] != null) {
            print('✅ [AUTH_PROVIDER] Token reçu et sauvegardé');
            // Le token est déjà sauvegardé par AuthService
            
            // Rafraîchir les données utilisateur depuis le serveur
            try {
              final meResponse = await _authService.getMe();
              if (meResponse['success'] && meResponse['user'] != null) {
                _user = UserModel.fromJson(meResponse['user']);
                print('✅ [AUTH_PROVIDER] Données utilisateur rafraîchies depuis /api/me');
              }
            } catch (e) {
              print('⚠️ [AUTH_PROVIDER] Erreur lors du rafraîchissement: $e');
            }
            
            // Envoyer le token FCM pending s'il existe
            await _sendPendingFcmToken();
          }
          
          _isAuthenticated = true;
        } else {
          print('📧 [AUTH_PROVIDER] Code de vérification requis');
        }
      } else {
        print('❌ [AUTH_PROVIDER] Login API échoué');
        _error = response['message'];
      }

      _isLoading = false;
      notifyListeners();
      return response;
    } catch (e) {
      print('❌ [AUTH_PROVIDER] Exception: $e');
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<Map<String, dynamic>> verifyLoginCode({
    required String email,
    required String code,
  }) async {
    print('🔵 [AUTH_PROVIDER] Début vérification code pour: $email');
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _authService.verifyLoginCode(
        email: email,
        code: code,
      );

      print(
        '📊 [AUTH_PROVIDER] Réponse verifyLoginCode: ${response['success']}',
      );
      print('📊 [AUTH_PROVIDER] Message: ${response['message']}');
      print('🔑 [AUTH_PROVIDER] Token présent: ${response['token'] != null}');
      print('👤 [AUTH_PROVIDER] User présent: ${response['user'] != null}');

      if (response['success']) {
        if (response['user'] != null) {
          _user = UserModel.fromJson(response['user']);
          print('✅ [AUTH_PROVIDER] User sauvegardé: ${_user!.email}');
          print('🔍 [AUTH_PROVIDER] User isSeller: ${_user!.isSeller}');
        }
        if (response['token'] != null) {
          print('✅ [AUTH_PROVIDER] Token reçu et sauvegardé');
          
          // Rafraîchir les données utilisateur depuis le serveur pour avoir is_seller à jour
          try {
            final meResponse = await _authService.getMe();
            if (meResponse['success'] && meResponse['user'] != null) {
              _user = UserModel.fromJson(meResponse['user']);
              print('✅ [AUTH_PROVIDER] Données utilisateur rafraîchies depuis /api/me');
              print('🔍 [AUTH_PROVIDER] User isSeller après refresh: ${_user!.isSeller}');
            }
          } catch (e) {
            print('⚠️ [AUTH_PROVIDER] Erreur lors du rafraîchissement: $e');
          }
          
          // Envoyer le token FCM pending s'il existe
          await _sendPendingFcmToken();
        } else {
          print('⚠️ [AUTH_PROVIDER] Token manquant dans la réponse!');
        }
        _isAuthenticated = true;
      } else {
        print('❌ [AUTH_PROVIDER] Vérification échouée');
        _error = response['message'];
      }

      _isLoading = false;
      notifyListeners();
      return response;
    } catch (e) {
      print('❌ [AUTH_PROVIDER] Exception: $e');
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<void> logout() async {
    _isLoading = true;
    notifyListeners();

    await _authService.logout();

    _user = null;
    _isAuthenticated = false;
    _error = null;

    _isLoading = false;
    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }

  /// Envoyer le token FCM pending après connexion
  Future<void> _sendPendingFcmToken() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final pendingToken = prefs.getString('pending_fcm_token');
      
      if (pendingToken != null) {
        print('📤 [AUTH_PROVIDER] Envoi du token FCM pending après connexion');
        
        final apiService = ApiService();
        final response = await apiService.post(
          '${ApiConfig.baseUrl}/notifications/register-token',
          {
            'token': pendingToken,
            'platform': Platform.isAndroid ? 'android' : 'ios',
            'device_name': Platform.isAndroid ? 'Android Device' : 'iOS Device',
            'device_model': Platform.isAndroid
                ? 'Android ${Platform.version}'
                : 'iOS ${Platform.version}',
          },
          requiresAuth: true,
        );
        
        if (response['success'] == true) {
          print('✅ [AUTH_PROVIDER] Token FCM pending enregistré avec succès');
          await prefs.remove('pending_fcm_token');
        } else {
          print('⚠️ [AUTH_PROVIDER] Erreur enregistrement token pending: ${response['message']}');
        }
      }
    } catch (e) {
      print('❌ [AUTH_PROVIDER] Erreur envoi token FCM pending: $e');
    }
  }

  /// Mettre à jour le profil utilisateur
  Future<Map<String, dynamic>> updateProfile(Map<String, dynamic> data) async {
    try {
      print('🔄 [PROFIL] Mise à jour du profil');
      print('📊 [PROFIL] Données reçues: $data');

      // Ajouter l'email de l'utilisateur courant si non fourni
      final email = data['email'] ?? _user?.email ?? '';

      print('📧 [PROFIL] Email utilisé: $email');

      // Convertir les données du Map aux paramètres nommés
      final response = await _authService.updateProfile(
        nom: data['name']?.toString().split(' ').last ?? '',
        prenoms: data['name']?.toString().split(' ').first ?? '',
        email: email,
        telephone: data['telephone'] ?? '',
        adresse: data['adresse']?.toString().trim().isEmpty == true
            ? null
            : data['adresse']?.toString().trim(),
        ville: data['ville']?.toString().trim().isEmpty == true
            ? null
            : data['ville']?.toString().trim(),
        codePostal: data['code_postal']?.toString().trim().isEmpty == true
            ? null
            : data['code_postal']?.toString().trim(),
        pays: data['pays']?.toString().trim().isEmpty == true
            ? null
            : data['pays']?.toString().trim(),
        bio: data['bio']?.toString().trim().isEmpty == true
            ? null
            : data['bio']?.toString().trim(),
      );

      print('📥 [PROFIL] Réponse API: $response');

      if (response['success'] && response['user'] != null) {
        _user = UserModel.fromJson(response['user']);
        notifyListeners();
        print('✅ [PROFIL] Profil mis à jour avec succès');
      } else {
        print('❌ [PROFIL] Erreur API: ${response['message']}');
      }

      return response;
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Changer le mot de passe
  Future<Map<String, dynamic>> changePassword(
    String oldPassword,
    String newPassword,
  ) async {
    try {
      return await _authService.changePassword(
        currentPassword: oldPassword,
        newPassword: newPassword,
      );
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Mettre à jour la photo de profil
  Future<Map<String, dynamic>> updateProfilePhoto(File photo) async {
    try {
      final response = await _authService.updateProfilePhoto(photo);
      
      // Si l'upload réussit, mettre à jour l'utilisateur avec les données de la réponse
      if (response['success']) {
        // D'abord, essayer d'utiliser les données de la réponse si disponibles
        if (response['user'] != null) {
          _user = UserModel.fromJson(response['user']);
          print('✅ [AUTH_PROVIDER] Photo de profil mise à jour depuis la réponse');
          print('📸 [AUTH_PROVIDER] Nouvelle URL: ${_user?.profilePicUrl}');
          notifyListeners();
        } else {
          // Sinon, recharger depuis l'API
          final meResponse = await _authService.getMe();
          if (meResponse['success'] && meResponse['user'] != null) {
            _user = UserModel.fromJson(meResponse['user']);
            print('✅ [AUTH_PROVIDER] Photo de profil mise à jour et utilisateur rechargé depuis /me');
            print('📸 [AUTH_PROVIDER] Nouvelle URL: ${_user?.profilePicUrl}');
            notifyListeners();
          }
        }
      }
      
      return response;
    } catch (e) {
      print('❌ [AUTH_PROVIDER] Erreur updateProfilePhoto: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Recharger les données utilisateur depuis le serveur
  Future<void> loadUser() async {
    try {
      final response = await _authService.getMe();
      if (response['success'] && response['user'] != null) {
        _user = UserModel.fromJson(response['user']);
        notifyListeners();
      }
    } catch (e) {
      print('❌ [AUTH_PROVIDER] Erreur loadUser: $e');
    }
  }

  /// Obtenir les commandes de l'utilisateur
  Future<Map<String, dynamic>> getMyOrders() async {
    try {
      return await _authService.getMyOrders();
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Obtenir les favoris de l'utilisateur
  Future<Map<String, dynamic>> getFavorites() async {
    try {
      return await _authService.getFavorites();
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Ajouter/Retirer un favori
  Future<Map<String, dynamic>> toggleFavorite(int productId) async {
    try {
      return await _authService.toggleFavorite(productId);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Renvoyer le code de vérification
  Future<Map<String, dynamic>> resendVerificationCode(String email) async {
    try {
      print('🔄 [AUTH] Renvoi du code de vérification pour: $email');

      final response = await _authService.resendVerificationCode(email: email);

      if (response['success']) {
        print('✅ [AUTH] Code renvoyé avec succès');
      } else {
        print('❌ [AUTH] Erreur renvoi code: ${response['message']}');
      }

      return response;
    } catch (e) {
      print('💥 [AUTH] Exception renvoi code: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Demander la réinitialisation du mot de passe
  Future<Map<String, dynamic>> forgotPassword(String email) async {
    try {
      print('🔄 [AUTH] Demande de réinitialisation pour: $email');

      final response = await _authService.forgotPassword(email: email);

      if (response['success']) {
        print('✅ [AUTH] Email de réinitialisation envoyé');
      } else {
        print('❌ [AUTH] Erreur réinitialisation: ${response['message']}');
      }

      return response;
    } catch (e) {
      print('💥 [AUTH] Exception réinitialisation: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Réinitialiser le mot de passe avec le token
  Future<Map<String, dynamic>> resetPassword({
    required String email,
    required String token,
    required String password,
  }) async {
    try {
      print('🔄 [AUTH] Réinitialisation du mot de passe pour: $email');

      final response = await _authService.resetPassword(
        email: email,
        token: token,
        password: password,
      );

      if (response['success']) {
        print('✅ [AUTH] Mot de passe réinitialisé avec succès');
      } else {
        print('❌ [AUTH] Erreur réinitialisation: ${response['message']}');
      }

      return response;
    } catch (e) {
      print('💥 [AUTH] Exception réinitialisation: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Connexion avec Google
  Future<Map<String, dynamic>> signInWithGoogle() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      print('🔄 [AUTH_PROVIDER] Début connexion Google');
      final response = await _socialAuthService.signInWithGoogle();

      if (response['success']) {
        if (response['user'] != null) {
          _user = UserModel.fromJson(response['user']);
        }
        _isAuthenticated = true;
        print('✅ [AUTH_PROVIDER] Connexion Google réussie');
      } else {
        _error = response['message'];
        print('❌ [AUTH_PROVIDER] Erreur Google: ${response['message']}');
      }

      _isLoading = false;
      notifyListeners();
      return response;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      print('💥 [AUTH_PROVIDER] Exception Google: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Connexion avec Facebook
  Future<Map<String, dynamic>> signInWithFacebook() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      print('🔄 [AUTH_PROVIDER] Début connexion Facebook');
      final response = await _socialAuthService.signInWithFacebook();

      if (response['success']) {
        if (response['user'] != null) {
          _user = UserModel.fromJson(response['user']);
        }
        _isAuthenticated = true;
        print('✅ [AUTH_PROVIDER] Connexion Facebook réussie');
      } else {
        _error = response['message'];
        print('❌ [AUTH_PROVIDER] Erreur Facebook: ${response['message']}');
      }

      _isLoading = false;
      notifyListeners();
      return response;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      print('💥 [AUTH_PROVIDER] Exception Facebook: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Récupérer les commandes de l'utilisateur
  Future<List<OrderModel>> getUserOrders() async {
    try {
      final orderService = OrderService();
      final response = await orderService.getUserOrders();

      print('📊 [AUTH] Réponse getUserOrders: ${response['success']}');
      print('📦 [AUTH] Clés disponibles: ${response.keys}');

      if (response['success'] && response['orders'] != null) {
        final List<dynamic> ordersData = response['orders'];
        print('📦 [AUTH] Nombre de commandes brutes: ${ordersData.length}');
        
        final orders = ordersData.map((json) {
          try {
            return OrderModel.fromJson(json);
          } catch (e) {
            print('❌ [AUTH] Erreur parsing commande: $e');
            print('📄 [AUTH] JSON: $json');
            return null;
          }
        }).whereType<OrderModel>().toList();
        
        print('✅ [AUTH] Commandes parsées: ${orders.length}');
        return orders;
      } else {
        print('⚠️ [AUTH] Pas de commandes trouvées: ${response['message']}');
        return [];
      }
    } catch (e) {
      print('💥 [AUTH] Exception récupération commandes: $e');
      return [];
    }
  }
}
