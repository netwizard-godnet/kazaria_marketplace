# 🎯 Système de Pop-ups Mobile - Documentation

## 📱 Vue d'ensemble

Le système de pop-ups mobile permet de créer et de gérer des pop-ups marketing directement depuis le dashboard admin, qui s'affichent automatiquement dans l'application mobile Flutter.

**Comment ça fonctionne:**
1. Admin crée/configure une pop-up dans le dashboard (`/admin/popups`)
2. App mobile fait une requête à l'API pour récupérer les pop-ups actives
3. PopupWidget affiche les pop-ups configurés avec les délais et fréquences configurés

---

## 🔧 Architecture Technique

### Backend (Laravel)
- **Model:** `app/Models/Popup.php` - Définit la structure des pop-ups
- **Controller:** `app/Http/Controllers/PopupController.php` - Endpoints API
- **Routes:** `routes/api.php` - Routes publiques `/api/popups/active`, `/api/popups/{id}/impression`, `/api/popups/{id}/click`

### Frontend (Flutter)
- **Service:** `lib/services/popup_service.dart` - API client pour récupérer les pop-ups
- **Widget:** `lib/widgets/popup_widget.dart` - Gère l'affichage et la logique des pop-ups
- **Integration:** `lib/screens/main_screen.dart` - PopupWidget intégré dans Stack

---

## 🎨 Étapes de Configuration

### 1. Accéder au Dashboard Admin
```
Accédez à: https://www.kazaria-ci.com/admin/popups
```

### 2. Créer une Nouvelle Pop-up
Cliquez sur **"Ajouter une popup"** et remplissez le formulaire:

