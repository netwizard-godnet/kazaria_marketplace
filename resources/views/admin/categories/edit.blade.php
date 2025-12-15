@extends('admin.layouts.app')

@section('title', 'Modifier la catégorie')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Modifier la catégorie</h4>
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
                <a href="{{ route('admin.categories.index') }}">Catégories</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <span>Modifier</span>
            </li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informations de la catégorie</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Nom de la catégorie *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $category->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4">{{ old('description', $category->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Personnalisation de la page -->
                        <div class="card mt-3 mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Personnalisation de la page catégorie</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_customized" id="is_customized" 
                                               value="1" {{ old('is_customized', $category->is_customized) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_customized">
                                            Activer la personnalisation de la page catégorie
                                        </label>
                                    </div>
                                    <small class="text-muted">Si activé, vous pourrez personnaliser l'ordre et la visibilité des sections de la page catégorie.</small>
                                </div>

                                <div id="customization-panel" style="display: {{ old('is_customized', $category->is_customized) ? 'block' : 'none' }};">
                                    <hr>
                                    <h6 class="mb-3">Configuration des sections</h6>
                                    <p class="text-muted small mb-3">Définissez l'ordre d'affichage et la visibilité de chaque section. Vous pouvez également personnaliser les titres.</p>
                                    
                                        @php
                                        $defaultSections = [
                                            'best_offers' => ['enabled' => true, 'order' => 1, 'title' => 'Meilleures offres'],
                                            'banners_top' => ['enabled' => true, 'order' => 2, 'title' => 'Bannières supérieures'],
                                            'new_products' => ['enabled' => true, 'order' => 3, 'title' => 'Nouveautés'],
                                            'products_list' => ['enabled' => true, 'order' => 4, 'title' => 'Liste des produits'],
                                            'banners_bottom' => ['enabled' => true, 'order' => 5, 'title' => 'Bannières inférieures'],
                                        ];
                                        
                                        $customLayout = old('custom_layout', $category->custom_layout);
                                        // S'assurer que c'est un tableau
                                        if (is_string($customLayout)) {
                                            $customLayout = json_decode($customLayout, true);
                                        }
                                        if (!is_array($customLayout)) {
                                            $customLayout = [];
                                        }
                                        
                                        // Fusionner les sections par défaut avec les sections personnalisées
                                        $sections = $defaultSections;
                                        if (!empty($customLayout)) {
                                            foreach ($customLayout as $key => $config) {
                                                if (isset($sections[$key])) {
                                                    $sections[$key] = array_merge($sections[$key], $config);
                                                }
                                            }
                                        }
                                    @endphp

                                    <div id="sections-container">
                                        @foreach($sections as $sectionKey => $sectionConfig)
                                            <div class="card mb-2 section-item" data-section-key="{{ $sectionKey }}">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-1">
                                                            <i class="fas fa-grip-vertical text-muted" style="cursor: move;"></i>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <strong>{{ $sectionConfig['title'] ?? ucfirst(str_replace('_', ' ', $sectionKey)) }}</strong>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="text" class="form-control form-control-sm section-title" 
                                                                   name="section_titles[{{ $sectionKey }}]" 
                                                                   value="{{ $sectionConfig['title'] ?? '' }}" 
                                                                   placeholder="Titre de la section">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="number" class="form-control form-control-sm section-order" 
                                                                   name="section_orders[{{ $sectionKey }}]" 
                                                                   value="{{ $sectionConfig['order'] ?? 999 }}" 
                                                                   min="1" placeholder="Ordre">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input section-enabled" type="checkbox" 
                                                                       name="section_enabled[{{ $sectionKey }}]" 
                                                                       value="1" 
                                                                       {{ ($sectionConfig['enabled'] ?? true) ? 'checked' : '' }}>
                                                                <label class="form-check-label">Activé</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-1">
                                                            <input type="hidden" class="section-key" value="{{ $sectionKey }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="custom_layout" id="custom_layout_input" value="">
                                </div>
                            </div>
                        </div>

                        <!-- Personnalisation avancée : Couleurs, Bannières, Carrousels -->
                        <div class="card mt-3 mb-3" id="advanced-customization-panel" style="display: {{ old('is_customized', $category->is_customized) ? 'block' : 'none' }};">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Personnalisation avancée</h5>
                            </div>
                            <div class="card-body">
                                <!-- Onglets -->
                                <ul class="nav nav-tabs" id="customizationTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="colors-tab" data-bs-toggle="tab" data-bs-target="#colors-pane" type="button" role="tab">
                                            <i class="fas fa-palette"></i> Couleurs
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="banners-tab" data-bs-toggle="tab" data-bs-target="#banners-pane" type="button" role="tab">
                                            <i class="fas fa-image"></i> Bannières
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="carousels-tab" data-bs-toggle="tab" data-bs-target="#carousels-pane" type="button" role="tab">
                                            <i class="fas fa-images"></i> Carrousels
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content mt-3" id="customizationTabsContent">
                                    <!-- Onglet Couleurs -->
                                    <div class="tab-pane fade show active" id="colors-pane" role="tabpanel">
                                        <h6 class="mb-3">Personnalisation des couleurs</h6>
                                        <p class="text-muted small mb-3">Définissez les couleurs de la page catégorie. Les couleurs par défaut seront utilisées si non renseignées.</p>
                                        
                                        @php
                                            $customColors = old('custom_colors', $category->custom_colors ?? []);
                                            // S'assurer que c'est un tableau
                                            if (is_string($customColors)) {
                                                $customColors = json_decode($customColors, true) ?? [];
                                            }
                                            if (!is_array($customColors)) {
                                                $customColors = [];
                                            }
                                            $colorFields = [
                                                'primary' => ['label' => 'Couleur principale', 'default' => '#f04e27', 'description' => 'Couleur des liens et éléments principaux'],
                                                'secondary' => ['label' => 'Couleur secondaire', 'default' => '#333333', 'description' => 'Couleur du texte secondaire'],
                                                'background' => ['label' => 'Couleur de fond', 'default' => '#ffffff', 'description' => 'Couleur de fond de la page'],
                                                'text' => ['label' => 'Couleur du texte', 'default' => '#333333', 'description' => 'Couleur principale du texte'],
                                                'accent' => ['label' => 'Couleur d\'accent', 'default' => '#f04e27', 'description' => 'Couleur pour les éléments d\'accentuation'],
                                            ];
                                        @endphp

                                        <div class="row">
                                            @foreach($colorFields as $colorKey => $colorInfo)
                                                <div class="col-md-6 mb-3">
                                                    <label for="color_{{ $colorKey }}" class="form-label">
                                                        {{ $colorInfo['label'] }}
                                                        <small class="text-muted d-block">{{ $colorInfo['description'] }}</small>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="color" 
                                                               class="form-control form-control-color" 
                                                               id="color_{{ $colorKey }}" 
                                                               name="custom_colors[{{ $colorKey }}]"
                                                               value="{{ $customColors[$colorKey] ?? $colorInfo['default'] }}"
                                                               title="{{ $colorInfo['label'] }}">
                                                        <input type="text" 
                                                               class="form-control color-text-input" 
                                                               id="color_{{ $colorKey }}_text"
                                                               value="{{ $customColors[$colorKey] ?? $colorInfo['default'] }}"
                                                               placeholder="{{ $colorInfo['default'] }}">
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Onglet Bannières -->
                                    <div class="tab-pane fade" id="banners-pane" role="tabpanel">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Bannières personnalisées</h6>
                                            <button type="button" class="btn btn-sm btn-primary" id="add-banner-btn">
                                                <i class="fas fa-plus"></i> Ajouter une bannière
                                            </button>
                                        </div>
                                        <p class="text-muted small mb-3">Ajoutez des bannières personnalisées qui s'afficheront sur la page catégorie.</p>
                                        
                                        <div id="banners-container">
                                            @php
                                                $customBanners = old('custom_banners', $category->custom_banners ?? []);
                                                // S'assurer que c'est un tableau
                                                if (is_string($customBanners)) {
                                                    $customBanners = json_decode($customBanners, true) ?? [];
                                                }
                                                if (!is_array($customBanners)) {
                                                    $customBanners = [];
                                                }
                                            @endphp
                                            @if(!empty($customBanners))
                                                @foreach($customBanners as $index => $banner)
                                                    @include('admin.categories.partials.banner-item', ['index' => $index, 'banner' => $banner])
                                                @endforeach
                                            @endif
                                        </div>
                                        <input type="hidden" name="custom_banners" id="custom_banners_input" value="{{ json_encode($customBanners ?? []) }}">
                                    </div>

                                    <!-- Onglet Carrousels -->
                                    <div class="tab-pane fade" id="carousels-pane" role="tabpanel">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Carrousels personnalisés</h6>
                                            <button type="button" class="btn btn-sm btn-primary" id="add-carousel-btn">
                                                <i class="fas fa-plus"></i> Ajouter un carrousel
                                            </button>
                                        </div>
                                        <p class="text-muted small mb-3">Créez des carrousels d'images personnalisés pour la page catégorie.</p>
                                        
                                        <div id="carousels-container">
                                            @php
                                                $customCarousels = old('custom_carousels', $category->custom_carousels ?? []);
                                                // S'assurer que c'est un tableau
                                                if (is_string($customCarousels)) {
                                                    $customCarousels = json_decode($customCarousels, true) ?? [];
                                                }
                                                if (!is_array($customCarousels)) {
                                                    $customCarousels = [];
                                                }
                                            @endphp
                                            @if(!empty($customCarousels))
                                                @foreach($customCarousels as $index => $carousel)
                                                    @include('admin.categories.partials.carousel-item', ['index' => $index, 'carousel' => $carousel])
                                                @endforeach
                                            @endif
                                        </div>
                                        <input type="hidden" name="custom_carousels" id="custom_carousels_input" value="{{ json_encode($customCarousels ?? []) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="order">Ordre d'affichage</label>
                                    <input type="number" class="form-control @error('order') is-invalid @enderror" 
                                           id="order" name="order" value="{{ old('order', $category->order) }}" min="0">
                                    @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                               value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Catégorie active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Image actuelle -->
                        @if($category->image)
                        <div class="form-group">
                            <label>Image actuelle</label>
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" 
                                     class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                            </div>
                        </div>
                        @endif

                        <div class="form-group">
                            <label for="image">Nouvelle image</label>
                            <input type="file" class="form-control-file @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*">
                            <small class="text-muted">Formats acceptés: JPG, PNG, GIF. Taille max: 2MB. Laisser vide pour conserver l'image actuelle.</small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Aperçu de la nouvelle image -->
                        <div class="form-group" id="image-preview" style="display: none;">
                            <label>Aperçu de la nouvelle image</label>
                            <div class="mb-3">
                                <img id="preview-img" src="" alt="Aperçu" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                            </div>
                        </div>

                        <div class="card-action">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Mettre à jour
                            </button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
// Aperçu de l'image
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('image-preview').style.display = 'none';
    }
});

