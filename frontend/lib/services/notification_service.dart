import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter/material.dart';
import 'dart:io';
import 'dart:convert';
import 'dart:async';
import 'api_service.dart';
import '../config/api_config.dart';

/// 🔔 Service de gestion des notifications push Firebase
class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;
  NotificationService._internal();

  final FirebaseMessaging _firebaseMessaging = FirebaseMessaging.instance;
  final ApiService _apiService = ApiService();
  
  String? _fcmToken;
  bool _isInitialized = false;

  /// Initialiser le service de notifications
  Future<void> initialize({BuildContext? context}) async {
    if (_isInitialized) {
      print('🔔 [NOTIFICATION_SERVICE] Déjà initialisé');
      return;
    }

    try {
      // ✅ Vérifier l'état actuel de la permission
      final permissionStatus = await _firebaseMessaging.getNotificationSettings();
      
      // Si la permission est déjà accordée, continuer directement
      if (permissionStatus.authorizationStatus == AuthorizationStatus.authorized) {
        print('🔔 [NOTIFICATION_SERVICE] Permission déjà accordée');
      } 
      // Si la permission n'est pas encore déterminée et qu'on a un contexte, afficher le dialog
      else if (permissionStatus.authorizationStatus == AuthorizationStatus.notDetermined && context != null) {
        final prefs = await SharedPreferences.getInstance();
        final permissionDialogShown = prefs.getBool('notification_permission_dialog_shown') ?? false;
        
        // Ne pas réafficher le dialog si déjà montré
        if (permissionDialogShown) {
          print('🔔 [NOTIFICATION_SERVICE] Dialog déjà affiché, demande de permission système uniquement');
        } else {
          // Le dialog sera affiché par MainScreen, on attend juste ici
          print('🔔 [NOTIFICATION_SERVICE] Dialog sera affiché par MainScreen');
          return; // Le dialog sera géré par MainScreen
        }
      }
      // Si la permission a été refusée, ne pas continuer
      else if (permissionStatus.authorizationStatus == AuthorizationStatus.denied) {
        print('🔔 [NOTIFICATION_SERVICE] Permission refusée par l\'utilisateur');
        return;
      }

      // ✅ Demander la permission système (iOS et Android 13+)
      // Utiliser un délai pour éviter de bloquer l'UI
      await Future.delayed(const Duration(milliseconds: 200));
      
      final settings = await _firebaseMessaging.requestPermission(
        alert: true,
        announcement: false,
        badge: true,
        carPlay: false,
        criticalAlert: false,
        provisional: false,
        sound: true,
      );

      if (settings.authorizationStatus == AuthorizationStatus.authorized) {
        print('🔔 [NOTIFICATION_SERVICE] Permission accordée');
      } else if (settings.authorizationStatus == AuthorizationStatus.provisional) {
        print('🔔 [NOTIFICATION_SERVICE] Permission provisoire');
      } else {
        print('🔔 [NOTIFICATION_SERVICE] Permission refusée (status: ${settings.authorizationStatus})');
        return;
      }

      // ✅ Obtenir le token FCM
      _fcmToken = await _firebaseMessaging.getToken();
      if (_fcmToken != null) {
        print('🔔 [NOTIFICATION_SERVICE] Token FCM: ${_fcmToken!.substring(0, 20)}...');
        await _registerTokenWithBackend(_fcmToken!);
      }

      // ✅ Écouter les refreshs de token
      _firebaseMessaging.onTokenRefresh.listen((newToken) {
        print('🔔 [NOTIFICATION_SERVICE] Token rafraîchi');
        _fcmToken = newToken;
        _registerTokenWithBackend(newToken);
      });

      // ✅ Configurer les handlers de notifications
      _configureNotificationHandlers();

      _isInitialized = true;
      print('✅ [NOTIFICATION_SERVICE] Initialisé avec succès');
    } catch (e) {
      print('❌ [NOTIFICATION_SERVICE] Erreur d\'initialisation: $e');
    }
  }

  /// Configurer les handlers pour les notifications
  void _configureNotificationHandlers() {
    // 📱 Notification reçue en foreground (app ouverte)
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      print('🔔 [NOTIFICATION_SERVICE] Message reçu (foreground)');
      print('   - Title: ${message.notification?.title}');
      print('   - Body: ${message.notification?.body}');
      print('   - Data: ${message.data}');

      _handleNotification(message, isForeground: true);
    });

    // 🖱️ Notification cliquée (app en background ou terminée)
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      print('🔔 [NOTIFICATION_SERVICE] Notification cliquée (background)');
      _handleNotificationTap(message);
    });

    // 🚀 Vérifier si l'app a été lancée depuis une notification
    _checkInitialMessage();
  }

  /// Vérifier si l'app a été lancée depuis une notification
  Future<void> _checkInitialMessage() async {
    final RemoteMessage? initialMessage = 
        await _firebaseMessaging.getInitialMessage();

    if (initialMessage != null) {
      print('🔔 [NOTIFICATION_SERVICE] App lancée depuis notification');
      _handleNotificationTap(initialMessage);
    }
  }

  /// Gérer une notification reçue
  void _handleNotification(RemoteMessage message, {bool isForeground = false}) {
    // Sauvegarder la notification localement
    _saveNotificationLocally(message);

    // Si en foreground, afficher une notification locale (optionnel)
    if (isForeground) {
      // Vous pouvez utiliser flutter_local_notifications ici
      // pour afficher une notification système
    }
  }

  /// Gérer le tap sur une notification
  void _handleNotificationTap(RemoteMessage message) {
    final data = message.data;
    final type = data['type'] as String?;

    print('🔔 [NOTIFICATION_SERVICE] Navigation vers: $type');

    // La navigation sera gérée par le widget principal
    // en écoutant les streams de notifications
    _notificationTapController.add(data);
  }

  /// Stream controller pour les taps de notifications
  final _notificationTapController = 
      StreamController<Map<String, dynamic>>.broadcast();

  /// Stream des taps de notifications
  Stream<Map<String, dynamic>> get onNotificationTap => 
      _notificationTapController.stream;

  /// Enregistrer le token FCM sur le backend
  Future<void> _registerTokenWithBackend(String token) async {
    try {
      // Obtenir les informations de l'appareil
      final prefs = await SharedPreferences.getInstance();
      final authToken = prefs.getString('token');

      if (authToken == null) {
        print('⚠️ [NOTIFICATION_SERVICE] Utilisateur non connecté, token FCM sauvegardé en attente');
        // Sauvegarder le token en attente pour l'envoyer après connexion
        await prefs.setString('pending_fcm_token', token);
        return;
      }

      final response = await _apiService.post(
        '${ApiConfig.baseUrl}/notifications/register-token',
        {
          'token': token,
          'platform': Platform.isAndroid ? 'android' : 'ios',
          'device_name': Platform.isAndroid ? 'Android Device' : 'iOS Device',
          'device_model': Platform.isAndroid
              ? 'Android ${Platform.operatingSystemVersion}'
              : 'iOS ${Platform.operatingSystemVersion}',
        },
        requiresAuth: true,
      );

      if (response['success'] == true) {
        print('✅ [NOTIFICATION_SERVICE] Token FCM enregistré sur le backend');
        // Supprimer le token pending s'il existait
        await prefs.remove('pending_fcm_token');
      } else {
        print('❌ [NOTIFICATION_SERVICE] Erreur enregistrement: ${response['message']}');
      }
    } catch (e) {
      print('❌ [NOTIFICATION_SERVICE] Exception enregistrement: $e');
    }
  }

  /// Sauvegarder une notification localement
  Future<void> _saveNotificationLocally(RemoteMessage message) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final notifications = prefs.getStringList('local_notifications') ?? [];

      final notificationData = {
        'title': message.notification?.title ?? '',
        'body': message.notification?.body ?? '',
        'data': message.data,
        'timestamp': DateTime.now().toIso8601String(),
      };

      notifications.insert(0, jsonEncode(notificationData));

      // Garder seulement les 50 dernières
      if (notifications.length > 50) {
        notifications.removeRange(50, notifications.length);
      }

      await prefs.setStringList('local_notifications', notifications);
      print('✅ [NOTIFICATION_SERVICE] Notification sauvegardée localement');
    } catch (e) {
      print('❌ [NOTIFICATION_SERVICE] Erreur sauvegarde locale: $e');
    }
  }

  /// Obtenir les notifications locales
  Future<List<Map<String, dynamic>>> getLocalNotifications() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final notifications = prefs.getStringList('local_notifications') ?? [];

      return notifications.map((notif) {
        return jsonDecode(notif) as Map<String, dynamic>;
      }).toList();
    } catch (e) {
      print('❌ [NOTIFICATION_SERVICE] Erreur récupération locale: $e');
      return [];
    }
  }

  /// Obtenir le token FCM actuel
  String? get fcmToken => _fcmToken;

  /// Désactiver les notifications (désenregistrer le token)
  Future<void> unregisterToken() async {
    if (_fcmToken == null) return;

    try {
      final prefs = await SharedPreferences.getInstance();
      final authToken = prefs.getString('token');

      if (authToken != null) {
        try {
          await _apiService.post(
            '${ApiConfig.baseUrl}/notifications/unregister-token',
            {'token': _fcmToken},
            requiresAuth: true,
          );
          print('✅ [NOTIFICATION_SERVICE] Token désenregistré du backend');
        } catch (e) {
          print('⚠️ [NOTIFICATION_SERVICE] Erreur désenregistrement backend: $e');
        }
      }

      await _firebaseMessaging.deleteToken();
      _fcmToken = null;
      print('✅ [NOTIFICATION_SERVICE] Token FCM supprimé localement');
    } catch (e) {
      print('❌ [NOTIFICATION_SERVICE] Erreur désenregistrement: $e');
    }
  }

  /// Fermer les streams
  void dispose() {
    _notificationTapController.close();
  }
}

/// Handler global pour les notifications en background
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  print('🔔 [BACKGROUND_HANDLER] Message reçu en background');
  print('   - Title: ${message.notification?.title}');
  print('   - Body: ${message.notification?.body}');
}

