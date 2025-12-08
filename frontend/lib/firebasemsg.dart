import 'dart:io';
import 'dart:convert';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter/material.dart';
import 'services/local_notification_service.dart';
import 'config/api_config.dart';

class FirebaseMsg {
  final msgService = FirebaseMessaging.instance;
  final LocalNotificationService _localNotificationService =
      LocalNotificationService();

  initFCM({BuildContext? context}) async {
    try {
      // Initialiser les notifications locales
      await _localNotificationService.initialize();

      // Vérifier l'état actuel de la permission
      final settings = await msgService.getNotificationSettings();
      
      // Si la permission n'est pas encore accordée, le dialog sera affiché par MainScreen
      // On ne demande pas directement la permission ici pour laisser le dialog personnalisé s'afficher
      if (settings.authorizationStatus == AuthorizationStatus.authorized) {
        print('✅ [FIREBASE] Permission déjà accordée');
      } else {
        print('ℹ️ [FIREBASE] Permission pas encore accordée, le dialog sera affiché');
      }

      // Obtenir le token FCM
      var token = await msgService.getToken();

      print("🔥 FIREBASE TOKEN : $token");

      // Sauvegarder le token dans l'API backend
      if (token != null) {
        await _saveTokenToBackend(token);
      }

      // Gérer les notifications en arrière-plan
      FirebaseMessaging.onBackgroundMessage(handleNotification);

      // Gérer les notifications quand l'app est au premier plan
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        print("📩 NOTIFICATION REÇUE (App au premier plan)");
        handleNotificationForeground(message);
      });

      // Gérer le tap sur une notification quand l'app était fermée
      FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
        print("🔔 App ouverte depuis une notification");
        // Naviguer vers l'écran approprié
      });
    } catch (e) {
      print("⚠️ Firebase Messaging non disponible: $e");
      print("ℹ️ L'application continuera sans les notifications push");
      // L'app continue de fonctionner même si Firebase échoue
    }
  }

  /// Gérer les notifications quand l'app est ouverte
  void handleNotificationForeground(RemoteMessage message) {
    print("📌 Titre: ${message.notification?.title ?? 'Sans titre'}");
    print("📝 Message: ${message.notification?.body ?? 'Sans message'}");
    print("📦 Data: ${message.data}");

    // Afficher la notification visuellement
    _localNotificationService.showNotificationFromFirebase(message);
  }

  /// Sauvegarder le token FCM dans le backend
  Future<void> _saveTokenToBackend(String token) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final userToken = prefs.getString('token');

      if (userToken == null) {
        print('⚠️ Utilisateur non connecté, token non sauvegardé');
        // Sauvegarder le token localement pour l'envoyer après connexion
        await prefs.setString('pending_fcm_token', token);
        return;
      }

      // ✅ Utiliser la nouvelle API
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/notifications/register-token'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $userToken',
        },
        body: json.encode({
          'token': token,
          'platform': Platform.isAndroid ? 'android' : 'ios',
          'device_name': Platform.isAndroid ? 'Android Device' : 'iOS Device',
          'device_model': Platform.isAndroid
              ? 'Android ${Platform.version}'
              : 'iOS ${Platform.version}',
        }),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          print('✅ Token FCM enregistré dans le backend');
          // Supprimer le token pending s'il existait
          await prefs.remove('pending_fcm_token');
        } else {
          print('⚠️ Erreur enregistrement token: ${data['message']}');
        }
      } else {
        print('⚠️ Erreur HTTP ${response.statusCode}');
      }
    } catch (e) {
      print('❌ Erreur sauvegarde token: $e');
    }
  }
}

/// Gérer les notifications en arrière-plan
@pragma('vm:entry-point')
Future<void> handleNotification(RemoteMessage msg) async {
  print("📩 NOTIFICATION REÇUE (Background)!");
  print("📌 Titre: ${msg.notification?.title ?? 'Sans titre'}");
  print("📝 Message: ${msg.notification?.body ?? 'Sans message'}");
  print("📦 Data: ${msg.data}");

  // Les notifications en arrière-plan s'affichent automatiquement sur Android
  // Pour iOS et plus de contrôle, utilisez LocalNotificationService
  final localNotificationService = LocalNotificationService();
  await localNotificationService.initialize();
  await localNotificationService.showNotificationFromFirebase(msg);
}
