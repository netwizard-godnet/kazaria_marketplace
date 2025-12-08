import 'package:flutter/material.dart';
import '../../utils/constants.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';
import '../../utils/helpers.dart';

class InboxScreen extends StatefulWidget {
  const InboxScreen({super.key});

  @override
  State<InboxScreen> createState() => _InboxScreenState();
}

class _InboxScreenState extends State<InboxScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  List<Map<String, dynamic>> _tickets = [];
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadTickets();
  }

  Future<void> _loadTickets() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final response = await _apiService.get(
        ApiConfig.inbox,
        requiresAuth: true,
      );
      
      if (response['success'] && response['tickets'] != null) {
        setState(() {
          _tickets = List<Map<String, dynamic>>.from(response['tickets']);
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response['message'] ?? 'Erreur lors du chargement des messages';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'Erreur de connexion: $e';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Boîte de réception'),
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadTickets,
            tooltip: 'Rafraîchir',
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(),
            )
          : _error != null
              ? _buildErrorState()
              : _tickets.isEmpty
                  ? _buildEmptyState()
                  : _buildMessagesList(),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppSizes.paddingLarge),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(AppSizes.paddingLarge),
              decoration: BoxDecoration(
                color: AppColors.errorLight,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.error_outline,
                color: AppColors.error,
                size: 64,
              ),
            ),
            const SizedBox(height: AppSizes.space4),
            Text(
              'Erreur de chargement',
              style: AppTextStyles.headlineMedium.copyWith(
                color: AppColors.textDark,
              ),
            ),
            const SizedBox(height: AppSizes.space2),
            Text(
              _error!,
              style: AppTextStyles.bodyMedium.copyWith(
                color: AppColors.textMuted,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppSizes.space4),
            ElevatedButton(
              onPressed: _loadTickets,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: AppColors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 32,
                  vertical: 16,
                ),
              ),
              child: const Text('Réessayer'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppSizes.paddingLarge),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(AppSizes.paddingLarge),
              decoration: BoxDecoration(
                color: AppColors.primary.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.inbox,
                color: AppColors.primary,
                size: 64,
              ),
            ),
            const SizedBox(height: AppSizes.space4),
            Text(
              'Aucun message pour le moment',
              style: AppTextStyles.headlineMedium.copyWith(
                color: AppColors.textDark,
              ),
            ),
            const SizedBox(height: AppSizes.space2),
            Text(
              'Les échanges avec le support apparaîtront ici dès qu\'un message vous sera assigné.',
              style: AppTextStyles.bodyMedium.copyWith(
                color: AppColors.textMuted,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppSizes.space6),
            ElevatedButton.icon(
              onPressed: () {
                // TODO: Ouvrir le formulaire de contact
                Navigator.pop(context);
              },
              icon: const Icon(Icons.email),
              label: const Text('Contacter le support'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: AppColors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 16,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMessagesList() {
    return RefreshIndicator(
      onRefresh: _loadTickets,
      child: ListView.builder(
        padding: const EdgeInsets.all(AppSizes.paddingMedium),
        itemCount: _tickets.length,
        itemBuilder: (context, index) {
          final ticket = _tickets[index];
          return _buildTicketCard(ticket);
        },
      ),
    );
  }

  Widget _buildTicketCard(Map<String, dynamic> ticket) {
    final status = ticket['status'] ?? 'open';
    final priority = ticket['priority'] ?? 'medium';
    final statusColor = _getStatusColor(status);
    final priorityColor = _getPriorityColor(priority);
    final statusLabel = _getStatusLabel(status);
    final priorityLabel = _getPriorityLabel(priority);
    final latestMessage = ticket['latest_message'];
    
    return Container(
      margin: const EdgeInsets.only(bottom: AppSizes.space3),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(AppSizes.radiusLarge),
        boxShadow: AppShadows.shadowMD,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () {
            // TODO: Ouvrir le détail du ticket
          },
          borderRadius: BorderRadius.circular(AppSizes.radiusLarge),
          child: Padding(
            padding: const EdgeInsets.all(AppSizes.paddingMedium),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(AppSizes.radiusMedium),
                      ),
                      child: Icon(
                        Icons.support_agent,
                        color: AppColors.primary,
                        size: 24,
                      ),
                    ),
                    const SizedBox(width: AppSizes.space3),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            ticket['subject'] ?? 'Sans objet',
                            style: AppTextStyles.bodyLarge.copyWith(
                              fontWeight: FontWeight.w600,
                              color: AppColors.textDark,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            '#${ticket['ticket_number'] ?? ''}',
                            style: AppTextStyles.caption.copyWith(
                              color: AppColors.textLight,
                            ),
                          ),
                        ],
                      ),
                    ),
                    if (ticket['has_unread'] == true)
                      Container(
                        width: 12,
                        height: 12,
                        decoration: const BoxDecoration(
                          color: AppColors.primary,
                          shape: BoxShape.circle,
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: AppSizes.space3),
                if (latestMessage != null) ...[
                  Text(
                    latestMessage['message'] ?? '',
                    style: AppTextStyles.bodySmall.copyWith(
                      color: AppColors.textMedium,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: AppSizes.space2),
                ],
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: statusColor.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(4),
                        border: Border.all(color: statusColor.withOpacity(0.3)),
                      ),
                      child: Text(
                        statusLabel,
                        style: AppTextStyles.caption.copyWith(
                          color: statusColor,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    const SizedBox(width: AppSizes.space2),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: priorityColor.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(4),
                        border: Border.all(color: priorityColor.withOpacity(0.3)),
                      ),
                      child: Text(
                        priorityLabel,
                        style: AppTextStyles.caption.copyWith(
                          color: priorityColor,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    const Spacer(),
                    Text(
                      ticket['updated_at'] != null
                          ? Helpers.formatDateTime(DateTime.parse(ticket['updated_at']))
                          : '',
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.textLight,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'open':
        return AppColors.info;
      case 'pending':
        return AppColors.warning;
      case 'resolved':
        return AppColors.success;
      case 'closed':
        return AppColors.textMedium;
      case 'escalated':
        return AppColors.error;
      default:
        return AppColors.textLight;
    }
  }

  String _getStatusLabel(String status) {
    switch (status) {
      case 'open':
        return 'Ouvert';
      case 'pending':
        return 'En attente';
      case 'resolved':
        return 'Résolu';
      case 'closed':
        return 'Fermé';
      case 'escalated':
        return 'Escaladé';
      default:
        return status;
    }
  }

  Color _getPriorityColor(String priority) {
    switch (priority) {
      case 'low':
        return AppColors.success;
      case 'medium':
        return AppColors.warning;
      case 'high':
        return AppColors.error;
      case 'urgent':
        return AppColors.error;
      default:
        return AppColors.textLight;
    }
  }

  String _getPriorityLabel(String priority) {
    switch (priority) {
      case 'low':
        return 'Basse';
      case 'medium':
        return 'Moyenne';
      case 'high':
        return 'Haute';
      case 'urgent':
        return 'Urgente';
      default:
        return priority;
    }
  }
}
