# Analyse : Configuration API pour Flutter & Dart

## Conclusion

**Le système hybride EST NÉCESSAIRE** car vous avez une application mobile Flutter qui utilisera les APIs.

Cependant, **il y a un problème critique** : Le login actuel ne retourne **PAS de token** pour les applications mobiles. Il faut corriger cela.

---

## 1. Problème identifié

### 1.1 Login actuel

**Route :** `POST /api/login`

**Problème :** Le login crée seulement une **session** mais ne retourne **pas de token Sanctum** pour Flutter.

**Code actuel :**
```php
// AuthController::login
// Connexion directe (sans 2FA)
Auth::login($user, $request->has('remember'));
$request->session()->regenerate();

return response()->json([
    'success' => true,
    'message' => 'Connexion réussie',
    'user' => $user->only([...]),
    'requires_code' => false,
    'redirect' => route('accueil')
]);
```

**Manque :** ❌ Pas de token retourné pour Flutter

### 1.2 Vérification du code 2FA

**Route :** `POST /verify-login-code` (route web, pas API)

**Problème :** Cette route est dans `web.php` et utilise la session. Pour Flutter, il faut une route API qui retourne un token.

---

## 2. Routes API disponibles pour Flutter

### 2.1 Routes publiques (sans authentification)

✅ **Disponibles :**
- `POST /api/register` : Inscription
- `POST /api/login` : Connexion (⚠️ **PROBLÈME** : ne retourne pas de token)
- `POST /api/forgot-password` : Mot de passe oublié
- `POST /api/reset-password` : Réinitialisation
- `POST /api/resend-verification-code` : Renvoyer code
- `GET /api/products/{productId}/reviews` : Avis produits
- `POST /api/coupons/apply` : Appliquer coupon
- `POST /api/contact` : Contact

### 2.2 Routes protégées (avec token Bearer)

✅ **Disponibles avec `auth:sanctum` :**
- `POST /api/logout` : Déconnexion
- `POST /api/logout-all-devices` : Déconnecter tous appareils
- `GET /api/me` : Informations utilisateur
- `POST /api/profile/update` : Mettre à jour profil
- `POST /api/profile/change-password` : Changer mot de passe
- `POST /api/profile/request-email-verification` : Vérification email
- `POST /api/cart/add` : Ajouter au panier
- `GET /api/cart/items` : Obtenir panier
- `PUT /api/cart/update/{id}` : Mettre à jour panier
- `DELETE /api/cart/remove/{id}` : Retirer du panier
- `DELETE /api/cart/clear` : Vider panier
- `GET /api/favorites` : Liste favoris
- `POST /api/favorites/toggle` : Ajouter/retirer favoris
- `POST /api/orders/create` : Créer commande
- `GET /api/orders/my-orders` : Mes commandes
- `GET /api/orders/{orderNumber}` : Détails commande
- `POST /api/orders/{orderNumber}/cancel` : Annuler commande
- `POST /api/reviews` : Ajouter avis
- `POST /api/reviews/{reviewId}/vote` : Voter avis
- `GET /api/check-seller-status` : Statut vendeur
- Routes vendeur (`/api/store/*`)

### 2.3 Routes problématiques

⚠️ **Problèmes identifiés :**

1. **`POST /api/login`** : Ne retourne pas de token
2. **`POST /verify-login-code`** : Route web, pas API (pour 2FA)
3. **`POST /api/profile/update-photo`** : Utilise `hybrid.auth` (session + token)
4. **`GET /api/activity/recent`** : Utilise `hybrid.auth` (session + token)
5. **`GET /api/orders/my-orders`** : Utilise `api.web.auth` (session uniquement)

---

## 3. Corrections nécessaires

### 3.1 Modifier le login pour retourner un token

**Fichier :** `app/Http/Controllers/AuthController.php`

**Modification nécessaire :**

```php
public function login(Request $request)
{
    // ... validation et vérification utilisateur ...
    
    // Détecter si c'est une requête API (mobile)
    $isApiRequest = $request->expectsJson() || $request->is('api/*');
    
    if (!$user->two_factor_enabled) {
        // Connexion directe
        if ($isApiRequest) {
            // Pour les requêtes API (Flutter), créer un token
            $token = $user->createToken('mobile-app')->plainTextToken;
            
            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'user' => $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'two_factor_enabled']),
                'token' => $token, // ⭐ Token pour Flutter
                'requires_code' => false
            ]);
        } else {
            // Pour les requêtes web, utiliser la session
            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();
            // ...
        }
    } else {
        // 2FA activé - envoyer code
        // ...
    }
}
```

