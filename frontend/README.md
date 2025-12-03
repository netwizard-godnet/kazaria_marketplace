<<<<<<< HEAD
# 📱 Kazaria App - Frontend Flutter

> Application Mobile pour Kazaria Marketplace

---

## 📖 Documentation Complète

**📚 [Documentation Générale →](../DOCUMENTATION_GENERALE.md)**

Consultez la documentation générale à la racine du projet pour :
- Architecture complète
- Guide d'installation
- Toutes les fonctionnalités
- Configuration API
- Déploiement

---

## 🚀 Installation Rapide

```bash
# Installer les dépendances
flutter pub get

# Configurer l'API
# Modifier lib/config/api_config.dart avec votre URL backend

# Lancer l'application
flutter run
=======
# 🛒 KAZARIA - Marketplace E-Commerce Hi-Tech

![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

> Une plateforme e-commerce moderne et complète dédiée aux produits hi-tech avec système d'authentification avancé 2FA, marketplace multi-vendeurs, panier intelligent et gestion complète des avis.

---

## 📋 Table des Matières

- [Fonctionnalités](#-fonctionnalités)
- [Technologies](#-technologies)
- [Architecture](#-architecture)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Structure du Projet](#-structure-du-projet)
- [Base de Données](#-base-de-données)
- [API Documentation](#-api-documentation)
- [Sécurité](#-sécurité)
- [Tests](#-tests)
- [Déploiement](#-déploiement)
- [Commandes Rapides](#-commandes-rapides)

---

## ✨ Fonctionnalités

### 🛍️ **E-Commerce Complet**

#### Catalogue & Navigation
- ✅ **Catalogue produits hi-tech** avec 4 catégories principales et 32 sous-catégories
- ✅ **Filtres avancés** : Prix, attributs, disponibilité, recherche
- ✅ **Recherche autocomplete** avec suggestions intelligentes
- ✅ **Multi-carousel** sur toutes les pages d'accueil
- ✅ **Pages produits détaillées** avec galerie d'images multiples
- ✅ **Système de favoris** pour les produits
- ✅ **Attributs produits** : Couleur, capacité, marque, garantie
- ✅ **Tags et étiquettes** : Featured, Trending, Best Offer, New

#### Catégories Principales
1. **📱 Téléphones et tablettes** (8 sous-catégories)
2. **💻 Ordinateurs et accessoires** (12 sous-catégories)
3. **🎮 Gaming et consoles** (6 sous-catégories)
4. **📺 TV et audio** (6 sous-catégories)

### 🔐 **Authentification & Sécurité**

#### Système 2FA par Email
- ✅ **Inscription** avec vérification d'email obligatoire
- ✅ **Connexion 2FA** : Code de 8 chiffres envoyé par email (validité 15 min)
- ✅ **Réinitialisation de mot de passe** sécurisée
- ✅ **Double authentification** sur tous les appareils
- ✅ **Codes temporaires** stockés en base de données
- ✅ **Expiration automatique** des codes

#### Protection Renforcée
- ✅ **Protection CSRF** sur tous les formulaires
- ✅ **Validation des données** backend + frontend
- ✅ **Sanitization** automatique des entrées
- ✅ **XSS Prevention** via Blade escaping
- ✅ **SQL Injection Prevention** avec Eloquent ORM
- ✅ **Sessions sécurisées** (HttpOnly, Secure)
- ✅ **Tokens Sanctum** pour l'API

### 🛒 **Panier & Commandes**

#### Panier Intelligent
- ✅ **Dual-mode** : Fonctionne pour utilisateurs connectés ET invités
- ✅ **Synchronisation** automatique au login
- ✅ **Gestion des quantités** en temps réel
- ✅ **Attributs multiples** par produit
- ✅ **Stock vérifié** avant ajout
- ✅ **Minimum de commande** configurable
- ✅ **Suppression** individuelle ou globale

#### Système de Commandes
- ✅ **Checkout complet** avec formulaire de livraison
- ✅ **Calcul automatique** : Sous-total, frais de port, taxes
- ✅ **Livraison gratuite** si seuil atteint
- ✅ **Réservation de stock** automatique
- ✅ **Facturation PDF** avec envoi par email
- ✅ **Statuts de commande** : pending → processing → shipped → delivered → cancelled
- ✅ **Suivi de commandes** dans le profil utilisateur
- ✅ **Historique d'achats** détaillé
- ✅ **Annulation** avec raison

### ⭐ **Avis & Notations**

#### Système Complet
- ✅ **Notes 1-5 étoiles** avec commentaires
- ✅ **Badge "Achat vérifié"** automatique
- ✅ **Vote utile** sur les avis (authentifiés et invités)
- ✅ **Tri des avis** : Récents, Utiles, Négatifs, Positifs
- ✅ **Pagination** intelligente
- ✅ **Statistiques détaillées** : Moyenne, distribution par note
- ✅ **Synchronisation automatique** des notes produits
- ✅ **Avatars automatiques** avec initiales

### 🏪 **Marketplace Multi-Vendeurs**

#### Système de Boutiques
- ✅ **Création de boutique** par utilisateurs
- ✅ **Validation automatique** des documents (DFE, Registre de commerce)
- ✅ **Dashboard vendeur complet** avec statistiques en temps réel
- ✅ **Gestion CRUD produits** depuis le dashboard
- ✅ **Page publique** de chaque boutique avec slug unique
- ✅ **Attribution automatique** des catégories
- ✅ **Upload multiple** d'images et documents
- ✅ **Logo et bannière** personnalisables
- ✅ **Statistiques** : Produits, commandes, ventes, revenus

#### Dashboard Vendeur
- ✅ **Vue d'ensemble** : 4 cartes de statistiques
- ✅ **Onglet Produits** : Liste, ajout, modification, suppression
- ✅ **Onglet Commandes** : Filtres, recherche, actions rapides
- ✅ **Onglet Paramètres** : Informations, logo, bannière
- ✅ **API complète** pour toutes les actions
- ✅ **Notifications** en temps réel pour nouvelles commandes

#### Page Publique Boutique
- ✅ **Bannière personnalisée**
- ✅ **Informations complètes** : Description, localisation, contact
- ✅ **Réseaux sociaux** : Facebook, Instagram, Twitter, Site Web
- ✅ **Filtres produits** : Prix, disponibilité
- ✅ **Tri dynamique** : Récent, prix, popularité
- ✅ **Vue grille/liste**
- ✅ **Pagination** (20 produits/page)

### 👤 **Profil Utilisateur**

#### Tableau de Bord
- ✅ **Statistiques** : Commandes, favoris, activité
- ✅ **Gestion du profil** : Photo, infos personnelles, adresse
- ✅ **Changement de mot de passe** sécurisé
- ✅ **Historique des commandes** avec téléchargement de factures
- ✅ **Liste de favoris** intégrée
- ✅ **Activité récente** : Commandes, favoris, vues
- ✅ **Sessions actives** avec déconnexion multiple
- ✅ **Upload de photo** avec stockage sécurisé

### 🎨 **Interface & UX**

#### Design Moderne
- ✅ **100% Responsive** (Bootstrap 5.3)
- ✅ **Mobile-first** avec menu offcanvas
- ✅ **Navigation intuitive** avec mega-menu
- ✅ **Notifications Bootstrap** élégantes
- ✅ **Animations CSS** fluides
- ✅ **Feedback visuel** pour toutes les actions
- ✅ **Accessibilité** : Navigation clavier, lecteurs d'écran
- ✅ **Loading states** et spinners

#### Personnalisation
- **Couleurs KAZARIA** : Orange #F04E27, Bleu #0F4A8A
- **Logo et favicon** personnalisables
- **Carrousels** configurables depuis l'admin
- **Bannières** gérées dynamiquement

---

## 🛠️ Technologies

### Backend
- **Laravel 12.0** - Framework PHP moderne
- **PHP 8.2+** - Langage serveur
- **MySQL 8.0** - Base de données relationnelle
- **Laravel Sanctum** - Authentification API
- **DomPDF** - Génération de factures PDF
- **Carbon** - Gestion des dates

### Frontend
- **Bootstrap 5.3** - Framework CSS
- **JavaScript ES6+** - Logique client
- **FontAwesome 6.x** - Icônes
- **AJAX/Fetch API** - Requêtes asynchrones
- **Vite** - Bundler moderne

### Outils & Services
- **Composer** - Gestionnaire de dépendances PHP
- **NPM** - Gestionnaire de paquets JS
- **Artisan** - CLI Laravel
- **Mailtrap/SMTP** - Envoi d'emails
- **Queue System** - Traitement asynchrone

---

## 🏗️ Architecture

### Séparation Web/API

#### Authentification Web (Sessions)
- **Middleware** : `auth:web`
- **Méthodes** : `auth()->user()`, `Auth::user()`, `@auth`
- **Usage** : Pages web, navigation, profil
- **Stockage** : Sessions Laravel dans la base de données

#### Authentification API (Tokens)
- **Middleware** : `auth:sanctum`
- **Méthodes** : `$request->user()`
- **Usage** : API, AJAX, vendeurs, commandes
- **Stockage** : Tokens Bearer dans le navigateur

#### Authentification Admin
- **Middleware** : `auth:web` + vérification `is_admin`
- **Usage** : Panneau d'administration complet
- **Fonctionnalités** : Gestion produits, catégories, commandes, utilisateurs

### Patterns Utilisés
- **MVC** : Modèles, Vues, Contrôleurs
- **Repository Pattern** : Services dédiés
- **Factory Pattern** : Création d'objets
- **Observer Pattern** : Événements et listeners
- **Middleware** : Filtrage des requêtes

---

## 🚀 Installation

### Prérequis
- **PHP** >= 8.2 avec extensions : `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `gd`
- **Composer** >= 2.5
- **MySQL** >= 8.0 ou **MariaDB** >= 10.11
- **Node.js** >= 18.x et **NPM** >= 9.x

