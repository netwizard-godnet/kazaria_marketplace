#!/bin/bash

# Script de correction des permissions KAZARIA
# À exécuter sur le serveur de production

echo "🔧 Correction des permissions KAZARIA"
echo "======================================"
echo ""

# Couleurs pour le terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Détecter le chemin du projet
if [ -z "$1" ]; then
    PROJECT_PATH="$(pwd)"
    echo -e "${YELLOW}Aucun chemin spécifié, utilisation du répertoire courant${NC}"
else
    PROJECT_PATH="$1"
fi

echo "📂 Chemin du projet : $PROJECT_PATH"
echo ""

# Vérifier que nous sommes dans un projet Laravel
if [ ! -f "$PROJECT_PATH/artisan" ]; then
    echo -e "${RED}❌ Erreur : Ce n'est pas un projet Laravel !${NC}"
    echo "Usage: ./fix-permissions.sh [chemin_du_projet]"
    exit 1
fi

cd "$PROJECT_PATH" || exit 1

# Détecter l'utilisateur du serveur web
WEB_USER=$(ps aux | grep -E '(apache|httpd|nginx)' | grep -v root | head -1 | awk '{print $1}')
if [ -z "$WEB_USER" ]; then
    WEB_USER="www-data"
fi

echo "👤 Utilisateur web détecté : $WEB_USER"
echo ""

# 1. Créer les dossiers manquants
echo "1️⃣  Création des dossiers nécessaires..."
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p storage/app/public/stores/logos
mkdir -p storage/app/public/stores/banners
mkdir -p storage/app/public/products
mkdir -p storage/app/public/profiles
mkdir -p bootstrap/cache
echo -e "${GREEN}✅ Dossiers créés${NC}"
echo ""

# 2. Permissions des fichiers et dossiers
echo "2️⃣  Ajustement des permissions..."

# Permissions pour tous les fichiers (644)
echo "   📄 Fichiers -> 644"
find . -type f -exec chmod 644 {} \;

# Permissions pour tous les dossiers (755)
echo "   📁 Dossiers -> 755"
find . -type d -exec chmod 755 {} \;

# Permissions spéciales pour storage et cache (775)
echo "   🔒 Storage et cache -> 775"
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Rendre artisan exécutable
chmod +x artisan
echo -e "${GREEN}✅ Permissions ajustées${NC}"
echo ""

# 3. Propriétaire des fichiers
echo "3️⃣  Ajustement du propriétaire..."

CURRENT_USER=$(whoami)
echo "   👤 Utilisateur actuel : $CURRENT_USER"
echo "   🌐 Utilisateur web : $WEB_USER"

# Si on a les droits sudo
if [ "$EUID" -eq 0 ]; then
    # Nous sommes root, on peut tout changer
    echo "   🔓 Mode root - Changement complet"
    chown -R $WEB_USER:$WEB_USER .
    chown -R $WEB_USER:$WEB_USER storage
    chown -R $WEB_USER:$WEB_USER bootstrap/cache
    echo -e "${GREEN}✅ Propriétaire changé${NC}"
else
    # Nous ne sommes pas root, essayons avec sudo
    if command -v sudo &> /dev/null; then
        echo "   🔐 Utilisation de sudo..."
        
        # Demander confirmation
        read -p "   Voulez-vous utiliser sudo pour changer le propriétaire ? (o/N) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Oo]$ ]]; then
            sudo chown -R $WEB_USER:$WEB_USER storage
            sudo chown -R $WEB_USER:$WEB_USER bootstrap/cache
            
            # Garder l'utilisateur actuel propriétaire, mais ajouter le groupe web
            sudo chown -R $CURRENT_USER:$WEB_USER .
            echo -e "${GREEN}✅ Propriétaire ajusté avec sudo${NC}"
        else
            echo -e "${YELLOW}⚠️  Propriétaire non modifié (nécessite sudo)${NC}"
        fi
    else
        echo -e "${YELLOW}⚠️  Sudo non disponible - Propriétaire non modifié${NC}"
        echo "   Exécutez manuellement :"
        echo "   sudo chown -R $WEB_USER:$WEB_USER storage bootstrap/cache"
    fi
fi
echo ""