// Gestion de la personnalisation
const isCustomizedCheckbox = document.getElementById('is_customized');
const customizationPanel = document.getElementById('customization-panel');
const advancedCustomizationPanel = document.getElementById('advanced-customization-panel');
const customLayoutInput = document.getElementById('custom_layout_input');

// Afficher/masquer les panneaux de personnalisation
isCustomizedCheckbox.addEventListener('change', function() {
    const isChecked = this.checked;
    customizationPanel.style.display = isChecked ? 'block' : 'none';
    if (advancedCustomizationPanel) {
        advancedCustomizationPanel.style.display = isChecked ? 'block' : 'none';
    }
    if (!isChecked) {
        customLayoutInput.value = '';
        updateCustomBanners();
        updateCustomCarousels();
        updateCustomColors();
    } else {
        updateCustomLayout();
    }
});

// Fonction pour mettre à jour le custom_layout
function updateCustomLayout() {
    const sections = [];
    const sectionItems = document.querySelectorAll('.section-item');
    
    sectionItems.forEach((item, index) => {
        const sectionKey = item.querySelector('.section-key').value;
        const sectionTitle = item.querySelector('.section-title').value;
        const sectionOrder = parseInt(item.querySelector('.section-order').value) || (index + 1);
        const sectionEnabled = item.querySelector('.section-enabled').checked;
        
        sections.push({
            key: sectionKey,
            title: sectionTitle,
            order: sectionOrder,
            enabled: sectionEnabled
        });
    });
    
    // Créer l'objet final avec les clés comme index
    const layout = {};
    sections.forEach(section => {
        layout[section.key] = {
            enabled: section.enabled,
            order: section.order,
            title: section.title
        };
    });
    
    customLayoutInput.value = JSON.stringify(layout);
}