### Étapes d'Installation

```bash
# 1. Cloner le projet
git clone https://github.com/votre-username/kazaria-marketplace.git
cd kazaria-marketplace

# 2. Installer les dépendances
composer install
npm install

# 3. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 4. Configurer la base de données dans .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kazaria_laravel
DB_USERNAME=root
DB_PASSWORD=

# 5. Créer la base de données
mysql -u root -p
CREATE DATABASE kazaria_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# 6. Exécuter les migrations et seeders
php artisan migrate:fresh --seed

# 7. Créer les liens symboliques
php artisan storage:link

# 8. Compiler les assets
npm run dev
# ou en production
npm run build

# 9. Lancer le serveur
php artisan serve
```

Le site sera accessible à : **http://localhost:8000**

---

## ⚙️ Configuration

### Configuration Email

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre_username
MAIL_PASSWORD=votre_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@kazaria.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Configuration des Queues (Optionnel)

```env
QUEUE_CONNECTION=database
```

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

### Configuration des Sessions

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

### Configuration du Storage

```bash
# Vérifier le lien symbolique
ls -la public/storage

# Si absent, créer le lien
php artisan storage:link

# Vérifier les permissions
chmod -R 775 storage bootstrap/cache
>>>>>>> c02cdc8e9ea503e0d3a532a4c513c48211010144
```

