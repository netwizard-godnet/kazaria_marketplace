import 'package:flutter/material.dart';
import '../services/popup_service.dart';
import 'package:url_launcher/url_launcher.dart' as url_launcher;

class PopupWidget extends StatefulWidget {
  const PopupWidget({Key? key}) : super(key: key);

  @override
  State<PopupWidget> createState() => _PopupWidgetState();
}

class _PopupWidgetState extends State<PopupWidget> {
  final PopupService _popupService = PopupService();
  List<PopupModel> _popups = [];
  Set<int> _displayedPopups = {};

  @override
  void initState() {
    super.initState();
    _loadAndDisplayPopups();
  }

  Future<void> _loadAndDisplayPopups() async {
    try {
      final popups = await _popupService.getActivePopups();
      if (!mounted) return;

      setState(() {
        _popups = popups;
      });

      if (popups.isNotEmpty) {
        _displayPopupsSequentially();
      }
    } catch (e) {
      debugPrint('Error loading popups: $e');
    }
  }

  void _displayPopupsSequentially() {
    for (int i = 0; i < _popups.length; i++) {
      final popup = _popups[i];
      final delayMs = popup.delaySeconds * 1000 + (i * 5000);

      Future.delayed(Duration(milliseconds: delayMs), () {
        if (!mounted) return;

        if (popup.frequency == 'once' && _displayedPopups.contains(popup.id)) {
          return;
        }

        _displayedPopups.add(popup.id);
        _showPopupDialog(popup);
      });
    }
  }

  void _showPopupDialog(PopupModel popup) {
    showDialog(
      context: context,
      barrierDismissible: true,
      builder: (context) => PopupDialog(popup: popup),
    );
  }

  @override
  Widget build(BuildContext context) {
    return const SizedBox.shrink();
  }
}

class PopupDialog extends StatefulWidget {
  final PopupModel popup;

  const PopupDialog({Key? key, required this.popup}) : super(key: key);

  @override
  State<PopupDialog> createState() => _PopupDialogState();
}

class _PopupDialogState extends State<PopupDialog> {
  @override
  void initState() {
    super.initState();
    _trackImpression();
  }

  Future<void> _trackImpression() async {
    try {
      await PopupService().trackImpression(widget.popup.id);
    } catch (e) {
      debugPrint('Error tracking impression: $e');
    }
  }

  Future<void> _trackClick() async {
    try {
      await PopupService().trackClick(widget.popup.id);
    } catch (e) {
      debugPrint('Error tracking click: $e');
    }
  }

  Future<void> _launchUrl(String url) async {
    try {
      if (url.isEmpty) {
        debugPrint('Empty URL provided');
        return;
      }

      // Add http:// if not present
      String finalUrl = url;
      if (!url.startsWith('http://') && !url.startsWith('https://')) {
        finalUrl = 'https://$url';
      }

      debugPrint('Attempting to launch URL: $finalUrl');

      if (await url_launcher.canLaunchUrl(Uri.parse(finalUrl))) {
        await url_launcher.launchUrl(Uri.parse(finalUrl), 
          mode: url_launcher.LaunchMode.externalApplication);
        debugPrint('URL launched successfully');
      } else {
        debugPrint('Cannot launch URL: $finalUrl');
      }
    } catch (e) {
      debugPrint('Error launching URL: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.of(context).size.width;
    final dialogWidth = screenWidth > 600 ? 500.0 : screenWidth * 0.85;

    return Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: const EdgeInsets.all(16),
      child: Container(
        width: dialogWidth,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.2),
              blurRadius: 16,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Close button
            Align(
              alignment: Alignment.topRight,
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: GestureDetector(
                  onTap: () => Navigator.pop(context),
                  child: Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: Colors.grey[200],
                      borderRadius: BorderRadius.circular(50),
                    ),
                    child: Icon(
                      Icons.close,
                      size: 18,
                      color: Colors.grey[700],
                    ),
                  ),
                ),
              ),
            ),
            // Image
            if (widget.popup.image != null)
              Container(
                width: double.infinity,
                height: 200,
                margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(12),
                  image: DecorationImage(
                    image: NetworkImage(widget.popup.image!),
                    fit: BoxFit.cover,
                  ),
                ),
              ),
            // Title
            if (widget.popup.title != null && widget.popup.title!.isNotEmpty)
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Text(
                  widget.popup.title!,
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                    color: Colors.black87,
                  ),
                  textAlign: TextAlign.center,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            if (widget.popup.title != null && widget.popup.title!.isNotEmpty)
              const SizedBox(height: 12),
            // Description
            if (widget.popup.content != null && widget.popup.content!.isNotEmpty)
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Text(
                  widget.popup.content!,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w400,
                    color: Colors.grey[700],
                    height: 1.5,
                  ),
                  textAlign: TextAlign.center,
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            if (widget.popup.content != null && widget.popup.content!.isNotEmpty)
              const SizedBox(height: 20),
            // CTA Button
            if (widget.popup.ctaText != null && 
                widget.popup.ctaText!.isNotEmpty && 
                widget.popup.ctaUrl != null && 
                widget.popup.ctaUrl!.isNotEmpty)
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: Material(
                    color: Colors.transparent,
                    child: InkWell(
                      onTap: () async {
                        _trackClick();
                        await _launchUrl(widget.popup.ctaUrl!);
                        if (mounted) {
                          Navigator.pop(context);
                        }
                      },
                      borderRadius: BorderRadius.circular(10),
                      child: Container(
                        decoration: BoxDecoration(
                          color: Colors.blue,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Center(
                          child: Text(
                            widget.popup.ctaText!,
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            if (widget.popup.ctaText != null && 
                widget.popup.ctaText!.isNotEmpty && 
                widget.popup.ctaUrl != null && 
                widget.popup.ctaUrl!.isNotEmpty)
              const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }
}