// Initialiser Sortable pour réorganiser les sections
const sectionsContainer = document.getElementById('sections-container');
if (sectionsContainer) {
    new Sortable(sectionsContainer, {
        animation: 150,
        handle: '.fa-grip-vertical',
        onEnd: function() {
            // Mettre à jour les ordres après le déplacement
            const items = sectionsContainer.querySelectorAll('.section-item');
            items.forEach((item, index) => {
                const orderInput = item.querySelector('.section-order');
                if (!orderInput.value || orderInput.value === '999') {
                    orderInput.value = index + 1;
                }
            });
            updateCustomLayout();
        }
    });
}

// Écouter les changements dans les champs
document.querySelectorAll('.section-title, .section-order, .section-enabled').forEach(element => {
    element.addEventListener('change', updateCustomLayout);
    element.addEventListener('input', updateCustomLayout);
});

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    if (isCustomizedCheckbox && isCustomizedCheckbox.checked) {
        updateCustomLayout();
        updateCustomBanners();
        updateCustomCarousels();
        updateCustomColors();
    }
});

// Gestion des couleurs personnalisées
function updateCustomColors() {
    const colors = {};
    document.querySelectorAll('input[type="color"][name^="custom_colors"]').forEach(input => {
        const key = input.name.match(/\[(.*?)\]/)[1];
        colors[key] = input.value;
    });
    // Pas besoin de champ caché, les couleurs sont déjà dans le formulaire
}

