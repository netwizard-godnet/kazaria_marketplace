#!/bin/bash

echo "🔑 Récupération du SHA-1 via Gradle"
echo "============================================"
echo ""
echo "Cette méthode utilise Gradle pour obtenir le SHA-1"
echo ""

cd android 2>/dev/null || {
    echo "❌ Répertoire android non trouvé"
    echo "   Exécutez ce script depuis le répertoire frontend/"
    exit 1
}

if [ ! -f "gradlew" ]; then
    echo "❌ gradlew non trouvé"
    echo "   Assurez-vous d'être dans le bon répertoire"
    exit 1
fi

echo "🔄 Exécution de Gradle signingReport..."
echo ""

./gradlew signingReport 2>&1 | grep -A 10 "Variant: debug" | grep -E "(SHA1|SHA256)" | head -2

echo ""
echo "============================================"
echo "✅ Copiez le SHA1 ci-dessus et ajoutez-le dans Firebase Console"
echo ""