---

<<<<<<< HEAD
## ⚙️ Configuration API

Fichier : `lib/config/api_config.dart`

```dart
// Émulateur Android
static const String baseUrl = 'http://10.0.2.2:8000/api';

// Appareil physique (remplacer par votre IP)
static const String baseUrl = 'http://192.168.1.100:8000/api';

// Production
static const String baseUrl = 'https://api.kazaria.ci/api';
=======
## 📁 Structure du Projet

```
kazaria-marketplace/
├── app/
│   ├── Console/Commands/         # Commandes Artisan personnalisées
│   ├── Helpers/                  # Fonctions helper globales
│   ├── Http/
│   │   ├── Controllers/          # Contrôleurs web
│   │   │   ├── Admin/           # Contrôleurs admin
│   │   │   ├── Seller/          # Contrôleurs vendeur
│   │   │   └── ...
│   │   └── Middleware/           # Middleware personnalisés
│   ├── Listeners/                # Événements listeners
│   ├── Mail/                     # Classes d'emails (Mailable)
│   ├── Models/                   # Modèles Eloquent
│   ├── Notifications/            # Notifications utilisateur
│   ├── Providers/                # Service Providers
│   └── Services/                 # Services métier
├── bootstrap/
│   └── cache/                    # Cache de bootstrap
├── config/                       # Fichiers de configuration
├── database/
│   ├── factories/                # Factories pour les modèles
│   ├── migrations/               # Migrations de base de données
│   └── seeders/                  # Seeders (données de test)
├── public/
│   ├── css/                      # CSS compilés
│   ├── js/                       # JavaScript compilés
│   ├── images/                   # Images (produits, catégories, profils)
│   ├── kazaria-admin/           # Assets admin
│   └── storage/                  # Lien symbolique vers storage/app/public
├── resources/
│   ├── css/                      # CSS source
│   ├── js/                       # JS source
│   ├── lang/                     # Fichiers de traduction
│   └── views/                    # Templates Blade
│       ├── admin/               # Vues admin
│       ├── auth/                # Pages d'authentification
│       ├── emails/              # Templates d'emails
│       ├── layouts/             # Layouts (header, footer, app)
│       └── components/          # Composants Blade
├── routes/
│   ├── admin.php                # Routes admin
│   ├── api.php                  # Routes API
│   ├── console.php              # Routes console
│   └── web.php                  # Routes web
├── storage/
│   ├── app/
│   │   ├── public/
│   │   │   ├── invoices/        # Factures PDF générées
│   │   │   ├── profiles/        # Photos de profil
│   │   │   ├── products/        # Images produits
│   │   │   └── stores/          # Logos et bannières boutiques
│   │   └── ...
│   ├── framework/
│   └── logs/                    # Logs Laravel
├── tests/                        # Tests PHPUnit
├── vendor/                       # Dépendances Composer
├── .env                         # Configuration environnement
├── composer.json                # Dépendances PHP
├── package.json                 # Dépendances JS
├── vite.config.js               # Configuration Vite
└── README.md                    # Ce fichier
>>>>>>> c02cdc8e9ea503e0d3a532a4c513c48211010144
```

---

<<<<<<< HEAD
## 📦 Dépendances Principales

- `provider` : State management
- `http` : Requêtes API
- `cached_network_image` : Cache images
- `shared_preferences` : Stockage local
- `flutter_local_notifications` : Notifications
- `path_provider` : Accès fichiers
- `open_file` : Ouverture PDF
- `share_plus` : Partage de fichiers

---

## 🏗️ Structure

```
lib/
├── screens/        # Écrans de l'app
├── widgets/        # Composants réutilisables
├── models/         # Modèles de données
├── providers/      # State management
├── services/       # Services API
├── config/         # Configuration
└── utils/          # Utilitaires
=======
## 🗄️ Base de Données

