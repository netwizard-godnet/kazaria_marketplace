import 'package:flutter/foundation.dart';
import '../models/cart_model.dart';
import '../models/product_model.dart';
import '../services/cart_service.dart';
import '../services/cart_reminder_service.dart';

class CartProvider with ChangeNotifier {
  final CartService _cartService = CartService();
  final CartReminderService _reminderService = CartReminderService();

  List<CartItemModel> _items = [];
  bool _isLoading = false;
  String? _error;
  String? _promoCode;
  double _discount = 0.0;
  double _promoPercent = 0.0;
  double _shippingCost = 0.0;
  double _tax = 0.0;

  // Getters
  List<CartItemModel> get items => _items;
  bool get isLoading => _isLoading;
  String? get error => _error;
  String? get promoCode => _promoCode;
  double get discount => _discount;
  double get promoPercent => _promoPercent;
  double get shippingCost => _shippingCost;
  double get tax => _tax;

  int get itemCount => _items.fold(0, (sum, item) => sum + item.quantity);

  double get subtotal => _items.fold(0.0, (sum, item) => sum + item.total);

  double get total => subtotal + _shippingCost - _discount; // Taxes retirées

  /// Charger le panier depuis l'API
  Future<void> loadCart() async {
    print('🔄 [CART] Chargement du panier...');
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _cartService.getCart();
      print('📥 [CART] Réponse getCart: $response');

      if (response['success']) {
        // Parser directement depuis la réponse (pas response['cart'])
        final cartModel = CartModel.fromJson(response);
        _items = cartModel.items;
        _discount = cartModel.discount;
        _shippingCost = cartModel.shippingCost;
        _tax = cartModel.tax;
        _promoCode = response['promo_code'];
        print('✅ [CART] Panier chargé: ${_items.length} articles');
        print('📊 [CART] Total: ${cartModel.total}');
        
        // Debug attributs
        for (var item in _items) {
          print('🔍 [CART] Item ${item.id}: ${item.product?.name}');
          print('   - Attributs: ${item.attributes}');
          print('   - Attributs vides: ${item.attributes == null || item.attributes!.isEmpty}');
        }
      } else {
        _error = response['message'];
        print('❌ [CART] Erreur lors du chargement: $_error');
      }
    } catch (e) {
      _error = e.toString();
      print('💥 [CART] Exception lors du chargement: $_error');
    }

    _isLoading = false;
    notifyListeners();

