@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/store.css') }}">
<link rel="stylesheet" href="{{ asset('css/seller-dashboard.css') }}">

<!-- Container pour les notifications toast du dashboard -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="dashboardNotificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i id="dashboardToastIcon" class="bi me-2"></i>
            <strong id="dashboardToastTitle" class="me-auto"></strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div id="dashboardToastBody" class="toast-body"></div>
    </div>
</div>

<!-- Styles pour les notifications toast -->
<style>
.toast {
    min-width: 300px;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.toast-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    font-weight: 600;
}

.toast-body {
    font-size: 0.9rem;
    line-height: 1.4;
}

.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1) !important;
    border-left: 4px solid #198754;
}

.bg-danger-subtle {
    background-color: rgba(220, 53, 69, 0.1) !important;
    border-left: 4px solid #dc3545;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
    border-left: 4px solid #ffc107;
}

.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.1) !important;
    border-left: 4px solid #0dcaf0;
}
</style>

<div class="container-fluid my-4 store-dashboard">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="card shadow-sm sticky-top" style="top: 80px;">
                <div class="card-body p-0">
                    <!-- Logo et nom de la boutique -->
                    <div class="text-center p-3 border-bottom">
                        @if($store->logo)
                            <img id="storeLogoSidebar" src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="img-fluid rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-shop orange-color" style="font-size: 2rem;"></i>
                            </div>
                        @endif
                        <h6 class="fw-bold mb-1">{{ $store->name }}</h6>
                        <small class="text-muted">
                            {{ $store->category->name }}
                            @if($store->subcategory)
                                → {{ $store->subcategory->name }}
                            @endif
                        </small>
                    </div>

                    <!-- Menu -->
                    <ul class="nav flex-column" id="storeTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#overview" role="tab">
                                <i class="bi bi-speedometer2 me-2"></i>
                                <span>Vue d'ensemble</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#products" role="tab">
                                <i class="bi bi-box-seam me-2"></i>
                                <span>Produits</span>
                                <span class="badge orange-bg ms-auto">{{ $stats['total_products'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active d-flex align-items-center" data-bs-toggle="tab" href="#orders" role="tab">
                                <i class="bi bi-bag me-2"></i>
                                <span>Commandes</span>
                                @if($stats['pending_orders'] > 0)
                                    <span class="badge bg-danger ms-auto">{{ $stats['pending_orders'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#settings" role="tab">
                                <i class="bi bi-gear me-2"></i>
                                <span>Paramètres</span>
                            </a>
                        </li>
                        <li class="nav-item border-top">
                            <a class="nav-link d-flex align-items-center" href="{{ route('store.show', $store->slug) }}" target="_blank">
                                <i class="bi bi-eye me-2"></i>
                                <span>Voir ma boutique</span>
                                <i class="bi bi-box-arrow-up-right ms-auto"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="{{ route('accueil') }}">
                                <i class="bi bi-house me-2"></i>
                                <span>Retour au site</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="col-md-9 col-lg-10">
            <div class="tab-content">
                <!-- Vue d'ensemble -->
                <div class="tab-pane fade" id="overview" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold blue-color mb-0">
                            <i class="bi bi-speedometer2 me-2"></i>Vue d'ensemble
                        </h2>
                        <div>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Boutique active
                            </span>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted mb-1 fs-7">Total Produits</p>
                                            <h3 class="fw-bold mb-0 stats-number" id="statTotalProducts">{{ $stats['total_products'] }}</h3>
                                        </div>
                                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                                            <i class="bi bi-box-seam text-primary" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted mb-1 fs-7">Commandes</p>
                                            <h3 class="fw-bold mb-0">{{ $stats['total_orders'] }}</h3>
                                        </div>
                                        <div class="bg-success bg-opacity-10 p-3 rounded">
                                            <i class="bi bi-bag text-success" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted mb-1 fs-7">Ventes Totales</p>
                                            <h3 class="fw-bold mb-0">{{ number_format($stats['total_sales'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></h3>
                                        </div>
                                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                                            <i class="bi bi-graph-up text-warning" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted mb-1 fs-7">Revenus ({{ 100 - $store->commission_rate }}%)</p>
                                            <h3 class="fw-bold mb-0 orange-color">{{ number_format($stats['total_revenue'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></h3>
                                        </div>
                                        <div class="orange-bg bg-opacity-10 p-3 rounded">
                                            <i class="bi bi-currency-dollar orange-color" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions rapides -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-lightning me-2"></i>Actions rapides
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <button class="btn btn-outline-primary w-100" onclick="showAddProductModal()">
                                        <i class="bi bi-plus-circle me-2"></i>Ajouter un produit
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-outline-success w-100" onclick="showTab('orders')">
                                        <i class="bi bi-bag me-2"></i>Voir les commandes
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-outline-warning w-100" onclick="showTab('settings')">
                                        <i class="bi bi-gear me-2"></i>Paramètres
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Commandes récentes -->
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock-history me-2"></i>Commandes récentes
                            </h5>
                            <a href="#" onclick="showTab('orders')" class="btn btn-sm btn-outline-primary">Voir tout</a>
                        </div>
                        <div class="card-body">
                            <div id="recentOrdersContainer">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Produits -->
                <div class="tab-pane fade" id="products" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold blue-color mb-0">
                            <i class="bi bi-box-seam me-2"></i>Mes Produits
                        </h2>
                        <button class="btn orange-bg text-white" onclick="showAddProductModal()">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter un produit
                        </button>
                    </div>

                    <!-- Liste des produits -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div id="productsContainer">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <p class="mt-2">Chargement des produits...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commandes -->
                <div class="tab-pane fade show active" id="orders" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold blue-color mb-0">
                            <i class="bi bi-bag me-2"></i>Commandes
                        </h2>
                        @if($stats['pending_orders'] > 0)
                            <span class="badge bg-danger fs-6">{{ $stats['pending_orders'] }} en attente</span>
                        @endif
                    </div>

                    <!-- Filtres et statistiques -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-funnel me-2"></i>Filtres et statistiques
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label small">Statut</label>
                                    <select class="form-select" id="orderStatusFilter">
                                        <option value="">Tous les statuts</option>
                                        <option value="pending">En attente</option>
                                        <option value="processing">En préparation</option>
                                        <option value="shipped">Expédiée</option>
                                        <option value="delivered">Livrée</option>
                                        <option value="cancelled">Annulée</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Date de</label>
                                    <input type="date" class="form-control" id="orderDateFrom">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Date à</label>
                                    <input type="date" class="form-control" id="orderDateTo">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Rechercher</label>
                                    <input type="text" class="form-control" id="orderSearchFilter" placeholder="N°, client, email...">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Trier par</label>
                                    <select class="form-select" id="orderSortFilter">
                                        <option value="created_at">Date (récent)</option>
                                        <option value="created_at_asc">Date (ancien)</option>
                                        <option value="total">Montant (élevé)</option>
                                        <option value="total_asc">Montant (faible)</option>
                                        <option value="status">Statut</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small">&nbsp;</label>
                                    <button class="btn btn-primary w-100" onclick="loadOrders()" title="Filtrer">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Statistiques rapides -->
                            <div class="row g-3 mt-3" id="orderStatsContainer">
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-primary" id="statTotalOrders">-</div>
                                        <small class="text-muted">Total</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-warning bg-opacity-10 rounded">
                                        <div class="fw-bold text-warning" id="statPendingOrders">-</div>
                                        <small class="text-muted">En attente</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-info bg-opacity-10 rounded">
                                        <div class="fw-bold text-info" id="statProcessingOrders">-</div>
                                        <small class="text-muted">En préparation</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-primary bg-opacity-10 rounded">
                                        <div class="fw-bold text-primary" id="statShippedOrders">-</div>
                                        <small class="text-muted">Expédiées</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-success bg-opacity-10 rounded">
                                        <div class="fw-bold text-success" id="statDeliveredOrders">-</div>
                                        <small class="text-muted">Livrées</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-danger bg-opacity-10 rounded">
                                        <div class="fw-bold text-danger" id="statCancelledOrders">-</div>
                                        <small class="text-muted">Annulées</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des commandes -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div id="ordersContainer">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <p class="mt-2">Chargement des commandes...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paramètres -->
                <div class="tab-pane fade" id="settings" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold blue-color mb-0">
                            <i class="bi bi-gear me-2"></i>Paramètres de la boutique
                        </h2>
                    </div>

                    <!-- Informations générales -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Informations générales</h5>
                        </div>
                        <div class="card-body">
                            <form id="updateStoreForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="store_name" class="form-label">Nom de la boutique</label>
                                        <input type="text" class="form-control" id="store_name" value="{{ $store->name }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="store_email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="store_email" value="{{ $store->email }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="store_phone" class="form-label">Téléphone</label>
                                        <input type="tel" class="form-control" id="store_phone" value="{{ $store->phone }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="store_category" class="form-label">Catégorie</label>
                                        <select class="form-select" id="store_category" required>
                                            @foreach($categories ?? [] as $cat)
                                                <option value="{{ $cat->id }}" {{ $store->category_id == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label for="store_description" class="form-label">Description</label>
                                        <textarea class="form-control" id="store_description" rows="4" required>{{ $store->description }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="store_address" class="form-label">Adresse</label>
                                        <input type="text" class="form-control" id="store_address" value="{{ $store->address }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="store_city" class="form-label">Ville</label>
                                        <input type="text" class="form-control" id="store_city" value="{{ $store->city }}">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn orange-bg text-white">
                                            <i class="bi bi-check-circle me-2"></i>Enregistrer les modifications
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Visuels -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Visuels</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Logo actuel</label>
                                    <div class="mb-3">
                                        <img id="storeLogoSettings" src="{{ $store->logo_url }}" alt="Logo" class="img-thumbnail" style="max-height: 150px;">
                                    </div>
                                    <input type="file" class="form-control" id="new_logo" accept="image/*">
                                    <button class="btn btn-sm btn-primary mt-2" onclick="uploadLogo()">
                                        <i class="bi bi-upload me-1"></i>Changer le logo
                                    </button>
                                    <small class="text-muted d-block mt-1">Format recommandé : PNG, JPG (max 5MB)</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bannière actuelle</label>
                                    <div class="mb-3">
                                        <img id="storeBannerSettings" src="{{ $store->banner_url }}" alt="Bannière" class="img-thumbnail" style="max-height: 150px; max-width: 100%;">
                                    </div>
                                    <input type="file" class="form-control" id="new_banner" accept="image/*">
                                    <button class="btn btn-sm btn-primary mt-2" onclick="uploadBanner()">
                                        <i class="bi bi-upload me-1"></i>Changer la bannière
                                    </button>
                                    <small class="text-muted d-block mt-1">Format recommandé : PNG, JPG (max 5MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Réseaux sociaux -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Réseaux sociaux</h5>
                        </div>
                        <div class="card-body">
                            <form id="updateSocialForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="facebook_url" class="form-label">
                                            <i class="bi bi-facebook text-primary me-2"></i>Facebook
                                        </label>
                                        <input type="url" class="form-control" id="facebook_url" 
                                               value="{{ $store->social_links['facebook'] ?? '' }}" 
                                               placeholder="https://facebook.com/votre-page">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="instagram_url" class="form-label">
                                            <i class="bi bi-instagram text-danger me-2"></i>Instagram
                                        </label>
                                        <input type="url" class="form-control" id="instagram_url" 
                                               value="{{ $store->social_links['instagram'] ?? '' }}" 
                                               placeholder="https://instagram.com/votre-compte">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="twitter_url" class="form-label">
                                            <i class="bi bi-twitter text-info me-2"></i>Twitter
                                        </label>
                                        <input type="url" class="form-control" id="twitter_url" 
                                               value="{{ $store->social_links['twitter'] ?? '' }}" 
                                               placeholder="https://twitter.com/votre-compte">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="website_url" class="form-label">
                                            <i class="bi bi-globe text-success me-2"></i>Site web
                                        </label>
                                        <input type="url" class="form-control" id="website_url" 
                                               value="{{ $store->social_links['website'] ?? '' }}" 
                                               placeholder="https://votre-site.com">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn orange-bg text-white">
                                            <i class="bi bi-check-circle me-2"></i>Enregistrer les liens
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Paramètres de sécurité -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Paramètres de sécurité</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Statut de la boutique</h6>
                                            <small class="text-muted">Votre boutique est actuellement 
                                                <span class="badge bg-success">{{ ucfirst($store->status) }}</span>
                                            </small>
                                        </div>
                                        <div>
                                            <i class="bi bi-shield-check text-success fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Vérification</h6>
                                            <small class="text-muted">
                                                @if($store->is_verified)
                                                    <span class="badge bg-success">Vérifiée</span>
                                                @else
                                                    <span class="badge bg-warning">En attente</span>
                                                @endif
                                            </small>
                                        </div>
                                        <div>
                                            <i class="bi bi-{{ $store->is_verified ? 'check-circle-fill' : 'clock' }} 
                                               text-{{ $store->is_verified ? 'success' : 'warning' }} fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Commission</h6>
                                            <small class="text-muted">Taux actuel : {{ $store->commission_rate }}%</small>
                                        </div>
                                        <div>
                                            <i class="bi bi-percent text-info fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Boutique officielle</h6>
                                            <small class="text-muted">
                                                @if($store->is_official)
                                                    <span class="badge bg-primary">Officielle</span>
                                                @else
                                                    <span class="badge bg-secondary">Standard</span>
                                                @endif
                                            </small>
                                        </div>
                                        <div>
                                            <i class="bi bi-{{ $store->is_official ? 'star-fill' : 'star' }} 
                                               text-{{ $store->is_official ? 'warning' : 'secondary' }} fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions dangereuses -->
                    <div class="card shadow-sm border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>Zone dangereuse
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <h6 class="text-danger">Désactiver la boutique</h6>
                                    <p class="text-muted small mb-3">Votre boutique ne sera plus visible par les clients.</p>
                                    <button class="btn btn-outline-warning btn-sm" onclick="toggleStoreStatus('suspended')">
                                        <i class="bi bi-pause-circle me-1"></i>Suspendre
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-danger">Supprimer la boutique</h6>
                                    <p class="text-muted small mb-3">Cette action est irréversible et supprimera tous vos produits.</p>
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteStore()">
                                        <i class="bi bi-trash me-1"></i>Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const storeId = {{ $store->id }};
const token = localStorage.getItem('auth_token');

// Fonction pour changer d'onglet
function showTab(tabName) {
    const tab = document.querySelector(`a[href="#${tabName}"]`);
    if (tab) {
        const bsTab = new bootstrap.Tab(tab);
        bsTab.show();
    }
}

// Charger les données au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    loadRecentOrders();
    
    // Test de la fonction de notification
    console.log('🔔 Test de showNotification disponible:', typeof showNotification);
    if (typeof showNotification === 'function') {
        console.log('✅ showNotification est disponible');
        
        // Test d'affichage d'une notification
        setTimeout(() => {
            console.log('🧪 Test d\'affichage d\'une notification...');
            showNotification('info', 'Dashboard chargé avec succès !');
        }, 1000);
    } else {
        console.error('❌ showNotification n\'est pas disponible');
    }
    
    // Charger les produits quand l'onglet est affiché
    document.querySelector('a[href="#products"]').addEventListener('shown.bs.tab', function() {
        loadProducts();
    });
    
    // Charger les commandes quand l'onglet est affiché
    document.querySelector('a[href="#orders"]').addEventListener('shown.bs.tab', function() {
        loadOrders();
        loadOrderStats();
    });
    
    // Auto-reload des commandes toutes les 30 secondes si l'onglet est actif
    setInterval(() => {
        const ordersTab = document.querySelector('a[href="#orders"]');
        if (ordersTab && ordersTab.classList.contains('active')) {
            checkForNewOrders(); // Vérifier les nouvelles commandes
        }
    }, 30000);
    
    // Vérifier les nouvelles commandes
    let lastOrderCount = 0;
    async function checkForNewOrders() {
        try {
            const response = await fetch('/api/store/orders/stats', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                const currentOrderCount = data.stats.total_orders || 0;
                
                // Si le nombre de commandes a augmenté
                if (currentOrderCount > lastOrderCount && lastOrderCount > 0) {
                    const newOrders = currentOrderCount - lastOrderCount;
                    showNotification('info', `🎉 ${newOrders} nouvelle${newOrders > 1 ? 's' : ''} commande${newOrders > 1 ? 's' : ''} reçue${newOrders > 1 ? 's' : ''} !`);
                    
                    // Mettre à jour le badge dans le menu
                    const ordersBadge = document.querySelector('a[href="#orders"] .badge');
                    if (ordersBadge) {
                        ordersBadge.textContent = currentOrderCount;
                        ordersBadge.className = 'badge bg-danger ms-auto';
                    }
                }
                
                lastOrderCount = currentOrderCount;
                loadOrderStats(); // Mettre à jour les statistiques affichées
            }
        } catch (error) {
            console.error('Erreur vérification nouvelles commandes:', error);
        }
    }

    // Activer l'onglet via paramètre d'URL (ex: ?tab=settings)
    try {
        const params = new URLSearchParams(window.location.search);
        const tabParam = params.get('tab');
        if (tabParam) {
            showTab(tabParam);
        }
    } catch (e) {
        console.warn('Paramètres URL non disponibles', e);
    }
});

// Charger les commandes récentes
async function loadRecentOrders() {
    const container = document.getElementById('recentOrdersContainer');
    
    try {
        const response = await fetch(`/api/store/recent-orders?limit=5`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.orders.length > 0) {
            container.innerHTML = data.orders.map(order => `
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${order.order_number}</h6>
                            <small class="text-muted">${new Date(order.created_at).toLocaleDateString('fr-FR')}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-${getStatusColor(order.status)}">${getStatusLabel(order.status)}</span>
                            <div class="fw-bold mt-1">${new Intl.NumberFormat('fr-FR').format(order.total)} FCFA</div>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="text-muted text-center py-3">Aucune commande pour le moment</p>';
        }
    } catch (error) {
        console.error('Erreur:', error);
        container.innerHTML = '<p class="text-danger text-center py-3">Erreur de chargement</p>';
    }
}

// Charger les produits
async function loadProducts() {
    const container = document.getElementById('productsContainer');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    
    try {
        const response = await fetch(`/api/store/products`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.products.length > 0) {
            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix</th>
                                <th>Stock</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.products.map(product => `
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="${product.image}" alt="${product.name}" class="me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                            <span>${product.name}</span>
                                        </div>
                                    </td>
                                    <td>${new Intl.NumberFormat('fr-FR').format(product.price)} FCFA</td>
                                    <td>${product.stock}</td>
                                    <td><span class="badge bg-${product.stock > 0 ? 'success' : 'danger'}">${product.stock > 0 ? 'En stock' : 'Rupture'}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editProduct(${product.id})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(${product.id})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-box-seam text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">Vous n'avez pas encore de produits</p>
                    <button class="btn orange-bg text-white" onclick="showAddProductModal()">
                        <i class="bi bi-plus-circle me-2"></i>Ajouter votre premier produit
                    </button>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erreur:', error);
        container.innerHTML = '<p class="text-danger text-center py-3">Erreur de chargement</p>';
    }
}

// Charger les commandes
async function loadOrders() {
    const container = document.getElementById('ordersContainer');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    
    try {
        // Récupérer les paramètres de filtrage
        const params = new URLSearchParams();
        const status = document.getElementById('orderStatusFilter').value;
        const dateFrom = document.getElementById('orderDateFrom').value;
        const dateTo = document.getElementById('orderDateTo').value;
        const search = document.getElementById('orderSearchFilter').value;
        const sort = document.getElementById('orderSortFilter').value;
        
        if (status) params.append('status', status);
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);
        if (search) params.append('search', search);
        if (sort) {
            const [sortBy, sortOrder] = sort.split('_');
            params.append('sort_by', sortBy);
            params.append('sort_order', sortOrder || 'desc');
        }
        
        const response = await fetch(`/api/store/orders?${params.toString()}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.orders.length > 0) {
            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>N° Commande</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Articles</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.orders.map(order => `
                                <tr>
                                    <td><strong>${order.order_number}</strong></td>
                                    <td>${new Date(order.created_at).toLocaleDateString('fr-FR')}</td>
                                    <td>
                                        <div>
                                            <strong>${order.shipping_name}</strong><br>
                                            <small class="text-muted">${order.shipping_email}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">${order.items_count} article${order.items_count > 1 ? 's' : ''}</span>
                                    </td>
                                    <td><strong>${new Intl.NumberFormat('fr-FR').format(order.total)} FCFA</strong></td>
                                    <td><span class="badge bg-${getStatusColor(order.status)}">${getStatusLabel(order.status)}</span></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder('${order.order_number}')" title="Voir les détails">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            ${getOrderActionButtons(order.status, order.order_number)}
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                ${data.pagination ? generatePagination(data.pagination) : ''}
            `;
        } else {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-bag text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">Aucune commande trouvée</p>
                    <button class="btn btn-outline-primary" onclick="clearFilters()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Effacer les filtres
                    </button>
                </div>
            `;
        }
        
        // Charger les statistiques des commandes
        loadOrderStats();
        
    } catch (error) {
        console.error('Erreur:', error);
        container.innerHTML = '<p class="text-danger text-center py-3">Erreur de chargement</p>';
    }
}

// Charger les statistiques des commandes
async function loadOrderStats() {
    try {
        const response = await fetch('/api/store/orders/stats', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const stats = data.stats;
            document.getElementById('statTotalOrders').textContent = stats.total_orders || 0;
            document.getElementById('statPendingOrders').textContent = stats.pending_orders || 0;
            document.getElementById('statProcessingOrders').textContent = stats.processing_orders || 0;
            document.getElementById('statShippedOrders').textContent = stats.shipped_orders || 0;
            document.getElementById('statDeliveredOrders').textContent = stats.delivered_orders || 0;
            document.getElementById('statCancelledOrders').textContent = stats.cancelled_orders || 0;
        }
    } catch (error) {
        console.error('Erreur chargement stats commandes:', error);
    }
}

// Générer la pagination
function generatePagination(pagination) {
    if (pagination.last_page <= 1) return '';
    
    let paginationHtml = '<nav aria-label="Pagination des commandes"><ul class="pagination justify-content-center">';
    
    // Bouton précédent
    if (pagination.current_page > 1) {
        paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadOrdersPage(${pagination.current_page - 1})">Précédent</a></li>`;
    }
    
    // Pages
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === pagination.current_page ? 'active' : '';
        paginationHtml += `<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="loadOrdersPage(${i})">${i}</a></li>`;
    }
    
    // Bouton suivant
    if (pagination.current_page < pagination.last_page) {
        paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadOrdersPage(${pagination.current_page + 1})">Suivant</a></li>`;
    }
    
    paginationHtml += '</ul></nav>';
    
    // Informations de pagination
    paginationHtml += `
        <div class="text-center mt-3">
            <small class="text-muted">
                Affichage de ${pagination.from || 0} à ${pagination.to || 0} sur ${pagination.total} commande${pagination.total > 1 ? 's' : ''}
            </small>
        </div>
    `;
    
    return paginationHtml;
}

// Charger une page spécifique
function loadOrdersPage(page) {
    // Ajouter le paramètre de page aux filtres existants
    const params = new URLSearchParams();
    const status = document.getElementById('orderStatusFilter').value;
    const dateFrom = document.getElementById('orderDateFrom').value;
    const dateTo = document.getElementById('orderDateTo').value;
    const search = document.getElementById('orderSearchFilter').value;
    const sort = document.getElementById('orderSortFilter').value;
    
    if (status) params.append('status', status);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    if (search) params.append('search', search);
    if (sort) {
        const [sortBy, sortOrder] = sort.split('_');
        params.append('sort_by', sortBy);
        params.append('sort_order', sortOrder || 'desc');
    }
    params.append('page', page);
    
    // Recharger avec la nouvelle page
    loadOrdersWithParams(params.toString());
}

// Charger les commandes avec des paramètres spécifiques
async function loadOrdersWithParams(params) {
    const container = document.getElementById('ordersContainer');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    
    try {
        const response = await fetch(`/api/store/orders?${params}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.orders.length > 0) {
            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>N° Commande</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Articles</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.orders.map(order => `
                                <tr>
                                    <td><strong>${order.order_number}</strong></td>
                                    <td>${new Date(order.created_at).toLocaleDateString('fr-FR')}</td>
                                    <td>
                                        <div>
                                            <strong>${order.shipping_name}</strong><br>
                                            <small class="text-muted">${order.shipping_email}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">${order.items_count} article${order.items_count > 1 ? 's' : ''}</span>
                                    </td>
                                    <td><strong>${new Intl.NumberFormat('fr-FR').format(order.total)} FCFA</strong></td>
                                    <td><span class="badge bg-${getStatusColor(order.status)}">${getStatusLabel(order.status)}</span></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder('${order.order_number}')" title="Voir les détails">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            ${getOrderActionButtons(order.status, order.order_number)}
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                ${data.pagination ? generatePagination(data.pagination) : ''}
            `;
        } else {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-bag text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">Aucune commande trouvée</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erreur:', error);
        container.innerHTML = '<p class="text-danger text-center py-3">Erreur de chargement</p>';
    }
}

// Effacer les filtres
function clearFilters() {
    document.getElementById('orderStatusFilter').value = '';
    document.getElementById('orderDateFrom').value = '';
    document.getElementById('orderDateTo').value = '';
    document.getElementById('orderSearchFilter').value = '';
    document.getElementById('orderSortFilter').value = 'created_at';
    loadOrders();
}

// Fonctions utilitaires
function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'processing': 'info',
        'shipped': 'primary',
        'delivered': 'success',
        'cancelled': 'danger'
    };
    return colors[status] || 'secondary';
}

function getStatusLabel(status) {
    const labels = {
        'pending': 'En attente',
        'processing': 'En préparation',
        'shipped': 'Expédiée',
        'delivered': 'Livrée',
        'cancelled': 'Annulée'
    };
    return labels[status] || status;
}

// Afficher le modal d'ajout de produit
function showAddProductModal() {
    // Créer le modal
    const modalHtml = `
        <div class="modal fade z-index-9x" id="addProductModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter un produit
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addProductForm" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Nom du produit <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Prix (FCFA) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="price" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Stock <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="stock" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Marque</label>
                                    <input type="text" class="form-control" name="brand">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Modèle</label>
                                    <input type="text" class="form-control" name="model">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Promotion (optionnel)</label>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label small">Prix promo (FCFA)</label>
                                            <input type="number" class="form-control" id="add_promo_price" name="promo_price" min="0" placeholder="Ex: 750000">
                                            <small class="text-muted">Prix de vente après réduction</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">OU Réduction (%)</label>
                                            <input type="number" class="form-control" id="add_discount_percent" name="discount" min="0" max="100" value="0" placeholder="Ex: 15">
                                            <small class="text-muted">Pourcentage de réduction</small>
                                        </div>
                                    </div>
                                    <small class="text-info d-block mt-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Remplissez soit le prix promo, soit le pourcentage. Le système calculera l'autre automatiquement.
                                    </small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="description" rows="4" required></textarea>
                                    <small class="text-muted">Minimum 50 caractères</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Garantie</label>
                                    <input type="text" class="form-control" name="warranty" placeholder="Ex: 12 mois">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tags (séparés par virgule)</label>
                                    <input type="text" class="form-control" name="tags" placeholder="Ex: nouveau, promo, tendance">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Images du produit</label>
                                    <input type="file" class="form-control" name="images[]" multiple accept="image/*">
                                    <small class="text-muted">Vous pouvez sélectionner plusieurs images (Max: 5 MB chacune)</small>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn orange-bg text-white" onclick="submitProduct()">
                            <i class="bi bi-check-circle me-2"></i>Ajouter le produit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Ajouter au DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('addProductModal'));
    
    // Gérer le calcul automatique entre prix promo et réduction
    setTimeout(() => {
        const priceInput = document.querySelector('#addProductForm input[name="price"]');
        const promoInput = document.getElementById('add_promo_price');
        const discountInput = document.getElementById('add_discount_percent');
        
        // Calcul automatique du pourcentage quand on saisit un prix promo
        promoInput.addEventListener('input', function() {
            if (this.value && priceInput.value) {
                const price = parseFloat(priceInput.value);
                const promo = parseFloat(this.value);
                const discount = ((price - promo) / price * 100).toFixed(2);
                discountInput.value = discount > 0 ? discount : 0;
            }
        });
        
        // Calcul automatique du prix promo quand on saisit un pourcentage
        discountInput.addEventListener('input', function() {
            if (this.value && priceInput.value) {
                const price = parseFloat(priceInput.value);
                const discount = parseFloat(this.value);
                const promo = price * (1 - discount / 100);
                promoInput.value = Math.round(promo);
            }
        });
        
        // Recalculer si le prix change
        priceInput.addEventListener('input', function() {
            if (discountInput.value && discountInput.value > 0) {
                const price = parseFloat(this.value);
                const discount = parseFloat(discountInput.value);
                const promo = price * (1 - discount / 100);
                promoInput.value = Math.round(promo);
            }
        });
    }, 100);
    
    // Supprimer le modal du DOM quand il est fermé
    document.getElementById('addProductModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
    
    modal.show();
}

// Soumettre le formulaire d'ajout de produit
async function submitProduct() {
    const form = document.getElementById('addProductForm');
    const formData = new FormData(form);
    
    // Validation de la description
    const description = formData.get('description');
    if (description.length < 50) {
        showNotification('danger', 'La description doit contenir au moins 50 caractères');
        return;
    }
    
    try {
        const response = await fetch('/api/store/products', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide();
            
            // Recharger la liste
            loadProducts();
            
            // Mettre à jour les statistiques
            updateStats();
        } else {
            showNotification('danger', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de l\'ajout du produit');
    }
}

// Éditer un produit
async function editProduct(id) {
    console.log('✏️ Édition du produit:', id);
    
    // Récupérer les données du produit
    try {
        const response = await fetch(`/api/store/products/${id}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error('Erreur lors de la récupération du produit');
        }
        
        const data = await response.json();
        console.log('📦 Données du produit:', data);
        
        if (data.success) {
            showEditProductModal(data.product);
        } else {
            showNotification('danger', data.message || 'Produit non trouvé');
        }
    } catch (error) {
        console.error('❌ Erreur:', error);
        showNotification('danger', 'Erreur lors du chargement du produit');
    }
}

// Afficher le modal d'édition
function showEditProductModal(product) {
    const modalHtml = `
        <div class="modal fade z-index-9x" id="editProductModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-pencil me-2"></i>Modifier le produit
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editProductForm">
                            <input type="hidden" name="product_id" value="${product.id}">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Nom du produit</label>
                                    <input type="text" class="form-control" name="name" value="${product.name}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Prix (FCFA)</label>
                                    <input type="number" class="form-control" name="price" value="${product.price}" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Stock</label>
                                    <input type="number" class="form-control" name="stock" value="${product.stock}" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Marque</label>
                                    <input type="text" class="form-control" name="brand" value="${product.brand || ''}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Modèle</label>
                                    <input type="text" class="form-control" name="model" value="${product.model || ''}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Promotion (optionnel)</label>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label small">Prix promo (FCFA)</label>
                                            <input type="number" class="form-control" id="edit_promo_price" name="promo_price" value="${product.old_price || ''}" min="0" placeholder="Ex: 750000">
                                            <small class="text-muted">Prix de vente après réduction</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">OU Réduction (%)</label>
                                            <input type="number" class="form-control" id="edit_discount_percent" name="discount" value="${product.discount || 0}" min="0" max="100" placeholder="Ex: 15">
                                            <small class="text-muted">Pourcentage de réduction</small>
                                        </div>
                                    </div>
                                    <small class="text-info d-block mt-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Le prix actuel est ${new Intl.NumberFormat('fr-FR').format(product.price)} FCFA
                                    </small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="4" required>${product.description}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Garantie</label>
                                    <input type="text" class="form-control" name="warranty" value="${product.warranty || ''}">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn orange-bg text-white" onclick="updateProduct(${product.id})">
                            <i class="bi bi-check-circle me-2"></i>Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
    
    // Gérer le calcul automatique entre prix promo et réduction
    setTimeout(() => {
        const priceInput = document.querySelector('#editProductForm input[name="price"]');
        const promoInput = document.getElementById('edit_promo_price');
        const discountInput = document.getElementById('edit_discount_percent');
        
        // Calcul automatique du pourcentage quand on saisit un prix promo
        promoInput.addEventListener('input', function() {
            if (this.value && priceInput.value) {
                const price = parseFloat(priceInput.value);
                const promo = parseFloat(this.value);
                const discount = ((price - promo) / price * 100).toFixed(2);
                discountInput.value = discount > 0 ? discount : 0;
            }
        });
        
        // Calcul automatique du prix promo quand on saisit un pourcentage
        discountInput.addEventListener('input', function() {
            if (this.value && priceInput.value) {
                const price = parseFloat(priceInput.value);
                const discount = parseFloat(this.value);
                const promo = price * (1 - discount / 100);
                promoInput.value = Math.round(promo);
            }
        });
        
        // Recalculer si le prix change
        priceInput.addEventListener('input', function() {
            if (discountInput.value && discountInput.value > 0) {
                const price = parseFloat(this.value);
                const discount = parseFloat(discountInput.value);
                const promo = price * (1 - discount / 100);
                promoInput.value = Math.round(promo);
            }
        });
    }, 100);
    
    document.getElementById('editProductModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
    
    modal.show();
}

// Mettre à jour un produit
async function updateProduct(productId) {
    const form = document.getElementById('editProductForm');
    const formData = new FormData(form);
    
    try {
        const response = await fetch(`/api/store/products/${productId}`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: formData.get('name'),
                description: formData.get('description'),
                price: formData.get('price'),
                promo_price: formData.get('promo_price'),
                stock: formData.get('stock'),
                discount: formData.get('discount'),
                brand: formData.get('brand'),
                model: formData.get('model'),
                warranty: formData.get('warranty')
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('editProductModal')).hide();
            loadProducts();
            updateStats();
        } else {
            showNotification('danger', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la mise à jour');
    }
}

// Supprimer un produit
async function deleteProduct(id) {
    if (!confirm('Voulez-vous vraiment supprimer ce produit ? Cette action est irréversible.')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/store/products/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message);
            loadProducts();
            updateStats();
        } else {
            showNotification('danger', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la suppression');
    }
}

function viewOrder(orderNumber) {
    // Rediriger vers la page de détails de la commande
    window.open(`/store/orders/${orderNumber}?token=${token}`, '_blank');
}

// Obtenir les boutons d'action pour les commandes
function getOrderActionButtons(status, orderNumber) {
    const buttons = [];
    
    if (status === 'pending') {
        buttons.push(`
            <button class="btn btn-sm btn-outline-success" onclick="quickAction('ship', '${orderNumber}')" title="Marquer comme expédiée">
                <i class="bi bi-truck"></i>
            </button>
        `);
        buttons.push(`
            <button class="btn btn-sm btn-outline-warning" onclick="quickAction('process', '${orderNumber}')" title="Marquer en préparation">
                <i class="bi bi-gear"></i>
            </button>
        `);
    } else if (status === 'processing') {
        buttons.push(`
            <button class="btn btn-sm btn-outline-success" onclick="quickAction('ship', '${orderNumber}')" title="Marquer comme expédiée">
                <i class="bi bi-truck"></i>
            </button>
        `);
    } else if (status === 'shipped') {
        buttons.push(`
            <button class="btn btn-sm btn-outline-success" onclick="quickAction('deliver', '${orderNumber}')" title="Marquer comme livrée">
                <i class="bi bi-check-circle"></i>
            </button>
        `);
    }
    
    return buttons.join('');
}

// Actions rapides sur les commandes
async function quickAction(action, orderNumber) {
    try {
        let endpoint = '';
        let message = '';
        
        switch (action) {
            case 'ship':
                endpoint = `/api/store/orders/${orderNumber}/ship`;
                message = 'Commande marquée comme expédiée';
                break;
            case 'process':
                endpoint = `/api/store/orders/${orderNumber}/status`;
                message = 'Commande mise en préparation';
                break;
            case 'deliver':
                endpoint = `/api/store/orders/${orderNumber}/status`;
                message = 'Commande marquée comme livrée';
                break;
        }
        
        const body = action === 'process' ? { status: 'processing' } : 
                    action === 'deliver' ? { status: 'delivered' } : {};
        
        const response = await fetch(endpoint, {
            method: action === 'ship' ? 'POST' : 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', message);
            loadOrders(); // Recharger la liste des commandes
        } else {
            showNotification('danger', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de l\'action');
    }
}

// Gestion des formulaires
document.addEventListener('DOMContentLoaded', function() {
    const updateStoreForm = document.getElementById('updateStoreForm');
    if (updateStoreForm) {
        updateStoreForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            await updateStoreInfo();
        });
    }
    
    const updateSocialForm = document.getElementById('updateSocialForm');
    if (updateSocialForm) {
        updateSocialForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            await updateSocialLinks();
        });
    }
});

// Mettre à jour les informations de la boutique
async function updateStoreInfo() {
    const formData = {
        name: document.getElementById('store_name').value,
        email: document.getElementById('store_email').value,
        phone: document.getElementById('store_phone').value,
        category_id: document.getElementById('store_category').value,
        description: document.getElementById('store_description').value,
        address: document.getElementById('store_address').value,
        city: document.getElementById('store_city').value,
    };

    try {
        showNotification('info', 'Mise à jour en cours...');
        
        const response = await fetch('/api/store/update', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', 'Boutique mise à jour avec succès !');
        } else {
            showNotification('danger', data.message || 'Erreur lors de la mise à jour');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la mise à jour');
    }
}

// Upload du logo
async function uploadLogo() {
    const fileInput = document.getElementById('new_logo');
    const file = fileInput.files[0];
    
    if (!file) {
        showNotification('warning', 'Veuillez sélectionner un fichier');
        return;
    }
    
    if (!file.type.startsWith('image/')) {
        showNotification('warning', 'Veuillez sélectionner une image');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) { // 5MB
        showNotification('warning', 'L\'image ne doit pas dépasser 5MB');
        return;
    }
    
    const formData = new FormData();
    formData.append('logo', file);
    
    try {
        showNotification('info', 'Upload du logo en cours...');
        
        const response = await fetch('/api/store/upload-logo', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
            if (data.success) {
                console.log('✅ Upload logo réussi, affichage notification...');
                showNotification('success', 'Logo mis à jour avec succès !');
                
                // Attendre un peu que l'image soit complètement écrite sur le serveur
                setTimeout(() => {
                    console.log('🔄 Début du rechargement des images logo...');
                    
                    // Cibler explicitement les images identifiées avec rechargement forcé
                    const logoSidebar = document.getElementById('storeLogoSidebar');
                    const logoSettings = document.getElementById('storeLogoSettings');
                    
                    console.log('🖼️ Mise à jour des images logo:', { 
                        logoSidebar: !!logoSidebar, 
                        logoSettings: !!logoSettings, 
                        newUrl: data.logo_url 
                    });
                    
                    if (logoSidebar) {
                        forceImageReload(logoSidebar, data.logo_url, 5, 2000); // 5 tentatives, 2s entre chaque
                    }
                    if (logoSettings) {
                        forceImageReload(logoSettings, data.logo_url, 5, 2000);
                    }
                }, 800); // Attendre un peu avant de commencer le rechargement
                
                // Vider le champ de fichier
                fileInput.value = '';
                
                // Rediriger vers l'onglet paramètres après un court délai
                setTimeout(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', 'settings');
                    window.location.href = url.toString();
                }, 1200);
            } else {
            showNotification('danger', data.message || 'Erreur lors de l\'upload');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de l\'upload');
    }
}

// Upload de la bannière
async function uploadBanner() {
    const fileInput = document.getElementById('new_banner');
    const file = fileInput.files[0];
    
    if (!file) {
        showNotification('warning', 'Veuillez sélectionner un fichier');
        return;
    }
    
    if (!file.type.startsWith('image/')) {
        showNotification('warning', 'Veuillez sélectionner une image');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) { // 5MB
        showNotification('warning', 'L\'image ne doit pas dépasser 5MB');
        return;
    }
    
    const formData = new FormData();
    formData.append('banner', file);
    
    try {
        showNotification('info', 'Upload de la bannière en cours...');
        
        const response = await fetch('/api/store/upload-banner', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
            if (data.success) {
                console.log('✅ Upload bannière réussi, affichage notification...');
                showNotification('success', 'Bannière mise à jour avec succès !');
                
                // Attendre un peu que l'image soit complètement écrite sur le serveur
                setTimeout(() => {
                    console.log('🔄 Début du rechargement de l\'image bannière...');
                    
                    // Cibler explicitement l'image identifiée avec rechargement forcé
                    const bannerSettings = document.getElementById('storeBannerSettings');
                    
                    console.log('🖼️ Mise à jour de l\'image bannière:', { 
                        bannerSettings: !!bannerSettings, 
                        newUrl: data.banner_url 
                    });
                    
                    if (bannerSettings) {
                        forceImageReload(bannerSettings, data.banner_url, 5, 2000); // 5 tentatives, 2s entre chaque
                    }
                }, 800); // Attendre un peu avant de commencer le rechargement
                
                // Vider le champ de fichier
                fileInput.value = '';

                // Rediriger vers l'onglet paramètres après un court délai
                setTimeout(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', 'settings');
                    window.location.href = url.toString();
                }, 1200);
            } else {
            showNotification('danger', data.message || 'Erreur lors de l\'upload');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de l\'upload');
    }
}

// Mettre à jour les statistiques
async function updateStats() {
    try {
        const response = await fetch('/api/store/stats', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Mettre à jour la carte "Total Produits"
            const totalProductsEl = document.getElementById('statTotalProducts');
            if (totalProductsEl) {
                totalProductsEl.textContent = data.stats.total_products;
            }
            
            // Mettre à jour le badge dans le menu "Produits"
            const productsBadge = document.querySelector('a[href="#products"] .badge');
            if (productsBadge) {
                productsBadge.textContent = data.stats.total_products;
            }
            
            console.log('✅ Statistiques mises à jour:', data.stats.total_products, 'produits');
        }
    } catch (error) {
        console.error('Erreur mise à jour stats:', error);
    }
}

// Mettre à jour les liens sociaux
async function updateSocialLinks() {
    const formData = {
        facebook: document.getElementById('facebook_url').value,
        instagram: document.getElementById('instagram_url').value,
        twitter: document.getElementById('twitter_url').value,
        website: document.getElementById('website_url').value,
    };

    try {
        showNotification('info', 'Mise à jour des liens sociaux...');
        
        const response = await fetch('/api/store/update-social', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', 'Liens sociaux mis à jour avec succès !');
        } else {
            showNotification('danger', data.message || 'Erreur lors de la mise à jour');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la mise à jour');
    }
}

// Basculer le statut de la boutique
async function toggleStoreStatus(status) {
    const action = status === 'suspended' ? 'suspendre' : (status === 'active' ? 'activer' : 'modifier le statut');
    
    if (!confirm(`Êtes-vous sûr de vouloir ${action} votre boutique ?`)) {
        return;
    }

    try {
        showNotification('info', `${action.charAt(0).toUpperCase() + action.slice(1)} la boutique...`);
        
        const response = await fetch('/api/store/toggle-status', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', `Boutique ${action}ée avec succès !`);
            // Recharger la page pour mettre à jour l'interface
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('danger', data.message || 'Erreur lors de l\'opération');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de l\'opération');
    }
}

// Supprimer la boutique
async function deleteStore() {
    const confirmText = 'SUPPRIMER';
    const userInput = prompt(`ATTENTION: Cette action est irréversible et supprimera définitivement votre boutique et tous vos produits.\n\nPour confirmer, tapez "${confirmText}" :`);
    
    if (userInput !== confirmText) {
        showNotification('info', 'Suppression annulée');
        return;
    }

    try {
        showNotification('warning', 'Suppression de la boutique en cours...');
        
        const response = await fetch('/api/store/delete', {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', 'Boutique supprimée avec succès');
            // Rediriger vers la page d'accueil
            setTimeout(() => {
                window.location.href = '/';
            }, 2000);
        } else {
            showNotification('danger', data.message || 'Erreur lors de la suppression');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la suppression');
    }
}

// Fonction pour forcer le rechargement d'une image avec délai et vérification
function forceImageReload(imgElement, newSrc, maxRetries = 3, delay = 1000) {
    if (!imgElement) return;
    
    let retryCount = 0;
    
    // Vérifier d'abord si l'URL est accessible
    function checkUrlAccessibility(url, callback) {
        fetch(url, { 
            method: 'HEAD',
            mode: 'no-cors' // Permet de contourner les problèmes CORS
        })
        .then(() => {
            console.log('✅ URL accessible:', url);
            callback(true);
        })
        .catch(error => {
            console.warn('⚠️ URL non accessible:', url, error);
            callback(false);
        });
    }
    
    function attemptReload() {
        console.log(`🔄 Tentative ${retryCount + 1}/${maxRetries} de rechargement de l'image:`, newSrc);
        
        // Créer un nouvel élément image pour forcer le téléchargement
        const tempImg = new Image();
        
        tempImg.onload = function() {
            console.log('✅ Image chargée avec succès, mise à jour de l\'élément');
            imgElement.src = newSrc;
            imgElement.style.opacity = '0.8';
            setTimeout(() => {
                imgElement.style.opacity = '1';
            }, 100);
        };
        
        tempImg.onerror = function() {
            retryCount++;
            console.warn(`❌ Erreur de chargement de l'image (tentative ${retryCount}/${maxRetries}):`, newSrc);
            
            // Tester l'accessibilité de l'URL
            checkUrlAccessibility(newSrc, (isAccessible) => {
                if (!isAccessible) {
                    console.error('🚫 URL non accessible, abandon du rechargement');
                    showNotification('warning', 'L\'image n\'est pas accessible sur le serveur. Vérifiez les permissions.');
                    return;
                }
                
                if (retryCount < maxRetries) {
                    console.log(`⏳ Nouvelle tentative dans ${delay}ms...`);
                    setTimeout(attemptReload, delay);
                } else {
                    console.error('💥 Échec définitif du rechargement de l\'image');
                    showNotification('warning', 'Erreur de chargement de l\'image après plusieurs tentatives. Vérifiez les permissions du serveur.');
                }
            });
        };
        
        // Ajouter un timestamp pour forcer le rechargement
        const srcWithTimestamp = newSrc + (newSrc.includes('?') ? '&' : '?') + 't=' + Date.now();
        tempImg.src = srcWithTimestamp;
    }
    
    // Délai initial avant la première tentative
    setTimeout(attemptReload, 500);
}

// Fonction pour forcer le rechargement des images
function refreshImages(imageType, newUrl) {
    const timestamp = '?t=' + Date.now();
    
    if (imageType === 'logo') {
        // Mettre à jour uniquement les images de logo de la boutique (pas le logo principal du site)
        const logoSelectors = [
            'img[alt="Logo"]',
            'img[alt="' + '{{ $store->name }}' + '"]',
            '.card-body .img-fluid.rounded-circle'
        ];
        
        logoSelectors.forEach(selector => {
            // Limiter la recherche au contexte du dashboard uniquement
            const dashboardContainer = document.querySelector('.store-dashboard');
            const searchScope = dashboardContainer || document;
            const images = searchScope.querySelectorAll(selector);
            
            images.forEach(img => {
                // Exclure le logo principal du site (qui contient 'logo.png')
                if (!img.src.includes('logo.png') && 
                    (img.src.includes('logo') || img.alt.includes('Logo') || img.alt.includes('{{ $store->name }}'))) {
                    // Forcer le rechargement en vidant d'abord le src
                    const currentSrc = img.src;
                    img.src = '';
                    setTimeout(() => {
                        img.src = newUrl + timestamp;
                    }, 50);
                }
            });
        });
    } else if (imageType === 'banner') {
        // Mettre à jour toutes les images de bannière
        const bannerSelectors = [
            'img[alt="Bannière"]',
            'img[src*="banner"]'
        ];
        
        bannerSelectors.forEach(selector => {
            // Limiter la recherche au contexte du dashboard uniquement
            const dashboardContainer = document.querySelector('.store-dashboard');
            const searchScope = dashboardContainer || document;
            const images = searchScope.querySelectorAll(selector);
            
            images.forEach(img => {
                if (img.src.includes('banner') || img.alt.includes('Bannière')) {
                    // Forcer le rechargement en vidant d'abord le src
                    img.src = '';
                    setTimeout(() => {
                        img.src = newUrl + timestamp;
                    }, 50);
                }
            });
        });
    }
}

// Fonction de notification spécifique au dashboard
function showNotification(type, message) {
    console.log('🔔 showNotification appelée:', type, message);
    
    const toastElement = document.getElementById('dashboardNotificationToast');
    const toastIcon = document.getElementById('dashboardToastIcon');
    const toastTitle = document.getElementById('dashboardToastTitle');
    const toastBody = document.getElementById('dashboardToastBody');
    
    if (!toastElement || !toastIcon || !toastTitle || !toastBody) {
        console.error('❌ Éléments toast du dashboard non trouvés');
        alert(message);
        return;
    }
    
    console.log('✅ Éléments toast trouvés, affichage de la notification');
    
    // Configuration selon le type
    const configs = {
        'success': {
            icon: 'bi-check-circle-fill',
            iconColor: 'text-success',
            title: 'Succès',
            bgClass: 'bg-success-subtle'
        },
        'error': {
            icon: 'bi-exclamation-circle-fill',
            iconColor: 'text-danger',
            title: 'Erreur',
            bgClass: 'bg-danger-subtle'
        },
        'warning': {
            icon: 'bi-exclamation-triangle-fill',
            iconColor: 'text-warning',
            title: 'Attention',
            bgClass: 'bg-warning-subtle'
        },
        'info': {
            icon: 'bi-info-circle-fill',
            iconColor: 'text-info',
            title: 'Information',
            bgClass: 'bg-info-subtle'
        }
    };
    
    const config = configs[type] || configs['info'];
    
    // Mettre à jour le contenu
    toastIcon.className = `bi ${config.icon} ${config.iconColor}`;
    toastTitle.textContent = config.title;
    toastBody.textContent = message;
    
    // Appliquer le style de fond
    toastElement.className = `toast ${config.bgClass}`;
    
    console.log('🎨 Toast configuré, affichage...');
    
    // Afficher le toast
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: type === 'error' ? 5000 : 3000
    });
    
    toast.show();
    console.log('🚀 Toast affiché !');
}
</script>
@endsection

