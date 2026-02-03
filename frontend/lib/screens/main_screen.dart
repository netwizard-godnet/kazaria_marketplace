import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../providers/cart_provider.dart';
import '../providers/product_provider.dart';
import '../services/cart_reminder_service.dart';
import '../services/notification_service.dart';
import '../widgets/notification_permission_dialog.dart';
import '../widgets/popup_widget.dart';
import 'home/home_screen.dart';
import 'categories/categories_screen.dart';
import 'stores/stores_screen.dart';
import 'cart/cart_screen.dart';
import 'profile/profile_screen.dart';

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _selectedIndex = 0;

  final List<Widget> _screens = const [
    HomeScreen(),
    CategoriesScreen(),
    StoresScreen(),
    CartScreen(),
    ProfileScreen(),
  ];

  @override
  void initState() {
    super.initState();
    // Précharger les données essentielles au démarrage
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final productProvider = Provider.of<ProductProvider>(context, listen: false);
      final cartProvider = Provider.of<CartProvider>(context, listen: false);
      
      // Charger les catégories en premier (données critiques pour l'UI)
      if (productProvider.categories.isEmpty) {
        print('🔄 [MAIN] Préchargement des catégories...');
        await productProvider.loadCategories();
      }
      
      // Charger le panier
      cartProvider.loadCart();
      
      // 🔔 Initialiser le service de rappel
      cartProvider.initializeReminder();
      
      // Définir le contexte pour le dialog
      CartReminderService().setContext(context);
      
      // 🔔 Demander la permission des notifications après un délai
      _requestNotificationPermission();
    });
  }

  /// Demander la permission des notifications avec un dialog personnalisé
  Future<void> _requestNotificationPermission() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final dialogShown = prefs.getBool('notification_permission_dialog_shown') ?? false;
      
      // Si le dialog a déjà été affiché, ne pas le réafficher
      if (dialogShown) {
        print('🔔 [MAIN] Dialog de permission déjà affiché');
        // Initialiser quand même le service pour les utilisateurs qui ont déjà accordé la permission
        await NotificationService().initialize();
        return;
      }

      // Attendre que l'app soit complètement chargée (3 secondes pour être sûr)
      await Future.delayed(const Duration(seconds: 3));

      // Vérifier si le widget est toujours monté
      if (!mounted) {
        print('⚠️ [MAIN] Widget non monté, annulation de la demande de permission');
        return;
      }

      // Afficher le dialog personnalisé
      bool? result;
      try {
        result = await NotificationPermissionDialog.show(context);
      } catch (e) {
        print('❌ [MAIN] Erreur affichage dialog: $e');
        return;
      }
      
      // Marquer que le dialog a été affiché
      await prefs.setBool('notification_permission_dialog_shown', true);
      
      // Vérifier à nouveau si le widget est toujours monté après le dialog
      if (!mounted) {
        print('⚠️ [MAIN] Widget non monté après le dialog');
        return;
      }
      
      if (result == true) {
        // L'utilisateur a accepté, initialiser le service de notifications
        print('✅ [MAIN] Utilisateur a accepté, initialisation du service...');
        
        // Attendre un peu pour que le dialog se ferme complètement et que l'UI se stabilise
        await Future.delayed(const Duration(milliseconds: 500));
        
        // Vérifier à nouveau si monté
        if (!mounted) {
          print('⚠️ [MAIN] Widget non monté après délai');
          return;
        }
        
        // Initialiser le service SANS context pour éviter de réafficher le dialog
        try {
          await NotificationService().initialize();
          print('✅ [MAIN] Service de notifications initialisé avec succès');
        } catch (e) {
          print('❌ [MAIN] Erreur initialisation service notifications: $e');
        }
      } else {
        // L'utilisateur a refusé
        print('🔔 [MAIN] Permission refusée par l\'utilisateur');
      }
    } catch (e, stackTrace) {
      print('❌ [MAIN] Erreur lors de la demande de permission: $e');
      print('Stack trace: $stackTrace');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      // PopupWidget - Gère l'affichage des pop-ups configurés via le dashboard admin
      body: Stack(
        children: [
          // IndexedStack garde tous les écrans en mémoire pour éviter le rechargement
          IndexedStack(
            index: _selectedIndex,
            children: _screens,
          ),
          // PopupWidget - Affiche les pop-ups de marketing
          const PopupWidget(),
        ],
      ),
      bottomNavigationBar: Consumer<CartProvider>(
        builder: (context, cartProvider, _) {
          return BottomNavigationBar(
            currentIndex: _selectedIndex,
            onTap: (index) {
              setState(() {
                _selectedIndex = index;
              });
            },
            items: [
              const BottomNavigationBarItem(
                icon: Icon(Icons.home_outlined),
                activeIcon: Icon(Icons.home),
                label: 'Accueil',
              ),
              const BottomNavigationBarItem(
                icon: Icon(Icons.grid_view_outlined),
                activeIcon: Icon(Icons.grid_view),
                label: 'Catégories',
              ),
              const BottomNavigationBarItem(
                icon: Icon(Icons.verified_outlined),
                activeIcon: Icon(Icons.verified),
                label: 'Boutiques',
              ),
              BottomNavigationBarItem(
                icon: Badge(
                  label: Text('${cartProvider.itemCount}'),
                  isLabelVisible: cartProvider.itemCount > 0,
                  child: const Icon(Icons.shopping_cart_outlined),
                ),
                activeIcon: Badge(
                  label: Text('${cartProvider.itemCount}'),
                  isLabelVisible: cartProvider.itemCount > 0,
                  child: const Icon(Icons.shopping_cart),
                ),
                label: 'Panier',
              ),
              const BottomNavigationBarItem(
                icon: Icon(Icons.person_outline),
                activeIcon: Icon(Icons.person),
                label: 'Profil',
              ),
            ],
          );
        },
      ),
    );
  }
}

