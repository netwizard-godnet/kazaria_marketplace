@extends('admin.layouts.app')
@section('title', 'Ajouter un produit')
@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Ajouter un produit</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="{{ route('admin.products.index') }}">Produits</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Ajouter</span></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informations du produit</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Nom du produit -->
                        <div class="form-group">
                            <label for="name">Nom du produit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Prix et Stock -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="price">Prix normal (FCFA) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" min="0" step="0.01" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="promo_price">Prix promo (FCFA)</label>
                                    <input type="number" class="form-control @error('promo_price') is-invalid @enderror" id="promo_price" name="promo_price" value="{{ old('promo_price') }}" min="0" step="0.01" placeholder="Sera calculé automatiquement">
                                    @error('promo_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Synchronisé avec la réduction</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="discount_percentage">Réduction (%)</label>
                                    <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror" id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage') }}" min="0" max="100" step="0.01" placeholder="Ex: 10">
                                    @error('discount_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Calcule automatiquement le prix promo</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="stock">Stock <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', 0) }}" min="0" required>
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Catégorie et Sous-catégorie -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">Catégorie <span class="text-danger">*</span></label>
                                    <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                        <option value="">Sélectionner une catégorie</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subcategory_id">Sous-catégorie</label>
                                    <select class="form-control @error('subcategory_id') is-invalid @enderror" id="subcategory_id" name="subcategory_id">
                                        <option value="">Sélectionner d'abord une catégorie</option>
                                    </select>
                                    @error('subcategory_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Boutique / Vendeur -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="store_id">Boutique / Vendeur (optionnel)</label>
                                    <select class="form-control @error('store_id') is-invalid @enderror" id="store_id" name="store_id">
                                        <option value="">Aucune boutique (produit global Kazaria)</option>
                                        @foreach($stores as $store)
                                            <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                                {{ $store->name }} @if($store->user) - {{ $store->user->nom }} {{ $store->user->prenoms }} @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('store_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Lier ce produit à une boutique pour qu'il apparaisse dans les produits du vendeur. Si laissé vide, le produit sera un produit global Kazaria.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Statut -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Statut <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approuvé</option>
                                        <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejeté</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Produit actif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Marque, Modèle, Garantie -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="brand">Marque</label>
                                    <input type="text" class="form-control @error('brand') is-invalid @enderror" id="brand" name="brand" value="{{ old('brand') }}">
                                    @error('brand')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="model">Modèle</label>
                                    <input type="text" class="form-control @error('model') is-invalid @enderror" id="model" name="model" value="{{ old('model') }}">
                                    @error('model')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="warranty">Garantie</label>
                                    <input type="text" class="form-control @error('warranty') is-invalid @enderror" id="warranty" name="warranty" value="{{ old('warranty') }}" placeholder="Ex: 1 an, 2 ans">
                                    @error('warranty')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="form-group">
                            <label for="tags">Tags (séparés par des virgules)</label>
                            <input type="text" class="form-control @error('tags') is-invalid @enderror" id="tags" name="tags" value="{{ old('tags') }}" placeholder="Ex: nouveau, promo, tendance">
                            @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Les tags seront stockés séparément</small>
                        </div>

                        <!-- SEO -->
                        <div class="form-group">
                            <label for="meta_description">Meta Description (SEO)</label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" rows="2" maxlength="500">{{ old('meta_description') }}</textarea>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum 500 caractères</small>
                        </div>

                        <div class="form-group">
                            <label for="meta_keywords">Meta Keywords (SEO)</label>
                            <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}" placeholder="Ex: produit, qualité, prix">
                            @error('meta_keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Section Images -->
                        <div class="form-group">
                            <label>Images du produit <span class="text-danger">*</span></label>
                            <input type="file" name="images[]" id="images" class="form-control @error('images.*') is-invalid @enderror" multiple accept="image/*">
                            @error('images.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Vous pouvez sélectionner plusieurs images. Formats acceptés: JPG, PNG, GIF. Taille max: 5MB par image.</small>
                            
                            <!-- Aperçu des images -->
                            <div id="imagePreview" class="row mt-3" style="display: none;"></div>
                            <!-- Champ caché pour stocker l'index de l'image principale -->
                            <input type="hidden" name="main_image_index" id="main_image_index" value="0">
                        </div>

                        <!-- Section Attributs -->
                        @if($attributes && $attributes->count() > 0)
                        <div class="form-group">
                            <label>Attributs du produit</label>
                            <div class="row">
                                @foreach($attributes as $attribute)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">{{ $attribute->name }}</h6>
                                            <small class="text-muted">{{ ucfirst($attribute->type) }}</small>
                                        </div>
                                        <div class="card-body">
                                            @if($attribute->type === 'radio')
                                                @foreach($attribute->attributeValues as $value)
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="radio" 
                                                           name="attributes[{{ $attribute->id }}]" 
                                                           value="{{ $value->id }}" 
                                                           id="attr_{{ $attribute->id }}_{{ $value->id }}">
                                                    <label class="form-check-label" for="attr_{{ $attribute->id }}_{{ $value->id }}">
                                                        {{ $value->value }}
                                                    </label>
                                                </div>
                                                @endforeach
                                            @else
                                                @foreach($attribute->attributeValues as $value)
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="attributes[{{ $attribute->id }}][]" 
                                                           value="{{ $value->id }}" 
                                                           id="attr_{{ $attribute->id }}_{{ $value->id }}">
                                                    <label class="form-check-label" for="attr_{{ $attribute->id }}_{{ $value->id }}">
                                                        {{ $value->value }}
                                                    </label>
                                                </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Aucun attribut disponible. <a href="{{ route('admin.attributes.index') }}">Gérer les attributs</a> pour pouvoir les assigner aux produits.
                        </div>
                        @endif

                        <!-- Boutons -->
                        <div class="card-action">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Charger les sous-catégories lors de la sélection d'une catégorie
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    if (!categorySelect) {
        console.error('Champ category_id non trouvé');
        return;
    }
    
    categorySelect.addEventListener('change', function() {
    const categoryId = this.value;
    const subcategorySelect = document.getElementById('subcategory_id');
    
    // Vider les options existantes
    subcategorySelect.innerHTML = '<option value="">Chargement...</option>';
    subcategorySelect.disabled = true;
    
    if (!categoryId) {
        subcategorySelect.innerHTML = '<option value="">Sélectionner d\'abord une catégorie</option>';
        return;
    }
    
    // Charger les sous-catégories via AJAX
    fetch(`/api/categories/${categoryId}/subcategories`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        subcategorySelect.innerHTML = '<option value="">Sélectionner une sous-catégorie</option>';
        
        if (data.success && data.subcategories && data.subcategories.length > 0) {
            data.subcategories.forEach(subcategory => {
                const option = document.createElement('option');
                option.value = subcategory.id;
                option.textContent = subcategory.name;
                subcategorySelect.appendChild(option);
            });
        } else {
            subcategorySelect.innerHTML = '<option value="">Aucune sous-catégorie disponible</option>';
        }
        
        subcategorySelect.disabled = false;
    })
    .catch(error => {
        console.error('Erreur lors du chargement des sous-catégories:', error);
        subcategorySelect.innerHTML = '<option value="">Erreur de chargement</option>';
        subcategorySelect.disabled = false;
    });
    });
});

