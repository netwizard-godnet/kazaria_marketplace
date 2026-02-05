import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../services/ai_chat_service.dart';
import '../../utils/constants.dart';
import '../../models/product_model.dart';
import '../products/product_details_screen.dart';

class AIChatbotScreen extends StatefulWidget {
  const AIChatbotScreen({Key? key}) : super(key: key);

  @override
  State<AIChatbotScreen> createState() => _AIChatbotScreenState();
}

class _AIChatbotScreenState extends State<AIChatbotScreen> with TickerProviderStateMixin {
  final AIChatService _aiChatService = AIChatService();
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final List<ChatMessage> _messages = [];
  final List<Map<String, String>> _conversationHistory = [];
  bool _isLoading = false;
  List<String> _suggestedQuestions = [];

  @override
  void initState() {
    super.initState();
    _loadSuggestedQuestions();
    _addWelcomeMessage();
  }

  Future<void> _loadSuggestedQuestions() async {
    final suggestions = await _aiChatService.getSuggestedQuestions();
    setState(() {
      _suggestedQuestions = suggestions;
    });
  }

  void _addWelcomeMessage() {
    setState(() {
      _messages.add(
        ChatMessage(
          text: "Bonjour ! 👋\n\nJe suis votre assistant virtuel KAZARIA. Je peux vous aider à :\n\n• Trouver des produits\n• Comparer les prix\n• Répondre à vos questions\n• Vous conseiller sur vos achats\n\nComment puis-je vous aider aujourd'hui ?",
          isBot: true,
          timestamp: DateTime.now(),
        ),
      );
    });
  }

  Future<void> _sendMessage(String text) async {
    if (text.trim().isEmpty) return;

    // Ajouter le message de l'utilisateur
    setState(() {
      _messages.add(
        ChatMessage(
          text: text,
          isBot: false,
          timestamp: DateTime.now(),
        ),
      );
      _conversationHistory.add({
        'role': 'user',
        'content': text,
      });
    });

    _messageController.clear();
    _scrollToBottom();

    // Envoyer à l'IA
    setState(() => _isLoading = true);

    try {
      final response = await _aiChatService.sendMessage(
        message: text,
        conversationHistory: _conversationHistory,
      );

      // Le backend retourne 'message', pas 'reply'
      final replyText = response['message'] ?? response['reply'];
      final items = response['items'] as List?;
      
      if (response['success'] == true && replyText != null) {
        setState(() {
          _messages.add(
            ChatMessage(
              text: replyText,
              isBot: true,
              timestamp: DateTime.now(),
              items: items?.cast<Map<String, dynamic>>(),
            ),
          );
          _conversationHistory.add({
            'role': 'assistant',
            'content': replyText,
          });
        });
      } else {
        _addErrorMessage();
      }
    } catch (e) {
      _addErrorMessage();
    } finally {
      setState(() => _isLoading = false);
      _scrollToBottom();
    }
  }

  void _addErrorMessage() {
    setState(() {
      _messages.add(
        ChatMessage(
          text: "Désolé, une erreur est survenue. Pouvez-vous reformuler votre question ?",
          isBot: true,
          timestamp: DateTime.now(),
          isError: true,
        ),
      );
    });
  }

