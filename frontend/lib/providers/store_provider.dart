import 'package:flutter/material.dart';
import '../models/store_model.dart';
import '../models/product_model.dart';
import '../services/store_service.dart';
import '../services/banner_service.dart';
import '../models/banner_model.dart';

class StoreProvider with ChangeNotifier {
  final StoreService _storeService = StoreService();
  final BannerService _bannerService = BannerService();

  // État des boutiques
  List<StoreModel> _stores = [];
  List<StoreModel> _popularStores = [];
  List<StoreModel> _verifiedStores = [];
  List<StoreModel> _officialStores = [];
  StoreModel? _selectedStore;
  List<StoreModel> _searchResults = [];

  // État de chargement
  bool _isLoading = false;
  bool _isLoadingPopular = false;
  bool _isLoadingVerified = false;
  bool _isLoadingOfficial = false;
  bool _isLoadingDetails = false;
  bool _isSearching = false;
  
  // Contrôle des chargements
  bool _popularStoresLoaded = false;
  bool _verifiedStoresLoaded = false;
  bool _officialStoresLoaded = false;

  // Erreurs
  String? _error;
  String? _searchError;

  // Pagination
  int _currentPage = 1;
  int _totalPages = 1;
  bool _hasMoreData = false;

  // Filtres
  String _selectedCategory = '';
  String _selectedCity = '';
  bool _verifiedOnly = false;

  // Carousel boutique
  List<BannerModel> _storeCarouselBanners = [];
  bool _isLoadingCarousel = false;

  // Meilleures offres boutiques officielles
  List<ProductModel> _bestOffersProducts = [];
  List<ProductModel> _officialNewProducts = [];
  bool _isLoadingBestOffers = false;
  bool _isLoadingOfficialNew = false;
  int _bestOffersTotal = 0;
  bool _bestOffersHasMore = false;
  int _officialNewTotal = 0;
  bool _officialNewHasMore = false;

  // Bannières publicitaires boutiques
  List<BannerModel> _storeAdsBanners = [];
  bool _isLoadingAds = false;

  // Getters
  List<StoreModel> get stores => _stores;
  List<StoreModel> get popularStores => _popularStores;
  List<StoreModel> get verifiedStores => _verifiedStores;
  List<StoreModel> get officialStores => _officialStores;
  StoreModel? get selectedStore => _selectedStore;
  List<StoreModel> get searchResults => _searchResults;

  bool get isLoading => _isLoading;
  bool get isLoadingPopular => _isLoadingPopular;
  bool get isLoadingVerified => _isLoadingVerified;
  bool get isLoadingOfficial => _isLoadingOfficial;
  bool get isLoadingDetails => _isLoadingDetails;
  bool get isSearching => _isSearching;

  String? get error => _error;
  String? get searchError => _searchError;

  int get currentPage => _currentPage;
  int get totalPages => _totalPages;
  bool get hasMoreData => _hasMoreData;

  String get selectedCategory => _selectedCategory;
  String get selectedCity => _selectedCity;
  bool get verifiedOnly => _verifiedOnly;

  List<BannerModel> get storeCarouselBanners => _storeCarouselBanners;
  bool get isLoadingCarousel => _isLoadingCarousel;

  List<ProductModel> get bestOffersProducts => _bestOffersProducts;
  List<ProductModel> get officialNewProducts => _officialNewProducts;
  bool get isLoadingBestOffers => _isLoadingBestOffers;
  bool get isLoadingOfficialNew => _isLoadingOfficialNew;
  int get bestOffersTotal => _bestOffersTotal;
  bool get bestOffersHasMore => _bestOffersHasMore;
  int get officialNewTotal => _officialNewTotal;
  bool get officialNewHasMore => _officialNewHasMore;

  List<BannerModel> get storeAdsBanners => _storeAdsBanners;
  bool get isLoadingAds => _isLoadingAds;

  int get storesCount => _stores.length;
  int get popularStoresCount => _popularStores.length;
  int get verifiedStoresCount => _verifiedStores.length;
  int get officialStoresCount => _officialStores.length;

