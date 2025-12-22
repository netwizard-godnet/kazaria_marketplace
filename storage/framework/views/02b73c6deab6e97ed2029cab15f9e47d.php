<?php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
?>

<?php $__env->startSection('content'); ?>
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

        /* Styles pour les boutons d'actions des commandes */
        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid;
            background: white;
            transition: all 0.3s ease;
            padding: 0;
            margin-right: 0.5rem;
            text-decoration: none;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .action-btn i {
            font-size: 1.1rem;
        }
        
        .action-btn-danger {
            border-color: #dc3545;
            color: #dc3545;
        }
        .action-btn-danger:hover {
            background-color: #dc3545;
            color: white;
        }
        
        .action-btn-success {
            border-color: #28a745;
            color: #28a745;
        }
        .action-btn-success:hover {
            background-color: #28a745;
            color: white;
        }
        
        .action-btn-primary {
            border-color: #007bff;
            color: #007bff;
        }
        .action-btn-primary:hover {
            background-color: #007bff;
            color: white;
        }
        
        .action-btn-orange {
            border-color: var(--main-color);
            background-color: var(--main-color);
            color: white;
        }
        .action-btn-orange:hover {
            background-color: #e03d1a;
            border-color: #e03d1a;
            color: white;
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
            
            .action-btn {
                width: 36px;
                height: 36px;
                margin-right: 0.25rem;
            }
            
            .action-btn i {
                font-size: 1rem;
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
                            <?php if($user->profile_pic_url): ?>
                                <img src="<?php echo e(asset($user->profile_pic_url)); ?>" alt="Photo de profil" class="profile-avatar rounded-circle" id="profileAvatar" style="cursor: pointer; width: 120px; height: 120px; object-fit: cover;">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/120x120/f04e26/ffffff?text=<?php echo e(strtoupper(substr($user->prenoms, 0, 1) . substr($user->nom, 0, 1))); ?>" alt="Photo de profil" class="profile-avatar rounded-circle" id="profileAvatar" style="cursor: pointer; width: 120px; height: 120px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="position-absolute bottom-0 end-0">
                                <button class="btn btn-sm orange-bg text-white rounded-circle" data-bs-toggle="modal" data-bs-target="#changePhotoModal" style="width: 35px; height: 35px;">
                                    <i class="bi bi-camera"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <h3 class="mb-1"><?php echo e($user->prenoms); ?> <?php echo e($user->nom); ?></h3>
                        <div class="d-flex gap-3">
                            <?php if(isset($userRating) && $userRating > 0): ?>
                                <span class="badge orange-bg text-white"><i class="bi bi-star-fill me-1"></i><?php echo e(number_format($userRating, 1)); ?>/5</span>
                            <?php endif; ?>
                            <?php if($user->is_verified): ?>
                            <span class="badge orange-bg text-white"><i class="bi bi-shield-check me-1"></i>Vérifié</span>
                            <?php else: ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Non vérifié</span>
                            <?php endif; ?>
                            <?php if($user->ville): ?>
                            <span class="badge orange-bg text-white"><i class="bi bi-geo-alt me-1"></i><?php echo e($user->ville); ?><?php echo e($user->pays ? ', ' . $user->pays : ''); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-md-end">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="card text-center orange-bg text-white">
                                    <div class="fs-2 stats-number" id="totalOrders"><?php echo e($stats['total_orders']); ?></div>
                                    <span class="fs-8">Commandes</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card text-center orange-bg text-white">
                                    <div class="fs-2 stats-number" id="totalFavorites"><?php echo e($stats['total_favorites']); ?></div>
                                    <span class="fs-8">Favoris</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card text-center orange-bg text-white">
                                    <div class="fs-2 stats-number" id="totalReviews"><?php echo e($stats['total_reviews']); ?></div>
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
                                <a class="nav-link px-1 py-2" href="#inbox" data-bs-toggle="pill">
                                    <i class="bi bi-inbox me-2"></i>Boîte de réception
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
                                                <input type="text" class="form-control" id="firstName" value="<?php echo e($user->prenoms); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="lastName" class="form-label">Nom</label>
                                                <input type="text" class="form-control" id="lastName" value="<?php echo e($user->nom); ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" value="<?php echo e($user->email); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="phone" class="form-label">Téléphone</label>
                                                <input type="tel" class="form-control" id="phone" value="<?php echo e($user->telephone); ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label for="address" class="form-label">Adresse</label>
                                                <input type="text" class="form-control" id="address" value="<?php echo e($user->adresse ?? ''); ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="postalCode" class="form-label">Code postal</label>
                                                <input type="text" class="form-control" id="postalCode" value="<?php echo e($user->code_postal ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="city" class="form-label">Ville</label>
                                                <input type="text" class="form-control" id="city" value="<?php echo e($user->ville ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="country" class="form-label">Pays</label>
                                                <select class="form-control" id="country">
                                                    <option value="CI" <?php echo e(($user->pays ?? 'CI') == 'CI' ? 'selected' : ''); ?>>Côte d'Ivoire</option>
                                                    <option value="SN" <?php echo e(($user->pays ?? '') == 'SN' ? 'selected' : ''); ?>>Sénégal</option>
                                                    <option value="ML" <?php echo e(($user->pays ?? '') == 'ML' ? 'selected' : ''); ?>>Mali</option>
                                                    <option value="BF" <?php echo e(($user->pays ?? '') == 'BF' ? 'selected' : ''); ?>>Burkina Faso</option>
                                                    <option value="GH" <?php echo e(($user->pays ?? '') == 'GH' ? 'selected' : ''); ?>>Ghana</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="bio" class="form-label">Biographie</label>
                                            <textarea class="form-control" id="bio" rows="3" placeholder="Parlez-nous de vous..."><?php echo e($user->bio ?? ''); ?></textarea>
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
                                                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="togglePassword('currentPassword')">
                                                            <i class="bi bi-eye" id="currentPassword-icon"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="newPassword" class="form-label">Nouveau mot de passe</label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" id="newPassword" required minlength="8">
                                                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="togglePassword('newPassword')">
                                                            <i class="bi bi-eye" id="newPassword-icon"></i>
                                                        </button>
                                                    </div>
                                                    <div class="form-text">Minimum 8 caractères</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" id="confirmPassword" required>
                                                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="togglePassword('confirmPassword')">
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
                                                Activez l'authentification à deux facteurs pour renforcer la sécurité de votre compte. Un code de vérification sera envoyé par email à chaque connexion.
                                            </div>
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="twoFactorEnabled" <?php echo e($user->two_factor_enabled ? 'checked' : ''); ?>>
                                                <label class="form-check-label" for="twoFactorEnabled">
                                                    Activer l'authentification à deux facteurs (2FA)
                                                </label>
                                            </div>
                                            <?php if($user->two_factor_enabled): ?>
                                            <div class="alert alert-success">
                                                <i class="bi bi-shield-check me-2"></i>
                                                L'authentification à deux facteurs est activée. Vous recevrez un code par email à chaque connexion.
                                            </div>
                                            <?php else: ?>
                                            <div class="alert alert-warning">
                                                <i class="bi bi-shield-exclamation me-2"></i>
                                                L'authentification à deux facteurs est désactivée. Votre compte est moins sécurisé.
                                            </div>
                                            <?php endif; ?>
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
                                                                Aujourd'hui à <?php echo e(date('H:i')); ?>

                                                            </div>
                                                        </div>
                                                        <span class="badge bg-success">Succès</span>
                                                    </div>
                                                </div>
                                                <?php if($user->email_verified_at): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="bi bi-envelope-check text-info me-2"></i>
                                                            <strong>Email vérifié</strong>
                                                            <div class="small text-muted">
                                                                <?php echo e(\Carbon\Carbon::parse($user->email_verified_at)->format('d/m/Y à H:i')); ?>

                                                            </div>
                                                        </div>
                                                        <span class="badge bg-info">Vérifié</span>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
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
                                        <div class="col-md-6 d-none">
                                            <h6 class="mb-3">Notifications</h6>
                                            <div class="form-check mb-3 d-none">
                                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                                <label class="form-check-label" for="emailNotifications">
                                                    Notifications par email
                                                </label>
                                            </div>
                                            <div class="form-check mb-3 d-none">
                                                <input class="form-check-input" type="checkbox" id="smsNotifications">
                                                <label class="form-check-label" for="smsNotifications">
                                                    Notifications SMS
                                                </label>
                                            </div>
                                            <div class="form-check mb-3 d-none">
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
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="currency" class="form-label">Devise</label>
                                                <select class="form-control" id="currency">
                                                    <option value="XOF" selected>Franc CFA (FCFA)</option>
                                                    <option value="EUR">Euro (€)</option>
                                                    <option value="USD">Dollar US ($)</option>
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
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="logout()">
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
                                    <!-- Filtres -->
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label small">Filtrer par date</label>
                                            <select class="form-select form-select-sm" id="filterDate">
                                                <option value="">Toutes les dates</option>
                                                <option value="today">Aujourd'hui</option>
                                                <option value="week">Cette semaine</option>
                                                <option value="month">Ce mois</option>
                                                <option value="3months">3 derniers mois</option>
                                                <option value="year">Cette année</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Filtrer par statut</label>
                                            <select class="form-select form-select-sm" id="filterStatus">
                                                <option value="">Commandes en cours (par défaut)</option>
                                                <option value="pending">En cours de validation</option>
                                                <option value="processing">En cours de livraison</option>
                                                <option value="delivered">Livrée</option>
                                                <option value="cancelled">Annulée</option>
                                                <option value="all">Toutes les commandes</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">&nbsp;</label>
                                            <button type="button" class="btn btn-sm orange-bg text-white w-100" onclick="loadOrders()">
                                                <i class="bi bi-search me-1"></i>Filtrer
                                            </button>
                                        </div>
                                    </div>
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

                        <!-- Inbox -->
                        <div class="tab-pane fade" id="inbox">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title mb-0"><i class="bi bi-inbox me-2"></i>Boîte de réception</h5>
                                        <small class="text-muted">Messages de support qui vous sont attribués</small>
                                    </div>
                                    <span class="badge orange-bg text-white"><?php echo e($tickets->count()); ?> message<?php echo e($tickets->count() > 1 ? 's' : ''); ?></span>
                                </div>
                                <div class="card-body">
                                    <?php if($tickets->isEmpty()): ?>
                                        <div class="text-center py-5">
                                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                            <h5 class="mt-3 text-muted">Aucun message pour le moment</h5>
                                            <p class="text-muted small">Les échanges avec le support apparaîtront ici dès qu'un message vous sera assigné.</p>
                                            <a href="mailto:support@kazaria.com" class="btn btn-sm orange-bg text-white">
                                                <i class="bi bi-envelope me-1"></i>Contacter le support
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="row g-3">
                                            <div class="col-lg-4">
                                                <div class="list-group" id="ticketList">
                                                    <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $statusLabels = [
                                                                'open' => 'Ouvert',
                                                                'pending' => 'En attente',
                                                                'resolved' => 'Résolu',
                                                                'closed' => 'Fermé',
                                                                'escalated' => 'Escaladé',
                                                            ];
                                                            $priorityLabels = [
                                                                'low' => 'Basse',
                                                                'medium' => 'Moyenne',
                                                                'high' => 'Haute',
                                                                'urgent' => 'Urgente',
                                                            ];
                                                            $statusClasses = [
                                                                'open' => 'bg-primary',
                                                                'pending' => 'bg-warning text-dark',
                                                                'resolved' => 'bg-success',
                                                                'closed' => 'bg-secondary',
                                                                'escalated' => 'bg-danger',
                                                            ];
                                                            $priorityClasses = [
                                                                'low' => 'badge bg-success-subtle text-success',
                                                                'medium' => 'badge bg-warning-subtle text-warning',
                                                                'high' => 'badge bg-danger-subtle text-danger',
                                                                'urgent' => 'badge bg-danger text-white',
                                                            ];
                                                            $latestMessage = $ticket->messages->where('is_internal', false)->sortByDesc('created_at')->first();
                                                        ?>
                                                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start <?php echo e($index === 0 ? 'active' : ''); ?>"
                                                           data-ticket="#ticket-thread-<?php echo e($ticket->id); ?>">
                                                            <div class="me-3">
                                                                <div class="fw-bold"><?php echo e($ticket->subject ?? 'Message #' . $ticket->ticket_number); ?></div>
                                                                <small class="text-muted d-block"><?php echo e($latestMessage ? $latestMessage->created_at->format('d/m/Y H:i') : $ticket->created_at->format('d/m/Y H:i')); ?></small>
                                                                <?php if($latestMessage): ?>
                                                                    <small class="text-truncate d-block" style="max-width: 220px;">
                                                                        <?php echo e(Str::limit(strip_tags($latestMessage->message), 50)); ?>

                                                                    </small>
                                                                <?php else: ?>
                                                                    <small class="text-muted">En attente d'un premier message</small>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="text-end">
                                                                <span class="badge <?php echo e($statusClasses[$ticket->status] ?? 'bg-secondary'); ?> mb-1">
                                                                    <?php echo e($statusLabels[$ticket->status] ?? ucfirst($ticket->status)); ?>

                                                                </span>
                                                                <div class="small text-muted">Priorité</div>
                                                                <span class="<?php echo e($priorityClasses[$ticket->priority] ?? 'badge bg-secondary'); ?>"><?php echo e($priorityLabels[$ticket->priority] ?? ucfirst($ticket->priority)); ?></span>
                                                            </div>
                                                        </a>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            </div>
                                            <div class="col-lg-8">
                                                <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $visibleMessages = $ticket->messages->where('is_internal', false);
                                                    ?>
                                                    <div class="ticket-thread <?php echo e($index === 0 ? '' : 'd-none'); ?>" id="ticket-thread-<?php echo e($ticket->id); ?>">
                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                            <div>
                                                                <h6 class="fw-bold mb-1"><?php echo e($ticket->subject ?? 'Message #' . $ticket->ticket_number); ?></h6>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <span class="badge <?php echo e($statusClasses[$ticket->status] ?? 'bg-secondary'); ?>">
                                                                        <?php echo e($statusLabels[$ticket->status] ?? ucfirst($ticket->status)); ?>

                                                                    </span>
                                                                    <span class="badge bg-light text-muted border">
                                                                        <i class="bi bi-hash me-1"></i><?php echo e($ticket->ticket_number ?? $ticket->id); ?>

                                                                    </span>
                                                                    <span class="<?php echo e($priorityClasses[$ticket->priority] ?? 'badge bg-secondary'); ?>">
                                                                        <i class="bi bi-flag me-1"></i><?php echo e($priorityLabels[$ticket->priority] ?? ucfirst($ticket->priority)); ?>

                                                                    </span>
                                                                    <?php if($ticket->order_id): ?>
                                                                        <span class="badge bg-light text-muted border">
                                                                            <i class="bi bi-bag me-1"></i>Commande #<?php echo e($ticket->order_id); ?>

                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">Mis à jour le <?php echo e($ticket->updated_at->format('d/m/Y à H:i')); ?></small>
                                                        </div>

                                                        <div class="border rounded p-3 bg-light-subtle">
                                                            <h6 class="fw-bold mb-2"><i class="bi bi-chat-left-text me-2"></i>Messages</h6>
                                                            <?php if($visibleMessages->isEmpty()): ?>
                                                        <p class="text-muted mb-0">Aucun message n'a encore été publié dans cette conversation.</p>
                                                            <?php else: ?>
                                                                <div class="timeline">
                                                                    <?php $__currentLoopData = $visibleMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <div class="mb-4">
                                                                            <div class="d-flex justify-content-between align-items-start">
                                                                                <div>
                                                                                    <strong><?php echo e($message->user_id === $user->id ? 'Vous' : ($message->author?->prenoms . ' ' . $message->author?->nom ?? 'Support Kazaria')); ?></strong>
                                                                                    <?php if($message->user_id === $user->id): ?>
                                                                                        <span class="badge bg-primary-subtle text-primary ms-2">Client</span>
                                                                                    <?php else: ?>
                                                                                        <span class="badge bg-success-subtle text-success ms-2">Support</span>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                                <small class="text-muted"><?php echo e($message->created_at->format('d/m/Y à H:i')); ?></small>
                                                                            </div>
                                                                            <div class="mt-2 ps-3 border-start">
                                                                                <p class="mb-2"><?php echo nl2br(e($message->message)); ?></p>
                                                                                <?php if(!empty($message->attachments)): ?>
                                                                                    <div class="mt-2">
                                                                                        <small class="text-muted d-block mb-1"><i class="bi bi-paperclip me-1"></i>Pièces jointes :</small>
                                                                                        <?php $__currentLoopData = $message->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                            <a href="<?php echo e($attachment); ?>" target="_blank" class="btn btn-outline-secondary btn-sm me-2 mb-2">
                                                                                                <i class="bi bi-file-earmark-arrow-down me-1"></i>Télécharger
                                                                                            </a>
                                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                                    </div>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="alert alert-info mt-3 mb-0">
                                                            <i class="bi bi-info-circle me-2"></i>
                                                            Pour répondre à ce message, merci de répondre directement à l'email reçu ou d'utiliser votre espace vendeur si vous êtes marchand.
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
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
                                        <a href="<?php echo e(route('boutique_officielle')); ?>" class="btn orange-bg text-white">
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
                                <?php if($user->profile_pic_url): ?>
                                    <img id="photoPreview" src="<?php echo e(asset($user->profile_pic_url)); ?>" alt="Aperçu" class="rounded-circle" width="150" height="150" style="object-fit: cover;">
                                <?php else: ?>
                                    <img id="photoPreview" src="https://via.placeholder.com/150x150/f04e27/ffffff?text=<?php echo e(strtoupper(substr($user->prenoms, 0, 1) . substr($user->nom, 0, 1))); ?>" alt="Aperçu" class="rounded-circle" width="150" height="150" style="object-fit: cover;">
                                <?php endif; ?>
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
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
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

        // L'authentification est maintenant gérée par Blade et le middleware hybride
        // Plus besoin de vérifier les tokens côté client

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
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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
                    
                    // L'authentification est maintenant gérée par le middleware hybride
                    // Plus besoin de vérifier les tokens côté client
                    
                    // Désactiver le bouton
                    const submitBtn = passwordForm.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Changement...';
                    
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        
                        const response = await fetch('/profile/change-password', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                current_password: currentPassword,
                                new_password: newPassword,
                                new_password_confirmation: confirmPassword
                            })
                        });

                        let data;
                        try {
                            data = await response.json();
                        } catch (e) {
                            showToast('error', 'Erreur lors du traitement de la réponse du serveur.');
                            return;
                        }
                        
                        if (response.ok && data.success) {
                            showToast('success', 'Mot de passe mis à jour avec succès !');
                            passwordForm.reset();
                            
                            // Demander si l'utilisateur veut déconnecter tous les appareils
                            setTimeout(() => {
                                if (confirm('Voulez-vous déconnecter tous vos appareils connectés ? Cela améliorera la sécurité de votre compte.')) {
                                    logoutAllDevicesAfterPasswordChange();
                                }
                            }, 500);
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

            // Gestion du toggle de l'authentification à deux facteurs
            const twoFactorToggle = document.getElementById('twoFactorEnabled');
            
            if (twoFactorToggle) {
                twoFactorToggle.addEventListener('change', async function(e) {
                    const isEnabled = e.target.checked;
                    
                    // Désactiver le toggle pendant la requête
                    twoFactorToggle.disabled = true;
                    
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        
                        const response = await fetch('/profile/update-two-factor', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                two_factor_enabled: isEnabled
                            })
                        });

                        let data;
                        try {
                            data = await response.json();
                        } catch (e) {
                            showToast('error', 'Erreur lors du traitement de la réponse du serveur.');
                            // Restaurer l'état précédent
                            e.target.checked = !isEnabled;
                            twoFactorToggle.disabled = false;
                            return;
                        }
                        
                        if (response.ok && data.success) {
                            showToast('success', data.message);
                            // Recharger la page pour mettre à jour l'interface
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showToast('error', data.message || 'Erreur lors de la mise à jour de l\'authentification à deux facteurs.');
                            // Restaurer l'état précédent
                            e.target.checked = !isEnabled;
                            twoFactorToggle.disabled = false;
                        }
                    } catch (error) {
                        showToast('error', 'Erreur de connexion. Veuillez réessayer.');
                        // Restaurer l'état précédent
                        e.target.checked = !isEnabled;
                        twoFactorToggle.disabled = false;
                    }
                });
            }

            initTicketThreads();

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
                                    
                                    // La page va être rechargée, pas besoin de mettre à jour manuellement l'image
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
                        xhr.withCredentials = true; // inclure les cookies de session
                        xhr.setRequestHeader('Accept', 'application/json');
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
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

        // Fonction pour déconnecter tous les appareils après changement de mot de passe (utilise la route web)
        async function logoutAllDevicesAfterPasswordChange() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            try {
                const response = await fetch('/profile/logout-all-devices', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    showToast('error', 'Erreur lors du traitement de la réponse.');
                    return;
                }
                
                if (response.ok && data.success) {
                    showToast('success', 'Tous les autres appareils ont été déconnectés avec succès. Vous restez connecté sur cet appareil.');
                    
                    // Nettoyer le localStorage des tokens (ils seront régénérés si nécessaire)
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user_data');
                } else {
                    showToast('error', data.message || 'Erreur lors de la déconnexion des appareils.');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast('error', 'Erreur de connexion. Veuillez réessayer.');
            }
        }

        // Fonction pour déconnecter tous les appareils (utilise la route web avec session)
        async function logoutAllDevices() {
            if (!confirm('Êtes-vous sûr de vouloir déconnecter tous les appareils ? Cela supprimera tous les tokens de connexion des autres appareils. Vous resterez connecté sur cet appareil.')) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            try {
                const response = await fetch('/profile/logout-all-devices', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    showToast('error', 'Erreur lors du traitement de la réponse.');
                    return;
                }
                
                if (response.ok && data.success) {
                    showToast('success', 'Tous les autres appareils ont été déconnectés avec succès. Vous restez connecté sur cet appareil.');
                    
                    // Nettoyer les tokens du localStorage (s'ils existent)
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user_data');
                } else {
                    showToast('error', data.message || 'Erreur lors de la déconnexion des appareils.');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast('error', 'Erreur de connexion. Veuillez réessayer.');
            }
        }

        // Charger les commandes de l'utilisateur
        async function loadOrders() {
            try {
                const tbody = document.getElementById('ordersTableBody');
                
                // Afficher un indicateur de chargement
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                                <p class="mt-2 text-muted">Chargement des commandes...</p>
                            </td>
                        </tr>
                    `;
                }
                
                // Récupérer les filtres avant de faire la requête
                const filterDate = document.getElementById('filterDate')?.value || '';
                const filterStatus = document.getElementById('filterStatus')?.value || '';
                
                // Construire l'URL avec les paramètres de filtre
                // Toujours envoyer le paramètre status, même s'il est vide, pour que le serveur applique le filtre par défaut
                const params = new URLSearchParams();
                if (filterDate) {
                    params.append('date', filterDate);
                }
                // Toujours envoyer le paramètre status
                params.append('status', filterStatus || '');
                
                const url = '/api/orders/my-orders' + (params.toString() ? '?' + params.toString() : '');
                
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin' // Important pour envoyer les cookies de session
                });

                if (!response.ok) {
                    console.error('Erreur HTTP:', response.status);
                    const tbody = document.getElementById('ordersTableBody');
                    let errorMessage = 'Erreur lors du chargement des commandes';
                    
                    if (response.status === 401) {
                        errorMessage = 'Vous devez être connecté pour voir vos commandes. Redirection...';
                        setTimeout(() => {
                            window.location.href = '/authentification';
                        }, 2000);
                    }
                    
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-danger py-4">
                                <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                                <p class="mt-2">${errorMessage}</p>
                            </td>
                        </tr>
                    `;
                    return;
                }

                const data = await response.json();
                
                if (!data.success) {
                    console.error('Erreur API:', data.message);
                    const tbody = document.getElementById('ordersTableBody');
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-danger py-4">
                                <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                                <p class="mt-2">${data.message || 'Erreur lors du chargement des commandes'}</p>
                            </td>
                        </tr>
                    `;
                    return;
                }
                
                console.log('Données reçues:', data);
                
                // Les commandes sont déjà filtrées côté serveur
                const filteredOrders = data.orders || [];
                
                if (filteredOrders.length > 0) {
                    tbody.innerHTML = '';
                    
                    filteredOrders.forEach(order => {
                        const statusBadge = getStatusBadge(order.status);
                        const row = document.createElement('tr');
                        
                        // Bouton d'annulation (seulement si statut = pending)
                        const cancelButton = order.status === 'pending' 
                            ? `<button class="action-btn action-btn-danger" onclick="cancelOrder('${order.order_number}')" title="Annuler la commande">
                                    <i class="bi bi-x-circle"></i>
                                </button>`
                            : '';
                        
                        row.innerHTML = `
                            <td><strong>${order.order_number}</strong></td>
                            <td>${new Date(order.created_at).toLocaleDateString('fr-FR')}</td>
                            <td><span class="badge ${statusBadge.class}">${statusBadge.label}</span></td>
                            <td><strong>${new Intl.NumberFormat('fr-FR').format(order.total)} FCFA</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                ${cancelButton}
                                    <button class="action-btn action-btn-success" onclick="trackOrder('${order.order_number}')" title="Suivre la commande">
                                    <i class="bi bi-truck"></i>
                                </button>
                                    <a href="/order/invoice/${order.order_number}" class="action-btn action-btn-primary" title="Voir la facture">
                                    <i class="bi bi-file-earmark-text"></i>
                                </a>
                                    <a href="/order/download/${order.order_number}" class="action-btn action-btn-orange" title="Télécharger PDF">
                                    <i class="bi bi-download"></i>
                                </a>
                                </div>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    
                    // Mettre à jour le compteur de commandes
                    document.getElementById('totalOrders').textContent = filteredOrders.length;
                } else {
                    // Vérifier si des filtres sont actifs
                    const hasActiveFilters = filterDate || (filterStatus && filterStatus !== '' && filterStatus !== 'all');
                    const message = hasActiveFilters 
                        ? 'Aucune commande ne correspond aux filtres sélectionnés'
                        : 'Vous n\'avez pas encore de commande';
                    
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">${message}</p>
                                ${!hasActiveFilters ? `
                                    <a href="/" class="btn btn-sm orange-bg text-white">
                                        <i class="bi bi-shop me-1"></i>Commencer mes achats
                                    </a>
                                ` : ''}
                            </td>
                        </tr>
                    `;
                }
            } catch (error) {
                console.error('Erreur lors du chargement des commandes:', error);
            }
        }

        // Fonction pour annuler une commande
        async function cancelOrder(orderNumber) {
            // Demander confirmation
            if (!confirm('Êtes-vous sûr de vouloir annuler cette commande ? Cette action est irréversible.')) {
                return;
            }
            
            try {
                // Trouver le bouton d'annulation pour cette commande
                const buttons = document.querySelectorAll(`button[onclick*="cancelOrder('${orderNumber}')"]`);
                const button = buttons.length > 0 ? buttons[0] : null;
                let originalHTML = '';
                
                if (button) {
                    originalHTML = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Annulation...';
                }
                
                const response = await fetch(`/api/orders/${orderNumber}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        reason: 'Annulation par le client'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Afficher un message de succès
                    if (window.showNotification) {
                        window.showNotification('success', data.message);
                    } else {
                        alert(data.message);
                    }
                    
                    // Recharger les commandes
                    loadOrders();
                } else {
                    // Afficher un message d'erreur
                    if (window.showNotification) {
                        window.showNotification('error', data.message);
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                    
                    // Restaurer le bouton
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = originalHTML;
                    }
                }
            } catch (error) {
                console.error('Erreur lors de l\'annulation:', error);
                if (window.showNotification) {
                    window.showNotification('error', 'Erreur lors de l\'annulation de la commande');
                } else {
                    alert('Erreur lors de l\'annulation de la commande');
                }
                
                // Restaurer le bouton
                const buttons = document.querySelectorAll(`button[onclick*="cancelOrder('${orderNumber}')"]`);
                if (buttons.length > 0) {
                    buttons[0].disabled = false;
                    buttons[0].innerHTML = '<i class="bi bi-x-circle"></i>';
                }
            }
        }

        // Fonction pour suivre une commande
        async function trackOrder(orderNumber) {
            console.log('🚚 Suivi de la commande:', orderNumber);
            
            // Pas besoin de vérifier le token avec l'authentification session
            
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
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fermer</button>
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
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin' // Inclure les cookies de session
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
            
            // Déterminer les états des étapes
            const isValidationComplete = order.status === 'pending' || order.status === 'processing' || order.status === 'delivered';
            const isDeliveryInProgress = order.status === 'processing' || order.status === 'delivered';
            const isDelivered = order.status === 'delivered';
            
            // Déterminer l'étape active
            let activeStep = 1;
            if (isDelivered) activeStep = 3;
            else if (isDeliveryInProgress) activeStep = 2;
            
            document.getElementById('trackOrderContent').innerHTML = `
                <style>
                    .order-info-card {
                        background: #fff;
                        border-radius: 12px;
                        padding: 1.5rem;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                        transition: transform 0.2s, box-shadow 0.2s;
                        height: 100%;
                    }
                    .order-info-card:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
                    }
                    .order-info-card .card-icon {
                        width: 48px;
                        height: 48px;
                        border-radius: 12px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-bottom: 1rem;
                    }
                    .order-info-card .card-icon.orange {
                        background: linear-gradient(135deg, var(--main-color) 0%, #ff8c00 100%);
                        color: white;
                    }
                    .order-info-card .card-icon.blue {
                        background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
                        color: white;
                    }
                    .info-item {
                        display: flex;
                        align-items: flex-start;
                        padding: 0.75rem 0;
                        border-bottom: 1px solid #f0f0f0;
                    }
                    .info-item:last-child {
                        border-bottom: none;
                    }
                    .info-label {
                        font-weight: 600;
                        color: #666;
                        min-width: 100px;
                        font-size: 0.9rem;
                    }
                    .info-value {
                        color: #333;
                        flex: 1;
                        font-size: 0.95rem;
                    }
                    .info-value.total {
                        font-size: 1.2rem;
                        font-weight: 700;
                        color: var(--main-color);
                    }
                    .timeline-container {
                        position: relative;
                        padding: 1.5rem 0;
                    }
                    .timeline-step {
                        position: relative;
                        padding-left: 3rem;
                        padding-bottom: 2rem;
                    }
                    .timeline-step:last-child {
                        padding-bottom: 0;
                    }
                    .timeline-step::before {
                        content: '';
                        position: absolute;
                        left: 0.75rem;
                        top: 2rem;
                        width: 2px;
                        height: calc(100% - 1rem);
                        background: #e0e0e0;
                    }
                    .timeline-step:last-child::before {
                        display: none;
                    }
                    .timeline-step.completed::before {
                        background: var(--main-color);
                    }
                    .timeline-step.active::before {
                        background: linear-gradient(to bottom, var(--main-color) 0%, #e0e0e0 100%);
                    }
                    .timeline-icon {
                        position: absolute;
                        left: 0;
                        top: 0;
                        width: 1.5rem;
                        height: 1.5rem;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 0.75rem;
                        border: 2px solid #e0e0e0;
                        background: white;
                        z-index: 2;
                    }
                    .timeline-step.completed .timeline-icon {
                        background: var(--main-color);
                        border-color: var(--main-color);
                        color: white;
                    }
                    .timeline-step.active .timeline-icon {
                        background: white;
                        border-color: var(--main-color);
                        color: var(--main-color);
                        box-shadow: 0 0 0 4px rgba(255, 140, 0, 0.1);
                    }
                    .timeline-content h6 {
                        margin: 0;
                        font-weight: 600;
                        color: #333;
                        font-size: 0.95rem;
                    }
                    .timeline-step.completed .timeline-content h6 {
                        color: var(--main-color);
                    }
                    .timeline-step.active .timeline-content h6 {
                        color: var(--main-color);
                        font-weight: 700;
                    }
                    .progress-bar-modern {
                        height: 6px;
                        background: #f0f0f0;
                        border-radius: 10px;
                        overflow: hidden;
                        margin-bottom: 2rem;
                    }
                    .progress-fill {
                        height: 100%;
                        background: linear-gradient(90deg, var(--main-color) 0%, #ff8c00 100%);
                        border-radius: 10px;
                        transition: width 0.5s ease;
                    }
                </style>
                
                <div class="row g-4 mb-4">
                    <!-- Informations de commande -->
                    <div class="col-md-6">
                        <div class="order-info-card">
                            <div class="card-icon orange">
                                <i class="bi bi-receipt fs-4"></i>
                    </div>
                            <h6 class="fw-bold mb-3" style="color: #333;">Informations de commande</h6>
                            <div class="info-item">
                                <span class="info-label"><i class="bi bi-hash me-1"></i>Numéro:</span>
                                <span class="info-value">${order.order_number}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="bi bi-calendar3 me-1"></i>Date:</span>
                                <span class="info-value">${new Date(order.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="bi bi-currency-exchange me-1"></i>Total:</span>
                                <span class="info-value total">${new Intl.NumberFormat('fr-FR').format(order.total)} FCFA</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="bi bi-credit-card me-1"></i>Paiement:</span>
                                <span class="info-value">${paymentMethodLabel}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Livraison -->
                    <div class="col-md-6">
                        <div class="order-info-card">
                            <div class="card-icon blue">
                                <i class="bi bi-truck fs-4"></i>
                            </div>
                            <h6 class="fw-bold mb-3" style="color: #333;">Livraison</h6>
                            <div class="info-item">
                                <span class="info-label"><i class="bi bi-person me-1"></i>Nom:</span>
                                <span class="info-value">${order.shipping_name}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="bi bi-geo-alt me-1"></i>Adresse:</span>
                                <span class="info-value">${order.shipping_address}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="bi bi-building me-1"></i>Ville:</span>
                                <span class="info-value">${order.shipping_city}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="bi bi-telephone me-1"></i>Téléphone:</span>
                                <span class="info-value">${order.shipping_phone}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${order.status === 'cancelled' ? `
                <!-- Commande annulée -->
                <div class="mb-4">
                    <div class="alert alert-danger d-flex align-items-center p-3" style="border-radius: 12px; border-left: 4px solid #dc3545;">
                        <i class="bi bi-x-circle-fill me-3 fs-3"></i>
                        <div>
                            <h6 class="mb-1 fw-bold">Commande annulée</h6>
                            <p class="mb-0 small">Cette commande a été annulée et n'est plus en cours de traitement.</p>
                        </div>
                    </div>
                </div>
                ` : `
                <!-- Suivi de livraison -->
                <div class="mb-4">
                    <div class="order-info-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="card-icon orange me-3" style="margin-bottom: 0;">
                                <i class="bi bi-clipboard-check fs-5"></i>
                    </div>
                            <h6 class="fw-bold mb-0" style="color: #333;">Suivi de livraison</h6>
                            </div>
                        
                        <!-- Barre de progression -->
                        <div class="progress-bar-modern">
                            <div class="progress-fill" style="width: ${progressPercentage}%"></div>
                        </div>
                        
                        <!-- Timeline -->
                        <div class="timeline-container">
                            <div class="timeline-step ${isValidationComplete ? 'completed' : activeStep === 1 ? 'active' : ''}">
                                <div class="timeline-icon">
                                    ${isValidationComplete ? '<i class="bi bi-check"></i>' : activeStep === 1 ? '<i class="bi bi-clock"></i>' : ''}
                            </div>
                                <div class="timeline-content">
                                    <h6>En cours de validation</h6>
                        </div>
                            </div>
                            
                            <div class="timeline-step ${isDelivered ? 'completed' : activeStep === 2 ? 'active' : ''}">
                                <div class="timeline-icon">
                                    ${isDelivered ? '<i class="bi bi-check"></i>' : activeStep === 2 ? '<i class="bi bi-truck"></i>' : ''}
                                </div>
                                <div class="timeline-content">
                                    <h6>En cours de livraison</h6>
                                </div>
                            </div>
                            
                            <div class="timeline-step ${isDelivered ? 'completed' : ''}">
                                <div class="timeline-icon">
                                    ${isDelivered ? '<i class="bi bi-check"></i>' : ''}
                                </div>
                                <div class="timeline-content">
                                    <h6>Livrée</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                `}
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

        // Calculer le pourcentage de progression (3 étapes: pending=33%, processing=66%, delivered=100%)
        function getProgressPercentage(status) {
            switch (status) {
                case 'pending': return 33;
                case 'processing': return 66;
                case 'delivered': return 100;
                case 'cancelled': return 0;
                default: return 33;
            }
        }

        // Obtenir le badge de statut (selon OrderStatusService)
        function getStatusBadge(status) {
            const badges = {
                'pending': { class: 'bg-warning', label: 'En cours de validation' },
                'processing': { class: 'bg-info', label: 'En cours de livraison' },
                'delivered': { class: 'bg-success', label: 'Livrée' },
                'cancelled': { class: 'bg-danger', label: 'Annulée' }
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

        function initTicketThreads() {
            const ticketList = document.getElementById('ticketList');
            if (!ticketList) {
                return;
            }

            ticketList.querySelectorAll('[data-ticket]').forEach(item => {
                item.addEventListener('click', function(event) {
                    event.preventDefault();

                    ticketList.querySelectorAll('.list-group-item').forEach(li => li.classList.remove('active'));
                    this.classList.add('active');

                    document.querySelectorAll('.ticket-thread').forEach(thread => thread.classList.add('d-none'));
                    const target = document.querySelector(this.dataset.ticket);
                    if (target) {
                        target.classList.remove('d-none');
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
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
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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
                // Vérifier si l'onglet orders est actif au chargement
                const ordersPane = document.querySelector('#orders');
                if (ordersPane && ordersPane.classList.contains('active')) {
                    loadOrders();
                }
                
                ordersTab.addEventListener('shown.bs.tab', function() {
                    loadOrders();
                    // Sauvegarder l'onglet actif
                    localStorage.setItem('activeTab', 'orders');
                });
            }

            // Charger les favoris quand l'onglet est affiché
            const favoritesTab = document.querySelector('a[href="#favorites"]');
            if (favoritesTab) {
                favoritesTab.addEventListener('shown.bs.tab', function() {
                    loadFavorites();
                    // Sauvegarder l'onglet actif
                    localStorage.setItem('activeTab', 'favorites');
                });
            }

            const inboxTab = document.querySelector('a[href="#inbox"]');
            if (inboxTab) {
                inboxTab.addEventListener('shown.bs.tab', function() {
                    localStorage.setItem('activeTab', 'inbox');

                    // S'assurer qu'un ticket est bien sélectionné
                    const activeTicket = document.querySelector('#ticketList .list-group-item.active');
                    if (!activeTicket) {
                        const firstTicket = document.querySelector('#ticketList .list-group-item');
                        if (firstTicket) {
                            firstTicket.classList.add('active');
                        }
                    }

                    const visibleThread = document.querySelector('.ticket-thread:not(.d-none)');
                    if (!visibleThread) {
                        const firstThread = document.querySelector('.ticket-thread');
                        if (firstThread) {
                            firstThread.classList.remove('d-none');
                        }
                    }
                });
            }

            // Charger l'activité quand l'onglet est affiché
            const activityTab = document.querySelector('a[href="#activity"]');
            if (activityTab) {
                activityTab.addEventListener('shown.bs.tab', function() {
                    loadActivity();
                    // Sauvegarder l'onglet actif
                    localStorage.setItem('activeTab', 'activity');
                });
            }

            // Sauvegarder l'onglet "Informations personnelles" quand affiché
            const profileTab = document.querySelector('a[href="#profile"]');
            if (profileTab) {
                profileTab.addEventListener('shown.bs.tab', function() {
                    localStorage.setItem('activeTab', 'profile');
                });
            }

            // Sauvegarder l'onglet "Sécurité" quand affiché
            const securityTab = document.querySelector('a[href="#security"]');
            if (securityTab) {
                securityTab.addEventListener('shown.bs.tab', function() {
                    localStorage.setItem('activeTab', 'security');
                });
            }

            // Sauvegarder l'onglet "Préférences" quand affiché
            const preferencesTab = document.querySelector('a[href="#preferences"]');
            if (preferencesTab) {
                preferencesTab.addEventListener('shown.bs.tab', function() {
                    localStorage.setItem('activeTab', 'preferences');
                });
            }

            // Restaurer l'onglet actif
            const activeTab = localStorage.getItem('activeTab') || 'profile';
            const tabToShow = document.querySelector(`a[href="#${activeTab}"]`);
            if (tabToShow) {
                const tabTrigger = new bootstrap.Tab(tabToShow);
                tabTrigger.show();
            }

            // Priorité aux hash de l'URL si présent
            if (window.location.hash) {
                const hashTab = document.querySelector(`a[href="${window.location.hash}"]`);
                if (hashTab) {
                    const hashTabTrigger = new bootstrap.Tab(hashTab);
                    hashTabTrigger.show();
                }
            }

            // Priorité aux paramètres URL si présents
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('tab')) {
                const urlTab = document.querySelector(`a[href="#${urlParams.get('tab')}"]`);
                if (urlTab) {
                    const urlTabTrigger = new bootstrap.Tab(urlTab);
                    urlTabTrigger.show();
                }
            }
        });

        // Charger l'activité récente de l'utilisateur
        async function loadActivity() {
            const activityContainer = document.getElementById('activityContainer');
            const noActivityMessage = document.getElementById('noActivityMessage');

            // Afficher le loader
            activityContainer.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div><p class="mt-2">Chargement de vos activités...</p></div>';
            noActivityMessage.style.display = 'none';

            try {
                const response = await fetch('/api/activity/recent', {
                    method: 'GET',
                    headers: {
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
                            <small class="text-muted"><i class="bi bi-clock me-1"></i>${activity.date}</small>
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

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\profil.blade.php ENDPATH**/ ?>