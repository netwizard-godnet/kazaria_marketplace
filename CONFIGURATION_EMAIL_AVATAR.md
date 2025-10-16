# 📧 Configuration Email - Avatar KAZARIA

## Problème Identifié

L'avatar dans les emails affiche une icône générique au lieu de la première lettre "K" de "Kazaria Marketplace".

## Solution

### 1. Configuration dans `.env`

Ajoutez ou modifiez ces lignes dans votre fichier `.env` :

```env
# Configuration de l'expéditeur des emails
MAIL_FROM_ADDRESS=kazaria2025@gmail.com
MAIL_FROM_NAME="Kazaria Marketplace"

# Configuration SMTP (exemple pour Gmail)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=kazaria2025@gmail.com
MAIL_PASSWORD=votre_mot_de_passe_application
MAIL_ENCRYPTION=tls
```

### 2. Configuration dans `config/mail.php`

Le fichier a été mis à jour avec les valeurs par défaut :

```php
'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'kazaria2025@gmail.com'),
    'name' => env('MAIL_FROM_NAME', 'Kazaria Marketplace'),
],
```

### 3. Pour Gmail - Mot de Passe d'Application

**Important** : Pour Gmail, vous devez utiliser un "mot de passe d'application" :

1. **Activez l'authentification à 2 facteurs** sur votre compte Google
2. **Générez un mot de passe d'application** :
   - Allez dans votre compte Google
   - Sécurité → Authentification à 2 facteurs
   - Mots de passe des applications
   - Générez un mot de passe pour "Mail"
3. **Utilisez ce mot de passe** dans `MAIL_PASSWORD`

### 4. Test de la Configuration

Pour tester que l'avatar fonctionne correctement :

```bash
# Envoyez un email de test
php artisan tinker
>>> Mail::raw('Test email', function($message) { $message->to('test@example.com')->subject('Test'); });
```

### 5. Vérification de l'Avatar

Après configuration, l'avatar devrait afficher :
- **Lettre "K"** dans un cercle coloré
- **Couleur** : Généralement bleu ou orange selon le client email
- **Nom** : "Kazaria Marketplace"
- **Email** : kazaria2025@gmail.com

## Pourquoi l'Avatar Change

L'avatar est généré automatiquement par le client email (Gmail, Outlook, etc.) en fonction de :
- **Nom de l'expéditeur** : "Kazaria Marketplace"
- **Adresse email** : kazaria2025@gmail.com
- **Première lettre** : "K" de "Kazaria"

## Dépannage

### Si l'avatar ne s'affiche toujours pas :

1. **Vérifiez la configuration** : Assurez-vous que `MAIL_FROM_NAME` est bien défini
2. **Cache du client email** : L'avatar peut prendre du temps à se mettre à jour
3. **Format du nom** : Utilisez des guillemets pour les noms avec espaces
4. **Test avec différents clients** : Gmail, Outlook, Apple Mail

### Configuration Alternative

Si vous voulez utiliser un autre nom :

```env
MAIL_FROM_NAME="KAZARIA"
# ou
MAIL_FROM_NAME="Kazaria"
# ou
MAIL_FROM_NAME="Kazaria E-commerce"
```

## Résultat Attendu

✅ **Avatar** : Lettre "K" dans un cercle coloré  
✅ **Nom** : "Kazaria Marketplace"  
✅ **Email** : kazaria2025@gmail.com  
✅ **Cohérence** : Même avatar sur tous les emails envoyés  

## Notes Importantes

- L'avatar est géré par le **client email du destinataire**
- Il peut prendre **quelques minutes** à se mettre à jour
- Chaque client email (Gmail, Outlook, etc.) peut afficher l'avatar différemment
- La **première lettre** du nom de l'expéditeur est utilisée pour l'avatar