### Tables Principales

#### Authentification
- **users** - Utilisateurs avec authentification 2FA
- **auth_codes** - Codes d'authentification temporaires (8 chiffres)
- **roles** - Rôles utilisateurs
- **permissions** - Permissions par rôle

#### Boutiques
- **stores** - Boutiques des vendeurs
  - `name`, `slug`, `description`
  - `category_id`, `subcategory_id`
  - `logo`, `banner`
  - `dfe_document`, `commerce_register`
  - `status` (pending, active, suspended, rejected)
  - `total_products`, `total_orders`, `total_sales`
  - `rating`, `reviews_count`

#### Produits
- **categories** - 4 catégories principales
- **subcategories** - 32 sous-catégories
- **products** - Produits avec images, prix, stock
- **product_categories** - Relation many-to-many produits/catégories
- **product_subcategories** - Relation many-to-many produits/sous-catégories
- **attributes** - Attributs produits (Couleur, Capacité, etc.)
- **attribute_values** - Valeurs des attributs

#### Commandes
- **orders** - Commandes avec statut et suivi
  - `order_number` (unique)
  - `status` (pending, processing, shipped, delivered, cancelled)
  - `payment_status` (pending, paid, failed, refunded)
  - `shipping_*` (nom, email, téléphone, adresse, ville, etc.)
  - `subtotal`, `shipping_cost`, `tax`, `discount`, `total`
