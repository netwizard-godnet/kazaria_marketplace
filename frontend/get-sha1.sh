#!/bin/bash

echo "🔑 Récupération du SHA-1 pour Google Sign-In"
echo "============================================"
echo ""

# Trouver Java (priorité: JAVA_HOME, Android Studio, système)
find_java() {
    if [ -n "$JAVA_HOME" ] && [ -f "$JAVA_HOME/bin/keytool" ]; then
        echo "$JAVA_HOME/bin/keytool"
        return 0
    fi
    
    # Chercher dans Android Studio
    ANDROID_STUDIO_JDK="$HOME/Library/Android/sdk/jbr/Contents/Home/bin/keytool"
    if [ -f "$ANDROID_STUDIO_JDK" ]; then
        echo "$ANDROID_STUDIO_JDK"
        return 0
    fi
    
    # Chercher dans les emplacements courants d'Android Studio
    for jdk_path in \
        "$HOME/Library/Android/sdk/jbr/bin/keytool" \
        "/Applications/Android Studio.app/Contents/jbr/Contents/Home/bin/keytool" \
        "/Applications/Android Studio.app/Contents/jre/Contents/Home/bin/keytool" \
        "/usr/libexec/java_home" \
        "/Library/Java/JavaVirtualMachines"/*/Contents/Home/bin/keytool
    do
        if [ -f "$jdk_path" ]; then
            echo "$jdk_path"
            return 0
        fi
    done
    
    # Essayer avec java_home sur macOS
    if command -v /usr/libexec/java_home >/dev/null 2>&1; then
        JAVA_HOME_PATH=$(/usr/libexec/java_home 2>/dev/null)
        if [ -n "$JAVA_HOME_PATH" ] && [ -f "$JAVA_HOME_PATH/bin/keytool" ]; then
            echo "$JAVA_HOME_PATH/bin/keytool"
            return 0
        fi
    fi
    
    # Essayer keytool directement
    if command -v keytool >/dev/null 2>&1; then
        echo "keytool"
        return 0
    fi
    
    return 1
}

KEYTOOL=$(find_java)

if [ $? -ne 0 ] || [ -z "$KEYTOOL" ]; then
    echo "❌ Java/keytool non trouvé"
    echo ""
    echo "💡 Solutions :"
    echo ""
    echo "Option 1 : Installer Java JDK"
    echo "   brew install openjdk@17"
    echo "   ou téléchargez depuis https://www.oracle.com/java/technologies/downloads/"
    echo ""
    echo "Option 2 : Utiliser le JDK d'Android Studio"
    echo "   export JAVA_HOME=\"\$HOME/Library/Android/sdk/jbr/Contents/Home\""
    echo "   export PATH=\"\$JAVA_HOME/bin:\$PATH\""
    echo "   ./get-sha1.sh"
    echo ""
    echo "Option 3 : Utiliser Gradle (si Android Studio est installé)"
    echo "   cd android"
    echo "   ./gradlew signingReport"
    echo "   (Le SHA-1 sera affiché dans la sortie)"
    echo ""
    exit 1
fi

echo "✅ Java trouvé : $KEYTOOL"
echo ""

KEYSTORE_PATH="$HOME/.android/debug.keystore"

if [ ! -f "$KEYSTORE_PATH" ]; then
    echo "⚠️  Clé debug non trouvée à $KEYSTORE_PATH"
    echo "   La clé sera créée automatiquement lors du premier build Flutter"
    echo ""
    echo "💡 Pour créer la clé manuellement :"
    echo "   $KEYTOOL -genkey -v -keystore $KEYSTORE_PATH -alias androiddebugkey -storepass android -keypass android -keyalg RSA -keysize 2048 -validity 10000"
    echo ""
    exit 1
fi

echo "📱 SHA-1 pour la clé DEBUG (développement):"
SHA1=$("$KEYTOOL" -list -v -keystore "$KEYSTORE_PATH" -alias androiddebugkey -storepass android -keypass android 2>/dev/null | grep -A 5 "Certificate fingerprints" | grep SHA1 | sed 's/.*SHA1: //' | tr -d ' ')

if [ -z "$SHA1" ]; then
    echo "   ❌ Impossible de récupérer le SHA-1"
    echo ""
    echo "   Essayez la commande complète :"
    echo "   $KEYTOOL -list -v -keystore $KEYSTORE_PATH -alias androiddebugkey -storepass android -keypass android"
    echo ""
    echo "   Ou utilisez Gradle :"
    echo "   cd android && ./gradlew signingReport"
else
    echo "   ✅ $SHA1"
fi

echo ""
echo "📱 SHA-256 pour la clé DEBUG:"
SHA256=$("$KEYTOOL" -list -v -keystore "$KEYSTORE_PATH" -alias androiddebugkey -storepass android -keypass android 2>/dev/null | grep -A 5 "Certificate fingerprints" | grep SHA256 | sed 's/.*SHA256: //' | tr -d ' ')

if [ -z "$SHA256" ]; then
    echo "   ❌ Impossible de récupérer le SHA-256"
else
    echo "   ✅ $SHA256"
fi

echo ""
echo "============================================"
echo "📋 Instructions :"
echo "   1. Copiez le SHA-1 ci-dessus"
echo "   2. Allez sur Firebase Console : https://console.firebase.google.com/"
echo "   3. Sélectionnez le projet 'kazaria-marketplace'"
echo "   4. Paramètres du projet > Vos applications > Android"
echo "   5. Cliquez sur 'Ajouter une empreinte digitale'"
echo "   6. Collez le SHA-1 et enregistrez"
echo "   7. Téléchargez le nouveau google-services.json"
echo "   8. Remplacez frontend/android/app/google-services.json"
echo ""
