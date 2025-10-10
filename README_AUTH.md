# 🔐 Système d'Authentification - KAZARIA Marketplace

## 📋 Table des matières
- [Vue d'ensemble](#vue-densemble)
- [Fonctionnalités](#fonctionnalités)
- [Installation et Configuration](#installation-et-configuration)
- [Structure du Projet](#structure-du-projet)
- [Guide d'Utilisation](#guide-dutilisation)
- [Flux d'Authentification](#flux-dauthentification)
- [Configuration Email](#configuration-email)
- [API Routes](#api-routes)
- [Base de Données](#base-de-données)
- [Sécurité](#sécurité)
- [Dépannage](#dépannage)

---

## 🎯 Vue d'ensemble

Ce système d'authentification complet a été développé pour la marketplace KAZARIA. Il inclut plusieurs mécanismes de sécurité avancés pour protéger les comptes utilisateurs.

### Caractéristiques principales :
- ✅ **Inscription** avec vérification d'email obligatoire
- ✅ **Connexion à deux facteurs** via code à 8 chiffres envoyé par email
- ✅ **Réinitialisation de mot de passe** sécurisée
- ✅ **Validation des emails** via lien unique
- ✅ **Gestion des sessions** utilisateur
- ✅ **Messages d'erreur** en français

---

## 🚀 Fonctionnalités

### 1. Inscription (Register)
- Formulaire complet avec validation côté serveur
- Champs : nom, prénom(s), email, téléphone (optionnel), adresse, mot de passe
- Acceptation des termes et conditions obligatoire
- Option d'inscription à la newsletter
- Envoi automatique d'un email de vérification après inscription
- Les utilisateurs ne peuvent pas se connecter avant d'avoir vérifié leur email

### 2. Vérification d'Email
- Un lien unique est envoyé par email lors de l'inscription
- Le lien contient un hash de sécurité basé sur l'email de l'utilisateur
- Les utilisateurs peuvent demander un nouvel email de vérification
- Page de notification dédiée avec possibilité de renvoyer l'email

### 3. Connexion (Login)
- **Étape 1** : L'utilisateur entre son email et mot de passe
- **Étape 2** : Un code à 8 chiffres est généré et envoyé par email
- **Étape 3** : L'utilisateur entre le code pour finaliser la connexion
- Le code expire après 15 minutes
- Possibilité de renvoyer un nouveau code
- Option "Se souvenir de moi" disponible

### 4. Code d'Authentification à 8 Chiffres
- Code numérique généré aléatoirement
- Valide pendant 15 minutes uniquement
- Envoyé par email avec un template professionnel
- Un seul code actif à la fois par utilisateur
- Le code est supprimé automatiquement après vérification

### 5. Mot de Passe Oublié
- Formulaire de demande avec email
- Génération d'un token de réinitialisation unique
- Token valide pendant 1 heure
- Email avec lien de réinitialisation sécurisé
- Formulaire de réinitialisation avec confirmation du mot de passe
- L'ancien mot de passe reste actif jusqu'à la réinitialisation

### 6. Déconnexion (Logout)
- Invalidation de la session utilisateur
- Régénération du token CSRF pour la sécurité
- Redirection vers la page d'accueil

---

## 🔧 Installation et Configuration

### Étape 1 : Exécuter les migrations

```bash
php artisan migrate
```

Cette commande va créer/modifier la table `users` avec tous les champs nécessaires :
- `nom`, `prenoms`, `email`, `telephone`, `adresse`
- `email_verified_at` (vérification email)
- `auth_code` (code d'authentification)
- `auth_code_expires_at` (expiration du code)
- `auth_code_verified` (statut de vérification)
- `is_verified`, `newsletter`, `termes_condition`, `statut`

### Étape 2 : Configurer l'envoi d'emails

Modifiez votre fichier `.env` avec vos paramètres SMTP :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.exemple.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@exemple.com
MAIL_PASSWORD=votre-mot-de-passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@kazaria.com
MAIL_FROM_NAME="KAZARIA Marketplace"
```

#### Exemples de configuration pour différents services :

**Gmail :**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe-app
MAIL_ENCRYPTION=tls
```

**Mailtrap (pour tests) :**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre-username
MAIL_PASSWORD=votre-password
MAIL_ENCRYPTION=tls
```

**SendGrid :**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=votre-api-key
MAIL_ENCRYPTION=tls
```

### Étape 3 : Tester l'envoi d'emails

Pour tester que votre configuration email fonctionne :

```bash
php artisan tinker
```

Puis dans la console :
```php
Mail::raw('Test email', function ($message) {
    $message->to('votre-email@exemple.com')->subject('Test KAZARIA');
});
```

### Étape 4 : Nettoyer le cache (si nécessaire)

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## 📁 Structure du Projet

### Contrôleurs
```
app/Http/Controllers/
├── AuthController.php          # Gestion complète de l'authentification
└── ProfileController.php       # Gestion du profil utilisateur
```

### Modèles
```
app/Models/
└── User.php                    # Modèle utilisateur avec méthodes d'authentification
```

### Emails (Mailable)
```
app/Mail/
├── AuthCodeMail.php           # Email du code d'authentification
├── VerifyEmailMail.php        # Email de vérification d'email
└── ResetPasswordMail.php      # Email de réinitialisation de mot de passe
```

### Vues
```
resources/views/
├── auth/
│   ├── login.blade.php              # Formulaire de connexion
│   ├── register.blade.php           # Formulaire d'inscription
│   ├── verify-code.blade.php        # Saisie du code à 8 chiffres
│   ├── verify-email.blade.php       # Page de notification de vérification
│   ├── forgot-password.blade.php    # Demande de réinitialisation
│   └── reset-password.blade.php     # Réinitialisation du mot de passe
└── emails/
    ├── auth-code.blade.php          # Template email code d'authentification
    ├── verify-email.blade.php       # Template email vérification
    └── reset-password.blade.php     # Template email réinitialisation
```

### Migrations
```
database/migrations/
├── 0001_01_01_000000_create_users_table.php        # Table users de base
└── 2025_10_10_000001_update_users_table_for_auth.php  # Ajout des champs d'authentification
```

### Routes
```
routes/
└── web.php                     # Toutes les routes d'authentification
```

---

## 📖 Guide d'Utilisation

### Pour les Développeurs

#### 1. Accéder aux pages d'authentification

- **Inscription** : `http://votre-domaine.com/register`
- **Connexion** : `http://votre-domaine.com/login`
- **Mot de passe oublié** : `http://votre-domaine.com/forgot-password`

#### 2. Utiliser le modèle User

```php
use App\Models\User;

// Générer un code d'authentification
$user = User::find(1);
$code = $user->generateAuthCode();

// Vérifier un code
if ($user->verifyAuthCode('12345678')) {
    // Code valide
}

// Vérifier si le code a expiré
if ($user->hasExpiredAuthCode()) {
    // Code expiré
}
```

#### 3. Protéger des routes

```php
// Routes nécessitant une authentification
Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfileController::class, 'index']);
    // Autres routes protégées
});
```

#### 4. Récupérer l'utilisateur connecté

```php
use Illuminate\Support\Facades\Auth;

// Dans un contrôleur
$user = Auth::user();

// Dans une vue Blade
@auth
    Bonjour {{ Auth::user()->prenoms }} {{ Auth::user()->nom }}
@endauth
```

### Pour les Utilisateurs Finaux

#### Processus d'Inscription
1. Accéder à la page d'inscription
2. Remplir le formulaire (tous les champs marqués * sont obligatoires)
3. Accepter les termes et conditions
4. Cliquer sur "S'inscrire"
5. Consulter votre boîte email
6. Cliquer sur le lien de vérification
7. Vous pouvez maintenant vous connecter

#### Processus de Connexion
1. Accéder à la page de connexion
2. Entrer votre email et mot de passe
3. Cliquer sur "Se connecter"
4. Consulter votre boîte email pour le code à 8 chiffres
5. Entrer le code dans les 15 minutes
6. Vous êtes connecté !

#### Mot de Passe Oublié
1. Cliquer sur "Mot de passe oublié ?" sur la page de connexion
2. Entrer votre adresse email
3. Consulter votre boîte email
4. Cliquer sur le lien de réinitialisation (valide 1 heure)
5. Entrer votre nouveau mot de passe
6. Confirmer le mot de passe
7. Vous pouvez vous connecter avec le nouveau mot de passe

---

## 🔄 Flux d'Authentification

### Diagramme de Flux - Inscription
```
Utilisateur remplit le formulaire
         ↓
Validation des données
         ↓
Création du compte (email_verified_at = null)
         ↓
Génération du lien de vérification
         ↓
Envoi de l'email de vérification
         ↓
Utilisateur clique sur le lien
         ↓
Vérification du hash
         ↓
Mise à jour : email_verified_at = now()
         ↓
Redirection vers la page de connexion
```

### Diagramme de Flux - Connexion
```
Utilisateur entre email + mot de passe
         ↓
Validation des identifiants
         ↓
Vérification que l'email est vérifié
         ↓
Génération du code à 8 chiffres
         ↓
Stockage : auth_code + auth_code_expires_at (15 min)
         ↓
Envoi de l'email avec le code
         ↓
Utilisateur entre le code
         ↓
Vérification du code et de l'expiration
         ↓
Suppression du code (auth_code = null)
         ↓
Connexion de l'utilisateur (Auth::login)
         ↓
Redirection vers la page d'accueil
```

### Diagramme de Flux - Réinitialisation
```
Utilisateur demande la réinitialisation
         ↓
Génération d'un token unique
         ↓
Stockage dans password_reset_tokens
         ↓
Envoi de l'email avec le lien
         ↓
Utilisateur clique sur le lien
         ↓
Vérification du token et de l'expiration (1h)
         ↓
Utilisateur entre le nouveau mot de passe
         ↓
Mise à jour du mot de passe (hashé)
         ↓
Suppression du token
         ↓
Redirection vers la page de connexion
```

---

## 📧 Configuration Email

### Templates d'Email

Tous les templates d'email sont personnalisables dans `resources/views/emails/`

#### 1. Code d'Authentification (`auth-code.blade.php`)
- Design responsive
- Code affiché en grand format avec espacement
- Avertissement sur l'expiration (15 minutes)
- Conseils de sécurité

#### 2. Vérification d'Email (`verify-email.blade.php`)
- Bouton CTA principal
- Lien alternatif au cas où le bouton ne fonctionne pas
- Liste des avantages après vérification
- Message d'accueil chaleureux

#### 3. Réinitialisation de Mot de Passe (`reset-password.blade.php`)
- Bouton de réinitialisation
- Avertissement sur l'expiration (1 heure)
- Conseils pour un mot de passe sécurisé
- Instructions claires

### Personnalisation des Templates

Pour modifier l'apparence des emails, éditez les fichiers dans `resources/views/emails/` :

```html
<!-- Changer les couleurs -->
<style>
    .header {
        background-color: #VOTRE_COULEUR;
    }
</style>

<!-- Ajouter votre logo -->
<div class="header">
    <img src="{{ asset('images/logo.png') }}" alt="Logo">
    <h1>KAZARIA</h1>
</div>
```

---

## 🛣️ API Routes

### Routes Publiques (Guest)

| Méthode | URL | Nom | Description |
|---------|-----|-----|-------------|
| GET | `/login` | `login` | Affiche le formulaire de connexion |
| POST | `/login` | - | Traite la connexion et envoie le code |
| GET | `/verify-code` | `verify-code.show` | Affiche le formulaire de saisie du code |
| POST | `/verify-code` | `verify-code.verify` | Vérifie le code d'authentification |
| POST | `/verify-code/resend` | `verify-code.resend` | Renvoie un nouveau code |
| GET | `/register` | `register` | Affiche le formulaire d'inscription |
| POST | `/register` | - | Traite l'inscription |
| GET | `/email/verify` | `verification.notice` | Page de notification de vérification |
| GET | `/email/verify/{id}/{hash}` | `verification.verify` | Vérifie l'email via le lien |
| POST | `/email/resend` | `verification.resend` | Renvoie l'email de vérification |
| GET | `/forgot-password` | `password.request` | Affiche le formulaire mot de passe oublié |
| POST | `/forgot-password` | `password.email` | Envoie le lien de réinitialisation |
| GET | `/reset-password/{token}` | `password.reset` | Affiche le formulaire de réinitialisation |
| POST | `/reset-password` | `password.update` | Réinitialise le mot de passe |

### Routes Protégées (Auth)

| Méthode | URL | Nom | Description |
|---------|-----|-----|-------------|
| GET | `/mon-profil` | `profil` | Affiche le profil de l'utilisateur |
| POST | `/logout` | `logout` | Déconnecte l'utilisateur |

---

## 🗄️ Base de Données

### Table : `users`

| Colonne | Type | Nullable | Description |
|---------|------|----------|-------------|
| `id` | bigint | Non | Identifiant unique |
| `nom` | varchar(255) | Non | Nom de famille |
| `prenoms` | varchar(255) | Non | Prénom(s) |
| `email` | varchar(255) | Non | Email (unique) |
| `email_verified_at` | timestamp | Oui | Date de vérification de l'email |
| `telephone` | varchar(20) | Oui | Numéro de téléphone (unique) |
| `telephone_verified_at` | timestamp | Oui | Date de vérification du téléphone |
| `password` | varchar(255) | Non | Mot de passe hashé |
| `profile_pic_url` | varchar(255) | Oui | URL de la photo de profil |
| `is_verified` | boolean | Non | Compte vérifié (défaut: false) |
| `adresse` | text | Oui | Adresse postale |
| `newsletter` | boolean | Non | Abonnement newsletter (défaut: false) |
| `termes_condition` | boolean | Non | Acceptation T&C (défaut: false) |
| `statut` | enum | Non | actif/inactif/suspendu (défaut: actif) |
| `auth_code` | varchar(8) | Oui | Code d'authentification temporaire |
| `auth_code_expires_at` | timestamp | Oui | Date d'expiration du code |
| `auth_code_verified` | boolean | Non | Code vérifié (défaut: false) |
| `remember_token` | varchar(100) | Oui | Token "Se souvenir de moi" |
| `created_at` | timestamp | Non | Date de création |
| `updated_at` | timestamp | Non | Date de modification |

### Table : `password_reset_tokens`

| Colonne | Type | Nullable | Description |
|---------|------|----------|-------------|
| `email` | varchar(255) | Non | Email (clé primaire) |
| `token` | varchar(255) | Non | Token de réinitialisation hashé |
| `created_at` | timestamp | Oui | Date de création |

---

## 🔒 Sécurité

### Mesures de Sécurité Implémentées

1. **Hashage des Mots de Passe**
   - Utilisation de `bcrypt` pour le hashage
   - Salage automatique par Laravel
   - Les mots de passe ne sont jamais stockés en clair

2. **Protection CSRF**
   - Token CSRF sur tous les formulaires
   - Validation automatique par Laravel
   - Régénération après déconnexion

3. **Validation des Données**
   - Validation côté serveur stricte
   - Messages d'erreur en français
   - Prévention des injections SQL

4. **Expiration des Tokens**
   - Code d'authentification : 15 minutes
   - Token de réinitialisation : 1 heure
   - Sessions configurables

5. **Vérification d'Email Obligatoire**
   - Les utilisateurs ne peuvent pas se connecter sans vérifier leur email
   - Hash de sécurité basé sur l'email
   - Liens de vérification à usage unique

6. **Authentification à Deux Facteurs**
   - Code à 8 chiffres envoyé par email
   - Un seul code actif à la fois
   - Suppression automatique après utilisation

7. **Protection contre le Brute Force**
   - Laravel inclut une limitation de tentatives (throttling)
   - Configurable dans `config/auth.php`

### Recommandations Supplémentaires

Pour renforcer la sécurité en production :

```php
// Dans config/session.php
'secure' => true,        // HTTPS uniquement
'http_only' => true,     // Pas d'accès JavaScript
'same_site' => 'strict', // Protection CSRF

// Activer la limitation de tentatives
// Dans app/Http/Kernel.php
'throttle:6,1' // 6 tentatives par minute
```

---

## 🐛 Dépannage

### Problème : Les emails ne sont pas envoyés

**Solution 1 : Vérifier la configuration**
```bash
php artisan config:clear
php artisan cache:clear
```

**Solution 2 : Vérifier les logs**
```bash
tail -f storage/logs/laravel.log
```

**Solution 3 : Tester manuellement**
```bash
php artisan tinker
Mail::raw('Test', function($m) { $m->to('test@test.com')->subject('Test'); });
```

### Problème : Le code d'authentification a expiré

**Solution** : L'utilisateur doit cliquer sur "Renvoyer le code" sur la page de vérification.

Le code est valide 15 minutes. Pour changer cette durée, modifiez dans `app/Models/User.php` :
```php
'auth_code_expires_at' => Carbon::now()->addMinutes(30), // 30 minutes au lieu de 15
```

### Problème : Le lien de réinitialisation a expiré

**Solution** : L'utilisateur doit refaire une demande de réinitialisation.

Le token est valide 1 heure. Pour changer cette durée, modifiez dans `app/Http/Controllers/AuthController.php` :
```php
Carbon::parse($passwordReset->created_at)->addHours(2)->isPast() // 2 heures au lieu de 1
```

### Problème : Erreur 419 - Token CSRF invalide

**Solution** : 
1. Vérifier que `@csrf` est présent dans tous les formulaires
2. Nettoyer le cache : `php artisan config:clear`
3. Vérifier que la session fonctionne correctement

### Problème : L'utilisateur ne reçoit pas l'email de vérification

**Solutions** :
1. Vérifier le dossier spam/courrier indésirable
2. Vérifier que l'email est correct dans la base de données
3. Utiliser la fonction "Renvoyer l'email de vérification"
4. Vérifier les logs Laravel pour les erreurs d'envoi

### Problème : Erreur "Class AuthController not found"

**Solution** : Nettoyer le cache et régénérer l'autoload
```bash
composer dump-autoload
php artisan clear-compiled
php artisan config:clear
```

---

## 📝 Notes Importantes

1. **En Développement** : 
   - Vous pouvez utiliser Mailtrap ou MailHog pour tester les emails
   - Les emails n'arriveront pas réellement aux destinataires

2. **En Production** :
   - Configurez un vrai service SMTP (Gmail, SendGrid, AWS SES, etc.)
   - Activez HTTPS pour la sécurité
   - Configurez les sessions pour être sécurisées

3. **Personnalisation** :
   - Tous les textes peuvent être modifiés dans les vues
   - Les durées d'expiration sont configurables
   - Les templates d'email sont entièrement personnalisables

4. **Performance** :
   - Envisagez d'utiliser une queue pour l'envoi d'emails en production
   - Configurez Redis pour les sessions si vous avez beaucoup d'utilisateurs

---

## 🎨 Personnalisation

### Changer les Couleurs

Modifiez les classes Bootstrap dans les vues ou ajoutez votre propre CSS :

```html
<!-- Dans resources/views/auth/login.blade.php -->
<div class="card-header bg-primary">  <!-- Changer bg-primary -->
```

### Ajouter des Champs

1. Ajouter le champ dans la migration
2. Ajouter le champ dans `$fillable` du modèle User
3. Ajouter le champ dans le formulaire
4. Ajouter la validation dans le contrôleur

### Modifier les Durées d'Expiration

```php
// Code d'authentification (dans User.php)
'auth_code_expires_at' => Carbon::now()->addMinutes(15)

// Token de réinitialisation (dans AuthController.php)
Carbon::parse($passwordReset->created_at)->addHour()->isPast()
```

---

## 📞 Support

Pour toute question ou problème :
1. Consultez d'abord cette documentation
2. Vérifiez les logs Laravel : `storage/logs/laravel.log`
3. Vérifiez la console du navigateur pour les erreurs JavaScript
4. Contactez l'équipe de développement

---

## 📜 Licence

Ce système d'authentification fait partie de la marketplace KAZARIA.

---

**Dernière mise à jour** : 10 Octobre 2025  
**Version** : 1.0.0  
**Auteur** : Équipe de développement KAZARIA