  void _scrollToBottom() {
    Future.delayed(const Duration(milliseconds: 300), () {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  void _resetConversation() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Nouvelle conversation'),
        content: const Text('Voulez-vous réinitialiser la conversation ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              setState(() {
                _messages.clear();
                _conversationHistory.clear();
              });
              _addWelcomeMessage();
              _aiChatService.resetConversation();
            },
            child: const Text('Réinitialiser'),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                gradient: AppColors.primaryGradient,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.smart_toy,
                color: Colors.white,
                size: 20,
              ),
            ),
            const SizedBox(width: 12),
            const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Assistant IA',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
                Text(
                  'Propulsé par IA',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.normal),
                ),
              ],
            ),
          ],
        ),
        backgroundColor: Colors.white,
        foregroundColor: AppColors.textDark,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _resetConversation,
            tooltip: 'Nouvelle conversation',
          ),
        ],
      ),
      body: Column(
        children: [
          // Messages
          Expanded(
            child: _messages.isEmpty
                ? _buildEmptyState()
                : ListView.builder(
                    controller: _scrollController,
                    padding: const EdgeInsets.all(16),
                    itemCount: _messages.length,
                    itemBuilder: (context, index) {
                      return _buildMessageBubble(_messages[index]);
                    },
                  ),
          ),

          // Questions suggérées (si conversation vide)
          if (_messages.length <= 1 && _suggestedQuestions.isNotEmpty)
            _buildSuggestedQuestions(),

          // Loading indicator
          if (_isLoading)
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.grey[200],
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation(AppColors.primary),
                          ),
                        ),
                        const SizedBox(width: 8),
                        const Text('L\'IA réfléchit...'),
                      ],
                    ),
                  ),
                ],
              ),
            ),

          // Input bar
          _buildInputBar(),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              gradient: AppColors.primaryGradient,
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                  color: AppColors.primary.withOpacity(0.3),
                  blurRadius: 20,
                  offset: const Offset(0, 10),
                ),
              ],
            ),
            child: const Icon(
              Icons.smart_toy,
              size: 64,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 24),
          const Text(
            'Assistant IA KAZARIA',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Posez-moi vos questions !',
            style: TextStyle(
              fontSize: 16,
              color: AppColors.textLight,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMessageBubble(ChatMessage message) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        mainAxisAlignment:
            message.isBot ? MainAxisAlignment.start : MainAxisAlignment.end,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (message.isBot) ...[
            // Avatar bot
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                gradient: message.isError 
                    ? LinearGradient(colors: [Colors.red, Colors.red.shade700])
                    : AppColors.primaryGradient,
                shape: BoxShape.circle,
              ),
              child: Icon(
                message.isError ? Icons.error_outline : Icons.smart_toy,
                color: Colors.white,
                size: 20,
              ),
            ),
            const SizedBox(width: 8),
          ],
          
          // Message bubble
          Flexible(
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                gradient: message.isBot
                    ? null
                    : AppColors.primaryGradient,
                color: message.isBot ? Colors.grey[100] : null,
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(message.isBot ? 4 : 20),
                  topRight: Radius.circular(message.isBot ? 20 : 4),
                  bottomLeft: const Radius.circular(20),
                  bottomRight: const Radius.circular(20),
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.05),
                    blurRadius: 5,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    message.text,
                    style: TextStyle(
                      color: message.isBot ? AppColors.textDark : Colors.white,
                      fontSize: 15,
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    _formatTime(message.timestamp),
                    style: TextStyle(
                      color: message.isBot
                          ? AppColors.textLight
                          : Colors.white.withOpacity(0.7),
                      fontSize: 11,
                    ),
                  ),
                  // Afficher les produits si disponibles
                  if (message.isBot && message.items != null && message.items!.isNotEmpty) ...[
                    const SizedBox(height: 12),
                    SizedBox(
                      height: 120,
                      child: ListView.builder(
                        scrollDirection: Axis.horizontal,
                        shrinkWrap: true,
                        itemCount: message.items!.length,
                        itemBuilder: (context, index) {
                          final product = message.items![index];
                          return _buildProductCard(context, product);
                        },
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
          
          if (!message.isBot) ...[
            const SizedBox(width: 8),
            // Avatar utilisateur
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.grey[300],
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.person,
                color: AppColors.textDark,
                size: 20,
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildSuggestedQuestions() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey[50],
        border: Border(
          top: BorderSide(color: Colors.grey[200]!),
        ),
      ),
      constraints: const BoxConstraints(maxHeight: 200), // Limite la hauteur
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Questions suggérées :',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: AppColors.textLight,
              ),
            ),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: _suggestedQuestions.map((question) {
                return InkWell(
                  onTap: () => _sendMessage(question),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 8,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: AppColors.primary.withOpacity(0.3)),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          Icons.lightbulb_outline,
                          size: 14,
                          color: AppColors.primary,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          question,
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppColors.primary,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }).toList(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInputBar() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
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
            // Bouton micro (future feature)
            Container(
              decoration: BoxDecoration(
                color: AppColors.primary.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: IconButton(
                icon: const Icon(Icons.mic, color: AppColors.primary),
                onPressed: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Recherche vocale bientôt disponible !'),
                      behavior: SnackBarBehavior.floating,
                    ),
                  );
                },
                tooltip: 'Recherche vocale',
              ),
            ),
            const SizedBox(width: 12),
            
            // Champ de texte
            Expanded(
              child: Container(
                decoration: BoxDecoration(
                  color: AppColors.background,
                  borderRadius: BorderRadius.circular(25),
                ),
                child: TextField(
                  controller: _messageController,
                  decoration: const InputDecoration(
                    hintText: 'Posez votre question...',
                    border: InputBorder.none,
                    contentPadding: EdgeInsets.symmetric(
                      horizontal: 20,
                      vertical: 12,
                    ),
                  ),
                  maxLines: null,
                  textCapitalization: TextCapitalization.sentences,
                  onSubmitted: _sendMessage,
                  enabled: !_isLoading,
                ),
              ),
            ),
            
            const SizedBox(width: 12),
            
            // Bouton envoyer
            Container(
              decoration: BoxDecoration(
                gradient: AppColors.primaryGradient,
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(
                    color: AppColors.primary.withOpacity(0.3),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: IconButton(
                icon: const Icon(Icons.send_rounded, color: Colors.white),
                onPressed: _isLoading
                    ? null
                    : () => _sendMessage(_messageController.text),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _formatTime(DateTime timestamp) {
    final now = DateTime.now();
    final difference = now.difference(timestamp);

    if (difference.inMinutes < 1) {
      return 'À l\'instant';
    } else if (difference.inHours < 1) {
      return '${difference.inMinutes}min';
    } else if (difference.inDays < 1) {
      return DateFormat('HH:mm').format(timestamp);
    } else {
      return DateFormat('dd/MM HH:mm').format(timestamp);
    }
  }

  Widget _buildProductCard(BuildContext context, Map<String, dynamic> product) {
    // Formater le prix en CFA
    String formatPrice(dynamic price) {
      if (price == null) return '0 CFA';
      
      final priceValue = price is String ? double.tryParse(price) ?? 0 : price as num;
      final formattedPrice = NumberFormat('#,##0', 'fr_FR').format(priceValue.toInt());
      return '$formattedPrice CFA';
    }

    // Créer un objet ProductModel à partir des données du backend
    ProductModel createProductFromMap(Map<String, dynamic> data) {
      return ProductModel(
        id: data['id'] ?? 0,
        categoryId: data['category_id'] ?? 0,
        name: data['name'] ?? 'Produit',
        slug: data['slug'] ?? '',
        description: data['description'] ?? '',
        price: double.tryParse(data['price'].toString()) ?? 0,
        oldPrice: data['old_price'] != null ? double.tryParse(data['old_price'].toString()) : null,
        image: data['image'] ?? '',
        imageUrl: data['image'] ?? '',
        images: (data['images'] as List?)?.cast<String>() ?? [],
        brand: data['brand'] ?? '',
        rating: (data['rating'] as num?)?.toDouble() ?? 0.0,
        reviewsCount: data['reviews_count'] ?? 0,
        stock: data['stock'] ?? 1,
        views: data['views'] ?? 0,
        isFeatured: data['is_featured'] ?? false,
        isTrending: data['is_trending'] ?? false,
        isNew: data['is_new'] ?? false,
        isBestOffer: data['is_best_offer'] ?? false,
        isActive: data['is_active'] ?? true,
        discountPercentage: (data['discount_percentage'] as num?)?.toDouble(),
      );
    }

    return GestureDetector(
      onTap: () {
        // Naviguer vers la page de détails du produit
        final productModel = createProductFromMap(product);
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => ProductDetailsScreen(product: productModel),
          ),
        );
      },
      child: Container(
        width: 100,
        margin: const EdgeInsets.only(right: 8),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(6),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.08),
              blurRadius: 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image
            Expanded(
              child: Container(
                decoration: BoxDecoration(
                  borderRadius: const BorderRadius.only(
                    topLeft: Radius.circular(6),
                    topRight: Radius.circular(6),
                  ),
                  color: Colors.grey[200],
                ),
                child: CachedNetworkImage(
                  imageUrl: product['image'] ?? '',
                  fit: BoxFit.cover,
                  placeholder: (context, url) => Container(
                    color: Colors.grey[300],
                    child: const Icon(Icons.image, size: 16),
                  ),
                  errorWidget: (context, url, error) => Container(
                    color: Colors.grey[300],
                    child: const Icon(Icons.broken_image, size: 16),
                  ),
                ),
              ),
            ),
            // Produit info
            Padding(
              padding: const EdgeInsets.all(4),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product['name'] ?? 'Produit',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    formatPrice(product['price']),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 9,
                      color: AppColors.primary,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

}

class ChatMessage {
  final String text;
  final bool isBot;
  final DateTime timestamp;
  final bool isError;
  final List<Map<String, dynamic>>? items; // Produits si l'IA en recommande

  ChatMessage({
    required this.text,
    required this.isBot,
    required this.timestamp,
    this.isError = false,
    this.items,
  });
}

