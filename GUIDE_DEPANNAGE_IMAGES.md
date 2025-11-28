# Guide de dépannage - Images non affichées sur le serveur

## Problème
Les fichiers du dossier `public/images` ne s'affichent pas sur le serveur de production.

## Solutions à vérifier

### 1. Vérifier que le dossier existe et contient les fichiers

```bash
# Se connecter au serveur via SSH
cd /chemin/vers/votre/projet

# Vérifier l'existence du dossier
ls -la public/images

# Vérifier les permissions
ls -la public/ | grep images
```

### 2. Vérifier et corriger les permissions

Les fichiers dans `public/images` doivent être accessibles en lecture :

```bash
# Donner les permissions correctes
chmod -R 755 public/images
chmod -R 644 public/images/*

# Si nécessaire, changer le propriétaire
chown -R www-data:www-data public/images  # Pour Apache
# ou
chown -R nginx:nginx public/images        # Pour Nginx
```

### 3. Vérifier la configuration du serveur web

#### Pour Apache (.htaccess)

Le fichier `public/.htaccess` doit être présent et permettre la lecture des fichiers statiques. Vérifiez qu'il contient :

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Permettre l'accès aux fichiers existants
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

#### Pour Nginx

Vérifiez que votre configuration Nginx permet la lecture des fichiers statiques :

```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    root /chemin/vers/projet/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Servir les fichiers statiques directement
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 4. Vérifier la variable d'environnement APP_URL

Dans votre fichier `.env` sur le serveur, vérifiez que `APP_URL` est correctement configuré :

```env
APP_URL=https://votre-domaine.com
```

Puis videz le cache de configuration :

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 5. Vérifier que les fichiers sont bien déployés

Si vous utilisez Git, vérifiez que les fichiers du dossier `public/images` ne sont pas ignorés dans `.gitignore`.

Le fichier `.gitignore` ne doit **PAS** contenir :
```
public/images/
```

### 6. Tester l'accès direct aux images

Testez l'accès direct à une image via l'URL :

```
https://votre-domaine.com/images/produit.jpg
```

Si cela ne fonctionne pas, le problème vient du serveur web ou des permissions.

### 7. Vérifier les logs du serveur web

#### Logs Apache
```bash
tail -f /var/log/apache2/error.log
# ou
tail -f /var/log/httpd/error_log
```

#### Logs Nginx
```bash
tail -f /var/log/nginx/error.log
```

#### Logs Laravel
```bash
tail -f storage/logs/laravel.log
```

### 8. Solution rapide : Recréer le dossier si nécessaire

Si le dossier n'existe pas ou est vide, créez-le et copiez les fichiers :

```bash
# Créer le dossier s'il n'existe pas
mkdir -p public/images

# Copier les fichiers depuis votre machine locale
# (via FTP, SCP, ou votre méthode de déploiement préférée)
```

### 9. Vérifier le DocumentRoot

Assurez-vous que le `DocumentRoot` de votre serveur web pointe vers le dossier `public` :

```
DocumentRoot /chemin/vers/projet/public
```

**Important** : Le DocumentRoot doit pointer vers `public`, pas vers la racine du projet !

### 10. Commandes de diagnostic

Exécutez ces commandes pour diagnostiquer le problème :

```bash
# Vérifier les permissions
ls -la public/images

# Tester si le serveur peut lire les fichiers
sudo -u www-data cat public/images/produit.jpg

# Vérifier la configuration Laravel
php artisan config:show app.url

# Vérifier les routes
php artisan route:list | grep images
```

## Corrections apportées au code

Un bug a été corrigé dans `app/Models/Product.php` :
- Les chemins commençant par `images/` étaient incorrectement redirigés vers `storage/`
- Maintenant, ils utilisent correctement `asset($image)` pour pointer vers `public/images/`

Assurez-vous de déployer cette correction sur le serveur.

## Checklist rapide

- [ ] Le dossier `public/images` existe et contient des fichiers
- [ ] Les permissions sont correctes (755 pour dossier, 644 pour fichiers)
- [ ] Le propriétaire est correct (www-data ou nginx)
- [ ] `.htaccess` (Apache) ou configuration Nginx est correcte
- [ ] `APP_URL` dans `.env` est correct
- [ ] Le cache Laravel a été vidé
- [ ] Le DocumentRoot pointe vers `public`
- [ ] Les fichiers ont été déployés sur le serveur
- [ ] Les logs ne montrent pas d'erreurs

