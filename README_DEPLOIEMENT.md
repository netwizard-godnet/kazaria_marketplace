# 🚀 Guide de Déploiement KAZARIA

## 📋 Vue d'Ensemble

Ce document contient toutes les informations nécessaires pour déployer KAZARIA sur un serveur de production.

---

## 📚 Documents Disponibles

| Document | Description | Quand l'utiliser |
|----------|-------------|------------------|
| **CORRECTION_URGENTE_PERMISSIONS.md** | Solution rapide aux erreurs 500 | ❌ Erreur de permissions en production |
| **GUIDE_DEPLOIEMENT_STORAGE.md** | Guide complet du système de stockage | 📦 Déploiement complet ou problèmes d'images |
| **README_DEPLOIEMENT.md** | Ce fichier - Vue d'ensemble | 📖 Point de départ |

---

## 🆘 PROBLÈMES COURANTS

### ❌ Erreur 500 : Permission denied

**Symptôme** : 
```
file_put_contents(/path/to/storage/framework/views/xxx.php): 
Failed to open stream: Permission denied
```

**Solution Rapide** :
```bash
cd /chemin/vers/votre/projet
php artisan permissions:fix
sudo chown -R www-data:www-data storage bootstrap/cache
```

📖 **Voir** : `CORRECTION_URGENTE_PERMISSIONS.md`

---

### 🖼️ Images ne s'affichent pas

**Symptôme** : Les logos, bannières et images de produits sont cassés

**Solution Rapide** :
```bash
cd /chemin/vers/votre/projet
php artisan storage:fix --force
php artisan storage:check
```

📖 **Voir** : `GUIDE_DEPLOIEMENT_STORAGE.md`

---

### 🔗 Erreur 404 sur les routes

**Symptôme** : Certaines pages retournent 404

**Solution Rapide** :
```bash
cd /chemin/vers/votre/projet
php artisan route:clear
php artisan config:clear
php artisan optimize
```

---

## ⚡ DÉPLOIEMENT RAPIDE (Checklist)

### Avant de Déployer

- [ ] Sauvegarder la base de données de production
- [ ] Sauvegarder les fichiers `storage/app/public/`
- [ ] Vérifier que `.env` est configuré
- [ ] Tester en local

### Sur le Serveur

```bash
# 1. Transférer les fichiers
# (via Git, FTP, ou rsync)

# 2. Aller dans le projet
cd /chemin/vers/votre/projet

# 3. Installer les dépendances
composer install --no-dev --optimize-autoloader

# 4. Configuration
cp .env.example .env
nano .env  # Configurer
php artisan key:generate

# 5. Base de données
php artisan migrate --force

# 6. Permissions (CRITIQUE !)
bash fix-permissions.sh
# OU
php artisan permissions:fix
sudo chown -R www-data:www-data storage bootstrap/cache

# 7. Storage
php artisan storage:fix --force

# 8. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Vérification
php artisan storage:check
php artisan permissions:fix
```

---

## 🛠️ COMMANDES ARTISAN PERSONNALISÉES

### `php artisan storage:check`
Vérifie l'état du système de stockage
```bash
php artisan storage:check
```
**Affiche** :
- État du lien symbolique
- Présence des dossiers
- Permissions
- Fichiers manquants

---

### `php artisan storage:fix`
Répare le système de stockage
```bash
php artisan storage:fix --force
```
**Effectue** :
- Création des dossiers
- Recréation du lien symbolique
- Ajustement des permissions
- Vérification

---

### `php artisan permissions:fix`
Corrige les permissions critiques
```bash
php artisan permissions:fix
```
**Effectue** :
- Création des dossiers manquants
- Test d'écriture
- Nettoyage du cache
- Vérification du lien symbolique

---

## 🔧 SCRIPTS DISPONIBLES

### `fix-permissions.sh`
Script Bash pour corriger toutes les permissions
```bash
bash fix-permissions.sh
```

**Fonctionnalités** :
- ✅ Détection automatique de l'utilisateur web
- ✅ Création de tous les dossiers
- ✅ Ajustement des permissions
- ✅ Configuration SELinux (si applicable)
- ✅ Tests de vérification
- ✅ Support sudo automatique

**Usage** :
```bash
# Exécution normale
bash fix-permissions.sh

# Avec sudo (recommandé)
sudo bash fix-permissions.sh

# Spécifier un chemin
bash fix-permissions.sh /chemin/vers/projet
```

---

## 📁 STRUCTURE DES DOSSIERS

```
projet/
├── storage/
│   ├── app/
│   │   ├── public/              # Fichiers accessibles publiquement
│   │   │   ├── stores/          # Boutiques
│   │   │   │   ├── logos/       # Logos des boutiques
│   │   │   │   └── banners/     # Bannières
│   │   │   ├── products/        # Images produits
│   │   │   └── profiles/        # Photos de profil
│   │   └── private/             # Fichiers privés
│   ├── framework/               # Cache Laravel
│   │   ├── sessions/            # Sessions
│   │   ├── views/               # Vues compilées
│   │   └── cache/               # Cache
│   └── logs/                    # Logs
├── bootstrap/
│   └── cache/                   # Cache de démarrage
└── public/
    └── storage -> ../storage/app/public  # Lien symbolique
```

---

## 🔐 PERMISSIONS RECOMMANDÉES

### Dossiers
```bash
storage/                  → 775
storage/framework/        → 775
storage/logs/             → 775
storage/app/public/       → 775
bootstrap/cache/          → 775
```