  /// Charger toutes les boutiques
  Future<void> loadStores({
    bool refresh = false,
    String? category,
    String? city,
    bool verifiedOnly = false,
  }) async {
    if (refresh) {
      _currentPage = 1;
      _stores.clear();
      _error = null;
    }

    _isLoading = true;
    _selectedCategory = category ?? '';
    _selectedCity = city ?? '';
    _verifiedOnly = verifiedOnly;
    notifyListeners();

    try {
      print('🔄 [STORE_PROVIDER] Chargement des boutiques (page $_currentPage)');

      final response = await _storeService.getAllStores(
        page: _currentPage,
        category: category,
        city: city,
        verifiedOnly: verifiedOnly,
      );

      if (response['success']) {
        final List<dynamic> storesData = response['stores'] ?? [];
        final List<StoreModel> newStores = storesData
            .map((store) => StoreModel.fromJson(store))
            .toList();

        if (refresh) {
          _stores = newStores;
        } else {
          _stores.addAll(newStores);
        }

        // Gestion de la pagination
        final pagination = response['pagination'] ?? {};
        _totalPages = pagination['last_page'] ?? 1;
        _hasMoreData = _currentPage < _totalPages;

        print('✅ [STORE_PROVIDER] Boutiques chargées: ${_stores.length}');
      } else {
        _error = response['message'] ?? 'Erreur lors du chargement des boutiques';
        print('❌ [STORE_PROVIDER] Erreur: $_error');
      }
    } catch (e) {
      _error = e.toString();
      print('💥 [STORE_PROVIDER] Exception: $_error');
    }

    _isLoading = false;
    notifyListeners();
  }

  /// Charger la page suivante
  Future<void> loadMoreStores() async {
    if (_hasMoreData && !_isLoading) {
      _currentPage++;
      await loadStores();
    }
  }

  /// Charger les boutiques populaires
  Future<void> loadPopularStores({bool forceRefresh = false}) async {
    // Vérifier le cache sauf si on force le rafraîchissement
    if (!forceRefresh && (_isLoadingPopular || _popularStoresLoaded)) {
      print('🚫 [STORE_PROVIDER] Boutiques populaires déjà en cache');
      return;
    }
    
    print('🔄 [STORE_PROVIDER] Début chargement boutiques populaires');
    _isLoadingPopular = true;
    notifyListeners();

    try {
      final response = await _storeService.getPopularStores();

      if (response['success']) {
        final List<dynamic> storesData = response['stores'] ?? [];
        _popularStores = storesData
            .map((store) => StoreModel.fromJson(store))
            .toList();

        _popularStoresLoaded = true;
        print('✅ [STORE_PROVIDER] Boutiques populaires chargées: ${_popularStores.length}');
      } else {
        print('❌ [STORE_PROVIDER] Erreur populaires: ${response['message']}');
      }
    } catch (e) {
      print('💥 [STORE_PROVIDER] Exception populaires: $e');
    }

    _isLoadingPopular = false;
    notifyListeners();
  }

  /// Charger les boutiques vérifiées
  Future<void> loadVerifiedStores({bool forceRefresh = false}) async {
    // Vérifier le cache sauf si on force le rafraîchissement
    if (!forceRefresh && (_isLoadingVerified || _verifiedStoresLoaded)) {
      print('🚫 [STORE_PROVIDER] Boutiques vérifiées déjà en cache');
      return;
    }
    
    _isLoadingVerified = true;
    notifyListeners();

    try {
      print('🔄 [STORE_PROVIDER] Chargement boutiques vérifiées');

      final response = await _storeService.getVerifiedStores();

      if (response['success']) {
        final List<dynamic> storesData = response['stores'] ?? [];
        _verifiedStores = storesData
            .map((store) => StoreModel.fromJson(store))
            .toList();

        _verifiedStoresLoaded = true;
        print('✅ [STORE_PROVIDER] Boutiques vérifiées chargées: ${_verifiedStores.length}');
      } else {
        print('❌ [STORE_PROVIDER] Erreur vérifiées: ${response['message']}');
      }
    } catch (e) {
      print('💥 [STORE_PROVIDER] Exception vérifiées: $e');
    }

    _isLoadingVerified = false;
    notifyListeners();
  }

  /// Charger les boutiques officielles
  Future<void> loadOfficialStores({bool forceRefresh = false}) async {
    // Vérifier le cache sauf si on force le rafraîchissement
    if (!forceRefresh && (_isLoadingOfficial || _officialStoresLoaded)) {
      print('🚫 [STORE_PROVIDER] Boutiques officielles déjà en cache');
      return;
    }
    
    _isLoadingOfficial = true;
    notifyListeners();

    try {
      print('🔄 [STORE_PROVIDER] Chargement boutiques officielles');

      final response = await _storeService.getVerifiedStores(
        limit: 20,
        officialOnly: true,
      );

      if (response['success']) {
        final List<dynamic> storesData = response['stores'] ?? [];
        // Filtrer uniquement les boutiques officielles
        _officialStores = storesData
            .map((store) => StoreModel.fromJson(store))
            .where((store) => store.isOfficial)
            .toList();

        _officialStoresLoaded = true;
        print('✅ [STORE_PROVIDER] Boutiques officielles chargées: ${_officialStores.length}');
      } else {
        print('❌ [STORE_PROVIDER] Erreur officielles: ${response['message']}');
      }
    } catch (e) {
      print('💥 [STORE_PROVIDER] Exception officielles: $e');
    }

    _isLoadingOfficial = false;
    notifyListeners();
  }