### 3.2 Créer une route API pour vérifier le code 2FA

**Nouvelle route :** `POST /api/verify-login-code`

**Modification nécessaire :**

```php
public function verifyLoginCodeApi(Request $request)
{
    // ... validation et vérification du code ...
    
    // Marquer le code comme utilisé
    $authCode->markAsUsed();
    
    // Pour les requêtes API (Flutter), créer un token
    $token = $user->createToken('mobile-app')->plainTextToken;
    
    return response()->json([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'two_factor_enabled']),
        'token' => $token, // ⭐ Token pour Flutter
    ]);
}
```

### 3.3 Corriger les routes hybrides

**Problème :** Certaines routes utilisent `hybrid.auth` ou `api.web.auth` qui nécessitent une session.

**Solution :** Utiliser `auth:sanctum` pour les routes API utilisées par Flutter.

**Routes à corriger :**
- `POST /api/profile/update-photo` : Changer `hybrid.auth` → `auth:sanctum`
- `GET /api/activity/recent` : Changer `hybrid.auth` → `auth:sanctum`
- `GET /api/orders/my-orders` : Changer `api.web.auth` → `auth:sanctum`
- `GET /api/orders/{orderNumber}` : Changer `api.web.auth` → `auth:sanctum`
- `POST /api/orders/{orderNumber}/cancel` : Changer `api.web.auth` → `auth:sanctum`

---

## 4. Architecture recommandée pour Flutter

### 4.1 Flux d'authentification Flutter

```
1. POST /api/login
   → Si 2FA désactivé : Retourne { token, user }
   → Si 2FA activé : Retourne { requires_code: true, email }

2. POST /api/verify-login-code (si 2FA)
   → Retourne { token, user }

3. Stocker le token dans Flutter (SecureStorage)
4. Utiliser le token dans toutes les requêtes :
   Authorization: Bearer {token}
```

### 4.2 Gestion du token dans Flutter

**Stockage :**
```dart
// Stocker le token
await secureStorage.write(key: 'auth_token', value: token);

// Récupérer le token
final token = await secureStorage.read(key: 'auth_token');

// Utiliser dans les requêtes
headers['Authorization'] = 'Bearer $token';
```

**Rafraîchissement :**
- Les tokens Sanctum n'expirent pas par défaut (`expiration: null`)
- Pas besoin de rafraîchissement automatique
- Si expiration souhaitée, configurer dans `config/sanctum.php`

### 4.3 Gestion des erreurs

**Erreur 401 (Non authentifié) :**
```dart
if (response.statusCode == 401) {
    // Supprimer le token
    await secureStorage.delete(key: 'auth_token');
    // Rediriger vers la page de connexion
    Navigator.pushReplacementNamed(context, '/login');
}
```

---

## 5. Routes API complètes pour Flutter

### 5.1 Authentification

| Route | Méthode | Auth | Description |
|-------|---------|------|-------------|
| `/api/register` | POST | ❌ | Inscription |
| `/api/login` | POST | ❌ | Connexion (⚠️ **À corriger** : ajouter token) |
| `/api/verify-login-code` | POST | ❌ | Vérifier code 2FA (⚠️ **À créer** : route API) |
| `/api/logout` | POST | ✅ | Déconnexion |
| `/api/logout-all-devices` | POST | ✅ | Déconnecter tous appareils |
| `/api/me` | GET | ✅ | Informations utilisateur |
| `/api/forgot-password` | POST | ❌ | Mot de passe oublié |
| `/api/reset-password` | POST | ❌ | Réinitialisation |
| `/api/resend-verification-code` | POST | ❌ | Renvoyer code |

### 5.2 Profil

| Route | Méthode | Auth | Description |
|-------|---------|------|-------------|
| `/api/profile/update` | POST | ✅ | Mettre à jour profil |
| `/api/profile/change-password` | POST | ✅ | Changer mot de passe |
| `/api/profile/update-photo` | POST | ✅ | Photo de profil (⚠️ **À corriger**) |
| `/api/profile/request-email-verification` | POST | ✅ | Vérification email |
| `/api/activity/recent` | GET | ✅ | Activité récente (⚠️ **À corriger**) |

### 5.3 Panier

| Route | Méthode | Auth | Description |
|-------|---------|------|-------------|
| `/api/cart/add` | POST | ✅ | Ajouter au panier |
| `/api/cart/items` | GET | ✅ | Obtenir panier |
| `/api/cart/update/{id}` | PUT | ✅ | Mettre à jour quantité |
| `/api/cart/remove/{id}` | DELETE | ✅ | Retirer du panier |
| `/api/cart/clear` | DELETE | ✅ | Vider panier |

