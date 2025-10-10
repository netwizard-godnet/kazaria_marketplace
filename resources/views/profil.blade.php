@extends('layouts.app')

@section('content')
    <main class="container-fluid">
        <!-- SECTION BREADCRUMB -->
         <!-- Profile Header -->
        <div class="container-fluid bg-light">
            <div class="container py-5">
                <div class="row g-2">
                    <div class="col-12 col-md-2">
                        <div class="position-relative">
                            <img src="https://via.placeholder.com/120x120/f04e26/ffffff?text=KW" alt="Photo de profil" class="profile-avatar" id="profileAvatar" style="cursor: pointer;">
                            <i class="fa-solid fa-circle-user fa-3x"></i>
                            <div class="position-absolute bottom-0 end-0">
                                <button class="btn btn-sm rounded-circle" data-bs-toggle="modal" data-bs-target="#changePhotoModal">
                                    <i class="bi bi-camera"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <h3 class="mb-1">Kazaria Web</h3>
                        <div class="d-flex gap-3">
                            <span class="badge orange-bg text-white"><i class="bi bi-star-fill me-1"></i>4.8/5</span>
                            <span class="badge orange-bg text-white"><i class="bi bi-shield-check me-1"></i>Vérifié</span>
                            <span class="badge orange-bg text-white"><i class="bi bi-geo-alt me-1"></i>Abidjan, Côte d'Ivoire</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-md-end">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="card text-center orange-bg text-white">
                                    <div class="fs-2 stats-number" id="totalOrders">0</div>
                                    <span class="fs-8">Commandes</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card text-center orange-bg text-white">
                                    <div class="fs-2 stats-number" id="totalFavorites">0</div>
                                    <span class="fs-8">Favoris</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card text-center orange-bg text-white">
                                    <div class="fs-2 stats-number" id="totalReviews">0</div>
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
                                                <input type="text" class="form-control" id="firstName" value="Kazaria">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="lastName" class="form-label">Nom</label>
                                                <input type="text" class="form-control" id="lastName" value="Web">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" value="kazaria.web@email.com">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="phone" class="form-label">Téléphone</label>
                                                <input type="tel" class="form-control" id="phone" value="+225 07 12 34 56 78">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label for="address" class="form-label">Adresse</label>
                                                <input type="text" class="form-control" id="address" value="123 Boulevard de la République, Cocody, Abidjan">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="postalCode" class="form-label">Code postal</label>
                                                <input type="text" class="form-control" id="postalCode" value="00225">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="city" class="form-label">Ville</label>
                                                <input type="text" class="form-control" id="city" value="Abidjan">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="country" class="form-label">Pays</label>
                                                <select class="form-control" id="country">
                                                    <option value="CI" selected>Côte d'Ivoire</option>
                                                    <option value="SN">Sénégal</option>
                                                    <option value="ML">Mali</option>
                                                    <option value="BF">Burkina Faso</option>
                                                    <option value="GH">Ghana</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="bio" class="form-label">Biographie</label>
                                                <textarea class="form-control" id="bio" rows="3" placeholder="Parlez-nous de vous...">Passionné d'e-commerce et de nouvelles technologies en Côte d'Ivoire. J'aime découvrir de nouveaux produits et partager mes expériences avec la communauté Kazaria en Afrique de l'Ouest.</textarea>
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
                                            <form>
                                                <div class="mb-3">
                                                    <label for="currentPassword" class="form-label">Mot de passe actuel</label>
                                                    <input type="password" class="form-control" id="currentPassword">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="newPassword" class="form-label">Nouveau mot de passe</label>
                                                    <input type="password" class="form-control" id="newPassword">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
                                                    <input type="password" class="form-control" id="confirmPassword">
                                                </div>
                                                <button type="submit" class="btn">
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
                                            <button type="button" class="btn btn-outline-primary">
                                                <i class="bi bi-qr-code me-2"></i>Configurer avec une application
                                            </button>
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
                                    <button type="button" class="btn">
                                        <i class="bi bi-check-lg me-2"></i>Enregistrer les préférences
                                    </button>
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
                                                <!-- Les commandes seront chargées dynamiquement -->
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
                                        <button class="btn">
                                            <i class="bi bi-shop me-2"></i>Découvrir la boutique
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activity -->
                        <div class="tab-pane fade" id="activity">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="bi bi-clock-history me-2"></i>Activité récente</h5>
                                </div>
                                <div class="card-body">
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">Nouvelle commande reçue</h6>
                                                <p class="mb-1 text-muted">Commande #KAZ-2024-004 pour Montre Connectée</p>
                                                <small class="text-muted">Il y a 2 heures</small>
                                            </div>
                                            <span class="badge badge-kazaria">Commande</span>
                                        </div>
                                    </div>
                                    
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">Produit ajouté aux favoris</h6>
                                                <p class="mb-1 text-muted">Smartphone Samsung Galaxy ajouté à vos favoris</p>
                                                <small class="text-muted">Il y a 3 heures</small>
                                            </div>
                                            <span class="badge bg-success">Favori</span>
                                        </div>
                                    </div>
                                    
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">Avis reçu</h6>
                                                <p class="mb-1 text-muted">5 étoiles pour Sac à Dos - "Excellent produit !"</p>
                                                <small class="text-muted">Il y a 1 jour</small>
                                            </div>
                                            <span class="badge bg-info">Avis</span>
                                        </div>
                                    </div>
                                    
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">Avis donné</h6>
                                                <p class="mb-1 text-muted">Vous avez donné 5 étoiles à "Sac à main cuir"</p>
                                                <small class="text-muted">Il y a 2 jours</small>
                                            </div>
                                            <span class="badge bg-primary">Avis</span>
                                        </div>
                                    </div>
                                    
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">Connexion</h6>
                                                <p class="mb-1 text-muted">Connexion depuis un nouvel appareil (iPhone)</p>
                                                <small class="text-muted">Il y a 3 jours</small>
                                            </div>
                                            <span class="badge bg-secondary">Sécurité</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Photo Modal -->
        <div class="modal fade" id="changePhotoModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-camera me-2"></i>Changer ma photo de profil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img id="photoPreview" src="https://via.placeholder.com/150x150/f04e26/ffffff?text=KW" alt="Aperçu" class="rounded-circle" width="150" height="150" style="object-fit: cover;">
                                <div class="position-absolute bottom-0 end-0">
                                    <label for="photoInput" class="btn btn-sm rounded-circle" style="width: 40px; height: 40px; cursor: pointer;">
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

@endsection