// Synchroniser les sélecteurs de couleur avec les champs texte
document.querySelectorAll('input[type="color"]').forEach(colorInput => {
    const textInput = document.getElementById(colorInput.id + '_text');
    if (textInput) {
        colorInput.addEventListener('input', function() {
            textInput.value = this.value;
            updateCustomColors();
        });
        textInput.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                colorInput.value = this.value;
                updateCustomColors();
            }
        });
    }
});

// Gestion des bannières
let bannerIndex = document.querySelectorAll('.banner-item').length;

function updateCustomBanners() {
    const banners = [];
    document.querySelectorAll('.banner-item').forEach((item, index) => {
        const imageUrl = item.querySelector('.banner-image-url').value;
        if (imageUrl) {
            banners.push({
                id: index,
                title: item.querySelector('.banner-title').value || '',
                order: parseInt(item.querySelector('.banner-order').value) || (index + 1),
                image: imageUrl,
                link_url: item.querySelector('.banner-link-url').value || '',
                link_target: item.querySelector('.banner-link-target').value || '_blank',
                columns: item.querySelector('.banner-columns').value || 'col-12',
                height: item.querySelector('.banner-height').value || '',
                alt: item.querySelector('.banner-alt').value || '',
                enabled: item.querySelector('.banner-enabled').checked
            });
        }
    });
    document.getElementById('custom_banners_input').value = JSON.stringify(banners);
}

