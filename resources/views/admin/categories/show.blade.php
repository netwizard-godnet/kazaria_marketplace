@extends('admin.layouts.app')

@section('title', 'Détails de la catégorie')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Détails de la catégorie</h4>
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
                <span>{{ $category->name }}</span>
            </li>
        </ul>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="card-title">{{ $category->name }}</h4>
                        <div class="btn-group">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <form action="{{ route('admin.categories.toggle-status', $category) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-{{ $category->is_active ? 'secondary' : 'success' }}" 
                                        onclick="return confirm('{{ $category->is_active ? 'Désactiver' : 'Activer' }} cette catégorie ?')">
                                    <i class="fas fa-{{ $category->is_active ? 'ban' : 'check' }}"></i> 
                                    {{ $category->is_active ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Cette action est irréversible.')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="img-fluid rounded">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 200px;">
                                    <i class="fas fa-folder fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h5>Description</h5>
                            <p class="text-muted">{{ $category->description ?? 'Aucune description' }}</p>
                            
                            <h5>Informations</h5>
                            <ul class="list-unstyled">
                                <li><strong>ID :</strong> {{ $category->id }}</li>
                                <li><strong>Nom :</strong> {{ $category->name }}</li>
                                <li><strong>Slug :</strong> {{ $category->slug ?? 'N/A' }}</li>
                                <li><strong>Statut :</strong> 
                                    <span class="badge badge-{{ $category->is_active ? 'success' : 'danger' }}">
                                        {{ $category->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </li>
                                <li><strong>Ordre d'affichage :</strong> {{ $category->order ?? 0 }}</li>
                                <li><strong>Créée le :</strong> {{ $category->created_at->format('d/m/Y H:i') }}</li>
                                <li><strong>Modifiée le :</strong> {{ $category->updated_at->format('d/m/Y H:i') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Statistiques</h4>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h3 class="text-primary">{{ $category->subcategories->count() }}</h3>
                            <p class="text-muted">Sous-catégories</p>
                        </div>
                        <div class="col-6">
                            <h3 class="text-success">{{ $category->products()->count() }}</h3>
                            <p class="text-muted">Produits</p>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($category->subcategories->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Sous-catégories</h4>
                </div>
                <div class="card-body">
                    @foreach($category->subcategories as $subcategory)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $subcategory->name }}</span>
                            <span class="badge badge-info">{{ $subcategory->products()->count() }} produits</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            @if($category->products->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Produits récents</h4>
                </div>
                <div class="card-body">
                    @foreach($category->products->take(5) as $product)
                        <div class="d-flex align-items-center mb-2">
                            @if($product->first_image_url)
                                <img src="{{ $product->first_image_url }}" alt="{{ $product->name }}" 
                                     class="img-thumbnail mr-2" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center mr-2" 
                                     style="width: 40px; height: 40px; border-radius: 4px;">
                                    <i class="fas fa-box text-muted"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <div class="font-weight-bold">{{ Str::limit($product->name, 30) }}</div>
                                <small class="text-muted">{{ number_format($product->price, 0, ',', ' ') }} FCFA</small>
                            </div>
                            <span class="badge badge-{{ $product->is_active ? 'success' : 'danger' }}">
                                {{ $product->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    @endforeach
                    @if($category->products->count() > 5)
                        <div class="text-center mt-2">
                            <small class="text-muted">Et {{ $category->products->count() - 5 }} autres produits...</small>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.btn-group .btn {
    margin-right: 5px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-group .btn:active {
    transform: translateY(0);
}
</style>
@endpush
