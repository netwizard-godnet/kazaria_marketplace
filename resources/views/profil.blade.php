@extends('layouts.app')

@section('content')
    <style>
        /* Styles pour les boutons de navigation de la sidebar */
        .sidebar .nav-pills .nav-link {
            color: var(--main-color); /* Orange pour le texte */
            background-color: transparent;
            border: none;
            transition: all 0.3s ease;
        }

        .sidebar .nav-pills .nav-link:hover {
            color: var(--main-color);
            background-color: rgba(255, 140, 0, 0.1);
        }

        .sidebar .nav-pills .nav-link.active {
            color: #ffffff !important; /* Texte blanc pour le bouton actif */
            background-color: var(--main-color) !important; /* Fond orange pour le bouton actif */
            border-radius: 0.375rem;
        }

        .sidebar .nav-pills .nav-link i {
            color: inherit;
        }

        /* Styles pour les badges et cartes orange */
        .orange-bg {
            background-color: var(--main-color) !important;
        }

        .bg-kazaria {
            background-color: var(--main-color) !important;
        }

        /* Animation pour les stats */
        .stats-number {
            font-weight: bold;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive pour mobile */
        @media (max-width: 768px) {
            .sidebar .nav-pills {
                flex-direction: row;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .sidebar .nav-pills .nav-item {
                flex: 0 0 auto;
            }
        }
    </style>

    <main class="container-fluid">
        <!-- SECTION BREADCRUMB -->
         <!-- Profile Header -->
        <div class="container-fluid bg-light">
            <div class="container py-5">
                <div class="row g-2">
                    <div class="col-12 col-md-2">
                        <div class="position-relative">
                            @if($user->profile_pic_url)
                                <img src="{{ asset($user->profile_pic_url) }}" alt="Photo de profil" class="profile-avatar rounded-circle" id="profileAvatar" style="cursor: pointer; width: 120px; height: 120px; object-fit: cover;">
                            @else
                                <img src="https://via.placeholder.com/120x120/f04e26/ffffff?text={{ strtoupper(substr($user->prenoms, 0, 1) . substr($user->nom, 0, 1)) }}" alt="Photo de profil" class="profile-avatar rounded-circle" id="profileAvatar" style="cursor: pointer; width: 120px; height: 120px; object-fit: cover;">
                            @endif
                            <div class="position-absolute bottom-0 end-0">
                                <button class="btn btn-sm orange-bg text-white rounded-circle" data-bs-toggle="modal" data-bs-target="#changePhotoModal" style="width: 35px; height: 35px;">
                                    <i class="bi bi-camera"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <h3 class="mb-1">{{ $user->prenoms }} {{ $user->nom }}</h3>
                        <div class="d-flex gap-3">
                            <span class="badge orange-bg text-white"><i class="bi bi-star-fill me-1"></i>4.8/5</span>
                            @if($user->is_verified)
                            <span class="badge orange-bg text-white"><i class="bi bi-shield-check me-1"></i>Vérifié</span>
                            @else
                            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Non vérifié</span>
                            @endif
                            <span class="badge orange-bg text-white"><i class="bi bi-geo-alt me-1"></i>Abidjan, Côte d'Ivoire</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-md-end">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="card text-center orange-bg text-white">
                                    <div class="fs-2 stats-number" id="totalOrders">{{ $stats['total_orders'] }}</div>
                                    <span class="fs-8">Commandes</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card text-center orange-bg text-white">
                                    <div class="fs-2 stats-number" id="totalFavorites">{{ $stats['total_favorites'] }}</div>
                                    <span class="fs-8">Favoris</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card text-center orange-bg text-white">
                                    <div class="fs-2 stats-number" id="totalReviews">{{ $stats['total_reviews'] }}</div>
                                    <span class="fs-8">Avis donnés</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container py-5">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3">
                    <div class="sidebar">
                        <ul class="nav nav-pills flex-column" id="profile-tabs">
                            <li class="nav-item">
                                <a class="nav-link active px-1 py-2" href="#profile" data-bs-toggle="pill">
                                    <i class="bi bi-person me-2"></i>Informations personnelles
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-1 py-2" href="#security" data-bs-toggle="pill">
                                    <i class="bi bi-shield-lock me-2"></i>Sécurité
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-1 py-2" href="#preferences" data-bs-toggle="pill">
                                    <i class="bi bi-gear me-2"></i>Préférences
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-1 py-2" href="#orders" data-bs-toggle="pill">
                                    <i class="bi bi-bag me-2"></i>Mes commandes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-1 py-2" href="#favorites" data-bs-toggle="pill">
                                    <i class="bi bi-heart me-2"></i>Mes favoris
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-1 py-2" href="#activity" data-bs-toggle="pill">
                                    <i class="bi bi-clock-history me-2"></i>Activité récente
                                </a>
                            </li>
                            <li class="nav-item" id="myStoreLink" style="display: none;">
                                <a class="nav-link px-1 py-2" href="#" onclick="goToMyStore(event)">
                                    <i class="bi bi-shop me-2"></i>Ma boutique
                                    <i class="bi bi-box-arrow-up-right ms-2 small"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <div class="tab-content" id="profile-tabsContent">
                        <!-- Profile Information -->
                        <div class="tab-pane fade show active" id="profile">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="bi bi-person me-2"></i>Informations personnelles</h5>
                                </div>
                                <div class="card-body">
                                    <form>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="firstName" class="form-label">Prénom</label>
                                                <input type="text" class="form-control" id="firstName" value="{{ $user->prenoms }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="lastName" class="form-label">Nom</label>
                                                <input type="text" class="form-control" id="lastName" value="{{ $user->nom }}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" value="{{ $user->email }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="phone" class="form-label">Téléphone</label>
                                                <input type="tel" class="form-control" id="phone" value="{{ $user->telephone }}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label for="address" class="form-label">Adresse</label>
                                                <input type="text" class="form-control" id="address" value="{{ $user->adresse ?? '' }}">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="postalCode" class="form-label">Code postal</label>
                                                <input type="text" class="form-control" id="postalCode" value="{{ $user->code_postal ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="city" class="form-label">Ville</label>
                                                <input type="text" class="form-control" id="city" value="{{ $user->ville ?? '' }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="country" class="form-label">Pays</label>
                                                <select class="form-control" id="country">
                                                    <option value="CI" {{ ($user->pays ?? 'CI') == 'CI' ? 'selected' : '' }}>Côte d'Ivoire</option>
                                                    <option value="SN" {{ ($user->pays ?? '') == 'SN' ? 'selected' : '' }}>Sénégal</option>
                                                    <option value="ML" {{ ($user->pays ?? '') == 'ML' ? 'selected' : '' }}>Mali</option>
                                                    <option value="BF" {{ ($user->pays ?? '') == 'BF' ? 'selected' : '' }}>Burkina Faso</option>
                                                    <option value="GH" {{ ($user->pays ?? '') == 'GH' ? 'selected' : '' }}>Ghana</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="bio" class="form-label">Biographie</label>
                                            <textarea class="form-control" id="bio" rows="3" placeholder="Parlez-nous de vous...">{{ $user->bio ?? '' }}</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-sm orange-bg text-white">
                                            <i class="bi bi-save me-2"></i>Enregistrer les modifications
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Security -->
                        <div class="tab-pane fade" id="security">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="bi bi-shield-lock me-2"></i>Paramètres de sécurité</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="mb-3">Changer le mot de passe</h6>
                                            <form id="passwordChangeForm">
                                                <div class="mb-3">
                                                    <label for="currentPassword" class="form-label">Mot de passe actuel</label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" id="currentPassword" required>
                                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('currentPassword')">
                                                            <i class="bi bi-eye" id="currentPassword-icon"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="newPassword" class="form-label">Nouveau mot de passe</label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" id="newPassword" required minlength="8">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newPassword')">
                                                            <i class="bi bi-eye" id="newPassword-icon"></i>
                                                        </button>
                                                    </div>
                                                    <div class="form-text">Minimum 8 caractères</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" id="confirmPassword" required>
                                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmPassword')">
                                                            <i class="bi bi-eye" id="confirmPassword-icon"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-sm orange-bg text-white">
                                                    <i class="bi bi-key me-2"></i>Changer le mot de passe
                                                </button>
                                            </form>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="mb-3">Authentification à deux facteurs</h6>
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle me-2"></i>
                                                Activez l'authentification à deux facteurs pour renforcer la sécurité de votre compte.
                                            </div>
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="twoFactorEnabled">
                                                <label class="form-check-label" for="twoFactorEnabled">
                                                    Activer l'authentification à deux facteurs
                                                </label>
                                            </div>
                                            <button type="button" class="btn btn-outline-secondary" disabled>
                                                <i class="bi bi-qr-code me-2"></i>Configurer avec une application
                                            </button>
                                            <p class="text-muted small mt-2">
                                                <i class="bi bi-clock me-1"></i>Fonctionnalité à venir
                                            </p>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Sessions actives -->
                                    <div class="row">
                                        <div class="col-12">
                                            <h6 class="mb-3">Sessions actives</h6>
                                            <div class="alert alert-light border">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <i class="bi bi-laptop text-primary me-2"></i>
                                                        <strong>Session actuelle</strong>
                                                        <div class="small text-muted mt-1">
                                                            <i class="bi bi-geo-alt me-1"></i>Abidjan, Côte d'Ivoire
                                                            <br>
                                                            <i class="bi bi-clock me-1"></i>Dernière activité: maintenant
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-success">Actif</span>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="logoutAllDevices()">
                                                <i class="bi bi-power me-2"></i>Déconnecter tous les appareils
                                            </button>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Historique de sécurité -->
                                    <div class="row">
                                        <div class="col-12">
                                            <h6 class="mb-3">Historique récent</h6>
                                            <div class="list-group">
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="bi bi-shield-check text-success me-2"></i>
                                                            <strong>Connexion réussie</strong>
                                                            <div class="small text-muted">
                                                                Aujourd'hui à {{ date('H:i') }}
                                                            </div>
                                                        </div>
                                                        <span class="badge bg-success">Succès</span>
                                                    </div>
                                                </div>
                                                @if($user->email_verified_at)
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="bi bi-envelope-check text-info me-2"></i>
                                                            <strong>Email vérifié</strong>
                                                            <div class="small text-muted">
                                                                {{ \Carbon\Carbon::parse($user->email_verified_at)->format('d/m/Y à H:i') }}
                                                            </div>
                                                        </div>
                                                        <span class="badge bg-info">Vérifié</span>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preferences -->
                        <div class="tab-pane fade" id="preferences">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="bi bi-gear me-2"></i>Préférences</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="mb-3">Notifications</h6>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                                <label class="form-check-label" for="emailNotifications">
                                                    Notifications par email
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="smsNotifications">
                                                <label class="form-check-label" for="smsNotifications">
                                                    Notifications SMS
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="pushNotifications" checked>
                                                <label class="form-check-label" for="pushNotifications">
                                                    Notifications push
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="mb-3">Langue et région</h6>
                                            <div class="mb-3">
                                                <label for="language" class="form-label">Langue</label>
                                                <select class="form-control" id="language">
                                                    <option value="fr" selected>Français</option>
                                                    <option value="en">English</option>
                                                    <option value="es">Español</option>
                                                    <option value="de">Deutsch</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="currency" class="form-label">Devise</label>
                                                <select class="form-control" id="currency">
                                                    <option value="XOF" selected>Franc CFA (FCFA)</option>
                                                    <option value="EUR">Euro (€)</option>
                                                    <option value="USD">Dollar US ($)</option>
                                                    <option value="GBP">Livre Sterling (£)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="mb-3">Préférences d'affichage</h6>
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="darkMode">
                                                <label class="form-check-label" for="darkMode">
                                                    Mode sombre
                                                </label>
                                            </div>
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="compactView">
                                                <label class="form-check-label" for="compactView">
                                                    Vue compacte
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn">
                                            <i class="bi bi-check-lg me-2"></i>Enregistrer les préférences
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="logout()">
                                            <i class="bi bi-box-arrow-right me-2"></i>Se déconnecter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Orders -->
                        <div class="tab-pane fade" id="orders">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="bi bi-bag me-2"></i>Mes commandes</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Commande</th>
                                                    <th>Date</th>
                                                    <th>Statut</th>
                                                    <th>Total</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ordersTableBody">
                                                <tr>
                                                    <td colspan="5" class="text-center">
                                                        <div class="spinner-border text-primary" role="status">
                                                            <span class="visually-hidden">Chargement...</span>
                                                        </div>
                                                        <p class="mt-2">Chargement de vos commandes...</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Favorites -->
                        <div class="tab-pane fade" id="favorites">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="bi bi-heart me-2"></i>Mes produits favoris</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row" id="favoritesContainer">
                                        <!-- Les favoris seront chargés dynamiquement -->
                                    </div>
                                    <div id="noFavoritesMessage" class="text-center py-5" style="display: none;">
                                        <i class="bi bi-heart text-muted" style="font-size: 3rem;"></i>
                                        <h5 class="mt-3 text-muted">Aucun produit favori</h5>
                                        <p class="text-muted">Découvrez nos produits et ajoutez-les à vos favoris !</p>
                                        <a href="{{ route('boutique_officielle') }}" class="btn orange-bg text-white">
                                            <i class="bi bi-shop me-2"></i>Découvrir la boutique
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activity -->
                        <div class="tab-pane fade" id="activity">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0"><i class="bi bi-clock-history me-2"></i>Activité récente</h5>
                                    <button class="btn btn-sm orange-bg text-white" onclick="loadActivity()">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
                                    </button>
                                </div>
                                <div class="card-body" id="activityContainer">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Chargement...</span>
                                        </div>
                                        <p class="mt-2">Chargement de vos activités...</p>
                                    </div>
                                </div>
                                <div id="noActivityMessage" class="card-body text-center py-5" style="display: none;">
                                    <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3 text-muted">Aucune activité récente</h5>
                                    <p class="text-muted">Commencez à explorer nos produits !</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Photo Modal -->
        <div class="modal fade z-index-9x" id="changePhotoModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-camera me-2"></i>Changer ma photo de profil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                @if($user->profile_pic_url)
                                    <img id="photoPreview" src="{{ asset($user->profile_pic_url) }}" alt="Aperçu" class="rounded-circle" width="150" height="150" style="object-fit: cover;">
                                @else
                                    <img id="photoPreview" src="https://via.placeholder.com/150x150/f04e27/ffffff?text={{ strtoupper(substr($user->prenoms, 0, 1) . substr($user->nom, 0, 1)) }}" alt="Aperçu" class="rounded-circle" width="150" height="150" style="object-fit: cover;">
                                @endif
                                <div class="position-absolute bottom-0 end-0">
                                    <label for="photoInput" class="btn btn-sm orange-bg text-white rounded-circle" style="width: 40px; height: 40px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-camera"></i>
                                    </label>
                                    <input type="file" id="photoInput" accept="image/*" style="display: none;">
                                </div>
                            </div>
                            <p class="text-muted mt-2">Cliquez sur l'icône caméra pour sélectionner une nouvelle photo</p>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Recommandations :</strong>
                            <ul class="mb-0 mt-2">
                                <li>Format accepté : JPG, PNG, GIF</li>
                                <li>Taille maximale : 5 MB</li>
                                <li>Résolution recommandée : 300x300 px minimum</li>
                                <li>Photo carrée pour un meilleur rendu</li>
                            </ul>
                        </div>
                        
                        <div class="mb-3">
                            <label for="photoDescription" class="form-label">Description (optionnelle)</label>
                            <textarea class="form-control" id="photoDescription" rows="2" placeholder="Ajoutez une description à votre photo de profil..."></textarea>
                        </div>
                        
                        <div id="uploadProgress" class="progress mb-3" style="display: none;">
                            <div class="progress-bar bg-kazaria" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn" id="savePhotoBtn" disabled>
                            <i class="bi bi-check-lg me-2"></i>Enregistrer la photo
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- SECTION BREADCRUMB END -->
    </main>

    <script>
        // Fonction pour afficher les alertes Bootstrap
        function showToast(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            
            // Créer l'alerte
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            alertDiv.setAttribute('role', 'alert');
            
            // Icône selon le type
            const icon = type === 'success' 
                ? '<i class="bi bi-check-circle-fill me-2"></i>' 
                : '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
            
            alertDiv.innerHTML = `
                ${icon}
                <strong>${type === 'success' ? 'Succès!' : 'Erreur!'}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Ajouter l'alerte au container
            alertContainer.appendChild(alertDiv);
            
            // Supprimer automatiquement après 5 secondes
            setTimeout(() => {
                alertDiv.classList.remove('show');
                setTimeout(() => alertDiv.remove(), 150);
            }, 5000);
        }

        // Vérifier l'authentification au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('auth_token');
            
            if (!token) {
                alert('Vous devez être connecté pour accéder à votre profil.');
                window.location.href = '/authentification';
                return;
            }
            
            // Vérifier la validité du token avec le serveur
            fetch('/api/me', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Token invalide');
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error('Authentification échouée');
                }
                console.log('Utilisateur authentifié:', data.user);
            })
            .catch(error => {
                console.error('Erreur d\'authentification:', error);
                alert('Session expirée. Veuillez vous reconnecter.');
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_data');
                window.location.href = '/authentification';
            });
        });

        // Fonction de déconnexion
        function logout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                if (typeof authManager !== 'undefined') {
                    authManager.logout();
                } else {
                    // Fallback si authManager n'est pas disponible
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user_data');
                    window.location.href = '/';
                }
            }
        }

        // Gestion du formulaire de profil
        document.addEventListener('DOMContentLoaded', function() {
            const profileForm = document.querySelector('#profile form');
            if (profileForm) {
                profileForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = {
                        prenoms: document.getElementById('firstName').value,
                        nom: document.getElementById('lastName').value,
                        email: document.getElementById('email').value,
                        telephone: document.getElementById('phone').value,
                        adresse: document.getElementById('address').value,
                        code_postal: document.getElementById('postalCode').value,
                        ville: document.getElementById('city').value,
                        pays: document.getElementById('country').value,
                        bio: document.getElementById('bio').value
                    };

                    const token = localStorage.getItem('auth_token');
                    
                    try {
                        const response = await fetch('/api/profile/update', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${token}`,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(formData)
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            showToast('success', 'Profil mis à jour avec succès !');
                            // Mettre à jour les données locales
                            if (typeof authManager !== 'undefined') {
                                authManager.user = data.user;
                                localStorage.setItem('user_data', JSON.stringify(data.user));
                            }
                        } else {
                            showToast('error', 'Erreur: ' + data.message);
                        }
                    } catch (error) {
                        showToast('error', 'Erreur de connexion. Veuillez réessayer.');
                    }
                });
            }

            // Gestion du formulaire de changement de mot de passe
            const passwordForm = document.getElementById('passwordChangeForm');
            
            if (passwordForm) {
                passwordForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    // Récupérer les valeurs
                    const currentPassword = document.getElementById('currentPassword').value;
                    const newPassword = document.getElementById('newPassword').value;
                    const confirmPassword = document.getElementById('confirmPassword').value;
                    
                    // Validation simple
                    if (!currentPassword || !newPassword || !confirmPassword) {
                        showToast('error', 'Veuillez remplir tous les champs.');
                        return;
                    }
                    
                    if (newPassword.length < 8) {
                        showToast('error', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
                        return;
                    }
                    
                    if (newPassword !== confirmPassword) {
                        showToast('error', 'Les mots de passe ne correspondent pas.');
                        return;
                    }
                    
                    const token = localStorage.getItem('auth_token');
                    if (!token) {
                        showToast('error', 'Session expirée. Veuillez vous reconnecter.');
                        setTimeout(() => window.location.href = '/authentification', 2000);
                        return;
                    }
                    
                    // Désactiver le bouton
                    const submitBtn = passwordForm.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Changement...';
                    
                    try {
                        const response = await fetch('/api/profile/change-password', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${token}`,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                current_password: currentPassword,
                                new_password: newPassword,
                                new_password_confirmation: confirmPassword
                            })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            showToast('success', 'Mot de passe mis à jour avec succès !');
                            passwordForm.reset();
                        } else {
                            if (data.errors) {
                                const firstError = Object.values(data.errors)[0];
                                showToast('error', Array.isArray(firstError) ? firstError[0] : firstError);
                            } else {
                                showToast('error', data.message || 'Erreur lors du changement de mot de passe.');
                            }
                        }
                    } catch (error) {
                        showToast('error', 'Erreur de connexion. Veuillez réessayer.');
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            }

            // Gestion du modal de changement de photo
            const photoInput = document.getElementById('photoInput');
            const photoPreview = document.getElementById('photoPreview');
            const savePhotoBtn = document.getElementById('savePhotoBtn');
            const uploadProgress = document.getElementById('uploadProgress');
            let selectedFile = null;

            // Prévisualiser la photo sélectionnée
            if (photoInput) {
                photoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    
                    if (file) {
                        // Vérifier la taille du fichier (5 MB max)
                        if (file.size > 5 * 1024 * 1024) {
                            showToast('error', 'La taille du fichier ne doit pas dépasser 5 MB.');
                            photoInput.value = '';
                            return;
                        }

                        // Vérifier le type de fichier
                        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                        if (!validTypes.includes(file.type)) {
                            showToast('error', 'Format de fichier non valide. Veuillez sélectionner une image JPG, PNG ou GIF.');
                            photoInput.value = '';
                            return;
                        }

                        // Prévisualiser l'image
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            photoPreview.src = e.target.result;
                            selectedFile = file;
                            savePhotoBtn.disabled = false;
                            savePhotoBtn.classList.add('orange-bg', 'text-white');
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Gérer la soumission du formulaire de photo
            if (savePhotoBtn) {
                savePhotoBtn.addEventListener('click', async function() {
                    if (!selectedFile) {
                        showToast('error', 'Veuillez sélectionner une photo.');
                        return;
                    }

                    const token = localStorage.getItem('auth_token');
                    if (!token) {
                        showToast('error', 'Session expirée. Veuillez vous reconnecter.');
                        setTimeout(() => {
                            window.location.href = '/authentification';
                        }, 2000);
                        return;
                    }

                    // Créer le FormData
                    const formData = new FormData();
                    formData.append('photo', selectedFile);
                    
                    const description = document.getElementById('photoDescription').value;
                    if (description) {
                        formData.append('description', description);
                    }

                    // Désactiver le bouton pendant l'upload
                    savePhotoBtn.disabled = true;
                    savePhotoBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Upload en cours...';
                    
                    // Afficher la barre de progression
                    uploadProgress.style.display = 'block';
                    const progressBar = uploadProgress.querySelector('.progress-bar');

                    try {
                        // Simuler la progression (à remplacer par un vrai upload avec XMLHttpRequest pour la progression réelle)
                        const xhr = new XMLHttpRequest();
                        
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percentComplete = (e.loaded / e.total) * 100;
                                progressBar.style.width = percentComplete + '%';
                                progressBar.textContent = Math.round(percentComplete) + '%';
                            }
                        });

                        xhr.addEventListener('load', function() {
                            if (xhr.status === 200) {
                                const data = JSON.parse(xhr.responseText);
                                
                                if (data.success) {
                                    showToast('success', 'Photo de profil mise à jour avec succès !');
                                    
                                    // Mettre à jour la photo de profil dans la page
                                    const profileAvatar = document.getElementById('profileAvatar');
                                    if (profileAvatar && data.photo_url) {
                                        profileAvatar.src = data.photo_url;
                                    }
                                    
                                    // Mettre à jour les données locales
                                    if (typeof authManager !== 'undefined' && data.user) {
                                        authManager.user = data.user;
                                        localStorage.setItem('user_data', JSON.stringify(data.user));
                                    }
                                    
                                    // Fermer le modal après un court délai
                                    setTimeout(() => {
                                        const modal = bootstrap.Modal.getInstance(document.getElementById('changePhotoModal'));
                                        if (modal) {
                                            modal.hide();
                                        }
                                        
                                        // Recharger la page pour afficher la nouvelle photo
                                        window.location.reload();
                                    }, 1500);
                                    
                                    // Réinitialiser le formulaire
                                    photoInput.value = '';
                                    document.getElementById('photoDescription').value = '';
                                    selectedFile = null;
                                    savePhotoBtn.disabled = true;
                                    savePhotoBtn.classList.remove('orange-bg', 'text-white');
                                    uploadProgress.style.display = 'none';
                                    progressBar.style.width = '0%';
                                } else {
                                    showToast('error', 'Erreur: ' + (data.message || 'Échec de l\'upload'));
                                }
                            } else {
                                showToast('error', 'Erreur lors de l\'upload. Code: ' + xhr.status);
                            }
                            
                            // Réactiver le bouton
                            savePhotoBtn.disabled = false;
                            savePhotoBtn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Enregistrer la photo';
                        });

                        xhr.addEventListener('error', function() {
                            showToast('error', 'Erreur de connexion lors de l\'upload.');
                            savePhotoBtn.disabled = false;
                            savePhotoBtn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Enregistrer la photo';
                        });

                        xhr.open('POST', '/api/profile/update-photo', true);
                        xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                        xhr.setRequestHeader('Accept', 'application/json');
                        xhr.send(formData);

                    } catch (error) {
                        console.error('Erreur:', error);
                        showToast('error', 'Erreur de connexion. Veuillez réessayer.');
                        savePhotoBtn.disabled = false;
                        savePhotoBtn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Enregistrer la photo';
                        uploadProgress.style.display = 'none';
                    }
                });
            }

            // Réinitialiser le modal quand il est fermé
            const changePhotoModal = document.getElementById('changePhotoModal');
            if (changePhotoModal) {
                changePhotoModal.addEventListener('hidden.bs.modal', function() {
                    photoInput.value = '';
                    document.getElementById('photoDescription').value = '';
                    
                    // Restaurer la photo originale (depuis profileAvatar)
                    const profileAvatar = document.getElementById('profileAvatar');
                    if (profileAvatar && photoPreview) {
                        photoPreview.src = profileAvatar.src;
                    }
                    
                    selectedFile = null;
                    savePhotoBtn.disabled = true;
                    savePhotoBtn.classList.remove('orange-bg', 'text-white');
                    uploadProgress.style.display = 'none';
                    const progressBar = uploadProgress.querySelector('.progress-bar');
                    if (progressBar) {
                        progressBar.style.width = '0%';
                        progressBar.textContent = '';
                    }
                });
            }
        });

        // Fonction pour afficher/masquer le mot de passe
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Fonction pour déconnecter tous les appareils
        async function logoutAllDevices() {
            if (!confirm('Êtes-vous sûr de vouloir déconnecter tous les appareils ? Vous devrez vous reconnecter.')) {
                return;
            }

            const token = localStorage.getItem('auth_token');
            if (!token) {
                showToast('error', 'Session expirée. Veuillez vous reconnecter.');
                setTimeout(() => {
                    window.location.href = '/authentification';
                }, 2000);
                return;
            }

            try {
                const response = await fetch('/api/logout-all-devices', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    showToast('success', 'Tous les appareils ont été déconnectés.');
                    
                    // Déconnecter l'utilisateur actuel
                    setTimeout(() => {
                        if (typeof authManager !== 'undefined') {
                            authManager.logout();
                        } else {
                            localStorage.removeItem('auth_token');
                            localStorage.removeItem('user_data');
                            window.location.href = '/authentification';
                        }
                    }, 2000);
                } else {
                    showToast('error', 'Erreur: ' + data.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast('error', 'Erreur de connexion. Veuillez réessayer.');
            }
        }

        // Charger les commandes de l'utilisateur
        async function loadOrders() {
            const token = localStorage.getItem('auth_token');
            if (!token) return;

            try {
                const response = await fetch('/api/orders/my-orders', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                const tbody = document.getElementById('ordersTableBody');
                
                if (data.success && data.orders.length > 0) {
                    tbody.innerHTML = '';
                    
                    data.orders.forEach(order => {
                        const statusBadge = getStatusBadge(order.status);
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><strong>${order.order_number}</strong></td>
                            <td>${new Date(order.created_at).toLocaleDateString('fr-FR')}</td>
                            <td><span class="badge ${statusBadge.class}">${statusBadge.label}</span></td>
                            <td><strong>${new Intl.NumberFormat('fr-FR').format(order.total)} FCFA</strong></td>
                            <td>
                                <button class="btn btn-sm btn-outline-success me-1" onclick="trackOrder('${order.order_number}')" title="Suivre la commande">
                                    <i class="bi bi-truck"></i>
                                </button>
                                <a href="/order/invoice/${order.order_number}?token=${token}" class="btn btn-sm btn-outline-primary me-1" title="Voir la facture">
                                    <i class="bi bi-file-earmark-text"></i>
                                </a>
                                <a href="/order/download/${order.order_number}" class="btn btn-sm orange-bg text-white" title="Télécharger PDF">
                                    <i class="bi bi-download"></i>
                                </a>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    
                    // Mettre à jour le compteur de commandes
                    document.getElementById('totalOrders').textContent = data.orders.length;
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Vous n'avez pas encore de commande</p>
                                <a href="/" class="btn btn-sm orange-bg text-white">
                                    <i class="bi bi-shop me-1"></i>Commencer mes achats
                                </a>
                            </td>
                        </tr>
                    `;
                }
            } catch (error) {
                console.error('Erreur lors du chargement des commandes:', error);
            }
        }

        // Fonction pour suivre une commande
        async function trackOrder(orderNumber) {
            console.log('🚚 Suivi de la commande:', orderNumber);
            
            const token = localStorage.getItem('auth_token');
            if (!token) {
                showToast('error', 'Vous devez être connecté pour suivre vos commandes');
                return;
            }
            
            try {
                // Afficher un modal de chargement
                const modalHtml = `
                    <div class="modal fade z-index-9x
                    " id="trackOrderModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="bi bi-truck me-2"></i>Suivi de la commande ${orderNumber}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body" id="trackOrderContent">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Chargement...</span>
                                        </div>
                                        <p class="mt-2">Chargement des détails de la commande...</p>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Ajouter le modal au DOM
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                const modal = new bootstrap.Modal(document.getElementById('trackOrderModal'));
                modal.show();
                
                // Charger les détails de la commande
                const response = await fetch(`/api/orders/${orderNumber}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayOrderTracking(data.order, orderNumber);
                } else {
                    document.getElementById('trackOrderContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Erreur: ${data.message}
                        </div>
                    `;
                }
                
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('trackOrderContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Erreur lors du chargement des détails de la commande
                    </div>
                `;
            }
        }
        
        // Afficher les détails de suivi de commande
        function displayOrderTracking(order, orderNumber) {
            const progressPercentage = getProgressPercentage(order.status);
            const paymentMethodLabel = getPaymentMethodLabel(order.payment_method);
            
            document.getElementById('trackOrderContent').innerHTML = `
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Informations de commande</h6>
                        <table class="table table-sm">
                            <tr><td class="fw-bold">Numéro:</td><td>${order.order_number}</td></tr>
                            <tr><td class="fw-bold">Date:</td><td>${new Date(order.created_at).toLocaleDateString('fr-FR')}</td></tr>
                            <tr><td class="fw-bold">Total:</td><td class="fw-bold orange-color">${new Intl.NumberFormat('fr-FR').format(order.total)} FCFA</td></tr>
                            <tr><td class="fw-bold">Paiement:</td><td>${paymentMethodLabel}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Livraison</h6>
                        <table class="table table-sm">
                            <tr><td class="fw-bold">Nom:</td><td>${order.shipping_name}</td></tr>
                            <tr><td class="fw-bold">Adresse:</td><td>${order.shipping_address}</td></tr>
                            <tr><td class="fw-bold">Ville:</td><td>${order.shipping_city}</td></tr>
                            <tr><td class="fw-bold">Téléphone:</td><td>${order.shipping_phone}</td></tr>
                        </table>
                    </div>
                </div>
                
                <hr>
                
                <!-- Progression de livraison -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Suivi de livraison</h6>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar orange-bg" role="progressbar" style="width: ${progressPercentage}%"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                <p class="mb-0 mt-1 small">Commande confirmée</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="bi ${order.status === 'processing' || order.status === 'shipped' || order.status === 'delivered' ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'} fs-4"></i>
                                <p class="mb-0 mt-1 small">En préparation</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="bi ${order.status === 'shipped' || order.status === 'delivered' ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'} fs-4"></i>
                                <p class="mb-0 mt-1 small">Expédiée</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="bi ${order.status === 'delivered' ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'} fs-4"></i>
                                <p class="mb-0 mt-1 small">Livrée</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Obtenir le libellé de la méthode de paiement
        function getPaymentMethodLabel(method) {
            const methods = {
                'cash_on_delivery': 'Paiement à la livraison',
                'mobile_money': 'Mobile Money',
                'bank_card': 'Carte bancaire',
                'orange_money': 'Orange Money',
                'mtn_money': 'MTN Mobile Money',
                'moov_money': 'Moov Money',
                'wave': 'Wave'
            };
            return methods[method] || method;
        }

        // Calculer le pourcentage de progression
        function getProgressPercentage(status) {
            switch (status) {
                case 'pending': return 25;
                case 'processing': return 50;
                case 'shipped': return 75;
                case 'delivered': return 100;
                default: return 25;
            }
        }

        // Obtenir le badge de statut
        function getStatusBadge(status) {
            const badges = {
                'pending': { class: 'bg-warning', label: 'En attente' },
                'paid': { class: 'bg-info', label: 'Payée' },
                'processing': { class: 'bg-primary', label: 'En préparation' },
                'shipped': { class: 'bg-success', label: 'Expédiée' },
                'delivered': { class: 'bg-success', label: 'Livrée' },
                'cancelled': { class: 'bg-danger', label: 'Annulée' },
                'refunded': { class: 'bg-secondary', label: 'Remboursée' }
            };
            return badges[status] || { class: 'bg-secondary', label: status };
        }

        // Charger les favoris de l'utilisateur
        async function loadFavorites() {
            console.log('🔄 Chargement des favoris...');
            
            const token = localStorage.getItem('auth_token');
            const sessionId = localStorage.getItem('guest_session_id');
            
            console.log('Token:', token ? '✅ Présent' : '❌ Absent');
            console.log('Session ID:', sessionId ? '✅ Présent' : '❌ Absent');
            
            const favoritesContainer = document.getElementById('favoritesContainer');
            const noFavoritesMessage = document.getElementById('noFavoritesMessage');
            
            if (!favoritesContainer) {
                console.error('❌ Element favoritesContainer introuvable!');
                return;
            }
            
            // Afficher un loader
            favoritesContainer.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div><p class="mt-2">Chargement de vos favoris...</p></div>';
            if (noFavoritesMessage) {
                noFavoritesMessage.style.display = 'none';
            }

            try {
                const headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                };

                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                } else if (sessionId) {
                    headers['X-Session-ID'] = sessionId;
                }

                console.log('📡 Requête API en cours...');
                const response = await fetch('/api/favorites/', {
                    method: 'GET',
                    headers: headers
                });

                console.log('📥 Réponse reçue:', response.status);
                const data = await response.json();
                console.log('📦 Données:', data);

                if (data.success && data.favorites && data.favorites.length > 0) {
                    favoritesContainer.innerHTML = '';
                    
                    data.favorites.forEach(favorite => {
                        const product = favorite.product;
                        
                        // Préparer l'URL de l'image
                        let imageUrl = '/images/produit.jpg'; // Default
                        if (product.image) {
                            if (product.image.startsWith('http')) {
                                imageUrl = product.image;
                            } else if (product.image.startsWith('/')) {
                                imageUrl = product.image;
                            } else {
                                imageUrl = '/' + product.image;
                            }
                        }
                        
                        // Convertir les prix en nombres
                        const price = parseFloat(product.price);
                        const oldPrice = product.old_price ? parseFloat(product.old_price) : null;
                        const rating = parseFloat(product.rating) || 0;
                        
                        // Déterminer si le produit est en promo (old_price > price)
                        const isPromo = oldPrice && oldPrice > price;
                        
                        const productCard = `
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="card h-100 position-relative">
                                    ${isPromo ? `<span class="badge bg-danger position-absolute top-0 start-0 m-2" style="z-index: 5;">-${Math.round(((oldPrice - price) / oldPrice) * 100)}%</span>` : ''}
                                    <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" 
                                            onclick="removeFavorite(${product.id})" 
                                            style="z-index: 10; opacity: 0.9;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <a href="/produit/${product.slug}" class="text-decoration-none text-dark">
                                        <img src="${imageUrl}" 
                                             class="card-img-top" 
                                             alt="${product.name}"
                                             style="height: 200px; object-fit: contain; padding: 10px;"
                                             onerror="this.src='/images/produit.jpg'">
                                        <div class="card-body">
                                            <h6 class="card-title" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${product.name}</h6>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="text-warning me-1">
                                                    ${'★'.repeat(Math.floor(rating))}${'☆'.repeat(5 - Math.floor(rating))}
                                                </span>
                                                <small class="text-muted">(${product.reviews_count || 0})</small>
                                            </div>
                                            <div class="d-flex flex-column">
                                                ${isPromo ? `
                                                    <p class="mb-0 text-muted small text-decoration-line-through">
                                                        ${new Intl.NumberFormat('fr-FR').format(oldPrice)} FCFA
                                                    </p>
                                                    <h5 class="mb-0 orange-color fw-bold">
                                                        ${new Intl.NumberFormat('fr-FR').format(price)} FCFA
                                                    </h5>
                                                ` : `
                                                    <h5 class="mb-0 fw-bold">
                                                        ${new Intl.NumberFormat('fr-FR').format(price)} FCFA
                                                    </h5>
                                                `}
                                            </div>
                                        </div>
                                    </a>
                                    <div class="card-footer bg-white border-0">
                                        <button class="btn btn-sm orange-bg text-white w-100" onclick="addToCartFromFavorite(${product.id})">
                                            <i class="bi bi-cart-plus me-1"></i>Ajouter au panier
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                        favoritesContainer.innerHTML += productCard;
                    });
                } else {
                    favoritesContainer.innerHTML = '';
                    noFavoritesMessage.style.display = 'block';
                }
            } catch (error) {
                console.error('❌ Erreur lors du chargement des favoris:', error);
                console.error('Détails:', error.message);
                favoritesContainer.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                        <p class="text-danger mt-3">Erreur lors du chargement des favoris</p>
                        <p class="text-muted small">${error.message}</p>
                        <button class="btn btn-sm orange-bg text-white" onclick="loadFavorites()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Réessayer
                        </button>
                    </div>
                `;
            }
        }

        // Retirer un produit des favoris
        async function removeFavorite(productId) {
            if (!confirm('Voulez-vous vraiment retirer ce produit de vos favoris ?')) {
                return;
            }

            const token = localStorage.getItem('auth_token');
            const sessionId = localStorage.getItem('guest_session_id');

            try {
                const headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                };

                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                } else if (sessionId) {
                    headers['X-Session-ID'] = sessionId;
                }

                const response = await fetch('/api/favorites/toggle', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ product_id: productId })
                });

                const data = await response.json();

                if (data.success) {
                    showNotification('success', data.message || 'Produit retiré des favoris');
                    loadFavorites(); // Recharger la liste
                    
                    // Mettre à jour le compteur
                    if (typeof updateFavoritesCount === 'function') {
                        const favCount = document.querySelectorAll('#favoritesContainer .col-md-3').length - 1;
                        updateFavoritesCount(Math.max(0, favCount));
                    }
                } else {
                    showNotification('danger', data.message || 'Erreur lors de la suppression');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('danger', 'Erreur de connexion');
            }
        }

        // Ajouter au panier depuis les favoris
        async function addToCartFromFavorite(productId) {
            if (typeof addToCart === 'function') {
                await addToCart(productId, 1);
            } else {
                showNotification('warning', 'Fonction panier non disponible');
            }
        }

        // Charger les commandes quand l'onglet est affiché
        document.addEventListener('DOMContentLoaded', function() {
            const ordersTab = document.querySelector('a[href="#orders"]');
            if (ordersTab) {
                ordersTab.addEventListener('shown.bs.tab', function() {
                    loadOrders();
                });
            }

            // Charger les favoris quand l'onglet est affiché
            const favoritesTab = document.querySelector('a[href="#favorites"]');
            if (favoritesTab) {
                favoritesTab.addEventListener('shown.bs.tab', function() {
                    loadFavorites();
                });
            }

            // Si l'URL contient #favorites, activer automatiquement l'onglet favoris
            if (window.location.hash === '#favorites') {
                const favoritesTabTrigger = new bootstrap.Tab(favoritesTab);
                favoritesTabTrigger.show();
            }

            // Si l'URL contient ?tab=orders, activer automatiquement l'onglet commandes
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('tab') === 'orders' && ordersTab) {
                const ordersTabTrigger = new bootstrap.Tab(ordersTab);
                ordersTabTrigger.show();
            }

            // Charger l'activité quand l'onglet est affiché
            const activityTab = document.querySelector('a[href="#activity"]');
            if (activityTab) {
                activityTab.addEventListener('shown.bs.tab', function() {
                    loadActivity();
                });
            }
        });

        // Charger l'activité récente de l'utilisateur
        async function loadActivity() {
            const token = localStorage.getItem('auth_token');
            if (!token) return;

            const activityContainer = document.getElementById('activityContainer');
            const noActivityMessage = document.getElementById('noActivityMessage');

            // Afficher le loader
            activityContainer.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div><p class="mt-2">Chargement de vos activités...</p></div>';
            noActivityMessage.style.display = 'none';

            try {
                const response = await fetch('/api/activity/recent', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success && data.activities && data.activities.length > 0) {
                    activityContainer.innerHTML = '';
                    
                    data.activities.forEach(activity => {
                        const activityItem = createActivityItem(activity);
                        activityContainer.innerHTML += activityItem;
                    });
                } else {
                    activityContainer.style.display = 'none';
                    noActivityMessage.style.display = 'block';
                }
            } catch (error) {
                console.error('Erreur lors du chargement de l\'activité:', error);
                activityContainer.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement de l\'activité</div>';
            }
        }

        // Créer un élément d'activité
        function createActivityItem(activity) {
            const badgeColors = {
                'order': 'orange-bg',
                'favorite': 'bg-success',
                'cart': 'bg-info',
                'view': 'bg-secondary',
                'review': 'bg-primary'
            };

            const badgeLabels = {
                'order': 'Commande',
                'favorite': 'Favori',
                'cart': 'Panier',
                'view': 'Consulté',
                'review': 'Avis'
            };

            const icons = {
                'order': 'bi-bag-check',
                'favorite': 'bi-heart-fill',
                'cart': 'bi-cart-plus',
                'view': 'bi-eye',
                'review': 'bi-star-fill'
            };

            const badgeClass = badgeColors[activity.type] || 'bg-secondary';
            const badgeLabel = badgeLabels[activity.type] || activity.type;
            const icon = icons[activity.type] || 'bi-circle';

            return `
                <div class="activity-item border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi ${icon} me-2 text-muted"></i>
                                <h6 class="mb-0">${activity.title}</h6>
                            </div>
                            <p class="mb-1 text-muted">${activity.description}</p>
                            <small class="text-muted"><i class="bi bi-clock me-1"></i>${activity.time_ago}</small>
                        </div>
                        <span class="badge ${badgeClass} ms-3">${badgeLabel}</span>
                    </div>
                </div>
            `;
        }

        // Fonction pour aller vers la boutique
        function goToMyStore(event) {
            event.preventDefault();
            const token = localStorage.getItem('auth_token');
            if (token) {
                window.location.href = '/store/dashboard?token=' + token;
            }
        }

        // Vérifier si l'utilisateur a une boutique et afficher le lien
        function checkUserStore() {
            const token = localStorage.getItem('auth_token');
            const myStoreLink = document.getElementById('myStoreLink');
            
            if (!token || !myStoreLink) return;
            
            fetch('/api/check-seller-status', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.is_seller && data.has_store) {
                    myStoreLink.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        }

        // Vérifier au chargement de la page
        document.addEventListener('DOMContentLoaded', checkUserStore);
    </script>

@endsection