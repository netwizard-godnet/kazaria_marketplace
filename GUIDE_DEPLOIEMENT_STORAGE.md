# 📦 Guide de Déploiement - Système de Stockage KAZARIA

## 🎯 Problème Résolu

Lorsque le site est déployé sur un serveur de production, les images de boutiques (logos, bannières, produits) ne s'affichent plus car :
- Le lien symbolique `public/storage` n'existe pas ou est cassé
- Les dossiers de stockage ne sont pas créés
- Les permissions ne sont pas correctement définies

## ✅ Solutions Implémentées

### 1. **Commandes Artisan Personnalisées**

#### `php artisan storage:check`
Diagnostique complet du système de stockage :
- Vérification du lien symbolique
- Contrôle des dossiers
- Analyse des permissions
- Liste des fichiers manquants

#### `php artisan storage:fix`
Réparation automatique du système :
- Nettoyage et recréation du lien symbolique
- Création des dossiers manquants
- Ajustement des permissions
- Configuration Git

### 2. **Scripts de Diagnostic**

- `check-storage.php` - Script PHP autonome pour vérifier le stockage
- `fix-storage.php` - Script PHP autonome pour réparer le stockage

---

## 🚀 Procédure de Déploiement

### **Sur Votre Serveur de Production**

#### **Étape 1 : Transférer les Fichiers**

```bash
# Transférer tous les fichiers du projet
# Assurez-vous d'inclure le dossier storage/app/public avec tous les fichiers
rsync -avz storage/app/public/ serveur:/chemin/vers/laravel/storage/app/public/
```

#### **Étape 2 : Installer les Dépendances**

```bash
cd /chemin/vers/laravel
composer install --no-dev --optimize-autoloader
```

#### **Étape 3 : Configuration .env**

```bash
# Copier et configurer le fichier .env
cp .env.example .env
nano .env

# Générer la clé d'application
php artisan key:generate
```

#### **Étape 4 : Configurer la Base de Données**

```bash
# Exécuter les migrations
php artisan migrate --force

# (Optionnel) Importer les données
php artisan db:seed
```

#### **Étape 5 : Réparer le Système de Stockage**

```bash
# Vérifier l'état du storage
php artisan storage:check

# Réparer automatiquement
php artisan storage:fix --force
```

#### **Étape 6 : Définir les Permissions** ⚠️ CRITIQUE

**Option A : Script Automatique** (RECOMMANDÉ)
```bash
# Utiliser le script de correction automatique
bash fix-permissions.sh

# OU avec sudo si nécessaire
sudo bash fix-permissions.sh
```

**Option B : Commande Artisan**
```bash
# Correction via Artisan
php artisan permissions:fix

# Puis ajuster le propriétaire
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**Option C : Manuellement**
```bash
# Permissions des dossiers
chmod -R 775 storage bootstrap/cache

# Propriétaire (adapter selon votre serveur)
sudo chown -R www-data:www-data storage bootstrap/cache

# Ou avec l'utilisateur de votre hébergement (cPanel)
sudo chown -R votreuser:votreuser storage bootstrap/cache
```

**⚠️ IMPORTANT** : Ajustez `www-data` selon votre serveur :
- Ubuntu/Debian : `www-data`
- CentOS/RHEL : `apache` ou `nginx`
- cPanel : Votre nom d'utilisateur

#### **Étape 7 : Optimisation Production**

```bash
# Cache des configurations
php artisan config:cache

# Cache des routes
php artisan route:cache

# Cache des vues
php artisan view:cache

# Optimiser l'autoloader
composer dump-autoload --optimize
```

---

## 🔧 Résolution des Problèmes

### **Problème 1 : Images ne s'affichent toujours pas**

```bash
# 1. Vérifier que le lien symbolique existe
ls -la public/storage

# 2. Si le lien est cassé, le supprimer et le recréer
rm public/storage
php artisan storage:link

# 3. Vérifier les permissions
ls -la storage/app/public
```

### **Problème 2 : Erreur de permissions**

```bash
# Sur Linux/Unix
find storage -type d -exec chmod 775 {} \;
find storage -type f -exec chmod 664 {} \;

# Rétablir le propriétaire
sudo chown -R www-data:www-data storage
```

### **Problème 3 : Dossiers manquants**

```bash
# Créer manuellement les dossiers
mkdir -p storage/app/public/stores/logos
mkdir -p storage/app/public/stores/banners
mkdir -p storage/app/public/products
mkdir -p storage/app/public/profiles

# Ou utiliser la commande automatique
php artisan storage:fix
```

### **Problème 4 : Fichiers images manquants**

```bash
# Vérifier les fichiers manquants
php artisan storage:check

