@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/store.css') }}">
<div class="container my-5 store-create-form">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="fw-bold blue-color">
                    <i class="bi bi-shop me-2"></i>Créer ma boutique sur KAZARIA
                </h1>
                <p class="text-muted">Rejoignez notre marketplace et développez votre business en ligne</p>
            </div>

            <!-- Formulaire -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form id="createStoreForm" enctype="multipart/form-data">
                        @csrf

                        <!-- Section 1: Informations générales -->
                        <div class="mb-5">
                            <h4 class="fw-bold orange-color mb-3">
                                <i class="bi bi-info-circle me-2"></i>Informations générales
                            </h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nom de la boutique <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                    <small class="text-muted">Le nom doit être unique</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="category_id" class="form-label">Catégorie <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category_select" required>
                                        <option value="">Sélectionnez une catégorie</option>
                                        @foreach($categories as $category)
                                            <optgroup label="{{ $category->name }}">
                                                <option value="{{ $category->id }}">
                                                    @if($category->icon)
                                                        {{ $category->name }} (Catégorie principale)
                                                    @else
                                                        {{ $category->name }}
                                                    @endif
                                                </option>
                                                @foreach($category->subcategories as $subcategory)
                                                    <option value="sub_{{ $subcategory->id }}">
                                                        &nbsp;&nbsp;&nbsp;└─ {{ $subcategory->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Choisissez une catégorie ou une sous-catégorie</small>
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label">Description de la boutique <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                                    <small class="text-muted">Minimum 50 caractères. Décrivez vos produits et services.</small>
                                    <div class="form-text" id="charCount">0 / 50 caractères minimum</div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Coordonnées -->
                        <div class="mb-5">
                            <h4 class="fw-bold orange-color mb-3">
                                <i class="bi bi-telephone me-2"></i>Coordonnées
                            </h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Numéro de téléphone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email de la boutique <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="address" class="form-label">Adresse physique</label>
                                    <input type="text" class="form-control" id="address" name="address">
                                </div>

                                <div class="col-md-6">
                                    <label for="city" class="form-label">Ville</label>
                                    <input type="text" class="form-control" id="city" name="city">
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Visuels -->
                        <div class="mb-5">
                            <h4 class="fw-bold orange-color mb-3">
                                <i class="bi bi-image me-2"></i>Visuels de la boutique
                            </h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="logo" class="form-label">Logo de la boutique</label>
                                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                    <small class="text-muted">Format: JPG, PNG (Max: 2 MB)</small>
                                    <div class="mt-2">
                                        <img id="logoPreview" src="" alt="" class="img-thumbnail d-none" style="max-height: 150px;">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="banner" class="form-label">Bannière de la boutique</label>
                                    <input type="file" class="form-control" id="banner" name="banner" accept="image/*">
                                    <small class="text-muted">Format: JPG, PNG (Max: 5 MB)</small>
                                    <div class="mt-2">
                                        <img id="bannerPreview" src="" alt="" class="img-thumbnail d-none" style="max-height: 150px; max-width: 100%;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Documents légaux -->
                        <div class="mb-5">
                            <h4 class="fw-bold orange-color mb-3">
                                <i class="bi bi-file-earmark-text me-2"></i>Documents légaux
                            </h4>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Ces documents sont nécessaires pour la validation de votre boutique par notre équipe.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="dfe_document" class="form-label">Déclaration Fiscale d'Existence (DFE) <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="dfe_document" name="dfe_document" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <small class="text-muted">Format: PDF, JPG, PNG (Max: 5 MB)</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="commerce_register" class="form-label">Registre de Commerce <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="commerce_register" name="commerce_register" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <small class="text-muted">Format: PDF, JPG, PNG (Max: 5 MB)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Réseaux sociaux -->
                        <div class="mb-5">
                            <h4 class="fw-bold orange-color mb-3">
                                <i class="bi bi-share me-2"></i>Réseaux sociaux (Optionnel)
                            </h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="facebook" class="form-label">
                                        <i class="bi bi-facebook text-primary"></i> Facebook
                                    </label>
                                    <input type="url" class="form-control" id="facebook" name="facebook" placeholder="https://facebook.com/ma-boutique">
                                </div>

                                <div class="col-md-6">
                                    <label for="instagram" class="form-label">
                                        <i class="bi bi-instagram text-danger"></i> Instagram
                                    </label>
                                    <input type="url" class="form-control" id="instagram" name="instagram" placeholder="https://instagram.com/ma-boutique">
                                </div>

                                <div class="col-md-6">
                                    <label for="twitter" class="form-label">
                                        <i class="bi bi-twitter text-info"></i> Twitter / X
                                    </label>
                                    <input type="url" class="form-control" id="twitter" name="twitter" placeholder="https://twitter.com/ma-boutique">
                                </div>

                                <div class="col-md-6">
                                    <label for="website" class="form-label">
                                        <i class="bi bi-globe"></i> Site Web
                                    </label>
                                    <input type="url" class="form-control" id="website" name="website" placeholder="https://mon-site.com">
                                </div>
                            </div>
                        </div>

                        <!-- Conditions -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    J'accepte les <a href="#" class="orange-color">conditions générales de vente</a> et la 
                                    <a href="#" class="orange-color">politique de confidentialité</a> de KAZARIA <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('accueil') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn orange-bg text-white px-5" id="submitBtn">
                                <i class="bi bi-check-circle me-2"></i>Soumettre ma demande
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Avantages -->
            <div class="row mt-5">
                <div class="col-md-4 text-center mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-graph-up-arrow orange-color" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Augmentez vos ventes</h5>
                            <p class="text-muted">Accédez à des milliers de clients potentiels</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-shield-check orange-color" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Sécurité garantie</h5>
                            <p class="text-muted">Paiements sécurisés et protection des données</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-headset orange-color" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Support dédié</h5>
                            <p class="text-muted">Une équipe à votre écoute 7j/7</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createStoreForm');
    const submitBtn = document.getElementById('submitBtn');
    const descriptionField = document.getElementById('description');
    const charCount = document.getElementById('charCount');

    // Compteur de caractères pour la description
    descriptionField.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = `${length} / 50 caractères minimum`;
        charCount.className = length >= 50 ? 'form-text text-success' : 'form-text text-danger';
    });

    // Prévisualisation du logo
    document.getElementById('logo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logoPreview');
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    // Prévisualisation de la bannière
    document.getElementById('banner').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('bannerPreview');
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    // Créer les champs cachés pour category_id et subcategory_id
    const categoryInput = document.createElement('input');
    categoryInput.type = 'hidden';
    categoryInput.id = 'category_id';
    categoryInput.name = 'category_id';
    form.appendChild(categoryInput);
    
    const subcategoryInput = document.createElement('input');
    subcategoryInput.type = 'hidden';
    subcategoryInput.id = 'subcategory_id';
    subcategoryInput.name = 'subcategory_id';
    form.appendChild(subcategoryInput);

    // Gérer le changement de catégorie
    const categorySelect = document.getElementById('category_select');
    categorySelect.addEventListener('change', function() {
        const value = this.value;
        
        if (!value) {
            categoryInput.value = '';
            subcategoryInput.value = '';
            return;
        }
        
        // Déterminer si c'est une catégorie ou sous-catégorie
        if (value.startsWith('sub_')) {
            // C'est une sous-catégorie
            const subcategoryId = value.replace('sub_', '');
            subcategoryInput.value = subcategoryId;
            
            // Trouver la catégorie parent
            const option = this.options[this.selectedIndex];
            const optgroup = option.closest('optgroup');
            const categoryOption = optgroup.querySelector('option:first-child');
            categoryInput.value = categoryOption.value;
            
            console.log('Sous-catégorie sélectionnée:', subcategoryId, 'Catégorie parent:', categoryOption.value);
        } else {
            // C'est une catégorie
            categoryInput.value = value;
            subcategoryInput.value = '';
            
            console.log('Catégorie sélectionnée:', value);
        }
    });

    // Soumission du formulaire
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validation de la description
        if (descriptionField.value.length < 50) {
            showNotification('danger', 'La description doit contenir au moins 50 caractères');
            return;
        }

        // Désactiver le bouton
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Envoi en cours...';

        const formData = new FormData(form);
        const token = localStorage.getItem('auth_token');

        try {
            const response = await fetch('{{ route("store.store") }}', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showNotification('success', data.message);
                setTimeout(() => {
                    window.location.href = data.redirect + '?token=' + token;
                }, 2000);
            } else {
                showNotification('danger', data.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Soumettre ma demande';
            }

        } catch (error) {
            console.error('Erreur:', error);
            showNotification('danger', 'Une erreur est survenue. Veuillez réessayer.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Soumettre ma demande';
        }
    });

    // Fonction de notification
    function showNotification(type, message) {
        if (typeof window.showNotification === 'function') {
            window.showNotification(type, message);
        } else {
            alert(message);
        }
    }
});
</script>
@endsection

