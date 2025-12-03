@extends('admin.layouts.app')

@section('title', 'Détails du Produit')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Détails du Produit</h4>
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
                <span>Détails</span>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">{{ $product->name }}</h4>
                        <div class="btn-group">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informations générales</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $product->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nom:</strong></td>
                                    <td>{{ $product->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Slug:</strong></td>
                                    <td>{{ $product->slug }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Prix:</strong></td>
                                    <td>{{ number_format($product->price, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                @if($product->old_price)
                                <tr>
                                    <td><strong>Ancien prix:</strong></td>
                                    <td>{{ number_format($product->old_price, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Stock:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $product->stock > 0 ? 'success' : 'danger' }}">
                                            {{ $product->stock }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Statut:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $product->is_active ? 'success' : 'danger' }}">
                                            {{ $product->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Catégorisation</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Catégorie:</strong></td>
                                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Sous-catégorie:</strong></td>
                                    <td>{{ $product->subcategory->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Boutique:</strong></td>
                                    <td>{{ $product->store->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Marque:</strong></td>
                                    <td>{{ $product->brand ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Modèle:</strong></td>
                                    <td>{{ $product->model ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Garantie:</strong></td>
                                    <td>{{ $product->warranty ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Description</h6>
                            <p class="text-muted">{{ $product->description ?? 'Aucune description' }}</p>
                        </div>
                    </div>

                    @if($product->images && is_array($product->images) && count($product->images) > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Images</h6>
                            <div class="row">
                                @foreach($product->images as $index => $image)
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <img src="{{ $product->images_urls[$index] ?? asset('storage/' . $image) }}" class="card-img-top" alt="Image {{ $index + 1 }}" style="height: 150px; object-fit: cover;">
                                        <div class="card-body p-2">
                                            <small class="text-muted">Image {{ $index + 1 }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6>Statistiques</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Note:</strong></td>
                                    <td>
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa-solid fa-star {{ $i <= floor($product->rating) ? 'text-warning' : 'text-secondary' }}"></i>
                                        @endfor
                                        ({{ $product->rating ?? 0 }})
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Nombre d'avis:</strong></td>
                                    <td>{{ $product->reviews_count ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Vues:</strong></td>
                                    <td>{{ $product->views_count ?? 0 }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Dates</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Créé le:</strong></td>
                                    <td>{{ $product->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Modifié le:</strong></td>
                                    <td>{{ $product->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Modifier le produit
                        </a>
                        
                        <form action="{{ route('admin.products.toggle-status', $product) }}" method="POST" class="d-grid">
                            @csrf
                            @if($product->is_active)
                                <button type="submit" class="btn btn-secondary" onclick="return confirm('Êtes-vous sûr de vouloir désactiver ce produit ?')">
                                    <i class="fas fa-ban"></i> Désactiver
                                </button>
                            @else
                                <button type="submit" class="btn btn-success" onclick="return confirm('Êtes-vous sûr de vouloir activer ce produit ?')">
                                    <i class="fas fa-check"></i> Activer
                                </button>
                            @endif
                        </form>
                        
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-grid">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.')">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if($product->store)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title">Informations boutique</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nom:</strong> {{ $product->store->name }}</p>
                    <p><strong>Description:</strong> {{ Str::limit($product->store->description ?? 'N/A', 100) }}</p>
                    <p><strong>Statut:</strong> 
                        <span class="badge badge-{{ $product->store->is_active ? 'success' : 'danger' }}">
                            {{ $product->store->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
