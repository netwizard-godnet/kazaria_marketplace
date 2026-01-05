# Analyse approfondie : Déconnexion lors de plusieurs actualisations

## Problème

**L'utilisateur est déconnecté après plusieurs actualisations ou lors de la navigation.**

## Analyse systématique de tous les éléments

### 1. Middleware `AuthenticateSession` ⚠️ **CRITIQUE**

**Fichier :** `vendor/laravel/framework/src/Illuminate/Session/Middleware/AuthenticateSession.php`

**Fonctionnement :**
```php
// Ligne 58-60 : Si password_hash_web n'existe pas, le créer
if (! $request->session()->has('password_hash_'.$this->auth->getDefaultDriver())) {
    $this->storePasswordHashInSession($request);
}

// Ligne 62-64 : ⚠️ DÉCONNEXION SI LE HASH NE CORRESPOND PAS
if (! hash_equals($request->session()->get('password_hash_'.$this->auth->getDefaultDriver()), $request->user()->getAuthPassword())) {
    $this->logout($request); // ← DÉCONNEXION AUTOMATIQUE
}
```

**Problème potentiel :**
- Si `password_hash_web` est absent ou incorrect, l'utilisateur est **déconnecté immédiatement**
- Cela peut arriver si :
  1. La session n'est pas sauvegardée correctement
  2. Le hash est modifié entre deux requêtes
  3. La session est régénérée sans mettre à jour `password_hash_web`

**✅ Solution appliquée :** Middleware `EnsurePasswordHashInSession` qui garantit que `password_hash_web` est toujours présent.

---

### 2. Démarrage manuel de session dans les middlewares ⚠️ **PROBLÈME POTENTIEL**

#### A. `HybridAuthMiddleware`

**Fichier :** `app/Http/Middleware/HybridAuthMiddleware.php`

**Code problématique :**
```php
// Lignes 22-33
if ($request->is('api/*')) {
    if (!$request->hasSession() || !session()->isStarted()) {
        $session = app('session.store');
        if (!$session->isStarted()) {
            $session->start(); // ⚠️ Démarrage manuel
        }
        $request->setLaravelSession($session);
    }
}
```

**Problème potentiel :**
- Démarre une session manuellement pour les routes API
- **Ne lit pas l'ID de session depuis les cookies** avant de démarrer
- Peut créer une **nouvelle session** au lieu d'utiliser l'existante
- **Résultat :** L'utilisateur perd sa session et est déconnecté

**Correction nécessaire :**
```php
if ($request->is('api/*')) {
    if (!$request->hasSession() || !session()->isStarted()) {
        $session = app('session.store');
        // ⚠️ IMPORTANT : Lire l'ID de session depuis les cookies
        $sessionId = $request->cookies->get($session->getName());
        if ($sessionId) {
            $session->setId($sessionId);
        }
        if (!$session->isStarted()) {
            $session->start();
        }
        $request->setLaravelSession($session);
    }
}
```

#### B. `ClientAuthMiddleware`

**Fichier :** `app/Http/Middleware/ClientAuthMiddleware.php`

**Code problématique :**
```php
// Lignes 18-25
if (!$request->hasSession() || !session()->isStarted()) {
    $session = app('session'); // ⚠️ Utilise 'session' au lieu de 'session.store'
    if (!$session->isStarted()) {
        $session->start(); // ⚠️ Démarrage manuel sans lire l'ID depuis les cookies
    }
    $request->setLaravelSession($session);
}
```

**Problèmes :**
1. Utilise `app('session')` au lieu de `app('session.store')` (incohérence)
2. **Ne lit pas l'ID de session depuis les cookies**
3. Peut créer une nouvelle session au lieu d'utiliser l'existante

**Correction nécessaire :**
```php
if (!$request->hasSession() || !session()->isStarted()) {
    $session = app('session.store');
    // ⚠️ IMPORTANT : Lire l'ID de session depuis les cookies
    $sessionId = $request->cookies->get($session->getName());
    if ($sessionId) {
        $session->setId($sessionId);
    }
    if (!$session->isStarted()) {
        $session->start();
    }
    $request->setLaravelSession($session);
}
```

#### C. `ApiWebAuth`

**Fichier :** `app/Http/Middleware/ApiWebAuth.php`

**Code :**
```php
// Lignes 34-45
$session = $request->session();
if (!$session->isStarted()) {
    $sessionId = $request->cookies->get($session->getName());
    if ($sessionId) {
        $session->setId($sessionId); // ✅ CORRECT : Lit l'ID depuis les cookies
    }
    $session->start();
}
```

**✅ Correct :** Lit l'ID de session depuis les cookies avant de démarrer.

---

### 3. Régénération de session ⚠️ **RISQUE MOYEN**

**Fichiers concernés :**
- `app/Http/Controllers/AuthController.php` (lignes 230, 477)
- `app/Http/Controllers/Auth/SocialAuthController.php` (ligne 119)
- `app/Http/Controllers/Admin/AuthController.php` (ligne 56)