# Restaurer depuis une sauvegarde
rsync -avz backup/storage/app/public/ storage/app/public/
```

---

## 📝 Checklist de Déploiement

### **Avant le Déploiement**

- [ ] Sauvegarder la base de données de production
- [ ] Sauvegarder tous les fichiers du dossier `storage/app/public`
- [ ] Vérifier que tous les fichiers sont dans le dépôt Git (sauf `storage/app/public/`)
- [ ] Tester le déploiement sur un environnement de staging

### **Pendant le Déploiement**

- [ ] Transférer les fichiers via FTP/SFTP/Git
- [ ] Transférer le dossier `storage/app/public` séparément
- [ ] Installer les dépendances Composer
- [ ] Configurer le fichier `.env`
- [ ] Exécuter les migrations
- [ ] Exécuter `php artisan storage:fix --force`
- [ ] Définir les permissions correctes
- [ ] Mettre en cache les configurations

### **Après le Déploiement**

- [ ] Tester l'affichage des images de boutiques
- [ ] Tester l'upload de nouvelles images
- [ ] Vérifier les logs d'erreur
- [ ] Exécuter `php artisan storage:check` pour confirmer

---

## 🔐 Configuration Serveur

### **Apache (.htaccess)**

Le fichier `public/.htaccess` est déjà configuré. Assurez-vous que `mod_rewrite` est activé :

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### **Nginx**

Configuration recommandée pour Nginx :

```nginx
server {
    listen 80;
    server_name votredomaine.com;
    root /chemin/vers/laravel/public;

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

### **Permissions Recommandées**

```bash
# Dossiers
chmod 755 /chemin/vers/laravel
chmod 775 storage
chmod 775 bootstrap/cache

# Fichiers
chmod 644 .env
chmod 644 composer.json
chmod 755 artisan
```

---

## 🛠️ Commandes Utiles

### **Diagnostic**

```bash
# Vérifier l'état du storage
php artisan storage:check

# Lister tous les fichiers storage
find storage/app/public -type f

# Vérifier les liens symboliques
ls -la public/
```

### **Maintenance**

```bash
# Nettoyer le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Recréer le cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimiser
php artisan optimize
```

### **Logs**

```bash
# Voir les logs Laravel
tail -f storage/logs/laravel.log

# Voir les logs serveur (Apache)
tail -f /var/log/apache2/error.log

# Voir les logs serveur (Nginx)
tail -f /var/log/nginx/error.log
```

---

## 📊 Structure des Dossiers de Stockage

```
storage/
├── app/
│   ├── public/                    # Fichiers accessibles publiquement
│   │   ├── stores/                # Dossier des boutiques
│   │   │   ├── logos/             # Logos des boutiques
│   │   │   │   └── store_logo_1_*.jpg
│   │   │   └── banners/           # Bannières des boutiques
│   │   │       └── store_banner_1_*.jpg
│   │   ├── products/              # Images des produits
│   │   │   └── product_*.jpg
│   │   ├── profiles/              # Photos de profil
│   │   │   └── profile_*.jpg
│   │   └── .gitignore             # Ignorer tous les fichiers uploadés
│   └── private/                   # Fichiers privés
├── framework/                     # Cache Laravel
├── logs/                          # Logs de l'application
└── .gitignore                     # Configuration Git

public/
└── storage -> ../storage/app/public  # Lien symbolique
```

---

## ⚠️ Important : .gitignore

Assurez-vous que votre `.gitignore` contient :

```gitignore
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
```

**MAIS NE PAS IGNORER** :
- `storage/app/.gitignore`
- `storage/app/public/.gitignore`
- `storage/framework/.gitignore`
- `storage/logs/.gitignore`

---

## 🎓 Bonnes Pratiques

### **1. Sauvegarde Automatique**

Configurez une sauvegarde automatique quotidienne :

```bash
#!/bin/bash
# backup-storage.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/kazaria"

# Sauvegarder la base de données
mysqldump -u user -p password kazaria > $BACKUP_DIR/db_$DATE.sql

# Sauvegarder les fichiers storage
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz storage/app/public

# Garder seulement les 7 derniers jours
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

### **2. Monitoring**

Surveillez l'espace disque utilisé :

```bash
# Voir la taille du dossier storage
du -sh storage/app/public/*

# Alerter si > 1GB
STORAGE_SIZE=$(du -s storage/app/public | cut -f1)
if [ $STORAGE_SIZE -gt 1048576 ]; then
    echo "ALERTE: Storage trop volumineux"
fi
```

### **3. Nettoyage Périodique**

```bash
# Supprimer les fichiers temporaires
php artisan cache:clear
php artisan view:clear

# Supprimer les fichiers de test
find storage/app/public -name "test_*" -delete
```

---

## 📞 Support

Si vous rencontrez des problèmes :

1. **Vérifier** : `php artisan storage:check`
2. **Réparer** : `php artisan storage:fix --force`
3. **Consulter** : `storage/logs/laravel.log`
4. **Documenter** : L'erreur exacte et les étapes de reproduction

---

## ✅ Résumé des Commandes Rapides

```bash
# Diagnostic complet
php artisan storage:check

# Réparation automatique
php artisan storage:fix --force

# Permissions (Linux/Unix)
chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage

# Cache production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Vérification finale
php artisan storage:check
```

---

*Document créé pour KAZARIA - Dernière mise à jour : {{ date('Y-m-d') }}*
