@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/store.css') }}">
<div class="store-public">
<!-- Bannière de la boutique -->
<div class="position-relative" style="height: 300px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    @if($store->banner)
        <img src="{{ asset('storage/' . $store->banner) }}" alt="{{ $store->name }}" class="w-100 h-100" style="object-fit: cover;">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.3);"></div>
    @endif
    
    <div class="container position-absolute bottom-0 start-50 translate-middle-x pb-4">
        <div class="row align-items-end">
            <div class="col-md-8">
                <div class="d-flex align-items-end">
                    <!-- Logo de la boutique -->
                    <div class="bg-white rounded-circle p-2 shadow" style="width: 120px; height: 120px; margin-bottom: -30px;">
                        @if($store->logo)
                            <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}" class="w-100 h-100 rounded-circle" style="object-fit: cover;">
                        @else
                            <div class="w-100 h-100 rounded-circle bg-light d-flex align-items-center justify-content-center">
                                <i class="bi bi-shop orange-color" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Informations de la boutique -->
                    <div class="ms-4 text-white">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <h1 class="fw-bold mb-0">{{ $store->name }}</h1>
                            @if($store->is_verified)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle-fill me-1"></i>Vérifiée
                                </span>
                            @endif
                            @if($store->is_official)
                                <span class="badge orange-bg">
                                    <i class="bi bi-award-fill me-1"></i>Officielle
                                </span>
                            @endif
                        </div>
                        <p class="mb-2">
                            <i class="bi bi-tag-fill me-2"></i>{{ $store->category->name }}
                            @if($store->subcategory)
                                <i class="bi bi-chevron-right mx-1"></i>{{ $store->subcategory->name }}
                            @endif
                        </p>
                        <div class="d-flex align-items-center gap-3">
                            <span>
                                <i class="bi bi-box-seam me-1"></i>{{ $store->total_products }} produits
                            </span>
                            <span>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-solid fa-star {{ $i <= floor($store->rating) ? 'text-warning' : 'text-white-50' }}"></i>
                                @endfor
                                <span class="ms-1">({{ $store->reviews_count }})</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Boutons d'action -->
            <div class="col-md-4 text-end pb-2">
                <button class="btn btn-light me-2" onclick="followStore()">
                    <i class="bi bi-heart me-2"></i>Suivre
                </button>
                <button class="btn btn-outline-light" onclick="shareStore()">
                    <i class="bi bi-share me-2"></i>Partager
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Contenu de la boutique -->
<div class="container my-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-info-circle me-2"></i>À propos
                    </h5>
                    <p class="text-muted">{{ $store->description }}</p>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <h6 class="fw-bold mb-2">
                            <i class="bi bi-geo-alt me-2 orange-color"></i>Localisation
                        </h6>
                        <p class="text-muted small mb-1">{{ $store->city ?? 'Non spécifiée' }}</p>
                        @if($store->address)
                            <p class="text-muted small">{{ $store->address }}</p>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="fw-bold mb-2">
                            <i class="bi bi-telephone me-2 orange-color"></i>Contact
                        </h6>
                        <p class="text-muted small mb-1">
                            <i class="bi bi-envelope me-1"></i>{{ $store->email }}
                        </p>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-phone me-1"></i>{{ $store->phone }}
                        </p>
                    </div>
                    
                    @if($store->social_links && count($store->social_links) > 0)
                        <hr>
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-share me-2 orange-color"></i>Réseaux sociaux
                        </h6>
                        <div class="d-flex gap-2">
                            @if(isset($store->social_links['facebook']))
                                <a href="{{ $store->social_links['facebook'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-facebook"></i>
                                </a>
                            @endif
                            @if(isset($store->social_links['instagram']))
                                <a href="{{ $store->social_links['instagram'] }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-instagram"></i>
                                </a>
                            @endif
                            @if(isset($store->social_links['twitter']))
                                <a href="{{ $store->social_links['twitter'] }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-twitter"></i>
                                </a>
                            @endif
                            @if(isset($store->social_links['website']))
                                <a href="{{ $store->social_links['website'] }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-globe"></i>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Filtres -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-funnel me-2"></i>Filtres
                    </h5>
                    
                    <!-- Catégories -->
                    <div class="mb-3">
                        <h6 class="fw-bold mb-2">Catégories</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="categoryFilter" id="allCategories" value="" checked>
                            <label class="form-check-label" for="allCategories">
                                Toutes les catégories
                            </label>
                        </div>
                        <!-- Les catégories seront ajoutées dynamiquement -->
                    </div>
                    
                    <!-- Prix -->
                    <div class="mb-3">
                        <h6 class="fw-bold mb-2">Prix</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" class="form-control form-control-sm" id="minPrice" placeholder="Min">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control form-control-sm" id="maxPrice" placeholder="Max">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Disponibilité -->
                    <div class="mb-3">
                        <h6 class="fw-bold mb-2">Disponibilité</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="inStock">
                            <label class="form-check-label" for="inStock">
                                En stock uniquement
                            </label>
                        </div>
                    </div>
                    
                    <button class="btn orange-bg text-white w-100" onclick="applyFilters()">
                        <i class="bi bi-search me-2"></i>Appliquer
                    </button>
                    <button class="btn btn-outline-secondary w-100 mt-2" onclick="resetFilters()">
                        <i class="bi bi-x-circle me-2"></i>Réinitialiser
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Produits -->
        <div class="col-md-9">
            <!-- Barre de tri -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0">Produits de la boutique</h4>
                    <p class="text-muted mb-0" id="productsCount">{{ $products->total() }} produits</p>
                </div>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="sortBy" onchange="applySort()">
                        <option value="newest">Plus récent</option>
                        <option value="price_asc">Prix croissant</option>
                        <option value="price_desc">Prix décroissant</option>
                        <option value="popular">Popularité</option>
                    </select>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary active" id="gridView" onclick="switchView('grid')">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="listView" onclick="switchView('list')">
                            <i class="bi bi-list"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Grille de produits -->
            <div id="productsContainer" class="row g-3">
                @forelse($products as $product)
                    <div class="col-md-4 col-lg-3 product-item">
                        @php
                            // Préparer l'image pour le composant
                            if ($product->images && is_array($product->images) && count($product->images) > 0) {
                                $firstImage = $product->images[0];
                                
                                // Vérifier si c'est un chemin storage (commence par "products/")
                                if (strpos($firstImage, 'products/') === 0) {
                                    $product->image = 'storage/' . $firstImage;
                                }
                                // Sinon, utiliser tel quel
                                else {
                                    $product->image = filter_var($firstImage, FILTER_VALIDATE_URL) 
                                        ? $firstImage 
                                        : 'images/' . $firstImage;
                                }
                            } else {
                                $product->image = 'images/produit.jpg';
                            }
                        @endphp
                        @include('components.product-card', ['product' => $product])
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 text-muted">Aucun produit disponible</h4>
                            <p class="text-muted">Cette boutique n'a pas encore ajouté de produits.</p>
                        </div>
                    </div>
                @endforelse
            </div>
            
            <!-- Pagination -->
            @if($products->hasPages())
                <div class="d-flex justify-content-center mt-5">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