**Code :**
```php
$request->session()->regenerate();
$request->session()->put('password_hash_web', $user->getAuthPassword());
```

**Problème potentiel :**
- La régénération crée un **nouvel ID de session**
- Si le cookie n'est pas mis à jour immédiatement, la prochaine requête peut utiliser l'ancien ID
- **Résultat :** Session perdue, utilisateur déconnecté

**✅ Solution :** Le middleware `StartSession` met automatiquement à jour le cookie après régénération.

---

### 4. ViewServiceProvider : Rechargement de l'utilisateur ⚠️ **RISQUE FAIBLE**

**Fichier :** `app/Providers/ViewServiceProvider.php`

**Code :**
```php
// Lignes 36-47
if (auth()->check()) {
    $userId = auth()->id();
    if ($userId) {
        // Recharger l'utilisateur depuis la base de données
        $user = \App\Models\User::with('store')->find($userId);
        if ($user) {
            $view->with('currentUser', $user);
        }
    }
}
```

**Problème potentiel :**
- Recharge l'utilisateur depuis la DB à **chaque rendu de vue**
- Si l'utilisateur est supprimé ou modifié, cela peut causer des problèmes
- **Risque faible** car ne modifie pas la session

**✅ Pas de problème majeur** : Ne modifie pas la session, juste les données de la vue.

---

### 5. Configuration de session ⚠️ **VÉRIFIER**

**Fichier :** `config/session.php`

**Points à vérifier :**

#### A. Lifetime
```php
'lifetime' => (int) env('SESSION_LIFETIME', 120), // 120 minutes
```
✅ **OK** : 120 minutes est raisonnable.

#### B. Expire on close
```php
'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),
```
✅ **OK** : `false` signifie que la session persiste après fermeture du navigateur.

#### C. Driver
```php
'driver' => env('SESSION_DRIVER', 'database'),
```
✅ **OK** : Utilise la base de données, persistant.

#### D. Cookie name
```php
'cookie' => env('SESSION_COOKIE', 'kazaria-laravel-session'),
```
✅ **OK** : Nom personnalisé.

#### E. Same site
```php
'same_site' => env('SESSION_SAME_SITE', 'lax'),
```
✅ **OK** : `lax` permet l'envoi du cookie lors de la navigation.

---

### 6. Ordre d'exécution des middlewares ⚠️ **CRITIQUE**

**Ordre actuel pour les routes web :**
1. `EncryptCookies`
2. `AddQueuedCookiesToResponse`
3. `StartSession` ← Démarre la session et lit le cookie
4. `ShareErrorsFromSession`
5. `ValidateCsrfToken`
6. `SubstituteBindings`
7. `AuthenticateSession` ← Vérifie password_hash_web
8. `SeoMiddleware`
9. `LandingPageMiddleware`
10. `TrackPageVisits`
11. `EnsurePasswordHashInSession` ← ✅ Garantit que password_hash_web est présent

**Problème potentiel :**
- Si `AuthenticateSession` s'exécute **avant** que `EnsurePasswordHashInSession` ne mette à jour `password_hash_web`, l'utilisateur peut être déconnecté.

**✅ Solution :** `EnsurePasswordHashInSession` s'exécute **après** `AuthenticateSession` et met à jour `password_hash_web` si nécessaire.

---

### 7. Problème de timing : Session non sauvegardée ⚠️ **RISQUE MOYEN**

**Scénario problématique :**

1. Requête 1 : Utilisateur se connecte
   - `Auth::login()` connecte l'utilisateur
   - `password_hash_web` est stocké
   - Session est sauvegardée
   - Cookie est envoyé

2. Requête 2 : Actualisation rapide (avant que le cookie ne soit reçu)
   - Le navigateur envoie l'ancien cookie (ou aucun cookie)
   - `StartSession` démarre une nouvelle session
   - `AuthenticateSession` ne trouve pas `password_hash_web`
   - **Résultat :** Utilisateur déconnecté

**✅ Solution partielle :** Le middleware `EnsurePasswordHashInSession` devrait corriger cela, mais il faut s'assurer que la session est bien sauvegardée.

---

### 8. Problème de cookie : Cookie non envoyé ou rejeté ⚠️ **RISQUE MOYEN**

**Causes possibles :**

1. **Configuration `same_site` trop stricte**
   - Si `same_site` est `strict`, le cookie n'est pas envoyé lors de la navigation
   - ✅ **Vérifié :** `lax` est utilisé, OK

2. **Configuration `secure` incorrecte**
   - Si `secure` est `true` mais le site est en HTTP, le cookie n'est pas envoyé
   - ✅ **Vérifié :** `env('SESSION_SECURE_COOKIE', null)` auto-détecte, OK

