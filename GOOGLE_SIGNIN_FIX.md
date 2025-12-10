# Correction de l'erreur Google Sign-In (ApiException: 10)

## 🔴 Problème

L'erreur `ApiException: 10` (DEVELOPER_ERROR) se produit lors de la connexion Google. Cela signifie que la configuration OAuth n'est pas correcte.

Le fichier `google-services.json` actuel a un `oauth_client` vide `[]`, ce qui signifie qu'aucun client OAuth Android n'est configuré.

## ✅ Solution Rapide

### Étape 1 : Obtenir le SHA-1 de votre clé de signature

**Option A : Via Gradle** (recommandé si Java n'est pas installé)
```bash
cd frontend
./get-sha1-gradle.sh
```

**Option B : Utiliser le script fourni** (nécessite Java)
```bash
cd frontend
./get-sha1.sh
```

Si vous obtenez une erreur "Unable to locate a Java Runtime", configurez Java :
```bash
# Sur macOS avec Android Studio
export JAVA_HOME="$HOME/Library/Android/sdk/jbr/Contents/Home"
export PATH="$JAVA_HOME/bin:$PATH"
./get-sha1.sh
```

**Option C : Commande manuelle**

Si Java est installé et dans votre PATH :
```bash
keytool -list -v -keystore ~/.android/debug.keystore -alias androiddebugkey -storepass android -keypass android | grep SHA1
```

**Copiez le SHA-1** affiché (format : `XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX`)

### Étape 2 : Configurer dans Firebase Console

1. Allez sur [Firebase Console](https://console.firebase.google.com/)
2. Sélectionnez votre projet `kazaria-marketplace`
3. Allez dans **Paramètres du projet** (icône d'engrenage en haut à gauche)
4. Allez dans l'onglet **Vos applications**
5. Cliquez sur votre application Android (`com.example.kazaria_app`)
6. Cliquez sur **Ajouter une empreinte digitale** (ou le bouton avec l'icône "+")
7. Collez le **SHA-1** que vous avez copié
8. Cliquez sur **Enregistrer**
9. **IMPORTANT** : Attendez quelques secondes, puis **téléchargez à nouveau** le fichier `google-services.json`
10. Remplacez le fichier dans `frontend/android/app/google-services.json`

### Étape 3 : Vérifier le nouveau google-services.json

Après avoir téléchargé le nouveau fichier, vérifiez qu'il contient maintenant un `oauth_client` :

```json
{
  "oauth_client": [
    {
      "client_id": "XXXXX.apps.googleusercontent.com",
      "client_type": 1,
      "android_info": {
        "package_name": "com.example.kazaria_app",
        "certificate_hash": "VOTRE_SHA1"
      }
    }
  ]
}
```

Si le `oauth_client` est toujours vide, passez à l'Étape 4.

### Étape 4 : Créer manuellement un OAuth Client dans Google Cloud Console

Si Firebase n'a pas créé automatiquement le client OAuth :

1. Allez sur [Google Cloud Console](https://console.cloud.google.com/)
2. Sélectionnez le projet `kazaria-marketplace`
3. Allez dans **APIs & Services** > **Credentials**
4. Cliquez sur **+ CREATE CREDENTIALS** > **OAuth client ID**
5. Si c'est la première fois, configurez l'écran de consentement OAuth :
   - Type d'utilisateur : Externe
   - Nom de l'application : Kazaria Marketplace
   - Email de support : votre email
   - Cliquez sur **Enregistrer et continuer**
   - Ajoutez votre email comme testeur
   - Cliquez sur **Enregistrer et continuer**
6. Créez le client OAuth :
   - Type d'application : **Android**
   - Nom : `Kazaria Android App`
   - Package name : `com.example.kazaria_app`
   - SHA-1 : Collez votre SHA-1 (sans les deux-points)
   - Cliquez sur **Create**
7. **Copiez le Client ID** créé (format : `XXXXX.apps.googleusercontent.com`)
8. Retournez dans Firebase Console et téléchargez à nouveau `google-services.json`

### Étape 5 : Vérifier le package name

Assurez-vous que le package name dans votre app correspond :
- Package name dans `build.gradle.kts` : `com.example.kazaria_app`
- Package name dans `google-services.json` : `com.example.kazaria_app`
- Package name dans Google Cloud Console : `com.example.kazaria_app`

### Étape 6 : Nettoyer et reconstruire

```bash
cd frontend
flutter clean
flutter pub get
cd android
./gradlew clean
cd ..
flutter run
```

## 🔧 Alternative : Obtenir le SHA-1 sans Java installé

Si vous n'avez pas Java installé, utilisez Gradle directement :

```bash
cd frontend/android
./gradlew signingReport
```

Cherchez dans la sortie la ligne avec "SHA1:" sous "Variant: debug". C'est votre SHA-1 à copier.

## 🔍 Vérification

Après ces étapes, le fichier `google-services.json` devrait contenir un `oauth_client` avec votre client ID Android :

```json
{
  "oauth_client": [
    {
      "client_id": "VOTRE_CLIENT_ID.apps.googleusercontent.com",
      "client_type": 1,
      "android_info": {
        "package_name": "com.example.kazaria_app",
        "certificate_hash": "VOTRE_SHA1"
      }
    }
  ]
}
```

## ⚠️ Notes Importantes

1. **Pour le debug** : Utilisez le SHA-1 de la clé debug (`~/.android/debug.keystore`)
2. **Pour la release** : Vous devrez ajouter le SHA-1 de votre clé de release également
3. **Temps de propagation** : Les changements peuvent prendre quelques minutes à se propager
4. **Test sur appareil réel** : Google Sign-In fonctionne mieux sur un appareil réel que sur un émulateur

## 🐛 Si le problème persiste

1. Vérifiez que vous avez bien redémarré l'app après avoir remplacé `google-services.json`
2. Vérifiez les logs Android avec `adb logcat | grep -i google`
3. Assurez-vous que Google Play Services est installé sur l'appareil/émulateur
4. Vérifiez que le package name est exactement le même partout
