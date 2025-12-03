import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import '../providers/cart_provider.dart';
import '../widgets/cart_reminder_dialog.dart';

/// 🔔 Service de rappel du panier
/// Rappelle à l'utilisateur ses articles non validés après 5 minutes
class CartReminderService {
  static final CartReminderService _instance = CartReminderService._internal();
  factory CartReminderService() => _instance;
  CartReminderService._internal();

  Timer? _reminderTimer;
  DateTime? _lastCartUpdate;
  final FlutterLocalNotificationsPlugin _notifications = FlutterLocalNotificationsPlugin();
  bool _isInitialized = false;
  BuildContext? _context; // Context pour afficher le dialog

  /// Initialiser le service de notifications
  Future<void> initialize() async {
    if (_isInitialized) return;

    try {
      const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
      const iosSettings = DarwinInitializationSettings(
        requestAlertPermission: true,
        requestBadgePermission: true,
        requestSoundPermission: true,
      );

      const initSettings = InitializationSettings(
        android: androidSettings,
        iOS: iosSettings,
      );

      await _notifications.initialize(
        initSettings,
        onDidReceiveNotificationResponse: _onNotificationTapped,
      );

      _isInitialized = true;
      print('✅ [CART_REMINDER] Service initialisé');
    } catch (e) {
      print('❌ [CART_REMINDER] Erreur initialisation: $e');
    }
  }

  /// Démarrer le rappel après ajout au panier
  void startReminder(CartProvider cartProvider) {
    // Annuler le timer précédent
    _reminderTimer?.cancel();

    // Mettre à jour la date de dernière modification
    _lastCartUpdate = DateTime.now();

    // Si le panier est vide, ne pas créer de rappel
    if (cartProvider.items.isEmpty) {
      print('📭 [CART_REMINDER] Panier vide, pas de rappel');
      return;
    }

    // Créer un nouveau timer de 5 minutes
    _reminderTimer = Timer(const Duration(minutes: 5), () {
      _checkAndNotify(cartProvider);
    });

    print('⏰ [CART_REMINDER] Rappel programmé dans 5 minutes');
  }

  /// Définir le contexte pour le dialog
  void setContext(BuildContext context) {
    _context = context;
  }

  /// Vérifier le panier et notifier si nécessaire
  Future<void> _checkAndNotify(CartProvider cartProvider) async {
    // Vérifier si le panier contient toujours des articles
    if (cartProvider.items.isEmpty) {
      print('📭 [CART_REMINDER] Panier vide, annulation du rappel');
      return;
    }

    // Calculer le temps écoulé
    final elapsed = DateTime.now().difference(_lastCartUpdate ?? DateTime.now());
    
    if (elapsed.inMinutes >= 5) {
      // Afficher le dialog si le context est disponible (app au premier plan)
      if (_context != null && _context!.mounted) {
        CartReminderDialog.show(_context!, cartProvider.items);
        print('🔔 [CART_REMINDER] Dialog affiché');
      } else {
        // Sinon afficher une notification système
        await _showCartReminder(cartProvider);
      }
    }
  }

  /// Afficher la notification de rappel
  Future<void> _showCartReminder(CartProvider cartProvider) async {
    if (!_isInitialized) {
      await initialize();
    }

    try {
      final itemCount = cartProvider.items.length;
      final productNames = cartProvider.items
          .take(2)
          .map((item) => item.product?.name ?? 'Produit')
          .join(', ');
      
      final moreItems = itemCount > 2 ? ' et ${itemCount - 2} autre(s)' : '';

      const androidDetails = AndroidNotificationDetails(
        'cart_reminder',
        'Rappels Panier',
        channelDescription: 'Notifications pour les articles non validés dans le panier',
        importance: Importance.high,
        priority: Priority.high,
        icon: '@mipmap/ic_launcher',
        color: Color(0xFFFF6B35),
        enableVibration: true,
        playSound: true,
      );

      const iosDetails = DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
      );

      const details = NotificationDetails(
        android: androidDetails,
        iOS: iosDetails,
      );

      await _notifications.show(
        0,
        '🛒 Votre panier vous attend !',
        '$itemCount article(s) : $productNames$moreItems',
        details,
        payload: 'cart_reminder',
      );

      print('🔔 [CART_REMINDER] Notification affichée: $itemCount articles');
    } catch (e) {
      print('❌ [CART_REMINDER] Erreur notification: $e');
    }
  }

  /// Gérer le tap sur la notification
  void _onNotificationTapped(NotificationResponse response) {
    if (response.payload == 'cart_reminder') {
      print('👆 [CART_REMINDER] Notification tappée - Ouvrir le panier');
      // Navigation gérée par le NavigatorKey dans main.dart
    }
  }

  /// Réinitialiser le timer (appelé quand l'utilisateur modifie le panier)
  void resetTimer(CartProvider cartProvider) {
    _lastCartUpdate = DateTime.now();
    startReminder(cartProvider);
  }

  /// Annuler tous les rappels
  void cancelReminder() {
    _reminderTimer?.cancel();
    _reminderTimer = null;
    _lastCartUpdate = null;
    print('❌ [CART_REMINDER] Rappels annulés');
  }

  /// Annuler toutes les notifications
  Future<void> cancelAllNotifications() async {
    await _notifications.cancelAll();
  }

  /// Nettoyer les ressources
  void dispose() {
    _reminderTimer?.cancel();
    _reminderTimer = null;
  }
}
