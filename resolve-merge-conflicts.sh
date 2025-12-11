#!/bin/bash

echo "🔧 Résolution automatique des conflits de merge"
echo "================================================"
echo ""

# Vérifier qu'on est dans un état de merge
if ! git rev-parse --verify MERGE_HEAD >/dev/null 2>&1; then
    echo "❌ Erreur: Aucun merge en cours"
    echo "   Ce script doit être exécuté pendant un merge"
    exit 1
fi

echo "📋 Étape 1/4: Suppression des fichiers de cache Laravel..."
# Supprimer les fichiers de cache (déjà fait, mais on vérifie)
git rm bootstrap/cache/config.php 2>/dev/null || true
git rm bootstrap/cache/routes-v7.php 2>/dev/null || true
echo "   ✅ Fichiers de cache supprimés"

echo ""
echo "📋 Étape 2/4: Suppression des fichiers générés storage/framework/views..."
# Supprimer tous les fichiers dans storage/framework/views (fichiers générés)
for file in $(git ls-files -u | awk '{print $4}' | sort -u | grep "^storage/framework/views/"); do
    git rm "$file" 2>/dev/null || true
done
echo "   ✅ Fichiers storage/framework/views supprimés"

echo ""
echo "📋 Étape 3/4: Suppression des fichiers vendor Laravel..."
# Supprimer les fichiers dans frontend/vendor/laravel (fichiers vendor)
for file in $(git ls-files -u | awk '{print $4}' | sort -u | grep "^frontend/vendor/laravel/framework"); do
    git rm "$file" 2>/dev/null || true
done
echo "   ✅ Fichiers vendor Laravel supprimés"

echo ""
echo "📋 Étape 4/4: Conservation des fichiers frontend/resources/views..."
# Garder tous les fichiers dans frontend/resources/views/ (version HEAD)
for file in $(git ls-files -u | awk '{print $4}' | sort -u | grep "^frontend/resources/views/"); do
    if [[ -f "$file" ]]; then
        git add "$file" 2>/dev/null || true
    fi
done
echo "   ✅ Fichiers frontend/resources/views conservés"

echo ""
echo "✅ Résolution automatique terminée!"
echo ""
echo "📊 État actuel:"
git status --short | head -30

echo ""
echo "💡 Vérifiez les conflits restants avec: git status"
echo ""
echo "Si tous les conflits sont résolus, vous pouvez maintenant:"
echo "   git add ."
echo "   git commit -m 'Merge origin/main: résolution des conflits'"