### 5.4 Favoris

| Route | Méthode | Auth | Description |
|-------|---------|------|-------------|
| `/api/favorites` | GET | ❌ | Liste favoris (public) |
| `/api/favorites/toggle` | POST | ❌ | Ajouter/retirer (public) |

### 5.5 Commandes

| Route | Méthode | Auth | Description |
|-------|---------|------|-------------|
| `/api/orders/create` | POST | ✅ | Créer commande |
| `/api/orders/my-orders` | GET | ✅ | Mes commandes (⚠️ **À corriger**) |
| `/api/orders/{orderNumber}` | GET | ✅ | Détails commande (⚠️ **À corriger**) |
| `/api/orders/{orderNumber}/cancel` | POST | ✅ | Annuler commande (⚠️ **À corriger**) |

### 5.6 Avis

| Route | Méthode | Auth | Description |
|-------|---------|------|-------------|
| `/api/products/{productId}/reviews` | GET | ❌ | Liste avis |
| `/api/reviews` | POST | ✅ | Ajouter avis |
| `/api/reviews/{reviewId}/vote` | POST | ❌ | Voter avis |

### 5.7 Boutique (Vendeur)

| Route | Méthode | Auth | Description |
|-------|---------|------|-------------|
| `/api/store/stats` | GET | ✅ | Statistiques |
| `/api/store/recent-orders` | GET | ✅ | Commandes récentes |
| `/api/store/products` | GET | ✅ | Liste produits |
| `/api/store/orders` | GET | ✅ | Liste commandes |
| `/api/store/orders/{orderNumber}` | GET | ✅ | Détails commande |
| `/api/store/products` | POST | ✅ | Créer produit |
| `/api/store/products/{id}` | PUT | ✅ | Modifier produit |
| `/api/store/products/{id}` | DELETE | ✅ | Supprimer produit |

---

## 6. Plan d'action

### Phase 1 : Corrections critiques (URGENT)

1. **Modifier `AuthController::login`**
   - Détecter les requêtes API
   - Créer et retourner un token Sanctum pour les requêtes API
   - Garder la session pour les requêtes web

2. **Créer `AuthController::verifyLoginCodeApi`**
   - Nouvelle méthode pour vérifier le code 2FA via API
   - Retourner un token Sanctum
   - Route : `POST /api/verify-login-code`

3. **Corriger les routes hybrides**
   - Remplacer `hybrid.auth` par `auth:sanctum` sur les routes API
   - Remplacer `api.web.auth` par `auth:sanctum` sur les routes API

### Phase 2 : Tests et validation

1. Tester le login depuis Flutter
2. Tester la vérification du code 2FA depuis Flutter
3. Tester toutes les routes protégées avec token
4. Vérifier la gestion des erreurs 401

### Phase 3 : Documentation

1. Documenter toutes les routes API pour Flutter
2. Créer des exemples de code Dart
3. Documenter la gestion des tokens
4. Documenter la gestion des erreurs

---

## 7. Code à modifier

### 7.1 AuthController::login

**Modification nécessaire :**

```php
public function login(Request $request)
{
    // ... validation existante ...
    
    // Détecter si c'est une requête API (mobile/Flutter)
    $isApiRequest = $request->expectsJson() || $request->is('api/*');
    
    if (!$user->two_factor_enabled) {
        // Connexion directe
        if ($isApiRequest) {
            // Pour Flutter : créer un token Sanctum
            $token = $user->createToken('mobile-app')->plainTextToken;
            
            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'user' => $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'two_factor_enabled']),
                'token' => $token,
                'requires_code' => false
            ]);
        } else {
            // Pour web : utiliser la session (code existant)
            // ... code session existant ...
        }
    } else {
        // 2FA activé
        // ... code existant ...
    }
}
```

### 7.2 AuthController::verifyLoginCodeApi (NOUVEAU)

**Nouvelle méthode :**

```php
public function verifyLoginCodeApi(Request $request)
{
    // ... validation existante (copier de verifyLoginCode) ...
    
    // Vérifier le code
    $authCode = AuthCode::where('email', $request->email)
        ->where('code', $request->code)
        ->where('type', 'login')
        ->unused()
        ->notExpired()
        ->first();
    
    if (!$authCode) {
        // ... gestion erreurs ...
    }
    
    $user = User::where('email', $request->email)->first();
    $authCode->markAsUsed();
    
    // Créer un token Sanctum pour Flutter
    $token = $user->createToken('mobile-app')->plainTextToken;
    
    return response()->json([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'two_factor_enabled']),
        'token' => $token
    ]);
}
```

### 7.3 Routes à modifier