// Synchronisation Prix / Prix Promo / Réduction
(function() {
    function initPriceSync() {
        const priceInput = document.getElementById('price');
        const promoPriceInput = document.getElementById('promo_price');
        const discountInput = document.getElementById('discount_percentage');
        
        if (!priceInput || !promoPriceInput || !discountInput) {
            console.warn('⚠️ Champs prix/promo/réduction non trouvés, nouvelle tentative...');
            setTimeout(initPriceSync, 100);
            return;
        }
        
        console.log('✅ Champs trouvés, initialisation de la synchronisation...');
        
        let isSyncing = false;
        
        const parseValue = (input) => {
            if (!input || !input.value || input.value.toString().trim() === '') {
                return null;
            }
            const value = parseFloat(input.value.toString().replace(',', '.'));
            return isNaN(value) ? null : value;
        };
        
        const formatPrice = (value) => {
            return Math.round(value * 100) / 100;
        };
        
        // Calculer la réduction depuis le prix promo
        const calculateDiscount = () => {
            if (isSyncing) return;
            isSyncing = true;
            
            const price = parseValue(priceInput);
            const promoPrice = parseValue(promoPriceInput);
            
            if (price && price > 0 && promoPrice && promoPrice > 0 && promoPrice < price) {
                const discount = ((price - promoPrice) / price) * 100;
                discountInput.value = formatPrice(discount);
            } else if (!promoPrice || promoPrice <= 0) {
                discountInput.value = '';
            }
            
            isSyncing = false;
        };
        
        // Calculer le prix promo depuis la réduction
        const calculatePromoPrice = () => {
            if (isSyncing) return;
            isSyncing = true;
            
            const price = parseValue(priceInput);
            const discount = parseValue(discountInput);
            
            if (price && price > 0 && discount && discount > 0 && discount < 100) {
                const promoPrice = price * (1 - discount / 100);
                promoPriceInput.value = formatPrice(promoPrice);
            } else if (!discount || discount <= 0) {
                promoPriceInput.value = '';
            }
            
            isSyncing = false;
        };
        
        // Synchroniser depuis le prix normal
        const syncFromPrice = () => {
            if (isSyncing) return;
            const discount = parseValue(discountInput);
            if (discount && discount > 0 && discount < 100) {
                calculatePromoPrice();
            } else {
                const promoPrice = parseValue(promoPriceInput);
                if (promoPrice && promoPrice > 0) {
                    calculateDiscount();
                }
            }
        };
        
        // Événements pour le prix normal
        priceInput.addEventListener('input', syncFromPrice);
        priceInput.addEventListener('blur', syncFromPrice);
        
        // Événements pour le prix promo
        promoPriceInput.addEventListener('input', calculateDiscount);
        promoPriceInput.addEventListener('blur', calculateDiscount);
        
        // Événements pour la réduction
        discountInput.addEventListener('input', calculatePromoPrice);
        discountInput.addEventListener('blur', calculatePromoPrice);
        
        console.log('✅ Synchronisation prix promo/réduction activée');
    }
    
    // Initialiser quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPriceSync);
    } else {
        // DOM déjà chargé
        initPriceSync();
    }
})();