- **order_items** - Articles d'une commande
- **cart_items** - Panier (invités et connectés)

#### Avis & Notations
- **reviews** - Avis clients avec notes et commentaires
- **review_votes** - Votes "utile" sur les avis
- **favorites** - Produits favoris des utilisateurs

#### Autres
- **product_views** - Historique des vues de produits
- **messages** - Messages utilisateurs (support)
- **notifications** - Notifications en base
- **settings** - Paramètres système configurables

### Seeders Disponibles

```bash
# Seed tous les données
php artisan migrate:fresh --seed

# Seed individuel
php artisan db:seed --class=CategorySeeder      # 4 catégories + 32 sous-catégories
php artisan db:seed --class=ProductSeeder       # 40 produits avec images
php artisan db:seed --class=AttributeSeeder     # Attributs et valeurs
php artisan db:seed --class=RoleSeeder          # Rôles et permissions
php artisan db:seed --class=UserSeeder          # Utilisateurs de test
>>>>>>> c02cdc8e9ea503e0d3a532a4c513c48211010144
```

---

<<<<<<< HEAD
## 📞 Support

Consultez la **[Documentation Générale](../DOCUMENTATION_GENERALE.md)** pour tous les détails.

---

**Kazaria App - Flutter 3.9.2+**
=======
## 🔌 API Documentation

### Endpoints Publics

#### Produits
```http
GET /api/products/{productId}/reviews?sort=recent&page=1
```

#### Recherche
```http
GET /api/search/suggestions?q={query}
```

#### Catégories
```http
GET /api/categories
GET /api/subcategories/{categoryId}
```

### Endpoints Authentifiés (Bearer Token)

#### Authentification
```http
POST /api/register
POST /api/login
POST /api/verify-login-code
POST /api/logout
POST /api/forgot-password
POST /api/reset-password
GET  /api/me
```

#### Profil
```http
GET  /api/profile
PUT  /api/profile/update
POST /api/profile/update-photo
PUT  /api/profile/change-password
GET  /api/activity/recent
POST /api/logout-all-devices
```

#### Panier
```http
GET    /api/cart/items
POST   /api/cart/add
PUT    /api/cart/update/{id}
DELETE /api/cart/remove/{id}
DELETE /api/cart/clear
```

#### Favoris
```http
GET  /api/favorites
POST /api/favorites/toggle
```

#### Commandes
```http
POST /api/orders/create
GET  /api/orders/my-orders
GET  /api/orders/{orderNumber}
GET  /api/orders/{orderNumber}/invoice/pdf
```

#### Avis
```http
POST /api/reviews
POST /api/reviews/{reviewId}/vote
```

