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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Prix (FCFA)</label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" min="0" step="0.01" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stock">Stock</label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" min="0" required>
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">Catégorie</label>
                                    <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                        <option value="">Sélectionner une catégorie</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
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
                                    <label for="status">Statut</label>
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
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Produit actif
                                </label>
                            </div>
                        </div>

                        <!-- Section Images -->
                        <div class="form-group">
                            <label>Images du produit</label>
                            
                            <!-- Images actuelles -->
                            @if($product->images && is_array($product->images) && count($product->images) > 0)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6>Images actuelles :</h6>
                                    <div class="row">
                                        @foreach($product->images as $index => $image)
                                        <div class="col-md-3 mb-2">
                                            <div class="card">
                                                <img src="{{ $product->images_urls[$index] ?? asset('storage/' . $image) }}" class="card-img-top" alt="Image {{ $index + 1 }}" style="height: 100px; object-fit: cover;">
                                                <div class="card-body p-2">
                                                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeImage({{ $index }})">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Upload de nouvelles images -->
                            <div class="row">
                                <div class="col-12">
                                    <h6>Ajouter de nouvelles images :</h6>
                                    <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*">
                                    <small class="text-muted">Vous pouvez sélectionner plusieurs images. Formats acceptés: JPG, PNG, GIF. Taille max: 5MB par image.</small>
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

// Aperçu des nouvelles images
document.getElementById('images').addEventListener('change', function(e) {
    const files = e.target.files;
    const previewContainer = document.createElement('div');
    previewContainer.className = 'row mt-3';
    previewContainer.innerHTML = '<div class="col-12"><h6>Aperçu des nouvelles images :</h6></div>';
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-md-3 mb-2';
                col.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" class="card-img-top" style="height: 100px; object-fit: cover;">
                        <div class="card-body p-2">
                            <small class="text-muted">${file.name}</small>
                        </div>
                    </div>
                `;
                previewContainer.appendChild(col);
            };
            reader.readAsDataURL(file);
        }
    }
    
    // Supprimer l'ancien aperçu s'il existe
    const oldPreview = document.querySelector('.preview-container');
    if (oldPreview) {
        oldPreview.remove();
    }
    
    previewContainer.className += ' preview-container';
    document.querySelector('.form-group:last-of-type').appendChild(previewContainer);
});
</script>
@endpush