# 4. Vérification SELinux (si présent)
if command -v getenforce &> /dev/null; then
    if [ "$(getenforce)" != "Disabled" ]; then
        echo "4️⃣  Configuration SELinux détectée..."
        echo "   📋 Ajustement du contexte SELinux..."
        
        if [ "$EUID" -eq 0 ] || command -v sudo &> /dev/null; then
            if [ "$EUID" -eq 0 ]; then
                semanage fcontext -a -t httpd_sys_rw_content_t "storage(/.*)?"
                semanage fcontext -a -t httpd_sys_rw_content_t "bootstrap/cache(/.*)?"
                restorecon -Rv storage
                restorecon -Rv bootstrap/cache
            else
                sudo semanage fcontext -a -t httpd_sys_rw_content_t "storage(/.*)?"
                sudo semanage fcontext -a -t httpd_sys_rw_content_t "bootstrap/cache(/.*)?"
                sudo restorecon -Rv storage
                sudo restorecon -Rv bootstrap/cache
            fi
            echo -e "${GREEN}✅ SELinux configuré${NC}"
        else
            echo -e "${YELLOW}⚠️  Exécutez manuellement :${NC}"
            echo "   sudo semanage fcontext -a -t httpd_sys_rw_content_t 'storage(/.*)?'"
            echo "   sudo restorecon -Rv storage"
        fi
        echo ""
    fi
fi

# 5. Créer le lien symbolique storage
echo "5️⃣  Configuration du lien symbolique storage..."
if [ -L "public/storage" ]; then
    echo "   ℹ️  Lien symbolique existe déjà"
elif [ -d "public/storage" ]; then
    echo "   ⚠️  public/storage est un dossier, sauvegarde et suppression..."
    mv public/storage public/storage_backup_$(date +%s)
    php artisan storage:link
    echo -e "${GREEN}✅ Lien symbolique créé${NC}"
else
    php artisan storage:link
    echo -e "${GREEN}✅ Lien symbolique créé${NC}"
fi
echo ""

# 6. Nettoyer le cache
echo "6️⃣  Nettoyage du cache..."
php artisan cache:clear 2>/dev/null || echo "   Cache déjà vide"
php artisan config:clear 2>/dev/null || echo "   Config déjà vidée"
php artisan view:clear 2>/dev/null || echo "   Vues déjà vidées"
php artisan route:clear 2>/dev/null || echo "   Routes déjà vidées"
echo -e "${GREEN}✅ Cache nettoyé${NC}"
echo ""

# 7. Vérification finale
echo "7️⃣  Vérification finale..."
echo ""

# Vérifier les permissions critiques
CRITICAL_DIRS=(
    "storage/framework/sessions"
    "storage/framework/views"
    "storage/framework/cache"
    "storage/logs"
    "bootstrap/cache"
)

ALL_OK=true
for dir in "${CRITICAL_DIRS[@]}"; do
    if [ -w "$dir" ]; then
        echo -e "   ${GREEN}✅${NC} $dir - Inscriptible"
    else
        echo -e "   ${RED}❌${NC} $dir - Non inscriptible"
        ALL_OK=false
    fi
done

echo ""

# Test d'écriture
TEST_FILE="storage/logs/permission_test_$(date +%s).txt"
if echo "test" > "$TEST_FILE" 2>/dev/null; then
    echo -e "${GREEN}✅ Test d'écriture : SUCCÈS${NC}"
    rm "$TEST_FILE"
else
    echo -e "${RED}❌ Test d'écriture : ÉCHEC${NC}"
    ALL_OK=false
fi

echo ""
echo "======================================"

if [ "$ALL_OK" = true ]; then
    echo -e "${GREEN}🎉 Toutes les permissions sont correctes !${NC}"
    echo ""
    echo "✅ Vous pouvez maintenant :"
    echo "   - Accéder à votre site"
    echo "   - Uploader des images"
    echo "   - Voir les vues compilées"
else
    echo -e "${YELLOW}⚠️  Certaines permissions nécessitent encore attention${NC}"
    echo ""
    echo "📝 Actions recommandées :"
    echo "   1. Exécutez ce script avec sudo :"
    echo "      sudo ./fix-permissions.sh"
    echo ""
    echo "   2. Ou ajustez manuellement :"
    echo "      sudo chown -R $WEB_USER:$WEB_USER storage bootstrap/cache"
    echo "      sudo chmod -R 775 storage bootstrap/cache"
fi

echo ""
echo "📋 Commandes utiles :"
echo "   - Vérifier les permissions : ls -la storage"
echo "   - Vérifier les logs : tail -f storage/logs/laravel.log"
echo "   - Nettoyer le cache : php artisan optimize:clear"
echo ""
