@extends('admin.layouts.app')

@section('title', 'Détails de la Sous-catégorie')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Détails de la Sous-catégorie</h4>
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
                <a href="{{ route('admin.subcategories.index') }}">Sous-catégories</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <span>Détails</span>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="card-title">{{ $subcategory->name }}</h4>
                        <div class="btn-group">
                            <a href="{{ route('admin.subcategories.edit', $subcategory) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <form action="{{ route('admin.subcategories.toggle-status', $subcategory) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-{{ $subcategory->is_active ? 'secondary' : 'success' }} btn-sm" 
                                        onclick="return confirm('{{ $subcategory->is_active ? 'Désactiver' : 'Activer' }} cette sous-catégorie ?')">
                                    <i class="fas fa-{{ $subcategory->is_active ? 'pause' : 'play' }}"></i> 
                                    {{ $subcategory->is_active ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.subcategories.destroy', $subcategory) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" 
                                        onclick="return confirm('Supprimer cette sous-catégorie ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($subcategory->image)
                                <img src="{{ $subcategory->image_url }}" alt="{{ $subcategory->name }}" 
                                     class="img-fluid rounded">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                     style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h5>Informations générales</h5>
                            <ul class="list-unstyled">
                                <li><strong>ID :</strong> {{ $subcategory->id }}</li>
                                <li><strong>Nom :</strong> {{ $subcategory->name }}</li>
                                <li><strong>Slug :</strong> {{ $subcategory->slug }}</li>
                                <li><strong>Catégorie :</strong> {{ $subcategory->category->name ?? 'N/A' }}</li>
                                <li><strong>Statut :</strong> 
                                    <span class="badge badge-{{ $subcategory->is_active ? 'success' : 'danger' }}">
                                        {{ $subcategory->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </li>
                                <li><strong>Ordre :</strong> {{ $subcategory->order ?? 0 }}</li>
                                <li><strong>Créée le :</strong> {{ $subcategory->created_at->format('d/m/Y H:i') }}</li>
                                <li><strong>Modifiée le :</strong> {{ $subcategory->updated_at->format('d/m/Y H:i') }}</li>
                            </ul>
                            
                            @if($subcategory->description)
                                <h5>Description</h5>
                                <p>{{ $subcategory->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
