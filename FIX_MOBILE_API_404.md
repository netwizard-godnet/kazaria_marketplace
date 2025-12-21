# 🔧 Correction de l'erreur 404 sur les routes API mobiles

## Problème identifié

L'application Flutter reçoit des erreurs 404 lors de l'appel aux routes API :
- `api/mobile/home-data` → 404
- `api/mobile/banners` → 404

## Cause

Le serveur Laravel écoute uniquement sur `127.0.0.1:8000` (localhost), ce qui le rend inaccessible depuis l'émulateur Android qui utilise `10.0.2.2:8000` pour accéder à la machine hôte.

## Solution

### Option 1 : Redémarrer le serveur Laravel sur toutes les interfaces (Recommandé)

Arrêter le serveur actuel (Ctrl+C) et le redémarrer avec :

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Cela permettra au serveur d'écouter sur toutes les interfaces réseau, le rendant accessible depuis :
- `localhost:8000` (depuis la machine)
- `10.0.2.2:8000` (depuis l'émulateur Android)
- `VOTRE_IP_LOCALE:8000` (depuis un appareil physique)

### Option 2 : Utiliser l'adresse IP locale de votre machine

Si vous ne pouvez pas redémarrer le serveur, vous pouvez modifier l'URL de base dans Flutter :

1. Trouver votre adresse IP locale :
   ```bash
   # Sur macOS/Linux
   ifconfig | grep "inet " | grep -v 127.0.0.1
   
   # Sur Windows
   ipconfig
   ```

2. Mettre à jour `frontend/lib/config/api_config.dart` :
   ```dart
   static const String baseUrl = 'http://VOTRE_IP_LOCALE:8000/api';
   ```

## Vérification

Après avoir appliqué la solution, testez depuis l'émulateur :

```bash
curl http://10.0.2.2:8000/api/mobile/home-data
```

Vous devriez recevoir une réponse JSON au lieu d'une erreur 404.

## Routes API disponibles

Les routes suivantes sont maintenant correctement enregistrées :
- ✅ `GET /api/mobile/home-data`
- ✅ `GET /api/mobile/banners`
- ✅ `GET /api/mobile/categories`
- ✅ `GET /api/mobile/products`
- ✅ `GET /api/mobile/stores`
- ✅ Et toutes les autres routes mobiles
