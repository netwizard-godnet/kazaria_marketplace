import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:intl/intl.dart' as intl;

import 'app_localizations_en.dart';
import 'app_localizations_fr.dart';

// ignore_for_file: type=lint

/// Callers can lookup localized strings with an instance of AppLocalizations
/// returned by `AppLocalizations.of(context)`.
///
/// Applications need to include `AppLocalizations.delegate()` in their app's
/// `localizationDelegates` list, and the locales they support in the app's
/// `supportedLocales` list. For example:
///
/// ```dart
/// import 'l10n/app_localizations.dart';
///
/// return MaterialApp(
///   localizationsDelegates: AppLocalizations.localizationsDelegates,
///   supportedLocales: AppLocalizations.supportedLocales,
///   home: MyApplicationHome(),
/// );
/// ```
///
/// ## Update pubspec.yaml
///
/// Please make sure to update your pubspec.yaml to include the following
/// packages:
///
/// ```yaml
/// dependencies:
///   # Internationalization support.
///   flutter_localizations:
///     sdk: flutter
///   intl: any # Use the pinned version from flutter_localizations
///
///   # Rest of dependencies
/// ```
///
/// ## iOS Applications
///
/// iOS applications define key application metadata, including supported
/// locales, in an Info.plist file that is built into the application bundle.
/// To configure the locales supported by your app, you’ll need to edit this
/// file.
///
/// First, open your project’s ios/Runner.xcworkspace Xcode workspace file.
/// Then, in the Project Navigator, open the Info.plist file under the Runner
/// project’s Runner folder.
///
/// Next, select the Information Property List item, select Add Item from the
/// Editor menu, then select Localizations from the pop-up menu.
///
/// Select and expand the newly-created Localizations item then, for each
/// locale your application supports, add a new item and select the locale
/// you wish to add from the pop-up menu in the Value field. This list should
/// be consistent with the languages listed in the AppLocalizations.supportedLocales
/// property.
abstract class AppLocalizations {
  AppLocalizations(String locale)
    : localeName = intl.Intl.canonicalizedLocale(locale.toString());

  final String localeName;