3. **Configuration `domain` incorrecte**
   - Si le domaine ne correspond pas, le cookie n'est pas envoyé
   - ⚠️ **À vérifier :** Vérifier que `SESSION_DOMAIN` est correctement configuré

4. **Cookie bloqué par le navigateur**
   - Les navigateurs peuvent bloquer les cookies tiers
   - ⚠️ **À vérifier :** Vérifier les paramètres du navigateur

---

## Corrections nécessaires

### 1. Corriger `HybridAuthMiddleware` ⚠️ **URGENT**

**Problème :** Ne lit pas l'ID de session depuis les cookies avant de démarrer.

**Correction :**
```php
if ($request->is('api/*')) {
    if (!$request->hasSession() || !session()->isStarted()) {
        $session = app('session.store');
        // ⚠️ IMPORTANT : Lire l'ID de session depuis les cookies
        $sessionId = $request->cookies->get($session->getName());
        if ($sessionId) {
            $session->setId($sessionId);
        }
        if (!$session->isStarted()) {
            $session->start();
        }
        $request->setLaravelSession($session);
    }
}
```

### 2. Corriger `ClientAuthMiddleware` ⚠️ **URGENT**

**Problème :** Utilise `app('session')` au lieu de `app('session.store')` et ne lit pas l'ID depuis les cookies.

**Correction :**
```php
if (!$request->hasSession() || !session()->isStarted()) {
    $session = app('session.store');
    // ⚠️ IMPORTANT : Lire l'ID de session depuis les cookies
    $sessionId = $request->cookies->get($session->getName());
    if ($sessionId) {
        $session->setId($sessionId);
    }
    if (!$session->isStarted()) {
        $session->start();
    }
    $request->setLaravelSession($session);
}
```

### 3. Vérifier la configuration `SESSION_DOMAIN` ⚠️ **IMPORTANT**

**À vérifier :**
- Le fichier `.env` doit avoir `SESSION_DOMAIN` correctement configuré
- Si le site est sur `localhost`, laisser vide ou utiliser `localhost`
- Si le site est sur un domaine, utiliser le domaine exact

---

## Tests à effectuer

### Test 1 : Vérifier que le cookie est envoyé

1. Ouvrir les DevTools (F12)
2. Onglet Network
3. Se connecter
4. Vérifier la requête `/api/login` :
   - Response Headers : Doit contenir `Set-Cookie: kazaria-laravel-session=...`
5. Actualiser la page
6. Vérifier la requête suivante :
   - Request Headers : Doit contenir `Cookie: kazaria-laravel-session=...`

### Test 2 : Vérifier que la session persiste

1. Se connecter
2. Ouvrir les DevTools > Application > Cookies
3. Vérifier que le cookie `kazaria-laravel-session` est présent
4. Actualiser plusieurs fois (F5)
5. Vérifier que le cookie est toujours présent
6. Vérifier que `auth()->check()` retourne `true`

### Test 3 : Vérifier password_hash_web dans la session

```bash
php artisan tinker
```

```php
// Voir la session actuelle
$session = \DB::table('sessions')
    ->orderBy('last_activity', 'desc')
    ->first();
$data = unserialize(base64_decode($session->payload));
dd([
    'user_id' => $data['login_web_59ba36addc2b2f9401580f014c7f58ea4'] ?? null,
    'password_hash_web' => $data['password_hash_web'] ?? 'ABSENT',
    'last_activity' => date('Y-m-d H:i:s', $session->last_activity),
]);
```

---

## Résumé des problèmes identifiés

| Problème | Fichier | Gravité | Statut |
|----------|---------|---------|--------|
| `HybridAuthMiddleware` ne lit pas l'ID depuis les cookies | `app/Http/Middleware/HybridAuthMiddleware.php` | ⚠️ **CRITIQUE** | ❌ À corriger |
| `ClientAuthMiddleware` ne lit pas l'ID depuis les cookies | `app/Http/Middleware/ClientAuthMiddleware.php` | ⚠️ **CRITIQUE** | ❌ À corriger |
| `AuthenticateSession` déconnecte si password_hash_web manque | Laravel core | ⚠️ **CRITIQUE** | ✅ Corrigé avec `EnsurePasswordHashInSession` |
| Configuration `SESSION_DOMAIN` | `.env` | ⚠️ **MOYEN** | ⚠️ À vérifier |
| Timing : Cookie non reçu avant actualisation | - | ⚠️ **FAIBLE** | ✅ Partiellement corrigé |

---

## Actions immédiates

1. ✅ **Corriger `HybridAuthMiddleware`** : Lire l'ID de session depuis les cookies
2. ✅ **Corriger `ClientAuthMiddleware`** : Lire l'ID de session depuis les cookies
3. ⚠️ **Vérifier `SESSION_DOMAIN`** dans `.env`
4. ✅ **Tester** : Vérifier que le cookie est envoyé et reçu correctement