const storeSlug = '{{ $store->slug }}';
let currentView = 'grid';

// Fonction pour changer de vue (grille/liste)
function switchView(view) {
    currentView = view;
    const container = document.getElementById('productsContainer');
    const productItems = container.querySelectorAll('.product-item');
    
    if (view === 'list') {
        container.classList.remove('row');
        container.classList.add('d-flex', 'flex-column', 'gap-3');
        productItems.forEach(item => {
            item.classList.add('list-view');
            item.classList.remove('col-md-4', 'col-lg-3');
        });
        document.getElementById('gridView').classList.remove('active');
        document.getElementById('listView').classList.add('active');
    } else {
        container.classList.add('row');
        container.classList.remove('d-flex', 'flex-column', 'gap-3');
        productItems.forEach(item => {
            item.classList.remove('list-view');
            item.classList.add('col-md-4', 'col-lg-3');
        });
        document.getElementById('gridView').classList.add('active');
        document.getElementById('listView').classList.remove('active');
    }
}

// Fonction pour appliquer les filtres
function applyFilters() {
    const minPrice = document.getElementById('minPrice').value;
    const maxPrice = document.getElementById('maxPrice').value;
    const inStock = document.getElementById('inStock').checked;
    
    // Construire l'URL avec les filtres
    let url = new URL(window.location.href);
    if (minPrice) url.searchParams.set('min_price', minPrice);
    if (maxPrice) url.searchParams.set('max_price', maxPrice);
    if (inStock) url.searchParams.set('in_stock', '1');
    
    window.location.href = url.toString();
}

// Fonction pour réinitialiser les filtres
function resetFilters() {
    window.location.href = window.location.pathname;
}

// Fonction pour appliquer le tri
function applySort() {
    const sortBy = document.getElementById('sortBy').value;
    let url = new URL(window.location.href);
    url.searchParams.set('sort', sortBy);
    window.location.href = url.toString();
}

// Fonction pour suivre la boutique
function followStore() {
    const token = localStorage.getItem('auth_token');
    if (!token) {
        if (confirm('Vous devez être connecté pour suivre une boutique.\n\nVoulez-vous vous connecter ?')) {
            window.location.href = '/authentification';
        }
        return;
    }
    
    // API à implémenter
    alert('Fonctionnalité en cours de développement');
}

// Fonction pour partager la boutique
function shareStore() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $store->name }}',
            text: '{{ $store->description }}',
            url: window.location.href
        }).catch(console.error);
    } else {
        // Fallback: copier le lien
        navigator.clipboard.writeText(window.location.href);
        showNotification('success', 'Lien copié dans le presse-papiers !');
    }
}

// Fonction pour ajouter aux favoris
function addToFavorite(productId) {
    const token = localStorage.getItem('auth_token');
    if (!token) {
        if (confirm('Vous devez être connecté pour ajouter aux favoris.\n\nVoulez-vous vous connecter ?')) {
            window.location.href = '/authentification';
        }
        return;
    }
    
    // Utiliser la fonction globale si disponible
    if (typeof window.toggleFavorite === 'function') {
        window.toggleFavorite(productId);
    } else {
        alert('Fonctionnalité en cours de développement');
    }
}

// Fonction de notification
function showNotification(type, message) {
    if (typeof window.showNotification === 'function') {
        window.showNotification(type, message);
    } else {
        alert(message);
    }
}
</script>
</div>
@endsection