  static AppLocalizations? of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations);
  }

  static const LocalizationsDelegate<AppLocalizations> delegate =
      _AppLocalizationsDelegate();

  /// A list of this localizations delegate along with the default localizations
  /// delegates.
  ///
  /// Returns a list of localizations delegates containing this delegate along with
  /// GlobalMaterialLocalizations.delegate, GlobalCupertinoLocalizations.delegate,
  /// and GlobalWidgetsLocalizations.delegate.
  ///
  /// Additional delegates can be added by appending to this list in
  /// MaterialApp. This list does not have to be used at all if a custom list
  /// of delegates is preferred or required.
  static const List<LocalizationsDelegate<dynamic>> localizationsDelegates =
      <LocalizationsDelegate<dynamic>>[
        delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
      ];

  /// A list of this localizations delegate's supported locales.
  static const List<Locale> supportedLocales = <Locale>[
    Locale('en'),
    Locale('fr'),
  ];

  /// No description provided for @appName.
  ///
  /// In fr, this message translates to:
  /// **'Kazaria'**
  String get appName;

  /// No description provided for @welcome.
  ///
  /// In fr, this message translates to:
  /// **'Bienvenue'**
  String get welcome;

  /// No description provided for @login.
  ///
  /// In fr, this message translates to:
  /// **'Connexion'**
  String get login;

  /// No description provided for @register.
  ///
  /// In fr, this message translates to:
  /// **'S\'inscrire'**
  String get register;

  /// No description provided for @logout.
  ///
  /// In fr, this message translates to:
  /// **'Déconnexion'**
  String get logout;

  /// No description provided for @email.
  ///
  /// In fr, this message translates to:
  /// **'Email'**
  String get email;

  /// No description provided for @password.
  ///
  /// In fr, this message translates to:
  /// **'Mot de passe'**
  String get password;

  /// No description provided for @confirmPassword.
  ///
  /// In fr, this message translates to:
  /// **'Confirmer le mot de passe'**
  String get confirmPassword;

  /// No description provided for @forgotPassword.
  ///
  /// In fr, this message translates to:
  /// **'Mot de passe oublié ?'**
  String get forgotPassword;

  /// No description provided for @name.
  ///
  /// In fr, this message translates to:
  /// **'Nom'**
  String get name;

  /// No description provided for @phone.
  ///
  /// In fr, this message translates to:
  /// **'Téléphone'**
  String get phone;

  /// No description provided for @home.
  ///
  /// In fr, this message translates to:
  /// **'Accueil'**
  String get home;

  /// No description provided for @categories.
  ///
  /// In fr, this message translates to:
  /// **'Catégories'**
  String get categories;

  /// No description provided for @stores.
  ///
  /// In fr, this message translates to:
  /// **'Boutiques'**
  String get stores;

  /// No description provided for @cart.
  ///
  /// In fr, this message translates to:
  /// **'Panier'**
  String get cart;

  /// No description provided for @profile.
  ///
  /// In fr, this message translates to:
  /// **'Profil'**
  String get profile;

  /// No description provided for @search.
  ///
  /// In fr, this message translates to:
  /// **'Rechercher'**
  String get search;

  /// No description provided for @searchProducts.
  ///
  /// In fr, this message translates to:
  /// **'Rechercher des produits...'**
  String get searchProducts;

  /// No description provided for @products.
  ///
  /// In fr, this message translates to:
  /// **'Produits'**
  String get products;

  /// No description provided for @product.
  ///
  /// In fr, this message translates to:
  /// **'Produit'**
  String get product;

  /// No description provided for @price.
  ///
  /// In fr, this message translates to:
  /// **'Prix'**
  String get price;

  /// No description provided for @description.
  ///
  /// In fr, this message translates to:
  /// **'Description'**
  String get description;

  /// No description provided for @addToCart.
  ///
  /// In fr, this message translates to:
  /// **'Ajouter au panier'**
  String get addToCart;

  /// No description provided for @buyNow.
  ///
  /// In fr, this message translates to:
  /// **'Acheter maintenant'**
  String get buyNow;

  /// No description provided for @outOfStock.
  ///
  /// In fr, this message translates to:
  /// **'Rupture de stock'**
  String get outOfStock;

  /// No description provided for @inStock.
  ///
  /// In fr, this message translates to:
  /// **'En stock'**
  String get inStock;

  /// No description provided for @orders.
  ///
  /// In fr, this message translates to:
  /// **'Commandes'**
  String get orders;

  /// No description provided for @order.
  ///
  /// In fr, this message translates to:
  /// **'Commande'**
  String get order;

  /// No description provided for @orderNumber.
  ///
  /// In fr, this message translates to:
  /// **'Numéro de commande'**
  String get orderNumber;

  /// No description provided for @orderDate.
  ///
  /// In fr, this message translates to:
  /// **'Date de commande'**
  String get orderDate;

  /// No description provided for @orderStatus.
  ///
  /// In fr, this message translates to:
  /// **'Statut de la commande'**
  String get orderStatus;

  /// No description provided for @orderTotal.
  ///
  /// In fr, this message translates to:
  /// **'Total de la commande'**
  String get orderTotal;

  /// No description provided for @myOrders.
  ///
  /// In fr, this message translates to:
  /// **'Mes commandes'**
  String get myOrders;

  /// No description provided for @favorites.
  ///
  /// In fr, this message translates to:
  /// **'Favoris'**
  String get favorites;

  /// No description provided for @addToFavorites.
  ///
  /// In fr, this message translates to:
  /// **'Ajouter aux favoris'**
  String get addToFavorites;

  /// No description provided for @removeFromFavorites.
  ///
  /// In fr, this message translates to:
  /// **'Retirer des favoris'**
  String get removeFromFavorites;

  /// No description provided for @checkout.
  ///
  /// In fr, this message translates to:
  /// **'Passer commande'**
  String get checkout;

  /// No description provided for @payment.
  ///
  /// In fr, this message translates to:
  /// **'Paiement'**
  String get payment;

  /// No description provided for @paymentMethod.
  ///
  /// In fr, this message translates to:
  /// **'Méthode de paiement'**
  String get paymentMethod;

  /// No description provided for @cashOnDelivery.
  ///
  /// In fr, this message translates to:
  /// **'Paiement à la livraison'**
  String get cashOnDelivery;

  /// No description provided for @mobileMoney.
  ///
  /// In fr, this message translates to:
  /// **'Mobile Money'**
  String get mobileMoney;

  /// No description provided for @creditCard.
  ///
  /// In fr, this message translates to:
  /// **'Carte bancaire'**
  String get creditCard;

  /// No description provided for @shipping.
  ///
  /// In fr, this message translates to:
  /// **'Livraison'**
  String get shipping;

  /// No description provided for @shippingAddress.
  ///
  /// In fr, this message translates to:
  /// **'Adresse de livraison'**
  String get shippingAddress;

  /// No description provided for @billingAddress.
  ///
  /// In fr, this message translates to:
  /// **'Adresse de facturation'**
  String get billingAddress;

  /// No description provided for @address.
  ///
  /// In fr, this message translates to:
  /// **'Adresse'**
  String get address;

  /// No description provided for @city.
  ///
  /// In fr, this message translates to:
  /// **'Ville'**
  String get city;

  /// No description provided for @region.
  ///
  /// In fr, this message translates to:
  /// **'Région'**
  String get region;

  /// No description provided for @country.
  ///
  /// In fr, this message translates to:
  /// **'Pays'**
  String get country;

  /// No description provided for @postalCode.
  ///
  /// In fr, this message translates to:
  /// **'Code postal'**
  String get postalCode;

  /// No description provided for @total.
  ///
  /// In fr, this message translates to:
  /// **'Total'**
  String get total;

  /// No description provided for @subtotal.
  ///
  /// In fr, this message translates to:
  /// **'Sous-total'**
  String get subtotal;

  /// No description provided for @discount.
  ///
  /// In fr, this message translates to:
  /// **'Réduction'**
  String get discount;

  /// No description provided for @shipping_cost.
  ///
  /// In fr, this message translates to:
  /// **'Frais de livraison'**
  String get shipping_cost;

  /// No description provided for @tax.
  ///
  /// In fr, this message translates to:
  /// **'Taxes'**
  String get tax;

  /// No description provided for @reviews.
  ///
  /// In fr, this message translates to:
  /// **'Avis'**
  String get reviews;

  /// No description provided for @review.
  ///
  /// In fr, this message translates to:
  /// **'Avis'**
  String get review;

  /// No description provided for @writeReview.
  ///
  /// In fr, this message translates to:
  /// **'Écrire un avis'**
  String get writeReview;

  /// No description provided for @rating.
  ///
  /// In fr, this message translates to:
  /// **'Note'**
  String get rating;

  /// No description provided for @noReviews.
  ///
  /// In fr, this message translates to:
  /// **'Aucun avis pour le moment'**
  String get noReviews;

  /// No description provided for @notifications.
  ///
  /// In fr, this message translates to:
  /// **'Notifications'**
  String get notifications;

  /// No description provided for @noNotifications.
  ///
  /// In fr, this message translates to:
  /// **'Aucune notification'**
  String get noNotifications;

  /// No description provided for @markAllAsRead.
  ///
  /// In fr, this message translates to:
  /// **'Tout marquer comme lu'**
  String get markAllAsRead;

  /// No description provided for @settings.
  ///
  /// In fr, this message translates to:
  /// **'Paramètres'**
  String get settings;

  /// No description provided for @language.
  ///
  /// In fr, this message translates to:
  /// **'Langue'**
  String get language;

  /// No description provided for @theme.
  ///
  /// In fr, this message translates to:
  /// **'Thème'**
  String get theme;

  /// No description provided for @about.
  ///
  /// In fr, this message translates to:
  /// **'À propos'**
  String get about;

  /// No description provided for @helpAndSupport.
  ///
  /// In fr, this message translates to:
  /// **'Aide et support'**
  String get helpAndSupport;

  /// No description provided for @termsAndConditions.
  ///
  /// In fr, this message translates to:
  /// **'Conditions générales'**
  String get termsAndConditions;

  /// No description provided for @privacyPolicy.
  ///
  /// In fr, this message translates to:
  /// **'Politique de confidentialité'**
  String get privacyPolicy;

  /// No description provided for @save.
  ///
  /// In fr, this message translates to:
  /// **'Enregistrer'**
  String get save;

  /// No description provided for @cancel.
  ///
  /// In fr, this message translates to:
  /// **'Annuler'**
  String get cancel;

  /// No description provided for @delete.
  ///
  /// In fr, this message translates to:
  /// **'Supprimer'**
  String get delete;

  /// No description provided for @edit.
  ///
  /// In fr, this message translates to:
  /// **'Modifier'**
  String get edit;

  /// No description provided for @update.
  ///
  /// In fr, this message translates to:
  /// **'Mettre à jour'**
  String get update;

  /// No description provided for @confirm.
  ///
  /// In fr, this message translates to:
  /// **'Confirmer'**
  String get confirm;

  /// No description provided for @yes.
  ///
  /// In fr, this message translates to:
  /// **'Oui'**
  String get yes;

  /// No description provided for @no.
  ///
  /// In fr, this message translates to:
  /// **'Non'**
  String get no;

  /// No description provided for @ok.
  ///
  /// In fr, this message translates to:
  /// **'OK'**
  String get ok;

  /// No description provided for @done.
  ///
  /// In fr, this message translates to:
  /// **'Terminé'**
  String get done;

  /// No description provided for @back.
  ///
  /// In fr, this message translates to:
  /// **'Retour'**
  String get back;

  /// No description provided for @next.
  ///
  /// In fr, this message translates to:
  /// **'Suivant'**
  String get next;

  /// No description provided for @skip.
  ///
  /// In fr, this message translates to:
  /// **'Passer'**
  String get skip;

  /// No description provided for @loading.
  ///
  /// In fr, this message translates to:
  /// **'Chargement...'**
  String get loading;

  /// No description provided for @error.
  ///
  /// In fr, this message translates to:
  /// **'Erreur'**
  String get error;

  /// No description provided for @success.
  ///
  /// In fr, this message translates to:
  /// **'Succès'**
  String get success;

  /// No description provided for @warning.
  ///
  /// In fr, this message translates to:
  /// **'Attention'**
  String get warning;

  /// No description provided for @info.
  ///
  /// In fr, this message translates to:
  /// **'Information'**
  String get info;

  /// No description provided for @emptyCart.
  ///
  /// In fr, this message translates to:
  /// **'Votre panier est vide'**
  String get emptyCart;

  /// No description provided for @emptyFavorites.
  ///
  /// In fr, this message translates to:
  /// **'Vous n\'avez aucun favori'**
  String get emptyFavorites;

  /// No description provided for @emptyOrders.
  ///
  /// In fr, this message translates to:
  /// **'Vous n\'avez aucune commande'**
  String get emptyOrders;

  /// No description provided for @seller.
  ///
  /// In fr, this message translates to:
  /// **'Vendeur'**
  String get seller;

  /// No description provided for @becomeSeller.
  ///
  /// In fr, this message translates to:
  /// **'Devenir vendeur'**
  String get becomeSeller;

  /// No description provided for @sellerDashboard.
  ///
  /// In fr, this message translates to:
  /// **'Dashboard Vendeur'**
  String get sellerDashboard;

  /// No description provided for @myProducts.
  ///
  /// In fr, this message translates to:
  /// **'Mes Produits'**
  String get myProducts;

  /// No description provided for @addProduct.
  ///
  /// In fr, this message translates to:
  /// **'Ajouter un produit'**
  String get addProduct;

  /// No description provided for @editProduct.
  ///
  /// In fr, this message translates to:
  /// **'Modifier le produit'**
  String get editProduct;

  /// No description provided for @deleteProduct.
  ///
  /// In fr, this message translates to:
  /// **'Supprimer le produit'**
  String get deleteProduct;

  /// No description provided for @points.
  ///
  /// In fr, this message translates to:
  /// **'Points'**
  String get points;

  /// No description provided for @loyaltyPoints.
  ///
  /// In fr, this message translates to:
  /// **'Points de fidélité'**
  String get loyaltyPoints;

  /// No description provided for @earnPoints.
  ///
  /// In fr, this message translates to:
  /// **'Gagner des points'**
  String get earnPoints;

  /// No description provided for @usePoints.
  ///
  /// In fr, this message translates to:
  /// **'Utiliser des points'**
  String get usePoints;

  /// No description provided for @pointsHistory.
  ///
  /// In fr, this message translates to:
  /// **'Historique des points'**
  String get pointsHistory;

  /// No description provided for @share.
  ///
  /// In fr, this message translates to:
  /// **'Partager'**
  String get share;

  /// No description provided for @shareProduct.
  ///
  /// In fr, this message translates to:
  /// **'Partager le produit'**
  String get shareProduct;

  /// No description provided for @shareStore.
  ///
  /// In fr, this message translates to:
  /// **'Partager la boutique'**
  String get shareStore;

  /// No description provided for @copyLink.
  ///
  /// In fr, this message translates to:
  /// **'Copier le lien'**
  String get copyLink;

  /// No description provided for @filter.
  ///
  /// In fr, this message translates to:
  /// **'Filtrer'**
  String get filter;

  /// No description provided for @sortBy.
  ///
  /// In fr, this message translates to:
  /// **'Trier par'**
  String get sortBy;

  /// No description provided for @priceHighToLow.
  ///
  /// In fr, this message translates to:
  /// **'Prix décroissant'**
  String get priceHighToLow;

  /// No description provided for @priceLowToHigh.
  ///
  /// In fr, this message translates to:
  /// **'Prix croissant'**
  String get priceLowToHigh;

  /// No description provided for @newest.
  ///
  /// In fr, this message translates to:
  /// **'Plus récent'**
  String get newest;

  /// No description provided for @popular.
  ///
  /// In fr, this message translates to:
  /// **'Populaire'**
  String get popular;

  /// No description provided for @promotions.
  ///
  /// In fr, this message translates to:
  /// **'Promotions'**
  String get promotions;

  /// No description provided for @flashSale.
  ///
  /// In fr, this message translates to:
  /// **'Vente flash'**
  String get flashSale;

  /// No description provided for @blackFriday.
  ///
  /// In fr, this message translates to:
  /// **'Black Friday'**
  String get blackFriday;

  /// No description provided for @specialOffers.
  ///
  /// In fr, this message translates to:
  /// **'Offres spéciales'**
  String get specialOffers;

  /// No description provided for @contactUs.
  ///
  /// In fr, this message translates to:
  /// **'Nous contacter'**
  String get contactUs;

  /// No description provided for @subject.
  ///
  /// In fr, this message translates to:
  /// **'Sujet'**
  String get subject;

  /// No description provided for @message.
  ///
  /// In fr, this message translates to:
  /// **'Message'**
  String get message;

  /// No description provided for @send.
  ///
  /// In fr, this message translates to:
  /// **'Envoyer'**
  String get send;

  /// No description provided for @selectLanguage.
  ///
  /// In fr, this message translates to:
  /// **'Sélectionner la langue'**
  String get selectLanguage;

  /// No description provided for @french.
  ///
  /// In fr, this message translates to:
  /// **'Français'**
  String get french;

  /// No description provided for @english.
  ///
  /// In fr, this message translates to:
  /// **'Anglais'**
  String get english;

  /// No description provided for @newArrivals.
  ///
  /// In fr, this message translates to:
  /// **'Nouveautés'**
  String get newArrivals;

  /// No description provided for @trending.
  ///
  /// In fr, this message translates to:
  /// **'Tendance'**
  String get trending;

  /// No description provided for @bestOffers.
  ///
  /// In fr, this message translates to:
  /// **'Meilleures offres'**
  String get bestOffers;

  /// No description provided for @dealsOfDay.
  ///
  /// In fr, this message translates to:
  /// **'Deals du Jour'**
  String get dealsOfDay;

  /// No description provided for @justForYou.
  ///
  /// In fr, this message translates to:
  /// **'Just For You'**
  String get justForYou;

  /// No description provided for @topSales.
  ///
  /// In fr, this message translates to:
  /// **'Meilleures ventes'**
  String get topSales;

  /// No description provided for @viewAll.
  ///
  /// In fr, this message translates to:
  /// **'Voir tout'**
  String get viewAll;

  /// No description provided for @seeMore.
  ///
  /// In fr, this message translates to:
  /// **'Voir plus'**
  String get seeMore;

  /// No description provided for @recommended.
  ///
  /// In fr, this message translates to:
  /// **'Recommandé'**
  String get recommended;

  /// No description provided for @featured.
  ///
  /// In fr, this message translates to:
  /// **'À la une'**
  String get featured;

  /// No description provided for @personalizedSelection.
  ///
  /// In fr, this message translates to:
  /// **'Sélection personnalisée'**
  String get personalizedSelection;

  /// No description provided for @limitedOffers.
  ///
  /// In fr, this message translates to:
  /// **'Offres limitées'**
  String get limitedOffers;

  /// No description provided for @allDeals.
  ///
  /// In fr, this message translates to:
  /// **'Voir tous les deals'**
  String get allDeals;

  /// No description provided for @recommendations.
  ///
  /// In fr, this message translates to:
  /// **'Recommandations'**
  String get recommendations;

  /// No description provided for @officialStores.
  ///
  /// In fr, this message translates to:
  /// **'Boutiques officielles'**
  String get officialStores;

  /// No description provided for @brandsPartners.
  ///
  /// In fr, this message translates to:
  /// **'Marques partenaires'**
  String get brandsPartners;

  /// No description provided for @newCollection.
  ///
  /// In fr, this message translates to:
  /// **'Nouvelle Collection'**
  String get newCollection;

  /// No description provided for @justForYouText.
  ///
  /// In fr, this message translates to:
  /// **'Juste pour vous'**
  String get justForYouText;

  /// No description provided for @discover.
  ///
  /// In fr, this message translates to:
  /// **'Découvrir'**
  String get discover;

  /// No description provided for @statistics.
  ///
  /// In fr, this message translates to:
  /// **'Statistiques'**
  String get statistics;

  /// No description provided for @totalProducts.
  ///
  /// In fr, this message translates to:
  /// **'Produits'**
  String get totalProducts;

  /// No description provided for @totalOrders.
  ///
  /// In fr, this message translates to:
  /// **'Commandes'**
  String get totalOrders;

  /// No description provided for @revenue.
  ///
  /// In fr, this message translates to:
  /// **'Revenus'**
  String get revenue;

  /// No description provided for @pending.
  ///
  /// In fr, this message translates to:
  /// **'En attente'**
  String get pending;

  /// No description provided for @quickActions.
  ///
  /// In fr, this message translates to:
  /// **'Actions rapides'**
  String get quickActions;

  /// No description provided for @modifyProfile.
  ///
  /// In fr, this message translates to:
  /// **'Modifier le profil'**
  String get modifyProfile;

  /// No description provided for @personalInfo.
  ///
  /// In fr, this message translates to:
  /// **'Informations personnelles'**
  String get personalInfo;

  /// No description provided for @modifyPassword.
  ///
  /// In fr, this message translates to:
  /// **'Modifier le mot de passe'**
  String get modifyPassword;

  /// No description provided for @accountSecurity.
  ///
  /// In fr, this message translates to:
  /// **'Sécurité du compte'**
  String get accountSecurity;

  /// No description provided for @notificationPreferences.
  ///
  /// In fr, this message translates to:
  /// **'Préférences de notification'**
  String get notificationPreferences;
}

class _AppLocalizationsDelegate
    extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  Future<AppLocalizations> load(Locale locale) {
    return SynchronousFuture<AppLocalizations>(lookupAppLocalizations(locale));
  }

  @override
  bool isSupported(Locale locale) =>
      <String>['en', 'fr'].contains(locale.languageCode);

  @override
  bool shouldReload(_AppLocalizationsDelegate old) => false;
}

AppLocalizations lookupAppLocalizations(Locale locale) {
  // Lookup logic when only language code is specified.
  switch (locale.languageCode) {
    case 'en':
      return AppLocalizationsEn();
    case 'fr':
      return AppLocalizationsFr();
  }

  throw FlutterError(
    'AppLocalizations.delegate failed to load unsupported locale "$locale". This is likely '
    'an issue with the localizations generation tool. Please file an issue '
    'on GitHub with a reproducible sample app and the gen-l10n configuration '
    'that was used.',
  );
}