#### **Contenu**
- **Titre** (optionnel) - Titre de la pop-up
- **Slug** (auto-généré) - Identifiant unique
- **Contenu** - Texte HTML ou texte simple
- **Texte du bouton** - Texte du CTA (appel à l'action)
- **URL du bouton** - Lien à ouvrir quand on clique le CTA

#### **Image**
- **Télécharger une image** - Image de la pop-up (jpg, png, webp)
- **URL d'image externe** - Ou utiliser une URL externe

#### **Apparence**
- **Largeur** (200-1200px) - Largeur de la pop-up
- **Hauteur** (200-1200px) - Hauteur de la pop-up
- **Layout** - Format d'affichage:
  - `stacked` - Image en haut, contenu en bas (par défaut)
  - `left-right` - Image à gauche, contenu à droite
  - `right-left` - Image à droite, contenu à gauche
  - `top-bottom` - Image en haut (grand), contenu en bas

#### **Fréquence d'affichage**
- `once_per_session` - Une fois par session (par défaut)
- `once_per_day` - Une fois par jour
- `once_per_week` - Une fois par semaine
- `every_visit` - À chaque visite
- `never` - Jamais

#### **Timing**
- **Délai (secondes)** - Attendre N secondes avant d'afficher la pop-up
- **Date de début** - Quand commencer à afficher (vide = immédiat)
- **Date de fin** - Quand arrêter d'afficher (vide = pas de limite)

#### **Appareil & Pages**
- **Appareils** - Sélectionner `mobile` ou `app` pour afficher sur l'app mobile
- **Pages** - Où afficher (optionnel - pour compatibilité web)

#### **Priorité**
- Plus la valeur est élevée, plus la pop-up s'affichera prioritairement

#### **Statut**
- Cocher **"Active"** pour activer la pop-up

### 3. Sauvegarder et Publier
Cliquez sur **"Sauvegarder"** pour publier la pop-up.

---

## 📊 Exemple de Configuration

### Pop-up Promotion Bienvenue
```
Titre: Bienvenue chez KAZARIA!
Contenu: Obtenez 15% de réduction sur votre première commande
Texte du CTA: Profiter de l'offre
URL du CTA: https://kazaria-ci.com/new-customer-15-percent
Image: image-promotion.jpg
Largeur: 400px
Hauteur: 500px
Layout: stacked
Fréquence: once_per_session (UNE SEULE FOIS par session)
Délai: 3 secondes (affiche après 3 secondes)
Date de début: Immédiat
Statut: Actif ✅
```

---

## 🔄 Flux de Données

```
┌─────────────────────────────────────────────────────────┐
│ Dashboard Admin (Admin crée une pop-up)                │
└──────────────────────┬──────────────────────────────────┘
                       │ Sauvegarde en BD
                       ▼
┌─────────────────────────────────────────────────────────┐
│ Base de Données (Popup Model)                           │
└──────────────────────┬──────────────────────────────────┘
                       │ Requête API: GET /api/popups/active
                       ▼
┌─────────────────────────────────────────────────────────┐
│ API Laravel (PopupController::getActivePopups)         │
│ - Filtre les pop-ups actives                            │
│ - Vérifie les dates (debut/fin)                         │
│ - Filtre par device (mobile/app)                        │
│ - Trie par priorité                                     │
└──────────────────────┬──────────────────────────────────┘
                       │ Retourne liste JSON
                       ▼
┌─────────────────────────────────────────────────────────┐
│ Flutter App (PopupService)                              │
│ - Récupère les pop-ups depuis l'API                     │
│ - Stocke localement                                     │
└──────────────────────┬──────────────────────────────────┘
                       │ PopupWidget affiche
                       ▼
┌─────────────────────────────────────────────────────────┐
│ PopupWidget                                             │
│ - Affiche le dialog avec la pop-up                      │
│ - Respecte le délai configuré                           │
│ - Gère la fréquence (once/day/week/etc)               │
│ - Affiche CTA avec lien                                 │
└────────────────────────────────────────────────────────┘
```

---

## 📱 Comportement de l'App Mobile

### Affichage
- **Au démarrage:** PopupWidget vérifie l'API pour les pop-ups actives
- **Délai:** Attend X secondes avant d'afficher (configurable)
- **Une par une:** Les pop-ups s'affichent séquentiellement
- **Fermeture:** L'utilisateur peut fermer avec le bouton X
- **CTA:** Clique ouvre le lien dans le navigateur

### Tracking
- **Impression:** Enregistrée quand la pop-up s'affiche
- **Click:** Enregistrée quand l'utilisateur clique le CTA

### LocalStorage
- Les pop-ups affichées sont sauvegardées localement pour respecter la fréquence
- À chaque session, les pop-ups s'affichent à nouveau selon leur fréquence

---

## 🚀 Cas d'usage

### 1. Promotion Saisonnière
```
Titre: Soldes d'été 🌞
Contenu: Jusqu'à 50% de réduction
Fréquence: once_per_week
Date début: 2026-06-01
Date fin: 2026-08-31
```

### 2. Notification Nouvelle Collection
```
Titre: Nouvelle collection arrivée!
Contenu: Découvrez nos nouveaux produits tendance
Fréquence: once_per_day
Image: nouvelle-collection.jpg
```

### 3. Encourager les Avis
```
Titre: Partagez votre expérience
Contenu: Donnez votre avis et gagnez 100 points
Texte CTA: Écrire un avis
URL CTA: https://kazaria-ci.com/reviews
Fréquence: every_visit
```

### 4. Inscription Newsletter
```
Titre: Restez à jour
Contenu: Inscrivez-vous pour recevoir nos meilleures offres
Texte CTA: S'inscrire
URL CTA: https://kazaria-ci.com/newsletter
Priorité: 100
```

---

## ⚙️ Configuration Avancée

### Variables d'Environnement
- **API Base URL:** `http://192.168.1.70:8000/api` (local testing)
- **Image Base URL:** `http://192.168.1.70:8000` (local testing)

### Options JSON (Optionnel)
Dans le champ `Options`, vous pouvez ajouter:
```json
{
  "track_impressions": true,
  "track_clicks": true,
  "animation": "fade",
  "backdrop_blur": true
}
```

---

## 🔍 Débogage

### Vérifier l'API
```
curl -X GET "http://192.168.1.70:8000/api/popups/active"
```

### Logs Flutter
Cherchez les logs commençant par:
- `📱 [POPUP_SERVICE]` - Requêtes API
- `✅ [POPUP_SERVICE]` - Succès
- `❌ [POPUP_SERVICE]` - Erreurs

### Logs Laravel
```bash
tail -f storage/logs/laravel.log
```

---

## 📞 Support

- **Issues:** Consultez les logs de l'app et du serveur
- **Reset:** Désactiver et réactiver la pop-up pour forcer un rechargement
- **Test:** Créer une pop-up avec priorité élevée et fréquence `every_visit` pour tester

