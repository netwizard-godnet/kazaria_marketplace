#!/bin/bash

echo "🔧 Script de correction des erreurs iOS (Resource Fork)"
echo "========================================================"
echo ""

# Vérifier qu'on est dans le bon répertoire
if [ ! -d "ios" ]; then
    echo "❌ Erreur: Ce script doit être exécuté depuis le répertoire frontend/"
    echo "   Exemple: cd frontend && ./fix-ios-resource-fork.sh"
    exit 1
fi

echo "🧹 Étape 1/6: Nettoyage des attributs étendus (resource forks)..."
# Nettoyer les attributs étendus du répertoire build
if [ -d "build" ]; then
    find build -type f -exec xattr -c {} \; 2>/dev/null || true
    echo "   ✅ Attributs étendus supprimés du répertoire build"
else
    echo "   ⚠️  Répertoire build non trouvé (normal si jamais compilé)"
fi

# Nettoyer les attributs étendus du répertoire ios
find ios -type f -exec xattr -c {} \; 2>/dev/null || true
echo "   ✅ Attributs étendus supprimés du répertoire ios"

# Nettoyer spécifiquement les frameworks Flutter
if [ -d "build/ios/Debug-iphonesimulator/Flutter.framework" ]; then
    xattr -cr build/ios/Debug-iphonesimulator/Flutter.framework 2>/dev/null || true
    echo "   ✅ Framework Flutter Debug nettoyé"
fi

if [ -d "build/ios/Release-iphonesimulator/Flutter.framework" ]; then
    xattr -cr build/ios/Release-iphonesimulator/Flutter.framework 2>/dev/null || true
    echo "   ✅ Framework Flutter Release nettoyé"
fi

echo ""
echo "🗑️  Étape 2/6: Nettoyage Flutter..."
flutter clean

echo ""
echo "📦 Étape 3/6: Suppression des Pods et caches..."
cd ios
rm -rf Pods Podfile.lock
rm -rf ~/Library/Developer/Xcode/DerivedData/* 2>/dev/null || true

echo ""
echo "📥 Étape 4/6: Réinstallation des Pods..."
pod install --repo-update

if [ $? -ne 0 ]; then
    echo ""
    echo "❌ Erreur lors de l'installation des pods"
    echo "   Essayez manuellement: cd ios && pod install"
    exit 1
fi

echo ""
echo "📚 Étape 5/6: Réinstallation des dépendances Flutter..."
cd ..
flutter pub get

if [ $? -ne 0 ]; then
    echo ""
    echo "❌ Erreur lors de flutter pub get"
    exit 1
fi

echo ""
echo "✅ Étape 6/6: Nettoyage terminé!"
echo ""
echo "🚀 Vous pouvez maintenant lancer l'application:"
echo "   flutter run"
echo ""
echo "💡 Si l'erreur persiste, consultez IOS_CODESIGN_RESOURCE_FORK_FIX.md"