// Gestion des images avec sélection de l'image principale
let selectedFiles = [];
let mainImageIndex = 0; // Index de l'image principale (0 par défaut)

// Aperçu des images avant upload avec sélection de l'image principale
document.addEventListener('DOMContentLoaded', function() {
    const imagesInput = document.getElementById('images');
    if (!imagesInput) return;
    
    imagesInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const previewContainer = document.getElementById('imagePreview');
        
        // Vider l'aperçu précédent
        previewContainer.innerHTML = '';
        previewContainer.style.display = 'none';
        
        if (files.length === 0) {
            selectedFiles = [];
            return;
        }
        
        selectedFiles = files;
        mainImageIndex = 0; // Réinitialiser à la première image
        
        previewContainer.style.display = 'block';
        previewContainer.innerHTML = '<div class="col-12 mb-2"><h6>Aperçu des images : <small class="text-muted">Cliquez sur une image pour la définir comme principale</small></h6></div>';
        
        files.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 mb-3';
                    col.dataset.index = index;
                    const isMain = index === mainImageIndex;
                    col.innerHTML = `
                        <div class="card image-preview-card ${isMain ? 'border-primary border-3' : ''}" style="cursor: pointer; position: relative;" onclick="setMainImage(${index})">
                            ${isMain ? '<span class="badge bg-primary position-absolute top-0 start-0 m-2">Principale</span>' : '<span class="badge bg-secondary position-absolute top-0 start-0 m-2">Cliquer pour définir</span>'}
                            <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                            <div class="card-body p-2">
                                <small class="text-muted d-block text-truncate" title="${file.name}">${file.name}</small>
                            </div>
                        </div>
                    `;
                    previewContainer.appendChild(col);
                };
                reader.readAsDataURL(file);
            }
        });
    });
});

// Fonction pour définir l'image principale
window.setMainImage = function(index) {
    if (index < 0 || index >= selectedFiles.length) return;
    
    mainImageIndex = index;
    
    // Mettre à jour le champ caché
    document.getElementById('main_image_index').value = index;
    
    // Mettre à jour l'affichage visuel
    const previewContainer = document.getElementById('imagePreview');
    const cards = previewContainer.querySelectorAll('.image-preview-card');
    
    cards.forEach((card) => {
        const col = card.closest('.col-md-3');
        const cardIndex = parseInt(col.dataset.index);
        
        if (cardIndex === index) {
            // Cette image devient principale
            card.classList.remove('border-secondary');
            card.classList.add('border-primary', 'border-3');
            const badge = card.querySelector('.badge');
            badge.className = 'badge bg-primary position-absolute top-0 start-0 m-2';
            badge.textContent = 'Principale';
        } else {
            // Cette image n'est plus principale
            card.classList.remove('border-primary', 'border-3');
            card.classList.add('border-secondary');
            const badge = card.querySelector('.badge');
            badge.className = 'badge bg-secondary position-absolute top-0 start-0 m-2';
            badge.textContent = 'Cliquer pour définir';
        }
    });
};
</script>
@endpush
