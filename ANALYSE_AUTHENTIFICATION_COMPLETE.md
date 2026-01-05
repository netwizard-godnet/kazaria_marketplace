# Analyse complète du système d'authentification - KAZARIA Laravel

## Table des matières
1. [Architecture générale](#architecture-générale)
2. [Mécanismes d'authentification](#mécanismes-dauthentification)
3. [Middlewares d'authentification](#middlewares-dauthentification)
4. [Routes et protection](#routes-et-protection)
5. [Utilisation dans les contrôleurs](#utilisation-dans-les-contrôleurs)
6. [Gestion des sessions](#gestion-des-sessions)
7. [Gestion des tokens](#gestion-des-tokens)
8. [Intégration frontend](#intégration-frontend)
9. [Cas d'usage spécifiques](#cas-dusage-spécifiques)
10. [Sécurité et bonnes pratiques](#sécurité-et-bonnes-pratiques)

---

## 1. Architecture générale

### 1.1 Système hybride (Session + Token)

Le système utilise une architecture hybride permettant deux modes d'authentification :

**A. Authentification par session (Web)**
- Guard : `web`
- Driver : `session`
- Utilisé pour : Applications web traditionnelles, pages Blade
- Stockage : Cookies de session Laravel
- Persistance : Session serveur (fichier/database/Redis)

**B. Authentification par token (API)**
- Guard : `sanctum`
- Driver : Laravel Sanctum
- Utilisé pour : API REST, applications mobiles, SPA
- Stockage : Tokens dans la table `personal_access_tokens`
- Format : Bearer token dans l'en-tête Authorization

### 1.2 Configuration

**Fichier : `config/auth.php`**
```php
'defaults' => [
    'guard' => 'web',
    'passwords' => 'users',
],

'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],
```

**Fichier : `config/sanctum.php`**
- Domains stateful configurés pour les requêtes frontend
- Expiration des tokens : `null` (pas d'expiration par défaut)
- Middleware Sanctum configuré pour les routes API

---

## 2. Mécanismes d'authentification

### 2.1 Inscription (`AuthController::register`)

**Processus complet :**

1. **Validation des données**
   - Nom, prénoms, email (unique), téléphone
   - Mot de passe (min 8 caractères, confirmé)
   - Acceptation des termes et conditions
   - Newsletter (optionnel)

2. **Création de l'utilisateur**
   ```php
   $user = User::create([
       'nom' => $request->nom,
       'prenoms' => $request->prenoms,
       'email' => $request->email,
       'telephone' => $request->telephone,
       'password' => Hash::make($request->password),
       'statut' => 'actif',
       'is_verified' => false, // Vérification email optionnelle
   ]);
   ```

3. **Génération du token de vérification**
   - Token de 64 caractères aléatoires
   - Stocké dans `email_verification_token`
   - URL de vérification générée : `route('verify-email', ['token' => $token])`

4. **Envoi de l'email**
   - Email envoyé avec lien de vérification
   - **IMPORTANT** : La vérification est optionnelle, l'utilisateur peut utiliser le compte immédiatement

### 2.2 Connexion (`AuthController::login`)

**Flux de connexion :**

1. **Validation initiale**
   - Email et mot de passe requis
   - Vérification de l'existence de l'utilisateur
   - Vérification du mot de passe avec `Hash::check()`

2. **Vérification du 2FA**
   - Si `two_factor_enabled = false` : Connexion directe
   - Si `two_factor_enabled = true` : Envoi d'un code à 8 chiffres par email

3. **Connexion directe (sans 2FA)**
   ```php
   // Démarrer la session
   if (!$request->hasSession()) {
       $request->setLaravelSession(app('session.store'));
   }
   $session = $request->session();
   if (!$session->isStarted()) {
       $session->start();
   }
   
   // Connecter l'utilisateur
   Auth::login($user, $request->has('remember'));
   
   // Sécurité : Régénérer l'ID de session
   $request->session()->regenerate();
   
   // Stocker le hash du mot de passe pour vérification
   $request->session()->put('password_hash_web', $user->getAuthPassword());
   
   // Régénérer le token CSRF
   $request->session()->regenerateToken();
   ```

4. **Connexion avec 2FA**
   - Génération d'un code à 8 chiffres via `AuthCode::createCode()`
   - Code valide 15 minutes
   - Envoi par email
   - Vérification via `verifyLoginCode()`

### 2.3 Vérification du code 2FA (`AuthController::verifyLoginCode`)

**Processus :**

1. **Validation du code**
   - Code doit contenir exactement 8 chiffres
   - Email requis

2. **Vérification dans la base**
   ```php
   $authCode = AuthCode::where('email', $request->email)
       ->where('code', $request->code)
       ->where('type', 'login')
       ->unused()
       ->notExpired()
       ->first();
   ```

3. **Marquage du code comme utilisé**
   ```php
   $authCode->markAsUsed();
   ```

4. **Connexion de l'utilisateur**
   - Même processus que la connexion directe
   - Session régénérée
   - Hash du mot de passe stocké

### 2.4 Authentification sociale (OAuth)

**Providers supportés :**
- Google
- Facebook

**Processus (`SocialAuthController::callback`) :**

1. **Récupération des données OAuth**
   ```php
   $socialUser = Socialite::driver($provider)->stateless()->user();
   ```

2. **Recherche d'utilisateur existant**
   - Par `provider_name` + `provider_id`
   - Par email (si compte existant)

3. **Création ou mise à jour**
   - Si nouvel utilisateur : création avec données OAuth
   - Si utilisateur existant : mise à jour des tokens OAuth
   - Email automatiquement vérifié (`is_verified = true`)

4. **Connexion automatique**
   - Session démarrée
   - Utilisateur connecté
   - Session régénérée pour sécurité

### 2.5 Réinitialisation de mot de passe

**Processus :**

1. **Demande (`forgotPassword`)**
   - Validation de l'email
   - Génération d'un token de 64 caractères
   - Token stocké avec expiration (1 heure)
   - Email envoyé avec lien de réinitialisation

2. **Réinitialisation (`resetPassword`)**
   - Validation du token et du nouveau mot de passe
   - Vérification de l'expiration
   - Hashage du nouveau mot de passe
   - Nettoyage du token

---

## 3. Middlewares d'authentification

### 3.1 Middlewares personnalisés

#### A. `HybridAuthMiddleware`
**Alias :** `hybrid.auth`

**Fonctionnalité :**
- Supporte à la fois session et token
- Vérifie d'abord la session web
- Si pas de session, vérifie le token Sanctum
- Démarrer la session pour les requêtes API depuis le frontend

**Code clé :**
```php
// Vérifier d'abord l'authentification par session (web)
if (Auth::guard('web')->check()) {
    return $next($request);
}

// Si pas de session, vérifier l'authentification par token (API)
$token = $request->bearerToken() ?? $request->cookie('auth_token') 
    ?? session('auth_token') ?? $request->query('token');

if ($token) {
    $user = PersonalAccessToken::findToken($token)?->tokenable;
    if ($user) {
        Auth::guard('web')->login($user);
        return $next($request);
    }
}
```

**Utilisation :**
- Routes nécessitant authentification flexible (web ou API)
- Exemples : `/reviews`, `/checkout`, routes vendeur

#### B. `RedirectIfNotAuthenticated`
**Alias :** `auth.redirect`

**Fonctionnalité :**
- Redirige vers la page de connexion si non authentifié
- Supporte plusieurs guards
- Gère les requêtes JSON/API

**Code clé :**
```php
foreach ($guards as $guard) {
    if (Auth::guard($guard)->check()) {
        return $next($request);
    }
}

// Pour les requêtes API, retourner JSON 401
if ($request->expectsJson() || $request->is('api/*')) {
    return response()->json([
        'success' => false,
        'message' => 'Utilisateur non authentifié'
    ], 401);
}

// Pour les requêtes web, rediriger
return redirect()->route('login')->with('error', '...');
```

#### C. `AdminMiddleware`
**Alias :** `admin`

**Fonctionnalité :**
- Vérifie que l'utilisateur est admin
- Vérifie `is_admin = true` OU rôle admin actif
- Redirige vers la page de connexion admin si non authentifié

**Code clé :**
```php
if (!auth()->guard('web')->check()) {
    return redirect()->route('admin.login')->with('error', '...');
}

$user = auth()->guard('web')->user();
if (!$user->is_admin && (!$user->role_id || !$user->role || !$user->role->is_active)) {
    abort(403, 'Accès refusé...');
}
```

#### D. `RedirectIfNotSeller`
**Alias :** `seller`

**Fonctionnalité :**
- Vérifie que l'utilisateur est vendeur (`is_seller = true`)
- Redirige vers l'accueil si non vendeur

#### E. `RedirectIfNotAdmin`
**Alias :** `admin.redirect`

**Fonctionnalité :**
- Vérifie que l'utilisateur est administrateur
- Redirige vers l'accueil si non admin

#### F. `ClientAuth`
**Fonctionnalité :**
- Authentification par token uniquement
- Récupère le token depuis :
  1. En-tête Authorization (Bearer)
  2. Cookie `auth_token`
  3. Paramètre de requête `token`

**Code clé :**
```php
$token = $this->getTokenFromRequest($request);
$personalAccessToken = PersonalAccessToken::findToken($token);

if (!$personalAccessToken || 
    ($personalAccessToken->expires_at && $personalAccessToken->expires_at->isPast())) {
    return $this->handleUnauthenticated($request);
}

$request->setUserResolver(function () use ($personalAccessToken) {
    return $personalAccessToken->tokenable;
});
```

#### G. `ClientAuthMiddleware`
**Alias :** `client.auth`

**Fonctionnalité :**
- Vérifie d'abord la session web
- Puis vérifie Sanctum
- Puis vérifie le token Bearer
- Connecte l'utilisateur via session si token valide trouvé

#### H. `ApiWebAuth`
**Alias :** `api.web.auth`

**Fonctionnalité :**
- Démarrer la session pour les routes API
- Vérifie l'authentification avec le guard web
- Utilisé pour les routes API appelées depuis le frontend web

**Code clé :**
```php
// Démarrer la session si nécessaire
if (!$request->hasSession()) {
    $session = app('session.store');
    $request->setLaravelSession($session);
}

$session = $request->session();
if (!$session->isStarted()) {
    $sessionId = $request->cookies->get($session->getName());
    if ($sessionId) {
        $session->setId($sessionId);
    }
    $session->start();
}

// Vérifier l'authentification
if (!Auth::guard('web')->check()) {
    return response()->json([
        'success' => false,
        'message' => 'Utilisateur non authentifié...'
    ], 401);
}
```

#### I. `CheckPermission`
**Alias :** `permission`

**Fonctionnalité :**
- Vérifie les permissions basées sur les rôles
- Les super admins (`is_admin = true`) ont accès à tout
- Vérifie les permissions via `$user->hasPermission($permissionSlug)`

### 3.2 Middlewares Laravel standards

- `auth` : Middleware Laravel standard pour l'authentification
- `auth:sanctum` : Middleware Sanctum pour l'authentification par token
- `guest` : Redirige si déjà authentifié (alias vers `RedirectIfAuthenticated`)

---

## 4. Routes et protection

### 4.1 Routes publiques (sans authentification)

**Routes web :**
- `/` : Accueil
- `/produit/{slug}` : Page produit
- `/boutique/{slug}` : Page boutique
- `/categorie/{slug}` : Page catégorie
- `/search` : Recherche
- `/authentification` : Page de connexion (middleware `guest`)
- `/verify-email/{token}` : Vérification email
- `/forgot-password` : Mot de passe oublié
- `/reset-password/{token}` : Réinitialisation mot de passe

**Routes API :**
- `POST /api/register` : Inscription
- `POST /api/login` : Connexion
- `POST /api/forgot-password` : Demande réinitialisation
- `POST /api/reset-password` : Réinitialisation
- `POST /api/resend-verification-code` : Renvoyer code

### 4.2 Routes protégées par authentification

#### A. Routes utilisateur (session)

**Middleware :** `auth` ou `auth.redirect`

**Routes :**
- `GET /profil` : Page profil
- `POST /profile/change-password` : Changer mot de passe
- `POST /profile/update-two-factor` : Activer/désactiver 2FA
- `POST /profile/logout-all-devices` : Déconnecter tous les appareils
- `POST /profile/request-email-verification` : Demander vérification email
- `GET /checkout` : Page checkout
- `GET /shipping` : Page livraison
- `POST /orders/create` : Créer commande
- `GET /order/invoice/{orderNumber}` : Facture
- `GET /order/details/{orderNumber}` : Détails commande
- `GET /store/create` : Créer boutique
- `POST /store/create` : Enregistrer boutique

#### B. Routes vendeur

**Middleware :** `seller` (vérifie `is_seller = true`)

**Routes :**
- `GET /store/pending` : Boutique en attente
- `GET /store/rejected` : Boutique rejetée
- `GET /store/dashboard` : Dashboard vendeur
- `GET /store/edit` : Modifier boutique
- `POST /store/update` : Mettre à jour boutique
- Routes API vendeur (produits, commandes, statistiques)

#### C. Routes admin

**Middleware :** `admin` ou `admin.redirect`

**Routes :**
- Toutes les routes sous `/admin/*`
- Dashboard admin
- Gestion utilisateurs
- Gestion boutiques
- Gestion produits
- Statistiques

#### D. Routes API (tokens)

**Middleware :** `auth:sanctum`

**Routes :**
- `POST /api/logout` : Déconnexion
- `POST /api/logout-all-devices` : Déconnecter tous appareils
- `GET /api/me` : Informations utilisateur
- `POST /api/profile/update` : Mettre à jour profil
- `POST /api/profile/change-password` : Changer mot de passe
- `POST /api/profile/request-email-verification` : Vérification email
- Routes panier API (`/api/cart/*`)
- Routes commandes API (`/api/orders/*`)
- Routes avis API (`/api/reviews`)
- Routes boutique API (`/api/store/*`)

#### E. Routes hybrides (session + token)

**Middleware :** `hybrid.auth`

**Routes :**
- `POST /reviews` : Ajouter avis (web)
- `GET /checkout` : Page checkout
- `GET /shipping` : Page livraison
- Routes vendeur API (`/store/api/*`)

---

## 5. Utilisation dans les contrôleurs

### 5.1 Récupération de l'utilisateur

#### A. Dans les contrôleurs web (session)

```php
// Méthode 1 : auth() helper
$user = auth()->user();

// Méthode 2 : Auth facade
$user = Auth::user();

// Méthode 3 : Guard explicite
$user = Auth::guard('web')->user();

// Méthode 4 : Depuis la requête
$user = $request->user(); // Fonctionne si middleware auth appliqué
```

#### B. Dans les contrôleurs API (token)

```php
// Méthode 1 : Depuis la requête (avec middleware auth:sanctum)
$user = $request->user();

// Méthode 2 : Depuis le token
$token = $request->bearerToken();
$personalAccessToken = PersonalAccessToken::findToken($token);
$user = $personalAccessToken?->tokenable;
```

#### C. Support hybride (session + token)

```php
// Pattern utilisé dans plusieurs contrôleurs
$user = $request->user() ?? auth()->user();

// Ou
$user = auth()->user() ?? $request->user();
```

### 5.2 Vérification de l'authentification

```php
// Vérifier si connecté
if (auth()->check()) {
    // Utilisateur connecté
}

// Vérifier avec guard spécifique
if (Auth::guard('web')->check()) {
    // Utilisateur connecté via session
}

// Vérifier avec token
if ($request->user()) {
    // Utilisateur authentifié via token
}
```

### 5.3 Exemples d'utilisation dans les contrôleurs

#### A. CartController

**Gestion hybride utilisateur/session :**

```php
private function getUserOrSession(Request $request)
{
    // Pour les pages web, utiliser l'authentification par session
    if (auth()->check()) {
        $sessionId = $request->header('X-Session-ID');
        if (!$sessionId && $request->hasSession()) {
            $sessionId = $request->session()->getId();
        }
        return ['user_id' => auth()->user()->id, 'session_id' => $sessionId];
    }
    
    // Pour les invités, utiliser session_id
    $sessionId = $request->header('X-Session-ID');
    if (!$sessionId && $request->hasSession()) {
        $sessionId = $request->session()->getId();
    }
    if (!$sessionId) {
        $sessionId = uniqid('guest_', true);
    }
    
    return ['user_id' => null, 'session_id' => $sessionId];
}
```

**Logique :**
- Si utilisateur connecté : utiliser `user_id` (priorité absolue)
- Si invité : utiliser `session_id` depuis header ou session Laravel
- Permet la persistance du panier pour les invités
- Fusion automatique du panier invité vers panier utilisateur à la connexion

#### B. OrderController

**Vérification obligatoire pour les commandes :**

```php
public function checkout(Request $request)
{
    $user = auth()->user();
    
    if (!$user) {
        return redirect()->route('login')
            ->with('message', 'Veuillez vous connecter pour passer commande');
    }
    
    // Récupérer le panier de l'utilisateur
    $cartItems = CartItem::getCartItems($user->id, null);
    
    // ...
}
```

**Création de commande (hybride) :**

```php
public function createOrder(Request $request)
{
    // Support à la fois pour les tokens (API) et les sessions (WEB)
    $user = $request->user() ?? auth()->user();
    
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Utilisateur non authentifié'
        ], 401);
    }
    
    // Créer la commande avec user_id
    $order = Order::create([
        'user_id' => $user->id,
        // ...
    ]);
}
```

#### C. StoreController

**Vérification du statut vendeur :**

```php
public function create(Request $request)
{
    // Middleware 'auth' garantit l'authentification
    $user = auth()->user();
    
    // Vérifier si l'utilisateur a déjà une boutique
    if ($user->store()->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'Vous avez déjà une boutique'
        ], 400);
    }
    
    // Créer la boutique
    $store = Store::create([
        'user_id' => $user->id,
        // ...
    ]);
    
    // Mettre à jour l'utilisateur en tant que vendeur
    $user->update(['is_seller' => true]);
}
```

#### D. ReviewController

**Vérification pour les avis :**

```php
public function store(Request $request)
{
    $user = $request->user(); // API - Token
    
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Vous devez être connecté pour laisser un avis'
        ], 401);
    }
    
    // Vérifier si l'utilisateur a acheté le produit
    $hasOrdered = Order::where('user_id', $user->id)
        ->where('status', '!=', 'cancelled')
        ->whereHas('items', function($query) use ($request) {
            $query->where('product_id', $request->product_id);
        })
        ->first();
    
    // Créer l'avis avec is_verified_purchase
    $review = Review::create([
        'user_id' => $user->id,
        'is_verified_purchase' => $hasOrdered ? true : false,
        // ...
    ]);
}
```

---

## 6. Gestion des sessions

### 6.1 Démarrage de session

**Pattern utilisé partout :**

```php
// Vérifier si la session existe
if (!$request->hasSession()) {
    $request->setLaravelSession(app('session.store'));
}

// Démarrer la session si nécessaire
$session = $request->session();
if (!$session->isStarted()) {
    $session->start();
}
```

### 6.2 Sécurité de session

**Régénération après login :**

```php
// Régénérer l'ID de session APRÈS le login
$request->session()->regenerate();

// Stocker le hash du mot de passe dans la session
$request->session()->put('password_hash_web', $user->getAuthPassword());

// Régénérer le token CSRF
$request->session()->regenerateToken();
```

**Protection contre la fixation de session :**
- Régénération systématique après authentification
- Hash du mot de passe stocké pour vérification
- Middleware `AuthenticateSession` de Laravel vérifie la cohérence

### 6.3 Stockage dans la session

**Données stockées :**
- `login_web_*` : ID utilisateur (géré par Laravel)
- `password_hash_web` : Hash du mot de passe (pour vérification)
- `_token` : Token CSRF
- `promo` : Code promo appliqué (pour commandes)
- `admin_token` : Token Sanctum pour admin (optionnel)

### 6.4 Session pour les invités

**Gestion du panier invité :**

```php
// Générer un ID de session pour les invités
$sessionId = $request->header('X-Session-ID');
if (!$sessionId && $request->hasSession()) {
    $sessionId = $request->session()->getId();
}
if (!$sessionId) {
    $sessionId = uniqid('guest_', true);
}
```

**Stockage :**
- `guest_session_id` dans `localStorage` (côté client)
- Envoyé via header `X-Session-ID` dans les requêtes
- Utilisé pour identifier le panier des invités

---

## 7. Gestion des tokens

### 7.1 Création de tokens

**Pour les utilisateurs normaux :**
- Pas de création automatique lors de la connexion web
- Création manuelle si nécessaire pour API

**Pour les admins :**
```php
// Dans Admin/AuthController::login
$token = $user->createToken('admin-token')->plainTextToken;
session(['admin_token' => $token]);
```

### 7.2 Récupération de tokens

**Sources multiples :**

```php
$token = $request->bearerToken()           // Header Authorization: Bearer ...
    ?? $request->cookie('auth_token')       // Cookie
    ?? session('auth_token')                // Session
    ?? $request->query('token');            // Paramètre URL
```

### 7.3 Validation de tokens

```php
$personalAccessToken = PersonalAccessToken::findToken($token);

// Vérifier l'expiration
if ($personalAccessToken->expires_at && 
    $personalAccessToken->expires_at->isPast()) {
    // Token expiré
}

// Récupérer l'utilisateur
$user = $personalAccessToken->tokenable;
```

### 7.4 Révocation de tokens

**Déconnexion simple :**
```php
$request->user()->currentAccessToken()->delete();
```

**Déconnexion de tous les appareils :**
```php
$user->tokens()->delete();
```

---

## 8. Intégration frontend

### 8.1 Métadonnées dans les vues

**Fichier : `resources/views/layouts/header.blade.php`**

```blade
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="user-logged-in" content="{{ auth()->check() ? 'true' : 'false' }}">
```

**Utilisation JavaScript :**
```javascript
const isLoggedIn = document.querySelector('meta[name="user-logged-in"]')
    ?.getAttribute('content') === 'true';
const csrfToken = document.querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');
```

### 8.2 Gestion des headers

**Fichier : `public/js/auth.js` et `public/js/cart.js`**

```javascript
window.getHeaders = function() {
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || ''
    };
    
    // Vérifier si l'utilisateur est connecté via session
    const isLoggedIn = document.querySelector('meta[name="user-logged-in"]')
        ?.getAttribute('content') === 'true';
    
    if (isLoggedIn) {
        // Utilisateur connecté via session - pas besoin de token Bearer
        headers['X-Session-ID'] = getSessionId();
    } else {
        // Utilisateur non connecté - utiliser seulement l'ID de session
        headers['X-Session-ID'] = getSessionId();
    }
    
    return headers;
};
```

**Fonction getSessionId() :**
```javascript
function getSessionId() {
    let sessionId = localStorage.getItem('guest_session_id');
    if (!sessionId) {
        sessionId = 'guest_' + Date.now() + '_' + 
            Math.random().toString(36).substr(2, 9);
        localStorage.setItem('guest_session_id', sessionId);
    }
    return sessionId;
}
```

### 8.3 Processus de connexion (JavaScript)

**Fichier : `resources/views/auth/authentification.blade.php`**

**Connexion initiale :**
```javascript
const response = await fetch('/api/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({
        email: email,
        password: password
    })
});

const data = await response.json();

if (data.success && data.requires_code) {
    // Afficher le formulaire de code
    showCodeForm(data.email);
} else if (data.success) {
    // Rediriger vers l'accueil
    window.location.replace('{{ route("accueil") }}');
}
```

**Vérification du code :**
```javascript
const response = await fetch('/verify-login-code', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'X-Requested-With': 'XMLHttpRequest'
    },
    credentials: 'same-origin', // Important pour les cookies de session
    body: JSON.stringify({
        email: email,
        code: code
    })
});
```

### 8.4 Vérification côté client

**Dans les vues Blade :**

```blade
@auth
    {{-- Contenu pour utilisateurs connectés --}}
    <p>Bonjour {{ auth()->user()->prenoms }} !</p>
@endauth

@guest
    {{-- Contenu pour invités --}}
    <a href="{{ route('login') }}">Se connecter</a>
@endguest
```

**Vérifications conditionnelles :**
```blade
@if(auth()->check() && auth()->user()->is_seller)
    {{-- Contenu vendeur --}}
@endif

@if(auth()->check() && auth()->user()->is_admin)
    {{-- Contenu admin --}}
@endif
```

---

## 9. Cas d'usage spécifiques

### 9.1 Panier (invités et utilisateurs)

**Logique :**
- **Invités** : Panier identifié par `session_id`
- **Utilisateurs connectés** : Panier identifié par `user_id`
- **Fusion** : À la connexion, les articles du panier invité sont transférés vers le panier utilisateur

**Code :**
```php
// Récupération flexible
$identifier = $this->getUserOrSession($request);
// Retourne : ['user_id' => X, 'session_id' => Y]

// Recherche dans le panier
CartItem::where(function($query) use ($identifier) {
    if ($identifier['user_id']) {
        $query->where('user_id', $identifier['user_id']);
    } else {
        $query->where('session_id', $identifier['session_id']);
    }
})->get();
```

### 9.2 Commandes

**Obligation d'authentification :**
- Les commandes nécessitent toujours un utilisateur authentifié
- Pas de commande pour les invités
- Redirection vers la page de connexion si non authentifié

**Vérification :**
```php
public function checkout(Request $request)
{
    $user = auth()->user();
    
    if (!$user) {
        return redirect()->route('login')
            ->with('message', 'Veuillez vous connecter pour passer commande');
    }
    
    // Récupérer le panier de l'utilisateur (user_id uniquement)
    $cartItems = CartItem::getCartItems($user->id, null);
}
```

### 9.3 Avis produits

**Authentification requise :**
- Seuls les utilisateurs connectés peuvent laisser des avis
- Vérification si l'utilisateur a acheté le produit
- Badge "Achat vérifié" si `is_verified_purchase = true`

**Code :**
```php
public function store(Request $request)
{
    $user = $request->user(); // API
    
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Vous devez être connecté pour laisser un avis'
        ], 401);
    }
    
    // Vérifier si achat vérifié
    $hasOrdered = Order::where('user_id', $user->id)
        ->where('status', '!=', 'cancelled')
        ->whereHas('items', function($query) use ($request) {
            $query->where('product_id', $request->product_id);
        })
        ->first();
    
    $review = Review::create([
        'is_verified_purchase' => $hasOrdered ? true : false,
        // ...
    ]);
}
```

### 9.4 Boutiques vendeurs

**Création de boutique :**
- Nécessite authentification (`middleware('auth')`)
- Vérifie si l'utilisateur a déjà une boutique
- Met à jour `is_seller = true` après création
- Statut initial : `pending` (en attente validation admin)

**Accès au dashboard :**
- Middleware `seller` vérifie `is_seller = true`
- Vérifie que la boutique est validée (`isKycValidated()`)
- Redirige vers `/store/pending` si en attente
- Redirige vers `/store/rejected` si rejetée

### 9.5 Administration

**Connexion admin :**
- Route séparée : `/admin/login`
- Vérifie `is_admin = true`
- Crée un token Sanctum pour l'API admin
- Stocke le token dans la session

**Protection des routes admin :**
- Middleware `admin` sur toutes les routes `/admin/*`
- Vérifie `is_admin = true` OU rôle admin actif
- Redirige vers `/admin/login` si non authentifié

### 9.6 Profil utilisateur

**Accès :**
- Route protégée : `middleware('auth')`
- Récupération : `auth()->user()`

**Fonctionnalités :**
- Mise à jour des informations
- Changement de mot de passe
- Activation/désactivation 2FA
- Déconnexion de tous les appareils
- Demande de vérification email

---

## 10. Sécurité et bonnes pratiques

### 10.1 Hashage des mots de passe

**Utilisation :**
```php
// Hashage lors de la création
'password' => Hash::make($request->password)

// Vérification
if (!Hash::check($request->password, $user->password)) {
    // Mot de passe incorrect
}
```

**Algorithme :** bcrypt (par défaut Laravel)

### 10.2 Protection CSRF

**Token CSRF :**
- Généré automatiquement par Laravel
- Régénéré après chaque login
- Inclus dans toutes les requêtes POST/PUT/DELETE
- Vérifié par le middleware `ValidateCsrfToken`

**Exclusions :**
- Routes API (`api/*`)
- Route `/logout`

### 10.3 Protection contre la fixation de session

**Mesures :**
- Régénération de l'ID de session après login
- Stockage du hash du mot de passe dans la session
- Vérification par `AuthenticateSession` middleware
- Déconnexion automatique si le mot de passe change

### 10.4 Codes d'authentification

**Sécurité :**
- Codes à 8 chiffres (0-99999999)
- Expiration : 15 minutes
- Usage unique (marqués comme utilisés)
- Traçabilité : IP et User-Agent stockés

**Génération :**
```php
$code = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
```

### 10.5 Tokens de réinitialisation

**Sécurité :**
- Tokens de 64 caractères aléatoires
- Expiration : 1 heure
- Usage unique (supprimés après utilisation)
- Stockés dans la base de données

### 10.6 Validation des entrées

**Toujours valider :**
- Email : format valide, unique
- Mot de passe : minimum 8 caractères, confirmé
- Téléphone : format valide
- Tous les champs requis

**Messages d'erreur :**
- Messages en français
- Messages spécifiques par champ
- Messages d'erreur compréhensibles

### 10.7 Gestion des erreurs

**Pattern utilisé :**
```php
try {
    // Opération
} catch (\Exception $e) {
    \Log::error('Erreur: ' . $e->getMessage());
    return response()->json([
        'success' => false,
        'message' => 'Message utilisateur compréhensible'
    ], 500);
}
```

### 10.8 Logging

**Événements loggés :**
- Erreurs d'authentification
- Erreurs d'envoi d'email
- Erreurs de création de commande
- Erreurs de gestion de panier
- Erreurs de gestion de boutique

**Format :**
```php
\Log::error('Contexte: ' . $e->getMessage());
\Log::info('Action réussie', ['user_id' => $user->id]);
```

---

## Conclusion

Le système d'authentification de KAZARIA est **robuste, flexible et sécurisé**. Il combine :

1. **Authentification hybride** : Session pour le web, tokens pour l'API
2. **Sécurité renforcée** : 2FA optionnel, protection CSRF, régénération de session
3. **Flexibilité** : Support des invités (panier), fusion automatique à la connexion
4. **Gestion des rôles** : Utilisateurs, vendeurs, administrateurs
5. **Intégration frontend** : Métadonnées, headers automatiques, gestion des sessions

Le système est bien conçu pour supporter à la fois une application web traditionnelle et une API REST, avec une transition transparente entre les deux modes d'authentification.

