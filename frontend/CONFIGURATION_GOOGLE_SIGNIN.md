# Configuration Google Sign-In pour Android

## 🔴 Problème Actuel

L'erreur `ApiException: 10` (DEVELOPER_ERROR) indique que le SHA-1 n'est pas configuré dans Firebase Console.

## ✅ Solution : Configurer le SHA-1 dans Firebase

### Étape 1 : Obtenir le SHA-1

**Option A : Via Android Studio (Recommandé)**

1. Ouvrez Android Studio
2. Ouvrez votre projet Flutter : `frontend/`
3. Dans le terminal Android Studio, exécutez :
   ```bash
   cd android
   ./gradlew signingReport
   ```
4. Cherchez dans la sortie la section `Variant: debug` et copiez le **SHA1**

**Option B : Via ligne de commande**

```bash
cd frontend/android
./gradlew signingReport
```

**Option C : Via keytool directement**

```bash
keytool -list -v -keystore ~/.android/debug.keystore -alias androiddebugkey -storepass android -keypass android
```

Cherchez la ligne `SHA1:` et copiez la valeur (format: `XX:XX:XX:XX:...`)

### Étape 2 : Ajouter le SHA-1 dans Firebase Console

1. Allez sur [Firebase Console](https://console.firebase.google.com/)
2. Sélectionnez le projet **kazaria-marketplace**
3. Cliquez sur l'icône ⚙️ **Paramètres du projet** (en haut à gauche)
4. Allez dans l'onglet **Vos applications**
5. Trouvez votre application Android (`com.example.kazaria_app`)
6. Cliquez sur **Ajouter une empreinte digitale**
7. Collez le SHA-1 que vous avez copié
8. Cliquez sur **Enregistrer**

### Étape 3 : Télécharger le nouveau google-services.json

1. Toujours dans Firebase Console, dans la section de votre app Android
2. Cliquez sur **Télécharger google-services.json**
3. Remplacez le fichier `frontend/android/app/google-services.json` par le nouveau
4. **Important** : Le nouveau fichier doit contenir une section `oauth_client` non vide

### Étape 4 : Vérifier le google-services.json

Le fichier doit contenir quelque chose comme :

```json
{
  "client": [
    {
      "client_info": {
        "package_name": "com.example.kazaria_app"
      },
      "oauth_client": [
        {
          "client_id": "XXXXX.apps.googleusercontent.com",
          "client_type": 1,
          "android_info": {
            "package_name": "com.example.kazaria_app",
            "certificate_hash": "VOTRE_SHA1_ICI"
          }
        },
        {
          "client_id": "XXXXX.apps.googleusercontent.com",
          "client_type": 3
        }
      ]
    }
  ]
}
```

**⚠️ Si `oauth_client` est vide (`[]`), c'est que le SHA-1 n'a pas été ajouté correctement.**

### Étape 5 : Rebuild l'application

```bash
cd frontend
flutter clean
flutter pub get
flutter run
```

## 🔍 Vérification

Après avoir configuré le SHA-1 et téléchargé le nouveau `google-services.json`, testez à nouveau la connexion Google. L'erreur `ApiException: 10` devrait disparaître.

## 📝 Notes Importantes

- **Pour la production** : Vous devrez aussi ajouter le SHA-1 de votre clé de release
- **Package name** : Assurez-vous que le package name dans Firebase (`com.example.kazaria_app`) correspond à celui dans `build.gradle.kts`
- **Temps de propagation** : Les changements dans Firebase peuvent prendre quelques minutes à se propager

## 🆘 Si ça ne fonctionne toujours pas

1. Vérifiez que le package name est exactement `com.example.kazaria_app`
2. Vérifiez que le SHA-1 a bien été ajouté (pas seulement copié)
3. Vérifiez que le nouveau `google-services.json` a été téléchargé et remplacé
4. Faites un `flutter clean` et rebuild
5. Vérifiez les logs Flutter pour d'autres erreurs