  /// Charger les détails d'une boutique
  Future<void> loadStoreDetails(int storeId) async {
    _isLoadingDetails = true;
    _error = null;
    notifyListeners();

    try {
      print('🔄 [STORE_PROVIDER] Chargement détails boutique: $storeId');

      final response = await _storeService.getStoreDetails(storeId);

      if (response['success']) {
        _selectedStore = StoreModel.fromJson(response['store']);
        print('✅ [STORE_PROVIDER] Détails boutique chargés');
      } else {
        _error = response['message'] ?? 'Erreur lors du chargement des détails';
        print('❌ [STORE_PROVIDER] Erreur détails: $_error');
      }
    } catch (e) {
      _error = e.toString();
      print('💥 [STORE_PROVIDER] Exception détails: $_error');
    }

    _isLoadingDetails = false;
    notifyListeners();
  }

  /// Rechercher des boutiques
  Future<void> searchStores(String query) async {
    if (query.trim().isEmpty) {
      _searchResults.clear();
      _searchError = null;
      notifyListeners();
      return;
    }

    _isSearching = true;
    _searchError = null;
    notifyListeners();

    try {
      print('🔄 [STORE_PROVIDER] Recherche boutiques: $query');

      final response = await _storeService.searchStores(query);

      if (response['success']) {
        final List<dynamic> storesData = response['stores'] ?? [];
        _searchResults = storesData
            .map((store) => StoreModel.fromJson(store))
            .toList();

        print('✅ [STORE_PROVIDER] Recherche terminée: ${_searchResults.length} résultats');
      } else {
        _searchError = response['message'] ?? 'Erreur lors de la recherche';
        print('❌ [STORE_PROVIDER] Erreur recherche: $_searchError');
      }
    } catch (e) {
      _searchError = e.toString();
      print('💥 [STORE_PROVIDER] Exception recherche: $_searchError');
    }

    _isSearching = false;
    notifyListeners();
  }

  /// Effacer les résultats de recherche
  void clearSearch() {
    _searchResults.clear();
    _searchError = null;
    notifyListeners();
  }

  /// Effacer l'erreur
  void clearError() {
    _error = null;
    notifyListeners();
  }

  /// Effacer l'erreur de recherche
  void clearSearchError() {
    _searchError = null;
    notifyListeners();
  }

  /// Obtenir une boutique par ID
  StoreModel? getStoreById(int storeId) {
    try {
      return _stores.firstWhere((store) => store.id == storeId);
    } catch (e) {
      return null;
    }
  }

  /// Obtenir une boutique populaire par ID
  StoreModel? getPopularStoreById(int storeId) {
    try {
      return _popularStores.firstWhere((store) => store.id == storeId);
    } catch (e) {
      return null;
    }
  }

  /// Obtenir une boutique vérifiée par ID
  StoreModel? getVerifiedStoreById(int storeId) {
    try {
      return _verifiedStores.firstWhere((store) => store.id == storeId);
    } catch (e) {
      return null;
    }
  }

  /// Obtenir une boutique officielle par ID
  StoreModel? getOfficialStoreById(int storeId) {
    try {
      return _officialStores.firstWhere((store) => store.id == storeId);
    } catch (e) {
      return null;
    }
  }

  /// Vérifier si une boutique est dans les favoris (si applicable)
  bool isStoreFavorited(int storeId) {
    // TODO: Implémenter si nécessaire
    return false;
  }

  /// Charger les images du carousel boutique
  Future<void> loadStoreCarousel() async {
    _isLoadingCarousel = true;
    notifyListeners();

    try {
      print('🔄 [STORE_PROVIDER] Chargement carousel boutique');
      final response = await _bannerService.getActiveBanners(
        placement: 'boutique_carousel',
      );

      if (response['success']) {
        _storeCarouselBanners = response['banners'] ?? [];
        print('✅ [STORE_PROVIDER] Carousel chargé: ${_storeCarouselBanners.length} images');
      } else {
        print('❌ [STORE_PROVIDER] Erreur carousel: ${response['message']}');
        _storeCarouselBanners = [];
      }
    } catch (e) {
      print('💥 [STORE_PROVIDER] Exception carousel: $e');
      _storeCarouselBanners = [];
    }

    _isLoadingCarousel = false;
    notifyListeners();
  }

