@extends('admin.layouts.app')

@section('title', 'Modifier le Produit')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Modifier le Produit</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.products.index') }}">Produits</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <span>Modifier</span>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informations du produit</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Nom du produit</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" required>{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @php
                            // Conversion : Dans la DB, price = prix actuel, old_price = ancien prix
                            // Pour le formulaire : prix normal = old_price si existe (avant promo), sinon price
                            // Prix promo = price si old_price existe (après promo)
                            $normalPrice = $product->old_price ?? $product->price;
                            $promoPrice = ($product->old_price && $product->old_price > $product->price) ? $product->price : null;
                            $discountPercentage = $product->discount_percentage;
                            if (!$discountPercentage && $product->old_price && $product->old_price > $product->price) {
                                $discountPercentage = round((($product->old_price - $product->price) / $product->old_price) * 100, 2);
                            }
                        @endphp
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="price">Prix normal (FCFA) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $normalPrice) }}" min="0" step="0.01" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="promo_price">Prix promo (FCFA)</label>
                                    <input type="number" class="form-control @error('promo_price') is-invalid @enderror" id="promo_price" name="promo_price" value="{{ old('promo_price', $promoPrice) }}" min="0" step="0.01" placeholder="Sera calculé automatiquement">
                                    @error('promo_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Synchronisé avec la réduction</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="discount_percentage">Réduction (%)</label>
                                    <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror" id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage', $discountPercentage) }}" min="0" max="100" step="0.01" placeholder="Ex: 10">
                                    @error('discount_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Calcule automatiquement le prix promo</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="stock">Stock <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" min="0" required>
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Catégories et Sous-catégories (sélection multiple) -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="categories">Catégories <span class="text-danger">*</span></label>
                                    <select class="form-control @error('categories') is-invalid @enderror" id="categories" name="categories[]" multiple size="6" required>
                                        @php
                                            $selectedCategories = old('categories', $product->categories->pluck('id')->toArray());
                                        @endphp
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ in_array($category->id, $selectedCategories) ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs catégories</small>
                                    @error('categories')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('categories.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subcategories">Sous-catégories</label>
                                    <select class="form-control @error('subcategories') is-invalid @enderror" id="subcategories" name="subcategories[]" multiple size="6">
                                        <option value="">Chargement...</option>
                                    </select>
                                    <small class="text-muted">Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs sous-catégories</small>
                                    @error('subcategories')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('subcategories.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Compatibilité avec l'ancien système (champs cachés pour category_id et subcategory_id) -->
                        <input type="hidden" id="category_id" name="category_id" value="{{ old('category_id', $product->category_id) }}">
                        <input type="hidden" id="subcategory_id" name="subcategory_id" value="{{ old('subcategory_id', $product->subcategory_id) }}">

                        <!-- Boutique / Vendeur -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="store_id">Boutique / Vendeur (optionnel)</label>
                                    <select class="form-control @error('store_id') is-invalid @enderror" id="store_id" name="store_id">
                                        <option value="">Aucune boutique (produit global Kazaria)</option>
                                        @foreach($stores as $store)
                                            <option value="{{ $store->id }}" {{ old('store_id', $product->store_id) == $store->id ? 'selected' : '' }}>
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Statut <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="pending" {{ old('status', $product->status) == 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="approved" {{ old('status', $product->status) == 'approved' ? 'selected' : '' }}>Approuvé</option>
                                        <option value="rejected" {{ old('status', $product->status) == 'rejected' ? 'selected' : '' }}>Rejeté</option>
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
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
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
                                    <input type="text" class="form-control @error('brand') is-invalid @enderror" id="brand" name="brand" value="{{ old('brand', $product->brand) }}">
                                    @error('brand')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="model">Modèle</label>
                                    <input type="text" class="form-control @error('model') is-invalid @enderror" id="model" name="model" value="{{ old('model', $product->model) }}">
                                    @error('model')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="warranty">Garantie</label>
                                    <input type="text" class="form-control @error('warranty') is-invalid @enderror" id="warranty" name="warranty" value="{{ old('warranty', $product->warranty) }}" placeholder="Ex: 1 an, 2 ans">
                                    @error('warranty')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Tags et SEO -->
                        <div class="form-group">
                            <label for="tags">Tags (séparés par des virgules)</label>
                            <input type="text" class="form-control @error('tags') is-invalid @enderror" id="tags" name="tags" value="{{ old('tags', is_array($product->tags) ? implode(', ', $product->tags) : $product->tags) }}" placeholder="Ex: nouveau, promo, tendance">
                            @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Les tags seront stockés séparément</small>
                        </div>

                        <div class="form-group">
                            <label for="meta_description">Meta Description (SEO)</label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" rows="2" maxlength="500">{{ old('meta_description', $product->meta_description) }}</textarea>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum 500 caractères</small>
                        </div>

                        <div class="form-group">
                            <label for="meta_keywords">Meta Keywords (SEO)</label>
                            <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $product->meta_keywords) }}" placeholder="Ex: produit, qualité, prix">
                            @error('meta_keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Produit actif
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_trending" id="is_trending" value="1" {{ old('is_trending', $product->is_trending) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_trending">
                                    Produit tendance
                                </label>
                                <small class="form-text text-muted">Les produits tendance apparaissent dans la section "Tendance" de la page d'accueil</small>
                            </div>
                        </div>

                        <!-- Section Images -->
                        <div class="form-group">
                            <label>Images du produit</label>
                            
                            <!-- Images actuelles -->
                            @if($product->images && is_array($product->images) && count($product->images) > 0)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6>Images actuelles : <small class="text-muted">Cliquez sur une image pour la définir comme principale</small></h6>
                                    <div class="row" id="existingImagesContainer">
                                        @php
                                            $mainImageIndex = 0; // Par défaut, la première image est principale
                                            if ($product->image && $product->images) {
                                                // Trouver l'index de l'image principale
                                                $mainImagePath = str_replace('storage/', '', $product->image);
                                                foreach ($product->images as $idx => $img) {
                                                    if (strpos($img, basename($mainImagePath)) !== false || $img === $mainImagePath) {
                                                        $mainImageIndex = $idx;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        @foreach($product->images as $index => $image)
                                        <div class="col-md-3 mb-2">
                                            <div class="card existing-image-card {{ $index === $mainImageIndex ? 'border-primary border-3' : '' }}" 
                                                 style="cursor: pointer; position: relative;"
                                                 data-index="{{ $index }}"
                                                 onclick="setMainExistingImage({{ $index }})">
                                                @if($index === $mainImageIndex)
                                                    <span class="badge bg-primary position-absolute top-0 start-0 m-2">Principale</span>
                                                @else
                                                    <span class="badge bg-secondary position-absolute top-0 start-0 m-2">Cliquer pour définir</span>
                                                @endif
                                                <img src="{{ $product->images_urls[$index] ?? asset('storage/' . $image) }}" class="card-img-top" alt="Image {{ $index + 1 }}" style="height: 100px; object-fit: cover;">
                                                <div class="card-body p-2">
                                                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="event.stopPropagation(); removeImage({{ $index }})">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="main_existing_image_index" id="main_existing_image_index" value="{{ $mainImageIndex }}">
                                </div>
                            </div>
                            @endif

                            <!-- Upload de nouvelles images -->
                            <div class="row">
                                <div class="col-12">
                                    <h6>Ajouter de nouvelles images :</h6>
                                    <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*">
                                    <small class="text-muted">Vous pouvez sélectionner plusieurs images. Formats acceptés: JPG, PNG, GIF. Taille max: 5MB par image.</small>
                                    
                                    <!-- Aperçu des nouvelles images -->
                                    <div id="newImagePreview" class="row mt-3" style="display: none;"></div>
                                    <input type="hidden" name="main_new_image_index" id="main_new_image_index" value="">
                                </div>
                            </div>
                        </div>

                        <!-- Section Attributs -->
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
                                                           id="attr_{{ $attribute->id }}_{{ $value->id }}"
                                                           {{ $product->attributeValues->contains($value->id) ? 'checked' : '' }}>
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
                                                           id="attr_{{ $attribute->id }}_{{ $value->id }}"
                                                           {{ $product->attributeValues->contains($value->id) ? 'checked' : '' }}>
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
                            @if($attributes->count() === 0)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Aucun attribut disponible. <a href="{{ route('admin.attributes.create') }}">Créer des attributs</a> pour pouvoir les assigner aux produits.
                            </div>
                            @endif
                        </div>

                        <!-- Section Variations de produits (avec prix différents selon les attributs) -->
                        @if($attributes && $attributes->count() > 0)
                        <div class="form-group mt-4" id="variations-section">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">
                                            <i class="fas fa-layer-group me-2"></i>
                                            Variations de produits (Prix différents selon les attributs)
                                        </h5>
                                        <small class="text-muted">Gérez les variations avec des prix et stocks différents selon les combinaisons d'attributs</small>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable_variations" name="enable_variations" value="1" {{ $product->variations && $product->variations->count() > 0 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_variations">
                                            Activer les variations
                                        </label>
                                    </div>
                                </div>
                                <div class="card-body" id="variations-container" style="display: {{ $product->variations && $product->variations->count() > 0 ? 'block' : 'none' }};">
                                    <div id="variations-list">
                                        <!-- Les variations existantes et nouvelles seront affichées ici -->
                                        @if($product->variations && $product->variations->count() > 0)
                                            @foreach($product->variations as $index => $variation)
                                                @include('admin.products.partials.variation-row', ['variation' => $variation, 'index' => $index + 1, 'attributes' => $attributes])
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary mt-3" id="add-variation-btn">
                                        <i class="fas fa-plus me-1"></i> Ajouter une variation
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="card-action">
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
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
// Données des attributs disponibles pour les variations
@php
    $attributesData = $attributes->map(function($attr) {
        return [
            'id' => $attr->id,
            'name' => $attr->name,
            'type' => $attr->type,
            'values' => $attr->attributeValues->map(function($val) {
                return ['id' => $val->id, 'value' => $val->value];
            })->toArray()
        ];
    })->toArray();
@endphp
const availableAttributes = @json($attributesData);
</script>
<script>
// Charger les sous-catégories lors de la sélection de catégories (sélection multiple)
document.addEventListener('DOMContentLoaded', function() {
    const categoriesSelect = document.getElementById('categories');
    const subcategoriesSelect = document.getElementById('subcategories');
    const categoryIdHidden = document.getElementById('category_id');
    const subcategoryIdHidden = document.getElementById('subcategory_id');
    
    @php
        $selectedSubcategories = old('subcategories', $product->subcategories->pluck('id')->toArray());
        // Convertir en tableau de strings pour la comparaison JavaScript
        $selectedSubcategories = array_map('strval', $selectedSubcategories);
    @endphp
    const currentSubcategoryIds = @json($selectedSubcategories);
    
    if (!categoriesSelect || !subcategoriesSelect) {
        console.error('Champs categories ou subcategories non trouvés');
        return;
    }
    
    // Fonction pour charger les sous-catégories pour plusieurs catégories
    function loadSubcategoriesForCategories() {
        // Vérifier que les éléments existent et sont valides
        if (!categoriesSelect || !subcategoriesSelect) {
            console.error('Les éléments categoriesSelect ou subcategoriesSelect ne sont pas disponibles');
            return;
        }
        
        // Vérifier que selectedOptions existe (peut être undefined dans certains navigateurs)
        let selectedCategories = [];
        
        try {
            if (categoriesSelect.selectedOptions && categoriesSelect.selectedOptions.length !== undefined) {
                selectedCategories = Array.from(categoriesSelect.selectedOptions)
                    .map(opt => opt && opt.value ? opt.value : '')
                    .filter(id => id !== '');
            }
            
            // Si aucune catégorie n'est sélectionnée via selectedOptions, vérifier les options avec l'attribut selected
            if (selectedCategories.length === 0 && categoriesSelect.options && categoriesSelect.options.length !== undefined) {
                selectedCategories = Array.from(categoriesSelect.options)
                    .filter(opt => opt && opt.selected && opt.value)
                    .map(opt => opt.value)
                    .filter(id => id !== '');
            }
        } catch (error) {
            console.error('Erreur lors de la récupération des catégories sélectionnées:', error);
            return;
        }
        
        console.log('Catégories sélectionnées:', selectedCategories);
        
        // Mettre à jour le champ caché avec la première catégorie sélectionnée (pour compatibilité)
        if (selectedCategories.length > 0) {
            categoryIdHidden.value = selectedCategories[0];
        } else {
            categoryIdHidden.value = '';
        }
        
        // Vider les sous-catégories
        subcategoriesSelect.innerHTML = '';
        subcategoryIdHidden.value = '';
        
        if (selectedCategories.length === 0) {
            subcategoriesSelect.innerHTML = '<option value="">Sélectionner d\'abord une ou plusieurs catégories</option>';
            return;
        }
        
        // Charger les sous-catégories pour toutes les catégories sélectionnées
        const allSubcategories = new Map(); // Utiliser Map pour éviter les doublons
        
        Promise.all(selectedCategories.map(categoryId => {
            console.log('Chargement des sous-catégories pour la catégorie:', categoryId);
            return fetch(`/api/categories/${categoryId}/subcategories`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Réponse API pour catégorie', categoryId, ':', data);
                if (data.success && data.subcategories && data.subcategories.length > 0) {
                    data.subcategories.forEach(subcategory => {
                        const subId = subcategory.id.toString();
                        if (!allSubcategories.has(subId)) {
                            allSubcategories.set(subId, subcategory);
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des sous-catégories pour catégorie', categoryId, ':', error);
            });
        }))
        .then(() => {
            console.log('Toutes les sous-catégories chargées:', Array.from(allSubcategories.values()));
            
            // Trier les sous-catégories par nom
            const sortedSubcategories = Array.from(allSubcategories.values()).sort((a, b) => 
                a.name.localeCompare(b.name)
            );
            
            // Ajouter les options
            if (sortedSubcategories.length === 0) {
                subcategoriesSelect.innerHTML = '<option value="">Aucune sous-catégorie disponible</option>';
            } else {
                sortedSubcategories.forEach(subcategory => {
                    const option = document.createElement('option');
                    const subId = subcategory.id.toString();
                    option.value = subId;
                    option.textContent = subcategory.name;
                    
                    // Vérifier si cette sous-catégorie était sélectionnée précédemment
                    const oldSubcategories = @json(old('subcategories', []));
                    const oldSubcategoriesStr = oldSubcategories.map(id => id.toString());
                    
                    // Comparer avec les IDs actuels (convertis en strings)
                    if (oldSubcategoriesStr.includes(subId) || currentSubcategoryIds.includes(subId)) {
                        option.selected = true;
                        console.log('Sous-catégorie sélectionnée:', subcategory.name);
                    }
                    
                    subcategoriesSelect.appendChild(option);
                });
            }
            
            // Mettre à jour le champ caché avec la première sous-catégorie sélectionnée (pour compatibilité)
            if (subcategoriesSelect.selectedOptions && subcategoriesSelect.selectedOptions.length > 0) {
                subcategoryIdHidden.value = subcategoriesSelect.selectedOptions[0].value;
            }
        })
        .catch(error => {
            console.error('Erreur générale lors du chargement des sous-catégories:', error);
            subcategoriesSelect.innerHTML = '<option value="">Erreur lors du chargement</option>';
        });
    }
    
    // Écouter les changements sur les catégories
    categoriesSelect.addEventListener('change', loadSubcategoriesForCategories);
    
    // Fonction pour vérifier et charger les sous-catégories au chargement
    function initializeSubcategories() {
        // Vérifier que les éléments existent avant de les utiliser
        if (!categoriesSelect || !subcategoriesSelect) {
            console.error('Les éléments categoriesSelect ou subcategoriesSelect ne sont pas disponibles');
            return;
        }
        
        // Vérifier les catégories sélectionnées de plusieurs façons avec gestion d'erreur
        let selectedViaSelectedOptions = [];
        let selectedViaOptions = [];
        
        try {
            if (categoriesSelect.selectedOptions && categoriesSelect.selectedOptions.length !== undefined) {
                selectedViaSelectedOptions = Array.from(categoriesSelect.selectedOptions)
                    .filter(opt => opt && opt.value && opt.value !== '')
                    .map(opt => opt.value);
            }
            
            if (categoriesSelect.options && categoriesSelect.options.length !== undefined) {
                selectedViaOptions = Array.from(categoriesSelect.options)
                    .filter(opt => opt && opt.selected && opt.value && opt.value !== '')
                    .map(opt => opt.value);
            }
        } catch (error) {
            console.error('Erreur lors de la vérification des catégories sélectionnées:', error);
            return;
        }
        
        const hasSelectedCategories = selectedViaSelectedOptions.length > 0 || selectedViaOptions.length > 0;
        
        console.log('Initialisation - Catégories via selectedOptions:', selectedViaSelectedOptions.length);
        console.log('Initialisation - Catégories via options:', selectedViaOptions.length);
        
        if (hasSelectedCategories) {
            console.log('Catégories présélectionnées trouvées, chargement des sous-catégories...');
            // Forcer le rechargement en déclenchant manuellement l'événement change
            loadSubcategoriesForCategories();
        } else {
            console.log('Aucune catégorie présélectionnée');
            try {
                if (subcategoriesSelect) {
                    subcategoriesSelect.innerHTML = '<option value="">Sélectionner d\'abord une ou plusieurs catégories</option>';
                }
            } catch (error) {
                console.error('Erreur lors de la mise à jour du select des sous-catégories:', error);
            }
        }
    }
    
    // Charger les sous-catégories au chargement si des catégories sont déjà sélectionnées
    // Utiliser plusieurs méthodes pour s'assurer que ça fonctionne
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initializeSubcategories, 200);
        });
    } else {
        // DOM déjà chargé
        setTimeout(initializeSubcategories, 200);
    }
    
    // Mettre à jour le champ caché lors de la sélection de sous-catégories
    subcategoriesSelect.addEventListener('change', function() {
        if (this.selectedOptions && this.selectedOptions.length > 0) {
            subcategoryIdHidden.value = this.selectedOptions[0].value;
        } else {
            subcategoryIdHidden.value = '';
        }
    });
    
    // Validation : au moins une catégorie doit être sélectionnée
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!categoriesSelect || !categoriesSelect.selectedOptions || categoriesSelect.selectedOptions.length === 0) {
                e.preventDefault();
                alert('Veuillez sélectionner au moins une catégorie.');
                if (categoriesSelect) {
                    categoriesSelect.focus();
                }
                return false;
            }
        });
    }
});

// Variables pour stocker les images à supprimer
let imagesToRemove = [];

function removeImage(index) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) {
        const imageCard = event.target.closest('.col-md-3');
        const productId = {{ $product->id }};
        
        // Suppression AJAX
        fetch(`/admin/products/${productId}/images/${index}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Masquer l'image visuellement
                imageCard.style.display = 'none';
                // Optionnel: recharger la page pour mettre à jour l'interface
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert('Erreur lors de la suppression: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la suppression de l\'image');
        });
    }
}

// Gestion de l'image principale pour les images existantes
window.setMainExistingImage = function(index) {
    // Mettre à jour le champ caché
    document.getElementById('main_existing_image_index').value = index;
    
    // Mettre à jour l'affichage visuel
    const cards = document.querySelectorAll('.existing-image-card');
    cards.forEach((card) => {
        const cardIndex = parseInt(card.dataset.index);
        if (cardIndex === index) {
            card.classList.remove('border-secondary');
            card.classList.add('border-primary', 'border-3');
            const badge = card.querySelector('.badge');
            badge.className = 'badge bg-primary position-absolute top-0 start-0 m-2';
            badge.textContent = 'Principale';
        } else {
            card.classList.remove('border-primary', 'border-3');
            card.classList.add('border-secondary');
            const badge = card.querySelector('.badge');
            badge.className = 'badge bg-secondary position-absolute top-0 start-0 m-2';
            badge.textContent = 'Cliquer pour définir';
        }
    });
    
    // Si une nouvelle image était principale, la désélectionner
    document.getElementById('main_new_image_index').value = '';
    const newCards = document.querySelectorAll('.new-image-preview-card');
    newCards.forEach(card => {
        card.classList.remove('border-primary', 'border-3');
        card.classList.add('border-secondary');
        const badge = card.querySelector('.badge');
        if (badge) {
            badge.className = 'badge bg-secondary position-absolute top-0 start-0 m-2';
            badge.textContent = 'Cliquer pour définir';
        }
    });
};

// Gestion des nouvelles images avec sélection de l'image principale
let selectedNewFiles = [];
let mainNewImageIndex = null;

// Aperçu des nouvelles images
document.addEventListener('DOMContentLoaded', function() {
    const imagesInput = document.getElementById('images');
    if (!imagesInput) return;
    
    imagesInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const previewContainer = document.getElementById('newImagePreview');
        
        // Vider l'aperçu précédent
        previewContainer.innerHTML = '';
        previewContainer.style.display = 'none';
        
        if (files.length === 0) {
            selectedNewFiles = [];
            mainNewImageIndex = null;
            document.getElementById('main_new_image_index').value = '';
            return;
        }
        
        selectedNewFiles = files;
        mainNewImageIndex = null;
        
        previewContainer.style.display = 'block';
        previewContainer.innerHTML = '<div class="col-12 mb-2"><h6>Aperçu des nouvelles images : <small class="text-muted">Cliquez sur une image pour la définir comme principale</small></h6></div>';
        
        files.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 mb-3';
                    col.dataset.index = index;
                    col.innerHTML = `
                        <div class="card new-image-preview-card border-secondary" style="cursor: pointer; position: relative;" onclick="setMainNewImage(${index})">
                            <span class="badge bg-secondary position-absolute top-0 start-0 m-2">Cliquer pour définir</span>
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

// Fonction pour définir l'image principale parmi les nouvelles images
window.setMainNewImage = function(index) {
    if (index < 0 || index >= selectedNewFiles.length) return;
    
    mainNewImageIndex = index;
    
    // Mettre à jour le champ caché
    document.getElementById('main_new_image_index').value = index;
    
    // Mettre à jour l'affichage visuel
    const previewContainer = document.getElementById('newImagePreview');
    const cards = previewContainer.querySelectorAll('.new-image-preview-card');
    
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
    
    // Si une image existante était principale, la désélectionner
    document.getElementById('main_existing_image_index').value = '';
    const existingCards = document.querySelectorAll('.existing-image-card');
    existingCards.forEach(card => {
        card.classList.remove('border-primary', 'border-3');
        card.classList.add('border-secondary');
        const badge = card.querySelector('.badge');
        if (badge && badge.textContent === 'Principale') {
            badge.className = 'badge bg-secondary position-absolute top-0 start-0 m-2';
            badge.textContent = 'Cliquer pour définir';
        }
    });
};

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

// Gestion des variations de produits dans le formulaire d'édition
(function() {
    let variationCounter = {{ $product->variations && $product->variations->count() > 0 ? $product->variations->count() : 0 }};
    let variationsToDelete = [];
    
    document.addEventListener('DOMContentLoaded', function() {
        const enableVariationsCheckbox = document.getElementById('enable_variations');
        const variationsContainer = document.getElementById('variations-container');
        const addVariationBtn = document.getElementById('add-variation-btn');
        
        if (!enableVariationsCheckbox || !variationsContainer || !addVariationBtn) {
            return;
        }
        
        // Afficher/masquer la section variations
        enableVariationsCheckbox.addEventListener('change', function() {
            if (this.checked) {
                variationsContainer.style.display = 'block';
                if (variationCounter === 0) {
                    addVariationRow();
                }
            } else {
                variationsContainer.style.display = 'none';
            }
        });
        
        // Bouton pour ajouter une variation
        addVariationBtn.addEventListener('click', function() {
            addVariationRow();
        });
    });
    
    // Fonction pour ajouter une ligne de variation
    function addVariationRow() {
        variationCounter++;
        const variationsList = document.getElementById('variations-list');
        if (!variationsList) return;
        
        let attributesHtml = '';
        
        // Utiliser les attributs disponibles depuis la variable JavaScript
        if (typeof availableAttributes !== 'undefined' && availableAttributes.length > 0) {
            // Créer les champs d'attributs depuis availableAttributes
            availableAttributes.forEach(attribute => {
                let optionsHtml = '<option value="">-- Sélectionner --</option>';
                
                if (attribute.values && attribute.values.length > 0) {
                    attribute.values.forEach(value => {
                        optionsHtml += `<option value="${value.id}">${value.value}</option>`;
                    });
                }
                
                attributesHtml += `
                    <div class="col-md-6 mb-2">
                        <label class="form-label small">${attribute.name}</label>
                        <select class="form-control form-control-sm variation-attribute" 
                                name="variations[${variationCounter}][attributes][${attribute.id}]">
                            ${optionsHtml}
                        </select>
                    </div>
                `;
            });
        } else {
            // Fallback: essayer de cloner depuis une variation existante
            const existingVariation = document.querySelector('.variation-row');
            if (existingVariation) {
                existingVariation.querySelectorAll('.variation-attribute').forEach(select => {
                    const match = select.name.match(/variations\[\d+\]\[attributes\]\[(\d+)\]/);
                    if (match) {
                        const attrId = match[1];
                        const label = select.closest('.mb-2')?.querySelector('label')?.textContent?.trim() || `Attribut ${attrId}`;
                        const options = Array.from(select.querySelectorAll('option')).map(opt => {
                            return `<option value="${opt.value}">${opt.textContent.trim()}</option>`;
                        }).join('');
                        
                        attributesHtml += `
                            <div class="col-md-6 mb-2">
                                <label class="form-label small">${label}</label>
                                <select class="form-control form-control-sm variation-attribute" 
                                        name="variations[${variationCounter}][attributes][${attrId}]">
                                    ${options}
                                </select>
                            </div>
                        `;
                    }
                });
            }
        }
        
        // Si toujours vide, afficher un message d'erreur
        if (!attributesHtml) {
            attributesHtml = '<div class="col-12"><div class="alert alert-warning">Aucun attribut disponible pour créer une variation.</div></div>';
        }
        
        const row = document.createElement('div');
        row.className = 'card mb-3 variation-row';
        row.dataset.index = variationCounter;
        
        row.innerHTML = `
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Variation #${variationCounter}</h6>
                <button type="button" class="btn btn-sm btn-danger remove-variation" onclick="removeVariationRow(${variationCounter}, null)">
                    <i class="fas fa-times"></i> Supprimer
                </button>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    ${attributesHtml}
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Prix (FCFA) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control variation-price" 
                               name="variations[${variationCounter}][price]" 
                               step="0.01" min="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prix promo (FCFA)</label>
                        <input type="number" class="form-control variation-promo-price" 
                               name="variations[${variationCounter}][promo_price]" 
                               step="0.01" min="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Stock <span class="text-danger">*</span></label>
                        <input type="number" class="form-control variation-stock" 
                               name="variations[${variationCounter}][stock]" 
                               min="0" value="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">SKU</label>
                        <input type="text" class="form-control variation-sku" 
                               name="variations[${variationCounter}][sku]">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Par défaut</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input variation-default" 
                                   type="checkbox" 
                                   name="variations[${variationCounter}][is_default]" 
                                   value="1">
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        variationsList.appendChild(row);
    }
    
    // Fonction pour supprimer une variation (accessible globalement)
    window.removeVariationRow = function(index, variationId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette variation ?')) {
            return;
        }
        
        const row = document.querySelector(`.variation-row[data-index="${index}"]`);
        if (row) {
            row.remove();
            
            // Si c'est une variation existante, l'ajouter à la liste de suppression
            if (variationId) {
                variationsToDelete.push(variationId);
                // Créer ou mettre à jour le champ caché pour les variations à supprimer
                let deleteInput = document.getElementById('variations_to_delete');
                if (!deleteInput) {
                    deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.id = 'variations_to_delete';
                    deleteInput.name = 'variations_to_delete[]';
                    document.querySelector('form').appendChild(deleteInput);
                }
                deleteInput.value = variationsToDelete.join(',');
            }
        }
    };
})();
</script>
@endpush
