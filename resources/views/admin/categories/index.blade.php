@extends('admin.layouts.app')

@section('title', 'Gestion des catégories')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Gestion des catégories</h4>
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
                <span>Catégories</span>
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
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="card-title">Catégories et sous-catégories</h4>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal">
                            <i class="fas fa-plus"></i> Ajouter une catégorie
                        </button>
                    </div>
                </div>

                <!-- Barre de recherche et filtres -->
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.categories.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">Rechercher</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" placeholder="Nom ou description...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Statut</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Tous</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actives</option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactives</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sort_by">Trier par</label>
                                    <select class="form-control" id="sort_by" name="sort_by">
                                        <option value="order" {{ request('sort_by') === 'order' ? 'selected' : '' }}>Ordre</option>
                                        <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Nom</option>
                                        <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Date de création</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="sort_direction">Direction</label>
                                    <select class="form-control" id="sort_direction" name="sort_direction">
                                        <option value="asc" {{ request('sort_direction') === 'asc' ? 'selected' : '' }}>Croissant</option>
                                        <option value="desc" {{ request('sort_direction') === 'desc' ? 'selected' : '' }}>Décroissant</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Rechercher
                                </button>
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Effacer
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="card-body">
                    @if($categories->count() > 0)
                        <div class="row">
                            @foreach($categories as $category)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            @if($category->image)
                                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" 
                                                     class="img-thumbnail mr-3" width="50" height="50" style="object-fit: cover;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center mr-3" 
                                                     style="width: 50px; height: 50px; border-radius: 4px;">
                                                    <i class="fas fa-folder text-muted"></i>
                                                </div>
                                            @endif
                                            <div class="flex-grow-1">
                                                <h5 class="mb-0">{{ $category->name }}</h5>
                                                <small class="text-muted">
                                                    {{ $category->subcategories->count() }} sous-catégories • 
                                                    {{ $category->products->count() }} produits
                                                </small>
                                                <div class="mt-1">
                                                    <span class="badge badge-{{ $category->is_active ? 'success' : 'danger' }}">
                                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                    @if($category->order)
                                                        <span class="badge badge-info">Ordre: {{ $category->order }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($category->description)
                                            <p class="text-muted small">{{ Str::limit($category->description, 100) }}</p>
                                        @endif
                                        
                                        @if($category->subcategories->count() > 0)
                                            <div class="mb-3">
                                                <h6 class="text-muted small">Sous-catégories :</h6>
                                                <div class="d-flex flex-wrap">
                                                    @foreach($category->subcategories->take(3) as $subcategory)
                                                        <span class="badge badge-secondary mr-1 mb-1 small">{{ $subcategory->name }}</span>
                                                    @endforeach
                                                    @if($category->subcategories->count() > 3)
                                                        <span class="badge badge-light mr-1 mb-1 small">+{{ $category->subcategories->count() - 3 }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <div class="mt-3">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                                                        <!-- Bouton Voir -->
                                                        <a href="{{ route('admin.categories.show', $category) }}" 
                                                           class="btn btn-info btn-sm action-btn" 
                                                           title="Voir les détails">
                                                            <i class="fas fa-eye me-1"></i>
                                                            Voir
                                                        </a>
                                                        
                                                        <!-- Bouton Modifier -->
                                                        <a href="{{ route('admin.categories.edit', $category) }}" 
                                                           class="btn btn-warning btn-sm action-btn" 
                                                           title="Modifier">
                                                            <i class="fas fa-edit me-1"></i>
                                                            Modifier
                                                        </a>
                                                        
                                                        <!-- Bouton Toggle Status -->
                                                        <form action="{{ route('admin.categories.toggle-status', $category) }}" 
                                                              method="POST" 
                                                              class="d-inline">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="btn btn-{{ $category->is_active ? 'secondary' : 'success' }} btn-sm action-btn" 
                                                                    title="{{ $category->is_active ? 'Désactiver' : 'Activer' }}"
                                                                    onclick="return confirm('{{ $category->is_active ? 'Désactiver' : 'Activer' }} cette catégorie ?')">
                                                                <i class="fas fa-{{ $category->is_active ? 'ban' : 'check' }} me-1"></i>
                                                                {{ $category->is_active ? 'Désactiver' : 'Activer' }}
                                                            </button>
                                                        </form>
                                                        
                                                        <!-- Bouton Supprimer -->
                                                        <form action="{{ route('admin.categories.destroy', $category) }}" 
                                                              method="POST" 
                                                              class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-danger btn-sm action-btn" 
                                                                    title="Supprimer"
                                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Cette action est irréversible.')">
                                                                <i class="fas fa-trash me-1"></i>
                                                                Supprimer
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($categories->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted">
                                Affichage de {{ $categories->firstItem() }} à {{ $categories->lastItem() }} sur {{ $categories->total() }} catégories
                            </div>
                            <div class="pagination-wrapper">
                                {{ $categories->appends(request()->query())->links('pagination.bootstrap-4') }}
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5>Aucune catégorie trouvée</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'status']))
                                    Aucune catégorie ne correspond à vos critères de recherche.
                                @else
                                    Commencez par créer votre première catégorie.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter Catégorie -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une catégorie</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nom de la catégorie *</label>
                                <input type="text" class="form-control" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="order">Ordre d'affichage</label>
                                <input type="number" class="form-control" 
                                       id="order" name="order" value="{{ old('order', 0) }}" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" class="form-control-file" 
                               id="image" name="image" accept="image/*">
                        <small class="text-muted">Formats acceptés: JPG, PNG, GIF. Taille max: 2MB.</small>
                    </div>

                    <!-- Aperçu de l'image -->
                    <div class="form-group" id="image-preview" style="display: none;">
                        <label>Aperçu de l'image</label>
                        <div class="mb-3">
                            <img id="preview-img" src="" alt="Aperçu" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                   value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Catégorie active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Styles pour les boutons d'action */