  /// Charger les meilleures offres des boutiques officielles
  Future<void> loadBestOffers({bool forceRefresh = false}) async {
    if (!forceRefresh && (_isLoadingBestOffers || _bestOffersProducts.isNotEmpty)) {
      print('🚫 [STORE_PROVIDER] Meilleures offres déjà chargées');
      return;
    }

    _isLoadingBestOffers = true;
    notifyListeners();

    try {
      print('🔄 [STORE_PROVIDER] Chargement meilleures offres');
      final response = await _storeService.getOfficialStoresBestOffers(limit: 12);

      if (response['success']) {
        final List<dynamic> productsData = response['products'] ?? [];
        _bestOffersProducts = productsData
            .map((product) => ProductModel.fromJson(product))
            .toList();
        _bestOffersTotal = response['total'] ?? _bestOffersProducts.length;
        _bestOffersHasMore = response['has_more'] ?? (_bestOffersTotal > _bestOffersProducts.length);
        print('✅ [STORE_PROVIDER] Meilleures offres chargées: ${_bestOffersProducts.length}/${_bestOffersTotal} produits');
      } else {
        print('❌ [STORE_PROVIDER] Erreur meilleures offres: ${response['message']}');
        _bestOffersProducts = [];
        _bestOffersTotal = 0;
        _bestOffersHasMore = false;
      }
    } catch (e) {
      print('💥 [STORE_PROVIDER] Exception meilleures offres: $e');
      _bestOffersProducts = [];
    }

    _isLoadingBestOffers = false;
    notifyListeners();
  }

  /// Charger les nouveautés des boutiques officielles
  Future<void> loadOfficialNewProducts({bool forceRefresh = false}) async {
    if (!forceRefresh && (_isLoadingOfficialNew || _officialNewProducts.isNotEmpty)) {
      print('🚫 [STORE_PROVIDER] Nouveautés officielles déjà chargées');
      return;
    }

    _isLoadingOfficialNew = true;
    notifyListeners();

    try {
      print('🔄 [STORE_PROVIDER] Chargement nouveautés officielles');
      final response = await _storeService.getOfficialStoresNewProducts(limit: 12);

      if (response['success']) {
        final List<dynamic> productsData = response['products'] ?? [];
        _officialNewProducts = productsData
            .map((product) => ProductModel.fromJson(product))
            .toList();
        _officialNewTotal = response['total'] ?? _officialNewProducts.length;
        _officialNewHasMore = response['has_more'] ?? (_officialNewTotal > _officialNewProducts.length);
        print('✅ [STORE_PROVIDER] Nouveautés officielles chargées: ${_officialNewProducts.length}/${_officialNewTotal}');
      } else {
        print('❌ [STORE_PROVIDER] Erreur nouveautés officielles: ${response['message']}');
        _officialNewProducts = [];
        _officialNewTotal = 0;
        _officialNewHasMore = false;
      }
    } catch (e) {
      print('💥 [STORE_PROVIDER] Exception nouveautés officielles: $e');
      _officialNewProducts = [];
    }

    _isLoadingOfficialNew = false;
    notifyListeners();
  }

  /// Charger les bannières publicitaires des boutiques
  Future<void> loadStoreAds() async {
    if (_isLoadingAds || _storeAdsBanners.isNotEmpty) {
      print('🚫 [STORE_PROVIDER] Bannières pub déjà chargées');
      return;
    }

    _isLoadingAds = true;
    notifyListeners();

    try {
      print('🔄 [STORE_PROVIDER] Chargement bannières pub boutique');
      final response = await _bannerService.getActiveBanners(
        placement: 'boutique_ads',
      );

      if (response['success']) {
        _storeAdsBanners = response['banners'] ?? [];
        print('✅ [STORE_PROVIDER] Bannières pub chargées: ${_storeAdsBanners.length}');
      } else {
        print('❌ [STORE_PROVIDER] Erreur bannières pub: ${response['message']}');
        _storeAdsBanners = [];
      }
    } catch (e) {
      print('💥 [STORE_PROVIDER] Exception bannières pub: $e');
      _storeAdsBanners = [];
    }

    _isLoadingAds = false;
    notifyListeners();
  }

  /// Actualiser toutes les données
  Future<void> refreshAll() async {
    await Future.wait([
      loadStores(refresh: true),
      loadPopularStores(),
      loadVerifiedStores(),
      loadOfficialStores(),
      loadStoreCarousel(),
      loadBestOffers(forceRefresh: true),
      loadOfficialNewProducts(forceRefresh: true),
      loadStoreAds(),
    ]);
  }

  @override
  void dispose() {
    super.dispose();
  }
}
