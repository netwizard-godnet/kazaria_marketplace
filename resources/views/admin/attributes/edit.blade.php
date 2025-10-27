@extends('admin.layouts.app')

@section('title', 'Modifier l\'Attribut')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Modifier l'Attribut: {{ $attribute->name }}
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.attributes.update', $attribute) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom de l'attribut <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $attribute->name) }}" 
                                           placeholder="Ex: Couleur, Taille, Marque"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type d'attribut <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" 
                                            id="type" 
                                            name="type" 
                                            required>
                                        <option value="">Sélectionner un type</option>
                                        <option value="select" {{ old('type', $attribute->type) === 'select' ? 'selected' : '' }}>Liste déroulante</option>
                                        <option value="checkbox" {{ old('type', $attribute->type) === 'checkbox' ? 'selected' : '' }}>Cases à cocher</option>
                                        <option value="radio" {{ old('type', $attribute->type) === 'radio' ? 'selected' : '' }}>Boutons radio</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order" class="form-label">Ordre d'affichage</label>
                                    <input type="number" 
                                           class="form-control @error('order') is-invalid @enderror" 
                                           id="order" 
                                           name="order" 
                                           value="{{ old('order', $attribute->order) }}" 
                                           min="0">
                                    @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_filterable" 
                                               name="is_filterable" 
                                               value="1" 
                                               {{ old('is_filterable', $attribute->is_filterable) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_filterable">
                                            Utilisable comme filtre
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Permet aux clients de filtrer les produits par cet attribut
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Valeurs de l'attribut</label>
                            <div id="values-container">
                                @if($attribute->attributeValues->count() > 0)
                                    @foreach($attribute->attributeValues as $value)
                                    <div class="input-group mb-2">
                                        <input type="text" 
                                               class="form-control" 
                                               name="values[]" 
                                               value="{{ $value->value }}"
                                               placeholder="Ex: Rouge, Bleu, Vert">
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="removeValue(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2">
                                        <input type="text" 
                                               class="form-control" 
                                               name="values[]" 
                                               placeholder="Ex: Rouge, Bleu, Vert">
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="removeValue(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" 
                                    class="btn btn-outline-primary btn-sm" 
                                    onclick="addValue()">
                                <i class="fas fa-plus me-2"></i>Ajouter une valeur
                            </button>
                            <small class="form-text text-muted d-block mt-2">
                                Ajoutez les valeurs possibles pour cet attribut (ex: Rouge, Bleu, Vert pour Couleur)
                            </small>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addValue() {
    const container = document.getElementById('values-container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" class="form-control" name="values[]" placeholder="Ex: Rouge, Bleu, Vert">
        <button type="button" class="btn btn-outline-danger" onclick="removeValue(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeValue(button) {
    button.parentElement.remove();
}
</script>
@endsection