.action-btn {
    min-width: 80px;
    border-radius: 6px !important;
    transition: all 0.3s ease;
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
    border: 1px solid transparent;
    font-weight: 500;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    border-color: rgba(0,0,0,0.1);
}

.action-btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.action-btn i {
    font-size: 0.8rem;
}

/* Espacement entre les boutons */
.gap-2 > * {
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
}

.gap-2 > *:last-child {
    margin-right: 0;
}

/* Amélioration des cartes */
.card {
    transition: all 0.3s ease;
    border: 1px solid #e3e6f0;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Amélioration des images */
.img-thumbnail {
    border: 2px solid #e3e6f0;
    transition: all 0.3s ease;
}

.img-thumbnail:hover {
    border-color: #007bff;
}

/* Amélioration des badges */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
}

/* Responsive pour les boutons */
@media (max-width: 768px) {
    .d-flex.flex-wrap {
        flex-direction: column;
        align-items: stretch;
    }
    
    .action-btn {
        width: 100%;
        margin-right: 0 !important;
        margin-bottom: 0.5rem;
        text-align: center;
    }
    
    .action-btn:last-child {
        margin-bottom: 0;
    }
}

@media (max-width: 576px) {
    .action-btn {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
        min-width: 70px;
    }
    
    .action-btn i {
        font-size: 0.75rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Aperçu de l'image dans le modal
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

// Recherche en temps réel
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        this.form.submit();
    }
});

// Filtres automatiques
document.getElementById('status').addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('sort_by').addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('sort_direction').addEventListener('change', function() {
    this.form.submit();
});

// Amélioration des boutons d'action
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter des événements de clic pour debug
    const actionButtons = document.querySelectorAll('.btn-group .btn');
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            console.log('Bouton cliqué:', this.title, this.href || this.form?.action);
        });
    });
});
</script>
@endpush
