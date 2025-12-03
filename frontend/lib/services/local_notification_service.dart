import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

class LocalNotificationService {
  static final LocalNotificationService _instance = LocalNotificationService._internal();
  factory LocalNotificationService() => _instance;
  LocalNotificationService._internal();

  final FlutterLocalNotificationsPlugin _notificationsPlugin = FlutterLocalNotificationsPlugin();
  bool _initialized = false;

  /// Initialiser les notifications locales
  Future<void> initialize() async {
    if (_initialized) return;

    // Configuration Android
    const AndroidInitializationSettings initializationSettingsAndroid =
        AndroidInitializationSettings('@mipmap/ic_launcher');

    // Configuration iOS
    const DarwinInitializationSettings initializationSettingsIOS = DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );

    // Configuration générale
    const InitializationSettings initializationSettings = InitializationSettings(
      android: initializationSettingsAndroid,
      iOS: initializationSettingsIOS,
    );

    await _notificationsPlugin.initialize(
      initializationSettings,
      onDidReceiveNotificationResponse: _onNotificationTap,
    );

    _initialized = true;
    print("✅ Notifications locales initialisées");
  }

  /// Gérer le tap sur une notification
  void _onNotificationTap(NotificationResponse response) {
    print("🔔 Notification tapée : ${response.payload}");
    // Naviguer vers l'écran approprié selon le payload
  }

  /// Afficher une notification locale
  Future<void> showNotification({
    required String title,
    required String body,
    String? payload,
  }) async {
    if (!_initialized) {
      await initialize();
    }

    const AndroidNotificationDetails androidDetails = AndroidNotificationDetails(
      'kazaria_channel', // ID du canal
      'Kazaria Notifications', // Nom du canal
      channelDescription: 'Notifications de Kazaria Marketplace',
      importance: Importance.high,
      priority: Priority.high,
      showWhen: true,
      enableVibration: true,
      playSound: true,
    );

    const DarwinNotificationDetails iOSDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );

    const NotificationDetails notificationDetails = NotificationDetails(
      android: androidDetails,
      iOS: iOSDetails,
    );

    await _notificationsPlugin.show(
      DateTime.now().millisecond, // ID unique
      title,
      body,
      notificationDetails,
      payload: payload,
    );

    print("✅ Notification affichée : $title");
  }

  /// Afficher une notification depuis un message Firebase
  Future<void> showNotificationFromFirebase(RemoteMessage message) async {
    final notification = message.notification;
    
    if (notification == null) {
      print("⚠️ Pas de notification dans le message");
      return;
    }

    await showNotification(
      title: notification.title ?? 'Kazaria',
      body: notification.body ?? '',
      payload: message.data.toString(),
    );
  }

  /// Annuler toutes les notifications
  Future<void> cancelAll() async {
    await _notificationsPlugin.cancelAll();
  }

  /// Annuler une notification spécifique
  Future<void> cancel(int id) async {
    await _notificationsPlugin.cancel(id);
  }
}