#### Dashboard Vendeur
```http
GET  /api/store/stats                    # Statistiques
GET  /api/store/recent-orders            # 5 dernières commandes
GET  /api/store/products                 # Liste produits
POST /api/store/products                 # Ajouter produit
PUT  /api/store/products/{id}            # Modifier produit
DELETE /api/store/products/{id}          # Supprimer produit
GET  /api/store/orders                   # Toutes les commandes
GET  /api/store/orders/{orderNumber}     # Détails commande
PUT  /api/store/orders/{orderNumber}/status  # Changer statut
POST /api/store/orders/{orderNumber}/ship    # Expédier
POST /api/store/orders/{orderNumber}/cancel  # Annuler
GET  /api/check-seller-status            # Vérifier statut vendeur
```

### Authentification API

```javascript
// Exemple d'appel API avec token
fetch('/api/profile', {
    headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
})
```

---

## 🔒 Sécurité

### Authentification
- ✅ **Vérification d'email** obligatoire
- ✅ **Code 2FA** à 8 chiffres (15 min d'expiration)
- ✅ **Hashing bcrypt** des mots de passe
- ✅ **Tokens de réinitialisation** sécurisés
- ✅ **Protection brute force** (rate limiting)
- ✅ **Codes à usage unique** pour 2FA

### Protection des Données
- ✅ **Validation** des entrées (backend + frontend)
- ✅ **Protection CSRF** sur tous les formulaires
- ✅ **Sanitization** des données
- ✅ **XSS Prevention** (Blade escaping)
- ✅ **SQL Injection Prevention** (Eloquent ORM)
- ✅ **File upload** validé (MIME type, taille)

### Sessions & Cookies
- ✅ **Sessions sécurisées** (HttpOnly, Secure)
- ✅ **Gestion des invités** par localStorage
- ✅ **Expiration automatique** des sessions
- ✅ **Déconnexion multi-appareils**
- ✅ **Tokens Sanctum** avec expiration

### Stockage
- ✅ **Permissions** strictes (775 pour storage)
- ✅ **Liens symboliques** pour assets publics
- ✅ **Upload sécurisé** avec validation
- ✅ **Soft deletes** pour données critiques

---

## 🧪 Tests

```bash
# Lancer tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter AuthTest
php artisan test --filter CartTest
php artisan test --filter OrderTest
php artisan test --filter StoreTest

# Coverage
php artisan test --coverage
```

---

## 📦 Déploiement

### Déploiement Production

```bash
# 1. Installer sans dépendances dev
composer install --no-dev --optimize-autoloader

# 2. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 3. Configurer la base de données
# Modifier .env avec les vraies credentials

# 4. Migrations
php artisan migrate --force

# 5. Permissions
chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# 6. Storage link
php artisan storage:link

# 7. Build assets
npm run build

# 8. Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Optimizer
php artisan optimize
```

### Permissions par Serveur

#### Ubuntu/Debian
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### CentOS/RHEL
```bash
sudo chown -R apache:apache storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### cPanel / Hébergement Partagé
```bash
chown -R votreuser:votreuser storage bootstrap/cache
chmod -R 755 storage bootstrap/cache
```

### Vérifications Post-Déploiement

```bash
# Vérifier storage
php artisan storage:check

# Vérifier permissions
php artisan permissions:check

# Vérifier routes
php artisan route:list

# Vérifier cache
php artisan config:show
```

---

## ⚡ Commandes Rapides

### Cache

```bash
# Nettoyer tout le cache
php artisan optimize:clear

# Recréer le cache
php artisan optimize

# Nettoyer spécifique
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Base de Données

```bash
# État des migrations
php artisan migrate:status

# Exécuter les migrations
php artisan migrate --force

# Rollback
php artisan migrate:rollback

# Fresh (⚠️ DANGER - supprime toutes les données)
php artisan migrate:fresh --seed
```

### Storage & Permissions

```bash
# Vérifier storage
php artisan storage:check

# Réparer storage
php artisan storage:fix --force

# Lien symbolique
php artisan storage:link

# Fix permissions
php artisan permissions:fix
chmod -R 775 storage bootstrap/cache
```

### Debug

```bash
# Logs en temps réel
tail -f storage/logs/laravel.log

# Tinker (Laravel REPL)
php artisan tinker

# Routes
php artisan route:list

# Config
php artisan config:show
```

### Maintenance

```bash
# Nettoyer vieux logs (30 jours)
find storage/logs -name "*.log" -mtime +30 -delete

# Backup base de données
mysqldump -u user -p kazaria > backup_$(date +%Y%m%d).sql

# Backup storage
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public
```

### One-Liners

```bash
# Tout réparer en une commande
php artisan optimize:clear && php artisan storage:fix --force && php artisan optimize

# Déploiement rapide
git pull && composer install --no-dev && php artisan migrate --force && php artisan optimize

# Vérification complète
php artisan storage:check && php artisan permissions:fix && echo "✅ OK"
```

---

## 📊 Statistiques du Projet

- **25 Modèles** Eloquent
- **50+ Contrôleurs** organisés par contexte
- **49 Migrations** avec relations complexes
- **500+ Vues Blade** responsive
- **100+ Routes** web et API
- **32 Sous-catégories** hi-tech
- **40 Produits** de démonstration

---

## 🎯 Fonctionnalités Avancées

### Gestion de Stock
- ✅ **Stock automatique** décrémenté à la commande
- ✅ **Vérification** avant ajout au panier
- ✅ **Alertes** de stock faible
- ✅ **Réservation** temporaire
- ✅ **Libération** sur annulation

### Facturation PDF
- ✅ **Génération automatique** avec DomPDF
- ✅ **Email automatique** au client
- ✅ **Téléchargement** depuis le profil
- ✅ **Numéro de commande** unique
- ✅ **Détails complets** : Articles, montants, adresse

### Notifications
- ✅ **Email** : Inscription, 2FA, commande, facture
- ✅ **Base de données** : Notifications stockées
- ✅ **Temps réel** : AJAX pour vendeurs

### SEO
- ✅ **Meta tags** dynamiques
- ✅ **URLs friendly** avec slugs
- ✅ **Sitemap XML** automatique
- ✅ **robots.txt** configuré
- ✅ **Structured data** pour produits

---

## 🚀 Roadmap Future

### Priorité Haute
- [ ] Système de paiement en ligne (Stripe, PayPal)
- [ ] Gestion avancée des livraisons
- [ ] Application mobile (React Native)
- [ ] Chat en direct vendeur-client
- [ ] Système de réclamations

### Priorité Moyenne
- [ ] Recommandations produits (IA)
- [ ] Analyses avancées pour vendeurs
- [ ] Promotions flash automatisées
- [ ] Programme de fidélité
- [ ] Multi-langue (EN, FR)

### Priorité Basse
- [ ] Réseau social intégré
- [ ] Marketplace B2B
- [ ] API publique documentée (Swagger)
- [ ] Widget analytics avancés
- [ ] Intégration réseaux sociaux

---

## 📧 Support & Contact

Pour toute question ou problème :

- **Email** : support@kazaria.com
- **Issues GitHub** : [Créer une issue](https://github.com/votre-username/kazaria-marketplace/issues)

---

## 📄 Licence

Ce projet est sous licence **MIT**. Voir le fichier `LICENSE` pour plus de détails.

---

## 🙏 Remerciements

- **Laravel** - Framework PHP élégant
- **Bootstrap** - Framework CSS moderne
- **FontAwesome** - Bibliothèque d'icônes
- **DomPDF** - Génération de PDF
- **Sanctum** - Authentification API

---

<div align="center">
    <p><strong>Développé avec ❤️ pour l'écosystème Laravel</strong></p>
    <p>© 2025 KAZARIA - Tous droits réservés</p>
</div>

>>>>>>> c02cdc8e9ea503e0d3a532a4c513c48211010144
