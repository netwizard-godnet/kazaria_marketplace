# Guide de Configuration de Session

## Configuration de Session via .env

### 1. Créer le fichier .env

Créez un fichier `.env` à la racine du projet avec la configuration suivante :

```env
# Configuration de base
APP_NAME=Kazaria
APP_ENV=local
APP_KEY=base64:your-app-key-here
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# Configuration de session
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Configuration de base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kazaria_laravel
DB_USERNAME=root
DB_PASSWORD=

# Autres configurations
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### 2. Paramètres de Session Expliqués

#### SESSION_DRIVER
- **`file`** : Stockage des sessions dans des fichiers (recommandé pour le développement)
- **`database`** : Stockage des sessions en base de données (recommandé pour la production)
- **`redis`** : Stockage des sessions dans Redis (recommandé pour les applications à haute charge)

#### SESSION_LIFETIME
- **Durée de vie** de la session en minutes
- **Défaut** : 120 minutes (2 heures)
- **Recommandé** : 120-480 minutes selon vos besoins

#### SESSION_ENCRYPT
- **`true`** : Chiffrement des données de session (plus sécurisé)
- **`false`** : Pas de chiffrement (plus rapide)
- **Recommandé** : `false` pour le développement, `true` pour la production

#### SESSION_SECURE_COOKIE
- **`true`** : Cookies sécurisés (HTTPS uniquement)
- **`false`** : Cookies non sécurisés (HTTP et HTTPS)
- **Recommandé** : `false` pour le développement local, `true` pour la production

#### SESSION_HTTP_ONLY
- **`true`** : Cookies accessibles uniquement via HTTP (plus sécurisé)
- **`false`** : Cookies accessibles via JavaScript
- **Recommandé** : `true` (sécurité)

#### SESSION_SAME_SITE
- **`lax`** : Cookies partagés entre sites (recommandé)
- **`strict`** : Cookies strictement limités au site
- **`none`** : Pas de restriction
- **Recommandé** : `lax`

### 3. Configuration pour différents environnements

#### Développement Local
```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

#### Production
```env
SESSION_DRIVER=database
SESSION_LIFETIME=480
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
```

### 4. Vérification de la configuration

Après avoir créé le fichier `.env`, exécutez :

```bash
# Vider les caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Vérifier la configuration
php artisan config:show session
```

### 5. Test de la session

Pour tester si la session fonctionne :

1. **Accédez au site** : `http://127.0.0.1:8000`
2. **Connectez-vous** via `/authentification`
3. **Vérifiez** que la directive `@auth` fonctionne
4. **Vérifiez** que le header affiche le dropdown utilisateur

### 6. Dépannage

#### Problème : Session non persistante
- Vérifiez que `SESSION_DRIVER=file`
- Vérifiez que le dossier `storage/framework/sessions` existe
- Vérifiez les permissions du dossier `storage`

#### Problème : Cookies non envoyés
- Vérifiez que `SESSION_SECURE_COOKIE=false` en développement
- Vérifiez que `SESSION_DOMAIN=null` ou correct
- Vérifiez que `SESSION_PATH=/`

#### Problème : Directive @auth ne fonctionne pas
- Vérifiez que l'utilisateur est connecté via le navigateur
- Vérifiez que les middlewares de session sont appliqués
- Videz les caches : `php artisan config:clear`

### 7. Configuration actuelle du projet

Le projet est configuré avec :
- **SESSION_DRIVER** : `file` (fichiers)
- **SESSION_LIFETIME** : `120` minutes
- **SESSION_ENCRYPT** : `false`
- **SESSION_SECURE_COOKIE** : `false` (développement)
- **SESSION_HTTP_ONLY** : `true`
- **SESSION_SAME_SITE** : `lax`

Cette configuration est optimale pour le développement local.
