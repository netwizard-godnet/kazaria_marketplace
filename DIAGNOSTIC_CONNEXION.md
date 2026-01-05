# Diagnostic : Problème de connexion

## Problème identifié

**L'utilisateur n'est pas connecté après la connexion via le formulaire.**

## Cause racine

La route `/api/login` est une **route API** (`routes/api.php`), donc :
- Le middleware `StartSession` n'est **pas appliqué** automatiquement
- Le cookie de session n'est **pas envoyé** dans la réponse JSON
- Même si la session est créée côté serveur, le navigateur ne reçoit pas le cookie
- Lors de la redirection vers l'accueil, la session n'existe pas car le cookie n'a pas été envoyé

## Corrections appliquées

### 1. Ajout du cookie de session manuellement

**Fichier : `app/Http/Controllers/AuthController.php`**

**Méthode helper ajoutée :**
```php
private function addSessionCookieToResponse($response, $request)
{
    if (!$request->hasSession()) {
        return $response;
    }

    $session = $request->session();
    $sessionName = config('session.cookie');
    $sessionId = $session->getId();
    $sessionLifetime = config('session.lifetime') * 60;
    
    $cookie = cookie(
        $sessionName,
        $sessionId,
        $sessionLifetime,
        config('session.path', '/'),
        config('session.domain'),
        config('session.secure', false),
        config('session.http_only', true),
        false,
        config('session.same_site', 'lax')
    );

    return $response->withCookie($cookie);
}
```

**Utilisation dans `login()` :**
```php
// Sauvegarder la session
$request->session()->save();

// Créer la réponse JSON
$response = response()->json([...]);

// Ajouter le cookie de session
return $this->addSessionCookieToResponse($response, $request);
```

### 2. Amélioration de la détection des requêtes web

**Fichier : `app/Http/Controllers/AuthController.php`**

**Détection améliorée :**
```php
// Si X-Requested-With est présent, c'est une requête AJAX depuis le frontend web
$isApiRequest = ($request->expectsJson() || $request->is('api/*')) 
    && !$request->header('X-Requested-With');
```

### 3. Ajout de `credentials: 'same-origin'` dans le fetch

**Fichier : `resources/views/auth/authentification.blade.php`**

**Correction :**
```javascript
const response = await fetch('/api/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'X-Requested-With': 'XMLHttpRequest' // ✅ Ajouté
    },
    credentials: 'same-origin', // ✅ Ajouté : inclure les cookies
    body: JSON.stringify(object)
});
```

### 4. Amélioration du démarrage de session

**Fichier : `app/Http/Controllers/AuthController.php`**

**Code amélioré :**
```php
if (!$request->hasSession()) {
    $sessionStore = app('session.store');
    // Lire l'ID de session depuis les cookies si disponible
    $sessionId = $request->cookies->get($sessionStore->getName());
    if ($sessionId) {
        $sessionStore->setId($sessionId);
    }
    $request->setLaravelSession($sessionStore);
}
```

## Vérification

### Test 1 : Vérifier que le cookie est envoyé

**Dans les DevTools du navigateur :**
1. Ouvrir l'onglet Network
2. Faire une connexion
3. Vérifier la réponse de `/api/login`
4. Vérifier dans l'onglet "Response Headers" qu'il y a un `Set-Cookie` avec le nom de la session

### Test 2 : Vérifier que la session est créée

**Dans la base de données :**
```sql
SELECT * FROM sessions ORDER BY last_activity DESC LIMIT 1;
```

**Vérifier :**
- La session existe
- `user_id` est présent dans les données de session
- `last_activity` est récent

### Test 3 : Vérifier après redirection

**Après la redirection vers l'accueil :**
1. Vérifier dans les DevTools que le cookie de session est présent
2. Vérifier que `auth()->check()` retourne `true`
3. Vérifier que le header affiche le nom de l'utilisateur

## Problèmes potentiels restants

### Problème 1 : Le cookie n'est pas accepté par le navigateur

**Causes possibles :**
- Configuration `same_site` trop stricte
- Configuration `secure` incorrecte (HTTPS requis)
- Configuration `domain` incorrecte

**Solution :**
Vérifier `config/session.php` :
```php
'same_site' => 'lax', // ✅ OK
'secure' => env('SESSION_SECURE_COOKIE', null), // ✅ Auto-détection
'domain' => env('SESSION_DOMAIN'), // ✅ Vérifier si nécessaire
```

### Problème 2 : Le cookie est envoyé mais la session n'est pas lue

**Causes possibles :**
- Le middleware `StartSession` n'est pas appliqué sur la route d'accueil
- La session est expirée
- Le cookie est supprimé par le navigateur

**Solution :**
Vérifier que la route d'accueil a le middleware `web` qui inclut `StartSession`.

### Problème 3 : La session est créée mais l'utilisateur n'est pas authentifié

**Causes possibles :**
- `Auth::login()` n'a pas fonctionné
- La session a été régénérée après le login
- Le guard `web` n'est pas utilisé

**Solution :**
Vérifier dans les logs si `Auth::login()` a fonctionné.

## Commandes de diagnostic

### Vérifier la session dans la base de données

```bash
php artisan tinker
```

```php
// Vérifier les sessions récentes
\DB::table('sessions')
    ->orderBy('last_activity', 'desc')
    ->limit(5)
    ->get();

// Vérifier une session spécifique
$session = \DB::table('sessions')
    ->where('id', 'SESSION_ID')
    ->first();
    
// Décoder les données de session
$data = unserialize(base64_decode($session->payload));
dd($data);
```

### Vérifier l'authentification

```php
// Dans tinker ou un contrôleur
auth()->check(); // Doit retourner true
auth()->user(); // Doit retourner l'utilisateur
```

## Solution alternative (si le problème persiste)

Si le cookie n'est toujours pas envoyé, on peut :

1. **Déplacer `/api/login` vers `routes/web.php`** (mais cela casse l'API Flutter)
2. **Créer une route web séparée** `/login` pour le frontend web
3. **Utiliser `EnsureFrontendRequestsAreStateful` correctement** en configurant les domaines

## Prochaines étapes

1. ✅ Ajouter le cookie de session manuellement
2. ✅ Ajouter `credentials: 'same-origin'` dans le fetch
3. ✅ Améliorer la détection des requêtes web
4. ⏳ **Tester** la connexion
5. ⏳ **Vérifier** que le cookie est envoyé
6. ⏳ **Vérifier** que la session est lue après redirection

## Test rapide

Pour tester rapidement :

1. Ouvrir les DevTools (F12)
2. Onglet Network
3. Se connecter
4. Vérifier la requête `/api/login` :
   - Status : 200
   - Response Headers : Doit contenir `Set-Cookie: kazaria-laravel-session=...`
5. Après redirection, vérifier :
   - Onglet Application > Cookies
   - Le cookie de session doit être présent
   - `auth()->check()` doit retourner `true`

