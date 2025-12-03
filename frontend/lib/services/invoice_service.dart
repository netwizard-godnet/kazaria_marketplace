import 'dart:io';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:path_provider/path_provider.dart';

/// 📄 Service de gestion des factures
/// Gère le cache, l'historique et les métadonnées des factures téléchargées
class InvoiceService {
  static const String _downloadHistoryKey = 'invoice_download_history';
  static const int maxCachedInvoices = 50;

  /// 📝 Enregistrer un téléchargement dans l'historique
  Future<void> recordDownload(String orderNumber, String filePath) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final history = prefs.getStringList(_downloadHistoryKey) ?? [];
      
      // Format: orderNumber|timestamp|filePath
      final record = '$orderNumber|${DateTime.now().millisecondsSinceEpoch}|$filePath';
      
      // Ajouter au début de la liste
      history.insert(0, record);
      
      // Limiter la taille de l'historique
      if (history.length > maxCachedInvoices) {
        // Supprimer les plus anciennes et leurs fichiers
        final toRemove = history.sublist(maxCachedInvoices);
        for (final item in toRemove) {
          final parts = item.split('|');
          if (parts.length >= 3) {
            final file = File(parts[2]);
            if (await file.exists()) {
              await file.delete();
            }
          }
        }
        
        // Garder seulement les 50 plus récentes
        history.removeRange(maxCachedInvoices, history.length);
      }
      
      await prefs.setStringList(_downloadHistoryKey, history);
      print('✅ [INVOICE_SERVICE] Historique mis à jour: ${history.length} factures');
    } catch (e) {
      print('❌ [INVOICE_SERVICE] Erreur enregistrement: $e');
    }
  }

  /// 🔍 Vérifier si une facture existe déjà
  Future<String?> getCachedInvoice(String orderNumber) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final history = prefs.getStringList(_downloadHistoryKey) ?? [];
      
      for (final item in history) {
        final parts = item.split('|');
        if (parts.length >= 3 && parts[0] == orderNumber) {
          final filePath = parts[2];
          final file = File(filePath);
          
          if (await file.exists()) {
            print('💾 [INVOICE_SERVICE] Facture en cache: $filePath');
            return filePath;
          } else {
            print('⚠️ [INVOICE_SERVICE] Fichier manquant, sera nettoyé');
          }
        }
      }
      
      print('📄 [INVOICE_SERVICE] Pas de cache pour $orderNumber');
      return null;
    } catch (e) {
      print('❌ [INVOICE_SERVICE] Erreur vérification cache: $e');
      return null;
    }
  }

  /// 📊 Obtenir l'historique des téléchargements
  Future<List<Map<String, dynamic>>> getDownloadHistory() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final history = prefs.getStringList(_downloadHistoryKey) ?? [];
      
      final List<Map<String, dynamic>> result = [];
      
      for (final item in history) {
        final parts = item.split('|');
        if (parts.length >= 3) {
          final file = File(parts[2]);
          if (await file.exists()) {
            result.add({
              'orderNumber': parts[0],
              'timestamp': int.parse(parts[1]),
              'filePath': parts[2],
              'exists': true,
            });
          }
        }
      }
      
      return result;
    } catch (e) {
      print('❌ [INVOICE_SERVICE] Erreur récupération historique: $e');
      return [];
    }
  }

  /// 🗑️ Nettoyer les factures anciennes
  Future<void> cleanOldInvoices({int daysOld = 30}) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final history = prefs.getStringList(_downloadHistoryKey) ?? [];
      final cutoffTimestamp = DateTime.now()
          .subtract(Duration(days: daysOld))
          .millisecondsSinceEpoch;
      
      final List<String> toKeep = [];
      int deletedCount = 0;
      
      for (final item in history) {
        final parts = item.split('|');
        if (parts.length >= 3) {
          final timestamp = int.parse(parts[1]);
          
          if (timestamp > cutoffTimestamp) {
            toKeep.add(item);
          } else {
            // Supprimer le fichier
            final file = File(parts[2]);
            if (await file.exists()) {
              await file.delete();
              deletedCount++;
            }
          }
        }
      }
      
      await prefs.setStringList(_downloadHistoryKey, toKeep);
      print('✅ [INVOICE_SERVICE] Nettoyage: $deletedCount factures supprimées');
    } catch (e) {
      print('❌ [INVOICE_SERVICE] Erreur nettoyage: $e');
    }
  }

  /// 📏 Obtenir la taille totale du cache
  Future<int> getCacheSize() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final history = prefs.getStringList(_downloadHistoryKey) ?? [];
      int totalSize = 0;
      
      for (final item in history) {
        final parts = item.split('|');
        if (parts.length >= 3) {
          final file = File(parts[2]);
          if (await file.exists()) {
            totalSize += await file.length();
          }
        }
      }
      
      return totalSize;
    } catch (e) {
      print('❌ [INVOICE_SERVICE] Erreur calcul taille: $e');
      return 0;
    }
  }

  /// 🗑️ Vider tout le cache
  Future<void> clearAllCache() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final history = prefs.getStringList(_downloadHistoryKey) ?? [];
      
      for (final item in history) {
        final parts = item.split('|');
        if (parts.length >= 3) {
          final file = File(parts[2]);
          if (await file.exists()) {
            await file.delete();
          }
        }
      }
      
      await prefs.remove(_downloadHistoryKey);
      print('✅ [INVOICE_SERVICE] Cache vidé');
    } catch (e) {
      print('❌ [INVOICE_SERVICE] Erreur vidage cache: $e');
    }
  }

  /// 🔄 Obtenir le chemin de stockage des factures
  Future<String> getInvoicesDirectory() async {
    Directory? directory;

    if (Platform.isAndroid) {
      directory = await getExternalStorageDirectory();
    } else {
      directory = await getApplicationDocumentsDirectory();
    }

    return directory?.path ?? '';
  }
}