### Propriétaire
```bash
# Ubuntu/Debian
www-data:www-data

# CentOS/RHEL
apache:apache  # ou nginx:nginx

# cPanel/Hébergement partagé
votreuser:votreuser
```

---

## 🌐 CONFIGURATION SERVEUR

### Apache

**.htaccess** (déjà inclus dans `public/.htaccess`)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

**Activer mod_rewrite** :
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

### Nginx

**Configuration recommandée** :
```nginx
server {
    listen 80;
    server_name kazaria-ci.com www.kazaria-ci.com;
    root /home/kazaria_dev/web/kazaria-ci.com/public_html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 📊 ENVIRONNEMENTS

### Local (Développement)
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
```

### Staging (Test)
```env
APP_ENV=staging
APP_DEBUG=true
APP_URL=https://staging.kazaria-ci.com
```

### Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kazaria-ci.com
```

---

## 🔍 DIAGNOSTIC

### Vérifier l'État du Système

```bash
# 1. Vérifier le storage
php artisan storage:check

# 2. Vérifier les permissions
php artisan permissions:fix

# 3. Vérifier les logs
tail -f storage/logs/laravel.log

# 4. Vérifier les logs serveur
# Apache
tail -f /var/log/apache2/error.log

# Nginx
tail -f /var/log/nginx/error.log
```

### Tests de Fonctionnement

```bash
# 1. Tester l'écriture storage
php -r "file_put_contents('storage/logs/test.txt', 'test');"

# 2. Tester les vues
php artisan view:clear

# 3. Tester le cache
php artisan cache:clear

# 4. Tester la base de données
php artisan migrate:status
```

---

## 🆘 DÉPANNAGE

### Problème : Erreur 500 après déploiement

**Solutions** :
1. Vérifier les logs : `tail -f storage/logs/laravel.log`
2. Vérifier les permissions : `php artisan permissions:fix`
3. Nettoyer le cache : `php artisan optimize:clear`
4. Vérifier le `.env`

---

### Problème : Images cassées

**Solutions** :
1. Vérifier le lien symbolique : `ls -la public/storage`
2. Recréer le lien : `php artisan storage:fix --force`
3. Vérifier les fichiers : `php artisan storage:check`
4. Vérifier les permissions : `ls -la storage/app/public`

---

### Problème : Page blanche

**Solutions** :
1. Activer le debug temporairement dans `.env` : `APP_DEBUG=true`
2. Vérifier les logs
3. Vérifier les permissions
4. Vider le cache : `php artisan optimize:clear`

---

### Problème : Erreurs de base de données

**Solutions** :
1. Vérifier la connexion dans `.env`
2. Tester la connexion : `php artisan migrate:status`
3. Vérifier que la base existe
4. Vérifier les credentials

---

## 📝 MAINTENANCE

### Quotidienne
```bash
# Nettoyer les vieux logs (optionnel)
find storage/logs -name "*.log" -mtime +30 -delete
```

### Hebdomadaire
```bash
# Optimiser la base de données
php artisan optimize

# Vérifier les permissions
php artisan permissions:fix

# Vérifier le storage
php artisan storage:check
```

### Mensuelle
```bash
# Sauvegarder la base de données
mysqldump -u user -p kazaria > backup_$(date +%Y%m%d).sql

# Sauvegarder les fichiers
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public
```

---

## 🔒 SÉCURITÉ

### Fichiers à Protéger
```bash
# .env ne doit JAMAIS être accessible publiquement
chmod 600 .env

# Vérifier que .env est dans .gitignore
grep "\.env" .gitignore
```

### Headers de Sécurité

Déjà configurés dans l'application via le middleware SEO :
- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`

---

## 📞 SUPPORT

### Commandes de Diagnostic

Si vous rencontrez un problème, exécutez et envoyez :

```bash
# Informations système
uname -a
php -v
composer --version

# État Laravel
php artisan --version
php artisan env

# Permissions
ls -la storage/
ls -la bootstrap/cache/

# Tests
php artisan storage:check
php artisan permissions:fix
```

---

## ✅ CHECKLIST POST-DÉPLOIEMENT

Après chaque déploiement, vérifier :

- [ ] Le site est accessible
- [ ] Pas d'erreur 500
- [ ] Les images s'affichent
- [ ] L'authentification fonctionne
- [ ] Les boutiques s'affichent
- [ ] L'upload d'images fonctionne
- [ ] Le panier fonctionne
- [ ] Les logs ne contiennent pas d'erreurs
- [ ] Les permissions sont correctes
- [ ] Le cache est à jour

```bash
# Script de vérification rapide
php artisan storage:check && \
php artisan permissions:fix && \
php artisan optimize && \
echo "✅ Tout est OK !"
```

---

## 🎯 RÉSUMÉ DES COMMANDES ESSENTIELLES

```bash
# Déploiement complet
composer install --no-dev --optimize-autoloader
php artisan migrate --force
bash fix-permissions.sh
php artisan storage:fix --force
php artisan optimize

# Correction d'urgence
php artisan permissions:fix
php artisan storage:fix --force
php artisan optimize:clear

# Diagnostic
php artisan storage:check
tail -f storage/logs/laravel.log

# Optimisation
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📖 RESSOURCES

- **Laravel Documentation** : https://laravel.com/docs
- **Serveur Requirements** : PHP 8.1+, MySQL 5.7+
- **Extensions PHP requises** : BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

---

*Guide créé pour KAZARIA - Marketplace en Côte d'Ivoire*
*Dernière mise à jour : Octobre 2024*
