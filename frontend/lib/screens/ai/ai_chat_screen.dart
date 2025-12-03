import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../utils/constants.dart';
import '../../config/api_config.dart';
import '../../services/ai_assistant_service.dart';
import '../../services/order_service.dart';
import '../../providers/cart_provider.dart';
import '../../models/cart_model.dart';
import '../../models/chat_message_model.dart';
import '../../models/product_model.dart';
import '../products/product_details_screen.dart';

/// 🤖 Écran de Chat avec l'Assistant IA
class AiChatScreen extends StatefulWidget {
  const AiChatScreen({super.key});

  @override
  State<AiChatScreen> createState() => _AiChatScreenState();
}

class _AiChatScreenState extends State<AiChatScreen> with SingleTickerProviderStateMixin {
  final AiAssistantService _aiService = AiAssistantService();
  final OrderService _orderService = OrderService();
  final TextEditingController _textController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final List<ChatMessageModel> _messages = [];
  List<String> _suggestions = [];
  bool _isLoading = false;
  late AnimationController _animationController;
  final NumberFormat _currencyFormatter = NumberFormat('#,##0', 'fr_FR');

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );
    
    // Message de bienvenue
    _addWelcomeMessage();
    _loadSuggestions();
  }

  @override
  void dispose() {
    _textController.dispose();
    _scrollController.dispose();
    _animationController.dispose();
    super.dispose();
  }

  void _addWelcomeMessage() {
    final welcomeMessage = ChatMessageModel(
      id: '0',
      text: '👋 Bonjour ! Je suis votre assistant shopping personnel.\n\nPosez-moi des questions comme :\n• "J\'ai 60 000 FCFA, quels téléphones tu proposes ?"\n• "Montre-moi les meilleurs ordinateurs"\n• "Je cherche un cadeau à moins de 50 000 FCFA"',
      isUser: false,
      timestamp: DateTime.now(),
    );

    setState(() {
      _messages.add(welcomeMessage);
    });
  }

  Future<void> _loadSuggestions() async {
    final suggestions = await _aiService.getSuggestions();
    setState(() {
      _suggestions = suggestions;
    });
  }

  Future<void> _sendMessage(String text) async {
    if (text.trim().isEmpty) return;

    // Ajouter le message utilisateur
    final userMessage = ChatMessageModel(
      id: DateTime.now().millisecondsSinceEpoch.toString(),
      text: text,
      isUser: true,
      timestamp: DateTime.now(),
    );

    setState(() {
      _messages.add(userMessage);
      _isLoading = true;
    });

    _textController.clear();
    _scrollToBottom();

    // Envoyer au service IA
    final response = await _aiService.sendMessage(text);

    // Ajouter la réponse de l'IA
    final aiMessage = _aiService.parseResponse(response);

    setState(() {
      _messages.add(aiMessage);
      _isLoading = false;
    });

    _handleIntent(aiMessage);
    _scrollToBottom();
  }

  void _scrollToBottom() {
    Future.delayed(const Duration(milliseconds: 100), () {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  void _handleIntent(ChatMessageModel message) {
    final metadata = message.metadata;
    if (metadata == null) return;

    final intent = metadata['intent']?.toString();
    if (intent == null) return;

    if (intent == 'track_order') {
      final params = _extractMap(metadata['intent_params']);
      final rawOrder = params?['order_number'];
      final orderNumber = rawOrder?.toString();
      if (orderNumber != null && orderNumber.isNotEmpty) {
        _fetchOrderDetails(orderNumber);
      }
    }
  }

  Future<void> _fetchOrderDetails(String orderNumber) async {
    _addAssistantMessage('🔍 Je vérifie la commande $orderNumber...');
    final response = await _orderService.getOrderDetails(orderNumber);

    if (!mounted) return;

    if (response['success'] == true && response['order'] != null) {
      final order = response['order'] as Map<String, dynamic>;
      final status = _formatOrderStatus(order['status']?.toString() ?? 'inconnu');
      final totalValue = _toDouble(order['total']);
      final totalText = totalValue != null ? '${_formatCurrency(totalValue)} FCFA' : 'N/A';

      String updatedText = '';
      final rawUpdated = order['updated_at']?.toString();
      if (rawUpdated != null) {
        try {
          final parsed = DateTime.parse(rawUpdated);
          final formatted = DateFormat('dd MMM yyyy HH:mm', 'fr_FR').format(parsed);
          updatedText = '\nMis à jour: $formatted';
        } catch (_) {}
      }

      _addAssistantMessage(
        'Commande $orderNumber\nStatut: $status\nTotal: $totalText$updatedText',
      );
    } else {
      _addAssistantMessage(
        response['message']?.toString() ??
            'Impossible de récupérer la commande. Vérifiez que vous êtes connecté.',
      );
    }
  }

  void _addAssistantMessage(String text) {
    final msg = ChatMessageModel(
      id: DateTime.now().millisecondsSinceEpoch.toString(),
      text: text,
      isUser: false,
      timestamp: DateTime.now(),
    );
    setState(() {
      _messages.add(msg);
    });
    _scrollToBottom();
  }

  void _showSnack(String message, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? AppColors.error : AppColors.primary,
      ),
    );
  }

  Future<void> _handleProductTap(ProductModel product) async {
    await _aiService.logInteraction(productId: product.id, type: 'click');
    if (!mounted) return;
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ProductDetailsScreen(product: product),
      ),
    );
  }

  Future<void> _handleAddToCart(ProductModel product) async {
    final cartProvider = context.read<CartProvider>();
    final response = await cartProvider.addToCart(product: product, quantity: 1);
    if (!mounted) return;

    if (response['success'] == true) {
      _showSnack('Ajouté au panier');
      await _aiService.logInteraction(productId: product.id, type: 'add_to_cart');
    } else {
      _showSnack(response['message']?.toString() ?? 'Impossible d\'ajouter au panier',
          isError: true);
    }
  }

  Future<void> _handleIncreaseQuantity(ProductModel product) async {
    final cartProvider = context.read<CartProvider>();
    final item = _findCartItem(cartProvider, product.id);
    if (item == null) return;

    final response = await cartProvider.updateQuantity(item.id, item.quantity + 1);
    if (!mounted) return;
    if (response['success'] != true) {
      _showSnack(response['message']?.toString() ?? 'Mise à jour impossible', isError: true);
    }
  }

  Future<void> _handleDecreaseQuantity(ProductModel product) async {
    final cartProvider = context.read<CartProvider>();
    final item = _findCartItem(cartProvider, product.id);
    if (item == null) return;

    final response = await cartProvider.updateQuantity(item.id, item.quantity - 1);
    if (!mounted) return;
    if (response['success'] != true) {
      _showSnack(response['message']?.toString() ?? 'Mise à jour impossible', isError: true);
    }
  }

  Future<void> _handleRemoveFromCart(ProductModel product) async {
    final cartProvider = context.read<CartProvider>();
    final item = _findCartItem(cartProvider, product.id);
    if (item == null) return;

    final response = await cartProvider.removeFromCart(item.id);
    if (!mounted) return;
    if (response['success'] == true) {
      _showSnack('Produit retiré du panier');
    } else {
      _showSnack(response['message']?.toString() ?? 'Suppression impossible', isError: true);
    }
  }

  CartItemModel? _findCartItem(CartProvider provider, int productId) {
    try {
      return provider.items.firstWhere((item) => item.productId == productId);
    } catch (_) {
      return null;
    }
  }

  List<Widget> _buildMetadataWidgets(ChatMessageModel message) {
    final metadata = message.metadata;
    if (metadata == null) return const [];

    final widgets = <Widget>[];

    final understood = _extractMap(metadata['understood']);
    final chips = understood != null ? _buildUnderstandingChips(understood) : null;
    if (chips != null) {
      widgets.add(chips);
    }

    final actionResult = _extractMap(metadata['action_result']);
    if (actionResult != null) {
      widgets.add(_buildActionResultCard(actionResult));
    }

    return widgets;
  }


  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [AppColors.primary, AppColors.accent],
                ),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.smart_toy,
                color: Colors.white,
                size: 20,
              ),
            ),
            const SizedBox(width: 12),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Assistant IA',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
                Text(
                  'En ligne',
                  style: TextStyle(
                    fontSize: 11,
                    color: AppColors.success,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ],
        ),
        backgroundColor: AppColors.white,
        foregroundColor: AppColors.textDark,
        elevation: 1,
      ),
      body: Column(
        children: [
          // Messages
          Expanded(
            child: ListView.builder(
              controller: _scrollController,
              padding: const EdgeInsets.all(16),
              itemCount: _messages.length + (_isLoading ? 1 : 0),
              itemBuilder: (context, index) {
                if (index == _messages.length && _isLoading) {
                  return _buildTypingIndicator();
                }
                
                final message = _messages[index];
                return _buildMessage(message);
              },
            ),
          ),

          // Suggestions rapides
          if (_suggestions.isNotEmpty && _messages.length == 1)
            _buildQuickSuggestions(),

          // Zone de saisie
          _buildInputArea(),
        ],
      ),
    );
  }

  Widget _buildMessage(ChatMessageModel message) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        mainAxisAlignment:
            message.isUser ? MainAxisAlignment.end : MainAxisAlignment.start,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (!message.isUser) ...[
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [AppColors.primary, AppColors.accent],
                ),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.smart_toy,
                color: Colors.white,
                size: 18,
              ),
            ),
            const SizedBox(width: 8),
          ],
          
          Flexible(
            child: Column(
              crossAxisAlignment: message.isUser
                  ? CrossAxisAlignment.end
                  : CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: message.isUser
                        ? AppColors.primary
                        : AppColors.white,
                    borderRadius: BorderRadius.only(
                      topLeft: const Radius.circular(16),
                      topRight: const Radius.circular(16),
                      bottomLeft: Radius.circular(message.isUser ? 16 : 4),
                      bottomRight: Radius.circular(message.isUser ? 4 : 16),
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.05),
                        blurRadius: 5,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Text(
                    message.text,
                    style: TextStyle(
                      color: message.isUser
                          ? Colors.white
                          : AppColors.textDark,
                      fontSize: 14,
                      height: 1.4,
                    ),
                  ),
                ),
                
                // Produits suggérés
                if (message.suggestedProducts != null &&
                    message.suggestedProducts!.isNotEmpty)
                  _buildProductSuggestions(message.suggestedProducts!),

                ..._buildMetadataWidgets(message),
                
                // Timestamp
                Padding(
                  padding: const EdgeInsets.only(top: 4, left: 8, right: 8),
                  child: Text(
                    _formatTime(message.timestamp),
                    style: TextStyle(
                      fontSize: 10,
                      color: AppColors.textLight,
                    ),
                  ),
                ),
              ],
            ),
          ),
          
          if (message.isUser) ...[
            const SizedBox(width: 8),
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: AppColors.grey200,
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.person,
                color: AppColors.textMedium,
                size: 18,
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildProductSuggestions(List<ProductModel> products) {
    return Container(
      margin: const EdgeInsets.only(top: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: products.map((product) => _buildProductCard(product)).toList(),
      ),
    );
  }

  Widget _buildProductCard(ProductModel product) {
    final imageUrl = _getProductImageUrl(product);

    return Consumer<CartProvider>(
      builder: (context, cartProvider, _) {
        final inCart = cartProvider.isInCart(product.id);
        final quantity = cartProvider.getProductQuantity(product.id);

        return Container(
          margin: const EdgeInsets.only(bottom: 8),
          decoration: BoxDecoration(
            color: AppColors.background,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.grey200),
          ),
          child: InkWell(
            onTap: () => _handleProductTap(product),
            borderRadius: BorderRadius.circular(12),
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                children: [
                  Row(
                    children: [
                      // Image
                      ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: imageUrl.isNotEmpty
                            ? CachedNetworkImage(
                                imageUrl: imageUrl,
                                width: 60,
                                height: 60,
                                fit: BoxFit.cover,
                                placeholder: (context, url) => Container(
                                  width: 60,
                                  height: 60,
                                  color: AppColors.grey100,
                                  child: const Center(
                                    child: CircularProgressIndicator(strokeWidth: 2),
                                  ),
                                ),
                                errorWidget: (context, url, error) => Container(
                                  width: 60,
                                  height: 60,
                                  color: AppColors.grey100,
                                  child: Icon(
                                    Icons.image_not_supported,
                                    color: AppColors.grey400,
                                  ),
                                ),
                              )
                            : Container(
                                width: 60,
                                height: 60,
                                color: AppColors.grey100,
                                child: Icon(
                                  Icons.shopping_bag,
                                  color: AppColors.grey400,
                                ),
                              ),
                      ),

                      const SizedBox(width: 12),

                      // Info
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              product.name,
                              style: const TextStyle(
                                fontWeight: FontWeight.w600,
                                fontSize: 13,
                              ),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '${_formatCurrency(product.price)} FCFA',
                              style: TextStyle(
                                color: AppColors.primary,
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                              ),
                            ),
                            if (product.oldPrice != null && product.oldPrice! > product.price) ...[
                              const SizedBox(height: 2),
                              Text(
                                '${_formatCurrency(product.oldPrice!)} FCFA',
                                style: TextStyle(
                                  color: AppColors.textLight,
                                  fontSize: 11,
                                  decoration: TextDecoration.lineThrough,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),

                      Icon(
                        Icons.arrow_forward_ios,
                        size: 16,
                        color: AppColors.primary,
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  _buildProductActions(product, inCart, quantity),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildProductActions(ProductModel product, bool inCart, int quantity) {
    if (!inCart) {
      return Align(
        alignment: Alignment.centerRight,
        child: TextButton.icon(
          icon: const Icon(Icons.add_shopping_cart, size: 16),
          label: const Text('Ajouter'),
          onPressed: () => _handleAddToCart(product),
          style: TextButton.styleFrom(
            foregroundColor: AppColors.primary,
          ),
        ),
      );
    }

    final safeQuantity = quantity > 0 ? quantity : 1;

    return Row(
      mainAxisAlignment: MainAxisAlignment.end,
      children: [
        IconButton(
          onPressed: () => _handleDecreaseQuantity(product),
          icon: const Icon(Icons.remove_circle_outline),
          color: AppColors.primary,
          iconSize: 20,
        ),
        Text(
          '$safeQuantity',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        IconButton(
          onPressed: () => _handleIncreaseQuantity(product),
          icon: const Icon(Icons.add_circle_outline),
          color: AppColors.primary,
          iconSize: 20,
        ),
        TextButton(
          onPressed: () => _handleRemoveFromCart(product),
          child: const Text('Retirer'),
        ),
      ],
    );
  }

  Widget _buildTypingIndicator() {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        children: [
          Container(
            width: 32,
            height: 32,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [AppColors.primary, AppColors.accent],
              ),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.smart_toy,
              color: Colors.white,
              size: 18,
            ),
          ),
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.white,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                _buildDot(0),
                const SizedBox(width: 4),
                _buildDot(1),
                const SizedBox(width: 4),
                _buildDot(2),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDot(int index) {
    return TweenAnimationBuilder<double>(
      tween: Tween(begin: 0.0, end: 1.0),
      duration: const Duration(milliseconds: 600),
      builder: (context, value, child) {
        final delayed = (index * 0.2);
        final animValue = (value - delayed).clamp(0.0, 1.0);
        final opacity = (animValue * 2).clamp(0.0, 1.0);
        
        return Opacity(
          opacity: opacity,
          child: Container(
            width: 8,
            height: 8,
            decoration: BoxDecoration(
              color: AppColors.primary,
              shape: BoxShape.circle,
            ),
          ),
        );
      },
    );
  }

  Widget _buildQuickSuggestions() {
    return Container(
      height: 50,
      margin: const EdgeInsets.only(bottom: 8),
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: _suggestions.length,
        itemBuilder: (context, index) {
          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: ActionChip(
              label: Text(
                _suggestions[index],
                style: const TextStyle(fontSize: 12),
              ),
              onPressed: () => _sendMessage(_suggestions[index]),
              backgroundColor: AppColors.white,
              side: BorderSide(color: AppColors.primary.withOpacity(0.3)),
              labelStyle: TextStyle(color: AppColors.primary),
            ),
          );
        },
      ),
    );
  }

  Widget _buildInputArea() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        child: Row(
          children: [
            Expanded(
              child: Container(
                decoration: BoxDecoration(
                  color: AppColors.background,
                  borderRadius: BorderRadius.circular(24),
                ),
                child: TextField(
                  controller: _textController,
                  decoration: InputDecoration(
                    hintText: 'Posez votre question...',
                    hintStyle: TextStyle(color: AppColors.textLight),
                    border: InputBorder.none,
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 20,
                      vertical: 12,
                    ),
                  ),
                  maxLines: null,
                  textInputAction: TextInputAction.send,
                  onSubmitted: _isLoading ? null : _sendMessage,
                ),
              ),
            ),
            const SizedBox(width: 8),
            Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [AppColors.primary, AppColors.accent],
                ),
                shape: BoxShape.circle,
              ),
              child: IconButton(
                icon: const Icon(Icons.send, color: Colors.white, size: 20),
                onPressed: _isLoading
                    ? null
                    : () => _sendMessage(_textController.text),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _formatTime(DateTime time) {
    return '${time.hour}:${time.minute.toString().padLeft(2, '0')}';
  }
  
  /// Obtenir l'URL de l'image du produit
  String _getProductImageUrl(ProductModel product) {
    if (product.image != null && product.image!.isNotEmpty) {
      return _fixImageUrl(product.image!);
    }
    if (product.images != null && product.images!.isNotEmpty) {
      return _fixImageUrl(product.images!.first);
    }
    return '';
  }
  
  /// Corriger et construire l'URL d'image
  String _fixImageUrl(String imagePath) {
    // Si l'URL est déjà complète et correcte
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return imagePath;
    }
    
    // ✅ CORRECTION : Si c'est "http:" sans "//"
    if (imagePath.startsWith('http:') && !imagePath.startsWith('http://')) {
      return imagePath.replaceFirst('http:', 'http://');
    }
    
    // ✅ CORRECTION : Si c'est "https:" sans "//"
    if (imagePath.startsWith('https:') && !imagePath.startsWith('https://')) {
      return imagePath.replaceFirst('https:', 'https://');
    }
    
    // Sinon, construire l'URL complète
    return '${ApiConfig.imageBaseUrl}/storage/$imagePath';
  }

  Widget? _buildUnderstandingChips(Map<String, dynamic> data) {
    final chips = <Widget>[];

    final priceMin = _toDouble(data['price_min']);
    if (priceMin != null) {
      chips.add(_buildCriteriaChip('Min ${_formatCurrency(priceMin)} FCFA'));
    }

    final priceMax = _toDouble(data['price_max']);
    if (priceMax != null) {
      chips.add(_buildCriteriaChip('Max ${_formatCurrency(priceMax)} FCFA'));
    }

    final storage = _toDouble(data['storage_gb']);
    if (storage != null) {
      chips.add(_buildCriteriaChip('Stockage ${storage.toStringAsFixed(0)} Go'));
    }

    final ram = _toDouble(data['ram_gb']);
    if (ram != null) {
      chips.add(_buildCriteriaChip('RAM ${ram.toStringAsFixed(0)} Go'));
    }

    final brand = data['brand']?.toString();
    if (brand != null && brand.isNotEmpty) {
      chips.add(_buildCriteriaChip('Marque ${brand[0].toUpperCase()}${brand.substring(1)}'));
    }

    final category = data['category']?.toString();
    if (category != null && category.isNotEmpty) {
      chips.add(_buildCriteriaChip(_categoryLabel(category)));
    }

    final color = data['color']?.toString();
    if (color != null && color.isNotEmpty) {
      chips.add(_buildCriteriaChip('Couleur ${color[0].toUpperCase()}${color.substring(1)}'));
    }

    final screen = data['screen_inch']?.toString();
    if (screen != null && screen.isNotEmpty) {
      chips.add(_buildCriteriaChip('Écran $screen"'));
    }

    final battery = _toDouble(data['battery_mah']);
    if (battery != null) {
      chips.add(_buildCriteriaChip('Batterie ${battery.toStringAsFixed(0)} mAh'));
    }

    final camera = _toDouble(data['camera_mp']);
    if (camera != null) {
      chips.add(_buildCriteriaChip('Caméra ${camera.toStringAsFixed(0)} MP'));
    }

    final dualSim = data['dual_sim'] == true;
    if (dualSim) {
      chips.add(_buildCriteriaChip('Dual SIM'));
    }

    final fiveG = data['five_g'] == true;
    if (fiveG) {
      chips.add(_buildCriteriaChip('5G'));
    }

    final refresh = _toDouble(data['refresh_hz']);
    if (refresh != null) {
      chips.add(_buildCriteriaChip('${refresh.toStringAsFixed(0)} Hz'));
    }

    if (data['nfc'] == true) {
      chips.add(_buildCriteriaChip('NFC'));
    }

    if (data['esim'] == true) {
      chips.add(_buildCriteriaChip('eSIM'));
    }

    final ipRating = data['ip_rating']?.toString();
    if (ipRating != null && ipRating.isNotEmpty) {
      chips.add(_buildCriteriaChip('IP$ipRating'));
    }

    final selfie = _toDouble(data['selfie_mp']);
    if (selfie != null) {
      chips.add(_buildCriteriaChip('Selfie ${selfie.toStringAsFixed(0)} MP'));
    }

    final ultra = _toDouble(data['ultra_wide_mp']);
    if (ultra != null) {
      chips.add(_buildCriteriaChip('Ultra-wide ${ultra.toStringAsFixed(0)} MP'));
    }

    if (chips.isEmpty) return null;

    return Padding(
      padding: const EdgeInsets.only(top: 8),
      child: Wrap(
        spacing: 8,
        runSpacing: 4,
        children: chips,
      ),
    );
  }

  Widget _buildCriteriaChip(String label) {
    return Chip(
      label: Text(
        label,
        style: const TextStyle(fontSize: 11),
      ),
      backgroundColor: AppColors.background,
      side: BorderSide(color: AppColors.grey200),
    );
  }

  Widget _buildActionResultCard(Map<String, dynamic> result) {
    final success = result['success'] == true;
    final icon = success ? Icons.check_circle : Icons.error_outline;
    final baseColor = success ? AppColors.success : AppColors.error;
    final message = result['message']?.toString() ??
        (success ? 'Action réussie' : 'Action impossible');
    final discountPercent =
        result['discount_percent'] ?? result['discount_percentage'];
    final discountAmount = _toDouble(result['discount_amount']);

    return Container(
      margin: const EdgeInsets.only(top: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: baseColor.withOpacity(0.08),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: baseColor),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  message,
                  style: TextStyle(
                    color: AppColors.textDark,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
          if (discountPercent != null || discountAmount != null) ...[
            const SizedBox(height: 6),
            Text(
              [
                if (discountPercent != null) '$discountPercent %',
                if (discountAmount != null)
                  '${_formatCurrency(discountAmount)} FCFA',
              ].join(' · '),
              style: TextStyle(
                color: baseColor,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ],
      ),
    );
  }

  String _formatCurrency(num value) {
    return _currencyFormatter.format(value);
  }

  String _categoryLabel(String category) {
    switch (category) {
      case 'phone':
        return 'Téléphone';
      case 'laptop':
        return 'Ordinateur';
      case 'tv':
        return 'Télévision';
      case 'fridge':
        return 'Réfrigérateur';
      case 'freezer':
        return 'Congélateur';
      case 'kettle':
        return 'Bouilloire';
      default:
        return category;
    }
  }

  String _formatOrderStatus(String status) {
    switch (status) {
      case 'pending':
        return 'En attente';
      case 'processing':
        return 'En préparation';
      case 'paid':
        return 'Payée';
      case 'shipped':
        return 'Expédiée';
      case 'delivered':
        return 'Livrée';
      case 'cancelled':
        return 'Annulée';
      case 'refunded':
        return 'Remboursée';
      default:
        return status;
    }
  }

  double? _toDouble(dynamic value) {
    if (value == null) return null;
    if (value is num) return value.toDouble();
    return double.tryParse(value.toString());
  }

  Map<String, dynamic>? _extractMap(dynamic value) {
    if (value is Map<String, dynamic>) return value;
    if (value is Map) {
      return value.map((key, val) => MapEntry(key.toString(), val));
    }
    return null;
  }
}

