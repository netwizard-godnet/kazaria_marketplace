#!/bin/bash

echo "🔧 Script de correction des erreurs iOS"
echo "=========================================="
echo ""

# Vérifier qu'on est dans le bon répertoire
if [ ! -d "ios" ]; then
    echo "❌ Erreur: Ce script doit être exécuté depuis le répertoire frontend/"
    echo "   Exemple: cd frontend && ./fix-ios-build.sh"
    exit 1
fi

echo "📦 Étape 1/5: Nettoyage Flutter..."
flutter clean

echo ""
echo "🗑️  Étape 2/5: Suppression des Pods et caches..."
cd ios
rm -rf Pods Podfile.lock
rm -rf ~/Library/Developer/Xcode/DerivedData/* 2>/dev/null || true

echo ""
echo "📥 Étape 3/5: Réinstallation des Pods..."
pod install --repo-update

if [ $? -ne 0 ]; then
    echo ""
    echo "❌ Erreur lors de l'installation des pods"
    echo "   Essayez manuellement: cd ios && pod install"
    exit 1
fi

echo ""
echo "📚 Étape 4/5: Réinstallation des dépendances Flutter..."
cd ..
flutter pub get

if [ $? -ne 0 ]; then
    echo ""
    echo "❌ Erreur lors de flutter pub get"
    exit 1
fi

echo ""
echo "✅ Étape 5/5: Nettoyage terminé!"
echo ""
echo "🚀 Vous pouvez maintenant lancer l'application:"
echo "   flutter run"
echo ""
echo "💡 Si l'erreur persiste, consultez IOS_CODESIGN_FIX.md pour plus d'options"