// Ajouter une bannière
document.getElementById('add-banner-btn')?.addEventListener('click', function() {
    const container = document.getElementById('banners-container');
    const index = bannerIndex++;
    const bannerHtml = `
        <div class="card mb-3 banner-item" data-banner-index="${index}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Bannière #${index + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger remove-banner-btn">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Titre (optionnel)</label>
                            <input type="text" class="form-control banner-title" placeholder="Titre de la bannière">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Ordre d'affichage</label>
                            <input type="number" class="form-control banner-order" value="${index + 1}" min="1">
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label>Image</label>
                    <input type="file" class="form-control banner-image-input" accept="image/*">
                    <div class="mt-2 banner-preview-container" style="display: none;">
                        <img src="" class="img-thumbnail banner-preview" style="max-width: 200px; max-height: 150px;">
                        <input type="hidden" class="banner-image-url" value="">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>URL du lien (optionnel)</label>
                            <input type="url" class="form-control banner-link-url" placeholder="https://example.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Cible du lien</label>
                            <select class="form-control banner-link-target">
                                <option value="_blank">Nouvel onglet</option>
                                <option value="_self">Même onglet</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Colonnes Bootstrap</label>
                            <select class="form-control banner-columns">
                                <option value="col-12">12 colonnes (pleine largeur)</option>
                                <option value="col-md-6">6 colonnes (moitié)</option>
                                <option value="col-md-4">4 colonnes (tiers)</option>
                                <option value="col-md-3">3 colonnes (quart)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Hauteur (optionnel)</label>
                            <input type="text" class="form-control banner-height" placeholder="ex: 200px, 50vh">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Texte alternatif</label>
                            <input type="text" class="form-control banner-alt" placeholder="Description de l'image">
                        </div>
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input banner-enabled" type="checkbox" checked>
                    <label class="form-check-label">Bannière activée</label>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', bannerHtml);
    attachBannerEvents(container.lastElementChild);
    updateCustomBanners();
});

// Attacher les événements à une bannière
function attachBannerEvents(bannerElement) {
    // Upload d'image
    const imageInput = bannerElement.querySelector('.banner-image-input');
    const previewContainer = bannerElement.querySelector('.banner-preview-container');
    const preview = bannerElement.querySelector('.banner-preview');
    const imageUrlInput = bannerElement.querySelector('.banner-image-url');
    const oldImagePath = imageUrlInput.value; // Sauvegarder l'ancien chemin si existe
    
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Désactiver l'input pendant l'upload
            imageInput.disabled = true;
            imageInput.parentElement.insertAdjacentHTML('beforeend', '<div class="spinner-border spinner-border-sm ms-2" role="status"><span class="visually-hidden">Chargement...</span></div>');
            
            // Créer FormData pour l'upload
            const formData = new FormData();
            formData.append('image', file);
            formData.append('type', 'banner');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
            
            // Upload via AJAX
            fetch('{{ route("admin.categories.upload-image") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Afficher l'aperçu
                    preview.src = data.url;
                    previewContainer.style.display = 'block';
                    imageUrlInput.value = data.path; // Stocker le chemin du fichier
                    
                    // Supprimer l'ancienne image si elle existe
                    if (oldImagePath && oldImagePath !== data.path && !oldImagePath.startsWith('data:')) {
                        fetch('{{ route("admin.categories.delete-image") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({ path: oldImagePath })
                        });
                    }
                    
                    updateCustomBanners();
                } else {
                    alert('Erreur lors de l\'upload : ' + (data.message || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'upload de l\'image');
            })
            .finally(() => {
                imageInput.disabled = false;
                const spinner = imageInput.parentElement.querySelector('.spinner-border');
                if (spinner) spinner.remove();
            });
        }
    });
    
    // Supprimer la bannière
    bannerElement.querySelector('.remove-banner-btn')?.addEventListener('click', function() {
        // Supprimer l'image du serveur si elle existe
        const imagePath = imageUrlInput.value;
        if (imagePath && !imagePath.startsWith('data:')) {
            fetch('{{ route("admin.categories.delete-image") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ path: imagePath })
            });
        }
        bannerElement.remove();
        updateCustomBanners();
    });
    
    // Écouter les changements
    bannerElement.querySelectorAll('.banner-title, .banner-order, .banner-link-url, .banner-link-target, .banner-columns, .banner-height, .banner-alt, .banner-enabled').forEach(el => {
        el.addEventListener('change', updateCustomBanners);
        el.addEventListener('input', updateCustomBanners);
    });
}

// Attacher les événements aux bannières existantes
document.querySelectorAll('.banner-item').forEach(item => attachBannerEvents(item));

// Gestion des carrousels
let carouselIndex = document.querySelectorAll('.carousel-item').length;

function updateCustomCarousels() {
    const carousels = [];
    document.querySelectorAll('.carousel-item').forEach((item, index) => {
        const images = [];
        item.querySelectorAll('.carousel-image-item').forEach(imgItem => {
            const imageUrl = imgItem.querySelector('.carousel-image-url').value;
            if (imageUrl) {
                images.push({
                    url: imageUrl,
                    link_url: imgItem.querySelector('.carousel-image-link-url').value || '',
                    link_target: imgItem.querySelector('.carousel-image-link-target').value || '_blank',
                    alt: imgItem.querySelector('.carousel-image-alt').value || ''
                });
            }
        });
        
        if (images.length > 0) {
            carousels.push({
                id: index,
                title: item.querySelector('.carousel-title').value || '',
                order: parseInt(item.querySelector('.carousel-order').value) || (index + 1),
                slides_to_show: parseInt(item.querySelector('.carousel-slides-to-show').value) || 6,
                slides_lg: parseInt(item.querySelector('.carousel-slides-lg').value) || 4,
                slides_md: parseInt(item.querySelector('.carousel-slides-md').value) || 3,
                slides_sm: parseInt(item.querySelector('.carousel-slides-sm').value) || 2,
                slides_xs: parseInt(item.querySelector('.carousel-slides-sm').value) || 2,
                gap: parseInt(item.querySelector('.carousel-gap').value) || 0,
                autoplay: item.querySelector('.carousel-autoplay').checked,
                autoplay_speed: parseInt(item.querySelector('.carousel-autoplay-speed').value) || 2000,
                pause_on_hover: item.querySelector('.carousel-pause-on-hover').checked,
                images: images,
                enabled: item.querySelector('.carousel-enabled').checked
            });
        }
    });
    document.getElementById('custom_carousels_input').value = JSON.stringify(carousels);
}

// Ajouter un carrousel
document.getElementById('add-carousel-btn')?.addEventListener('click', function() {
    const container = document.getElementById('carousels-container');
    const index = carouselIndex++;
    const carouselHtml = `
        <div class="card mb-3 carousel-item" data-carousel-index="${index}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Carrousel #${index + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger remove-carousel-btn">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Titre du carrousel</label>
                            <input type="text" class="form-control carousel-title" placeholder="Titre du carrousel">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Ordre d'affichage</label>
                            <input type="number" class="form-control carousel-order" value="${index + 1}" min="1">
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Slides à afficher</label>
                        <input type="number" class="form-control carousel-slides-to-show" value="6" min="1" max="12">
                    </div>
                    <div class="col-md-3">
                        <label>Slides (Large)</label>
                        <input type="number" class="form-control carousel-slides-lg" value="4" min="1" max="12">
                    </div>
                    <div class="col-md-3">
                        <label>Slides (Moyen)</label>
                        <input type="number" class="form-control carousel-slides-md" value="3" min="1" max="12">
                    </div>
                    <div class="col-md-3">
                        <label>Slides (Petit)</label>
                        <input type="number" class="form-control carousel-slides-sm" value="2" min="1" max="12">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Espacement (gap)</label>
                        <input type="number" class="form-control carousel-gap" value="0" min="0">
                    </div>
                    <div class="col-md-4">
                        <label>Vitesse autoplay (ms)</label>
                        <input type="number" class="form-control carousel-autoplay-speed" value="2000" min="500">
                    </div>
                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input class="form-check-input carousel-autoplay" type="checkbox" checked>
                            <label class="form-check-label">Autoplay</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input carousel-pause-on-hover" type="checkbox" checked>
                            <label class="form-check-label">Pause au survol</label>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Images du carrousel</h6>
                    <button type="button" class="btn btn-sm btn-primary add-carousel-image-btn">
                        <i class="fas fa-plus"></i> Ajouter une image
                    </button>
                </div>
                <div class="carousel-images-container"></div>
                <div class="form-check mt-3">
                    <input class="form-check-input carousel-enabled" type="checkbox" checked>
                    <label class="form-check-label">Carrousel activé</label>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', carouselHtml);
    attachCarouselEvents(container.lastElementChild);
    updateCustomCarousels();
});

