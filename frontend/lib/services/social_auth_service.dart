import 'package:flutter_facebook_auth/flutter_facebook_auth.dart';
import 'package:google_sign_in/google_sign_in.dart';
import '../config/api_config.dart';
import 'api_service.dart';
import 'storage_service.dart';

class SocialAuthService {
  final ApiService _apiService = ApiService();
  final StorageService _storageService = StorageService();
  
  // GoogleSignIn sera initialisé avec le client ID si disponible
  GoogleSignIn get _googleSignIn {
    // Pour Android, Google Sign-In utilise automatiquement le google-services.json
    // Si vous avez un client ID spécifique, vous pouvez le passer ici :
    // return GoogleSignIn(
    //   scopes: ['email', 'profile'],
    //   serverClientId: 'YOUR_WEB_CLIENT_ID', // Optionnel pour obtenir un idToken
    // );
    return GoogleSignIn(
      scopes: ['email', 'profile'],
    );
  }

  /// Connexion avec Google
  Future<Map<String, dynamic>> signInWithGoogle() async {
    try {
      print('🔄 [SOCIAL_AUTH] Début connexion Google');
      
      // Lancer la connexion Google
      final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();
      
      if (googleUser == null) {
        print('❌ [SOCIAL_AUTH] Connexion Google annulée par l\'utilisateur');
        return {
          'success': false,
          'message': 'Connexion Google annulée',
        };
      }

      print('✅ [SOCIAL_AUTH] Google user obtenu: ${googleUser.email}');
      
      // Obtenir les détails d'authentification
      final GoogleSignInAuthentication googleAuth = await googleUser.authentication;
      
      if (googleAuth.accessToken == null) {
        print('❌ [SOCIAL_AUTH] Access token Google manquant');
        return {
          'success': false,
          'message': 'Erreur: Token d\'accès Google manquant',
        };
      }
      
      print('✅ [SOCIAL_AUTH] Access token obtenu, envoi au backend...');
      
      // Envoyer les données au backend
      final response = await _apiService.post(
        ApiConfig.socialAuth('google'),
        {
          'access_token': googleAuth.accessToken,
          'id': googleUser.id,
          'email': googleUser.email,
          'name': googleUser.displayName ?? '',
          'avatar': googleUser.photoUrl,
        },
      );

      if (response['success'] && response['token'] != null) {
        print('✅ [SOCIAL_AUTH] Token reçu, sauvegarde...');
        await _storageService.saveToken(response['token']);
        if (response['user'] != null) {
          await _storageService.saveUser(response['user']);
        }
      }

      return response;
    } catch (e) {
      print('❌ [SOCIAL_AUTH] Erreur Google: $e');
      
      String errorMessage = 'Erreur lors de la connexion Google';
      
      // Messages d'erreur plus explicites
      if (e.toString().contains('ApiException: 10')) {
        errorMessage = 'Configuration Google Sign-In incorrecte. Veuillez vérifier le SHA-1 dans Firebase Console.';
      } else if (e.toString().contains('sign_in_failed')) {
        errorMessage = 'Échec de la connexion Google. Vérifiez votre configuration OAuth.';
      } else {
        errorMessage = 'Erreur lors de la connexion Google: ${e.toString()}';
      }
      
      return {
        'success': false,
        'message': errorMessage,
      };
    }
  }

  /// Connexion avec Facebook
  Future<Map<String, dynamic>> signInWithFacebook() async {
    try {
      print('🔄 [SOCIAL_AUTH] Début connexion Facebook');
      
      // Lancer la connexion Facebook
      final LoginResult result = await FacebookAuth.instance.login(
        permissions: ['email', 'public_profile'],
      );

      if (result.status != LoginStatus.success) {
        print('❌ [SOCIAL_AUTH] Connexion Facebook échouée: ${result.status}');
        return {
          'success': false,
          'message': 'Connexion Facebook échouée',
        };
      }

      print('✅ [SOCIAL_AUTH] Facebook login réussi, récupération des données...');
      
      // Obtenir les données utilisateur
      final userData = await FacebookAuth.instance.getUserData(
        fields: 'email,name,picture.width(200).height(200)',
      );

      final accessToken = result.accessToken?.tokenString;
      final userId = userData['id'] as String?;
      final email = userData['email'] as String?;
      final name = userData['name'] as String?;
      final picture = userData['picture'] as Map<String, dynamic>?;
      final avatarUrl = picture?['data']?['url'] as String?;

      if (accessToken == null || userId == null || email == null || name == null) {
        print('❌ [SOCIAL_AUTH] Données Facebook incomplètes');
        return {
          'success': false,
          'message': 'Données Facebook incomplètes',
        };
      }

      print('✅ [SOCIAL_AUTH] Données Facebook obtenues: $email');
      
      // Envoyer les données au backend
      final response = await _apiService.post(
        ApiConfig.socialAuth('facebook'),
        {
          'access_token': accessToken,
          'id': userId,
          'email': email,
          'name': name,
          'avatar': avatarUrl,
        },
      );

      if (response['success'] && response['token'] != null) {
        print('✅ [SOCIAL_AUTH] Token reçu, sauvegarde...');
        await _storageService.saveToken(response['token']);
        if (response['user'] != null) {
          await _storageService.saveUser(response['user']);
        }
      }

      return response;
    } catch (e) {
      print('❌ [SOCIAL_AUTH] Erreur Facebook: $e');
      return {
        'success': false,
        'message': 'Erreur lors de la connexion Facebook: ${e.toString()}',
      };
    }
  }

  /// Déconnexion Google
  Future<void> signOutGoogle() async {
    try {
      await _googleSignIn.signOut();
    } catch (e) {
      print('Erreur lors de la déconnexion Google: $e');
    }
  }

  /// Déconnexion Facebook
  Future<void> signOutFacebook() async {
    try {
      await FacebookAuth.instance.logOut();
    } catch (e) {
      print('Erreur lors de la déconnexion Facebook: $e');
    }
  }
}