**Fichier : `routes/api.php`**

```php
// Ajouter la route pour vérifier le code 2FA (API)
Route::post('/verify-login-code', [AuthController::class, 'verifyLoginCodeApi']);

// Corriger les routes hybrides
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/profile/update-photo', [ProfileController::class, 'updatePhotoApi']);
    Route::get('/activity/recent', [ProfileController::class, 'getRecentActivityApi']);
    Route::get('/orders/my-orders', [OrderController::class, 'myOrders']);
    Route::get('/orders/{orderNumber}', [OrderController::class, 'getOrderDetails']);
    Route::post('/orders/{orderNumber}/cancel', [OrderController::class, 'cancelOrder']);
});
```

---

## 8. Configuration Sanctum pour Flutter

### 8.1 Configuration actuelle

**Fichier : `config/sanctum.php`**

```php
'expiration' => null, // Pas d'expiration (OK pour Flutter)
'stateful' => [...], // Domains stateful (pour SPA web, pas pour Flutter)
```

**Pour Flutter :**
- ✅ `expiration: null` : Tokens sans expiration (OK)
- ⚠️ `stateful` : Pas utilisé par Flutter (OK, Flutter utilise Bearer tokens)

### 8.2 CORS (si nécessaire)

**Fichier : `config/cors.php`**

Assurez-vous que CORS est configuré pour accepter les requêtes depuis Flutter :

```php
'allowed_origins' => ['*'], // Ou spécifier les domaines
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => false, // Pas de cookies pour Flutter
```

---

## 9. Exemple d'utilisation Flutter

### 9.1 Service d'authentification

```dart
class AuthService {
  final _storage = FlutterSecureStorage();
  final _baseUrl = 'https://votre-domaine.com/api';
  
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'email': email, 'password': password}),
    );
    
    final data = jsonDecode(response.body);
    
    if (data['success'] && data['requires_code'] == true) {
      // 2FA activé, retourner pour saisie du code
      return {'requires_code': true, 'email': email};
    }
    
    if (data['success'] && data['token'] != null) {
      // Stocker le token
      await _storage.write(key: 'auth_token', value: data['token']);
      return {'success': true, 'user': data['user']};
    }
    
    throw Exception(data['message'] ?? 'Erreur de connexion');
  }
  
  Future<Map<String, dynamic>> verifyCode(String email, String code) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/verify-login-code'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'email': email, 'code': code}),
    );
    
    final data = jsonDecode(response.body);
    
    if (data['success'] && data['token'] != null) {
      await _storage.write(key: 'auth_token', value: data['token']);
      return {'success': true, 'user': data['user']};
    }
    
    throw Exception(data['message'] ?? 'Code invalide');
  }
  
  Future<Map<String, String>> getHeaders() async {
    final token = await _storage.read(key: 'auth_token');
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }
}
```

### 9.2 Utilisation dans les requêtes

```dart
final authService = AuthService();
final headers = await authService.getHeaders();

final response = await http.get(
  Uri.parse('https://votre-domaine.com/api/cart/items'),
  headers: headers,
);
```

---

## 10. Conclusion

### 10.1 Le système hybride est nécessaire

✅ **OUI**, le système hybride est nécessaire car :
- Application web utilise les sessions
- Application Flutter utilise les tokens
- Les deux doivent coexister

### 10.2 Corrections nécessaires

**URGENT :**
1. ✅ Modifier `login` pour retourner un token pour les requêtes API
2. ✅ Créer `verifyLoginCodeApi` pour le 2FA via API
3. ✅ Corriger les routes hybrides pour utiliser `auth:sanctum`

**IMPORTANT :**
4. Documenter toutes les routes API pour Flutter
5. Tester toutes les routes avec tokens
6. Configurer CORS si nécessaire

### 10.3 Architecture finale

**Web (Blade) :**
- Sessions Laravel
- Cookies de session
- CSRF tokens
- Routes web (`/cart/add`, `/orders/create`, etc.)

**Flutter (Mobile) :**
- Tokens Sanctum
- Bearer tokens dans headers
- Routes API (`/api/cart/add`, `/api/orders/create`, etc.)
- Pas de CSRF (stateless)

**Système hybride :**
- `HybridAuthMiddleware` : Supporte session + token
- Routes web : Sessions
- Routes API : Tokens
- Transition transparente

---

## 11. Prochaines étapes

1. **Modifier le code** (voir section 7)
2. **Tester avec Flutter** (voir section 9)
3. **Documenter l'API** pour l'équipe Flutter
4. **Configurer CORS** si nécessaire
5. **Monitorer les erreurs** 401 dans les logs