// Attacher les événements à un carrousel
function attachCarouselEvents(carouselElement) {
    // Supprimer le carrousel
    carouselElement.querySelector('.remove-carousel-btn')?.addEventListener('click', function() {
        carouselElement.remove();
        updateCustomCarousels();
    });
    
    // Ajouter une image au carrousel
    carouselElement.querySelector('.add-carousel-image-btn')?.addEventListener('click', function() {
        const imagesContainer = carouselElement.querySelector('.carousel-images-container');
        const imgIndex = imagesContainer.querySelectorAll('.carousel-image-item').length;
        const imageHtml = `
            <div class="card mb-2 carousel-image-item" data-image-index="${imgIndex}">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="carousel-image-preview-container" style="display: none;">
                                <img src="" class="img-thumbnail carousel-image-preview" style="max-width: 100px; max-height: 80px;">
                            </div>
                            <input type="file" class="form-control form-control-sm mt-2 carousel-image-input" accept="image/*">
                            <input type="hidden" class="carousel-image-url" value="">
                        </div>
                        <div class="col-md-3">
                            <label>URL du lien</label>
                            <input type="url" class="form-control form-control-sm carousel-image-link-url" placeholder="https://example.com">
                        </div>
                        <div class="col-md-2">
                            <label>Cible</label>
                            <select class="form-control form-control-sm carousel-image-link-target">
                                <option value="_blank">Nouvel onglet</option>
                                <option value="_self">Même onglet</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Texte alternatif</label>
                            <input type="text" class="form-control form-control-sm carousel-image-alt" placeholder="Description">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-danger remove-carousel-image-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        imagesContainer.insertAdjacentHTML('beforeend', imageHtml);
        attachCarouselImageEvents(imagesContainer.lastElementChild);
        updateCustomCarousels();
    });
    
    // Écouter les changements
    carouselElement.querySelectorAll('.carousel-title, .carousel-order, .carousel-slides-to-show, .carousel-slides-lg, .carousel-slides-md, .carousel-slides-sm, .carousel-gap, .carousel-autoplay-speed, .carousel-autoplay, .carousel-pause-on-hover, .carousel-enabled').forEach(el => {
        el.addEventListener('change', updateCustomCarousels);
        el.addEventListener('input', updateCustomCarousels);
    });
}

// Attacher les événements à une image de carrousel
function attachCarouselImageEvents(imageElement) {
    const imageInput = imageElement.querySelector('.carousel-image-input');
    const previewContainer = imageElement.querySelector('.carousel-image-preview-container');
    const preview = imageElement.querySelector('.carousel-image-preview');
    const imageUrlInput = imageElement.querySelector('.carousel-image-url');
    const oldImagePath = imageUrlInput.value; // Sauvegarder l'ancien chemin si existe
    
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Désactiver l'input pendant l'upload
            imageInput.disabled = true;
            imageInput.parentElement.insertAdjacentHTML('beforeend', '<div class="spinner-border spinner-border-sm ms-2" role="status"><span class="visually-hidden">Chargement...</span></div>');
            
            // Créer FormData pour l'upload
            const formData = new FormData();
            formData.append('image', file);
            formData.append('type', 'carousel');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
            
            // Upload via AJAX
            fetch('{{ route("admin.categories.upload-image") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Afficher l'aperçu
                    preview.src = data.url;
                    previewContainer.style.display = 'block';
                    imageUrlInput.value = data.path; // Stocker le chemin du fichier
                    
                    // Supprimer l'ancienne image si elle existe
                    if (oldImagePath && oldImagePath !== data.path && !oldImagePath.startsWith('data:')) {
                        fetch('{{ route("admin.categories.delete-image") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({ path: oldImagePath })
                        });
                    }
                    
                    updateCustomCarousels();
                } else {
                    alert('Erreur lors de l\'upload : ' + (data.message || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'upload de l\'image');
            })
            .finally(() => {
                imageInput.disabled = false;
                const spinner = imageInput.parentElement.querySelector('.spinner-border');
                if (spinner) spinner.remove();
            });
        }
    });
    
    imageElement.querySelector('.remove-carousel-image-btn')?.addEventListener('click', function() {
        // Supprimer l'image du serveur si elle existe
        const imagePath = imageUrlInput.value;
        if (imagePath && !imagePath.startsWith('data:')) {
            fetch('{{ route("admin.categories.delete-image") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ path: imagePath })
            });
        }
        imageElement.remove();
        updateCustomCarousels();
    });
    
    imageElement.querySelectorAll('.carousel-image-link-url, .carousel-image-link-target, .carousel-image-alt').forEach(el => {
        el.addEventListener('change', updateCustomCarousels);
        el.addEventListener('input', updateCustomCarousels);
    });
}

// Attacher les événements aux carrousels existants
document.querySelectorAll('.carousel-item').forEach(item => {
    attachCarouselEvents(item);
    item.querySelectorAll('.carousel-image-item').forEach(imgItem => attachCarouselImageEvents(imgItem));
});

// Mettre à jour le layout avant la soumission du formulaire
document.querySelector('form').addEventListener('submit', function(e) {
    if (isCustomizedCheckbox.checked) {
        updateCustomLayout();
        updateCustomBanners();
        updateCustomCarousels();
        updateCustomColors();
    }
});
</script>
@endpush