    // 🔔 Démarrer le rappel du panier si des articles sont présents
    if (_items.isNotEmpty) {
      _reminderService.startReminder(this);
    }
  }

  /// Ajouter un produit au panier
  Future<Map<String, dynamic>> addToCart({
    required ProductModel product,
    required int quantity,
    Map<String, String>? attributes, // ✅ Ajout paramètre attributs
  }) async {
    print('🛒 [CART] Tentative d\'ajout au panier');
    print('📦 [CART] Produit: ${product.name} (ID: ${product.id})');
    print('🔢 [CART] Quantité: $quantity');

    try {
      // Vérifier si le produit est déjà dans le panier
      final existingIndex = _items.indexWhere(
        (item) => item.productId == product.id,
      );

      if (existingIndex != -1) {
        // Mettre à jour la quantité
        print('➕ [CART] Mise à jour de la quantité existante');
        return await updateQuantity(
          _items[existingIndex].id,
          _items[existingIndex].quantity + quantity,
        );
      }

      // ✅ MISE À JOUR OPTIMISTE : Ajouter immédiatement à l'UI
      final tempItem = CartItemModel(
        id: DateTime.now().millisecondsSinceEpoch, // ID temporaire
        productId: product.id,
        quantity: quantity,
        price: product.price,
        product: product,
        attributes: attributes,
      );
      
      _items.add(tempItem);
      notifyListeners(); // ✅ UI mise à jour INSTANTANÉMENT
      print('⚡ [CART] Produit ajouté à l\'UI (optimiste)');

      // 🔔 Réinitialiser le timer de rappel immédiatement
      _reminderService.resetTimer(this);

      // Faire l'appel API en arrière-plan
      _cartService.addToCart(
        productId: product.id,
        quantity: quantity,
        attributes: attributes,
      ).then((response) async {
        print('📥 [CART] Réponse API reçue: ${response['success']}');
        
        if (response['success']) {
          // Recharger silencieusement en arrière-plan pour avoir les vrais IDs
          await loadCart();
          print('✅ [CART] Panier synchronisé avec le serveur');
        } else {
          // ❌ Rollback en cas d'erreur
          _items.removeWhere((item) => item.id == tempItem.id);
          notifyListeners();
          print('❌ [CART] Erreur API, rollback effectué');
        }
      }).catchError((e) {
        // ❌ Rollback en cas d'erreur réseau
        _items.removeWhere((item) => item.id == tempItem.id);
        notifyListeners();
        print('💥 [CART] Exception réseau, rollback effectué: $e');
      });

      return {'success': true, 'message': 'Produit ajouté au panier'};
    } catch (e) {
      print('💥 [CART] Exception: $e');
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Mettre à jour la quantité d'un produit
  Future<Map<String, dynamic>> updateQuantity(
    int cartItemId,
    int quantity,
  ) async {
    if (quantity <= 0) {
      return await removeFromCart(cartItemId);
    }

    // ✅ Mise à jour optimiste de l'UI pour réactivité immédiate
    final index = _items.indexWhere((item) => item.id == cartItemId);
    if (index != -1) {
      final oldQuantity = _items[index].quantity;
      _items[index] = _items[index].copyWith(quantity: quantity);
      notifyListeners(); // ✅ UI mise à jour immédiatement

      try {
        final response = await _cartService.updateCartItem(
          cartItemId: cartItemId,
          quantity: quantity,
        );

        if (response['success']) {
          // ✅ Mettre à jour le total depuis la réponse si disponible
          if (response['cart_total'] != null) {
            // Pas besoin de recharger tout le panier
          }
          return {'success': true, 'message': 'Quantité mise à jour'};
        } else {
          // ❌ Rollback en cas d'erreur
          _items[index] = _items[index].copyWith(quantity: oldQuantity);
          notifyListeners();
          return response;
        }
      } catch (e) {
        // ❌ Rollback en cas d'erreur
        _items[index] = _items[index].copyWith(quantity: oldQuantity);
        notifyListeners();
        return {'success': false, 'message': e.toString()};
      }
    }

    return {'success': false, 'message': 'Article non trouvé'};
  }

  /// Incrémenter la quantité
  Future<void> incrementQuantity(int cartItemId) async {
    final index = _items.indexWhere((item) => item.id == cartItemId);
    if (index != -1) {
      final item = _items[index];
      if (item.product != null && item.quantity < item.product!.stock) {
        await updateQuantity(cartItemId, item.quantity + 1);
      }
    }
  }

  /// Décrémenter la quantité
  Future<void> decrementQuantity(int cartItemId) async {
    final index = _items.indexWhere((item) => item.id == cartItemId);
    if (index != -1) {
      final item = _items[index];
      await updateQuantity(cartItemId, item.quantity - 1);
    }
  }

  /// Supprimer un produit du panier
  Future<Map<String, dynamic>> removeFromCart(int cartItemId) async {
    // ✅ Suppression optimiste pour réactivité immédiate
    final index = _items.indexWhere((item) => item.id == cartItemId);
    if (index == -1) {
      return {'success': false, 'message': 'Article non trouvé'};
    }

    final removedItem = _items[index];
    _items.removeAt(index);
    notifyListeners(); // ✅ UI mise à jour immédiatement

    try {
      final response = await _cartService.removeFromCart(cartItemId);

      if (response['success']) {
        // 🔔 Réinitialiser le rappel après suppression
        if (_items.isNotEmpty) {
          _reminderService.resetTimer(this);
        } else {
          _reminderService.cancelReminder();
        }
        return {'success': true, 'message': 'Produit retiré du panier'};
      } else {
        // ❌ Rollback en cas d'erreur
        _items.insert(index, removedItem);
        notifyListeners();
        return response;
      }
    } catch (e) {
      // ❌ Rollback en cas d'erreur
      _items.insert(index, removedItem);
      notifyListeners();
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Vider le panier
  Future<Map<String, dynamic>> clearCart() async {
    try {
      final response = await _cartService.clearCart();

      if (response['success']) {
        _items.clear();
        _promoCode = null;
        _discount = 0.0;
        _promoPercent = 0.0;
        notifyListeners();
        return {'success': true, 'message': 'Panier vidé'};
      } else {
        return response;
      }
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Appliquer un code promo
  Future<Map<String, dynamic>> applyPromoCode(String code) async {
    try {
      // ✅ Passer le subtotal requis par le backend
      final response = await _cartService.applyPromoCode(
        code: code,
        subtotal: subtotal,
      );

      if (response['success']) {
        _promoCode = code;
        // ✅ Le backend retourne discount_amount (montant) et discount_percent (pourcentage)
        _discount = response['discount_amount']?.toDouble() ?? 
                   (subtotal * (response['discount_percent']?.toDouble() ?? 0.0) / 100);
        _promoPercent = response['discount_percent']?.toDouble() ??
            response['discount_percentage']?.toDouble() ??
            0.0;
        notifyListeners();
        return {
          'success': true,
          'message':
              'Code promo appliqué : ${response['discount_percent'] ?? response['discount_percentage']}% de réduction',
        };
      } else {
        return response;
      }
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Retirer le code promo
  Future<Map<String, dynamic>> removePromoCode() async {
    try {
      final response = await _cartService.removePromoCode();

      if (response['success']) {
        _promoCode = null;
        _discount = 0.0;
        _promoPercent = 0.0;
        notifyListeners();
        return {'success': true, 'message': 'Code promo retiré'};
      } else {
        return response;
      }
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Vérifier si un produit est dans le panier
  bool isInCart(int productId) {
    return _items.any((item) => item.productId == productId);
  }

  /// Obtenir la quantité d'un produit dans le panier
  int getProductQuantity(int productId) {
    try {
      final item = _items.firstWhere((item) => item.productId == productId);
      return item.quantity;
    } catch (e) {
      return 0;
    }
  }

  /// 🔔 Annuler le rappel du panier (appelé lors du checkout ou vidage du panier)
  void cancelCartReminder() {
    _reminderService.cancelReminder();
    print('❌ [CART] Rappels annulés');
  }

  /// Initialiser le service de rappel
  Future<void> initializeReminder() async {
    await _reminderService.initialize();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }

  @override
  void dispose() {
    _reminderService.dispose();
    super.dispose();
  }
}
