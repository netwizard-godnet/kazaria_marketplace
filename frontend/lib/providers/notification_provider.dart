import 'package:flutter/foundation.dart';
import '../services/api_service.dart';
import '../config/api_config.dart';

class NotificationProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();

  List<Map<String, dynamic>> _notifications = [];
  int _unreadCount = 0;
  bool _isLoading = false;
  String? _error;

  // Getters
  List<Map<String, dynamic>> get notifications => _notifications;
  int get unreadCount => _unreadCount;
  bool get isLoading => _isLoading;
  String? get error => _error;

  /// Charger les notifications depuis l'API
  Future<void> loadNotifications({int page = 1, int perPage = 20}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _apiService.get(
        '${ApiConfig.baseUrl}/notifications?per_page=$perPage&page=$page',
      );

      if (response['success'] == true) {
        final List<dynamic> notificationsData = response['notifications'] ?? [];
        
        if (page == 1) {
          _notifications = notificationsData
              .map((item) => item as Map<String, dynamic>)
              .toList();
        } else {
          _notifications.addAll(
            notificationsData.map((item) => item as Map<String, dynamic>),
          );
        }

        print('✅ [NOTIFICATION_PROVIDER] ${_notifications.length} notifications chargées');
      } else {
        _error = response['message'] ?? 'Erreur de chargement';
        print('❌ [NOTIFICATION_PROVIDER] Erreur: $_error');
      }
    } catch (e) {
      _error = e.toString();
      print('💥 [NOTIFICATION_PROVIDER] Exception: $_error');
    }

    _isLoading = false;
    notifyListeners();
  }

  /// Charger le compteur de notifications non lues
  Future<void> loadUnreadCount() async {
    try {
      final response = await _apiService.get(
        '${ApiConfig.baseUrl}/notifications/unread-count',
      );

      if (response['success'] == true) {
        _unreadCount = response['unread_count'] ?? 0;
        print('✅ [NOTIFICATION_PROVIDER] Notifications non lues: $_unreadCount');
        notifyListeners();
      }
    } catch (e) {
      print('❌ [NOTIFICATION_PROVIDER] Erreur compteur: $e');
    }
  }

  /// Marquer une notification comme lue
  Future<bool> markAsRead(int notificationId) async {
    try {
      final response = await _apiService.post(
        '${ApiConfig.baseUrl}/notifications/$notificationId/mark-as-read',
        {},
        requiresAuth: true,
      );

      if (response['success'] == true) {
        // Mettre à jour localement
        final index = _notifications.indexWhere((n) => n['id'] == notificationId);
        if (index != -1) {
          _notifications[index]['is_read'] = true;
          _unreadCount = (_unreadCount - 1).clamp(0, 999);
          notifyListeners();
        }
        
        print('✅ [NOTIFICATION_PROVIDER] Notification #$notificationId marquée comme lue');
        return true;
      }
      return false;
    } catch (e) {
      print('❌ [NOTIFICATION_PROVIDER] Erreur marquage: $e');
      return false;
    }
  }

  /// Marquer toutes les notifications comme lues
  Future<bool> markAllAsRead() async {
    try {
      final response = await _apiService.post(
        '${ApiConfig.baseUrl}/notifications/mark-all-as-read',
        {},
        requiresAuth: true,
      );

      if (response['success'] == true) {
        // Mettre à jour localement
        for (var notification in _notifications) {
          notification['is_read'] = true;
        }
        _unreadCount = 0;
        notifyListeners();
        
        print('✅ [NOTIFICATION_PROVIDER] Toutes les notifications marquées comme lues');
        return true;
      }
      return false;
    } catch (e) {
      print('❌ [NOTIFICATION_PROVIDER] Erreur marquage global: $e');
      return false;
    }
  }

  /// Sauvegarder un panier abandonné
  Future<bool> saveAbandonedCart(List<Map<String, dynamic>> cartData, double totalAmount) async {
    try {
      final response = await _apiService.post(
        '${ApiConfig.baseUrl}/notifications/abandoned-cart/save',
        {
          'cart_data': cartData,
          'total_amount': totalAmount,
          'items_count': cartData.length,
        },
        requiresAuth: true,
      );

      if (response['success'] == true) {
        print('✅ [NOTIFICATION_PROVIDER] Panier abandonné sauvegardé');
        return true;
      }
      return false;
    } catch (e) {
      print('❌ [NOTIFICATION_PROVIDER] Erreur sauvegarde panier: $e');
      return false;
    }
  }

  /// Marquer un panier comme récupéré (après finalisation commande)
  Future<bool> recoverCart() async {
    try {
      final response = await _apiService.post(
        '${ApiConfig.baseUrl}/notifications/abandoned-cart/recover',
        {},
        requiresAuth: true,
      );

      if (response['success'] == true) {
        print('✅ [NOTIFICATION_PROVIDER] Panier récupéré');
        return true;
      }
      return false;
    } catch (e) {
      print('❌ [NOTIFICATION_PROVIDER] Erreur récupération panier: $e');
      return false;
    }
  }

  /// Ajouter une notification localement (quand reçue en temps réel)
  void addNotification(Map<String, dynamic> notification) {
    _notifications.insert(0, notification);
    if (notification['is_read'] == false) {
      _unreadCount++;
    }
    notifyListeners();
  }

  /// Effacer l'erreur
  void clearError() {
    _error = null;
    notifyListeners();
  }

  /// Réinitialiser
  void reset() {
    _notifications = [];
    _unreadCount = 0;
    _isLoading = false;
    _error = null;
    notifyListeners();
  }
}

