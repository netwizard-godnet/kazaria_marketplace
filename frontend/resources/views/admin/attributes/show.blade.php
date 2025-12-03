@extends('admin.layouts.app')

@section('title', 'Détails de l\'Attribut')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-tag me-2"></i>Détails de l'Attribut: {{ $attribute->name }}
                    </h4>
                    <div class="btn-group">
                        <a href="{{ route('admin.attributes.edit', $attribute) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </a>
                        <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5>Informations générales</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Nom:</strong></td>
                                        <td>{{ $attribute->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Slug:</strong></td>
                                        <td><code>{{ $attribute->slug }}</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Type:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $attribute->type === 'select' ? 'primary' : ($attribute->type === 'checkbox' ? 'success' : 'info') }}">
                                                {{ ucfirst($attribute->type) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Filtrable:</strong></td>
                                        <td>
                                            @if($attribute->is_filterable)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> Oui
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times"></i> Non
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Ordre:</strong></td>
                                        <td><span class="badge bg-secondary">{{ $attribute->order }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Créé le:</strong></td>
                                        <td>{{ $attribute->created_at->format('d/m/Y à H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Modifié le:</strong></td>
                                        <td>{{ $attribute->updated_at->format('d/m/Y à H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5>Valeurs de l'attribut</h5>
                                @if($attribute->attributeValues->count() > 0)
                                    <div class="list-group">
                                        @foreach($attribute->attributeValues as $value)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $value->value }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $value->slug }}</small>
                                            </div>
                                            <span class="badge bg-info">Ordre: {{ $value->order }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Aucune valeur définie pour cet attribut.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($attribute->attributeValues->count() > 0)
                    <div class="row">
                        <div class="col-12">
                            <h5>Statistiques d'utilisation</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $attribute->attributeValues->count() }}</h3>
                                            <p class="mb-0">Valeurs définies</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h3>0</h3>
                                            <p class="mb-0">Produits utilisant cet attribut</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $attribute->is_filterable ? 'Oui' : 'Non' }}</h3>
                                            <p class="mb-0">Utilisable comme filtre</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ ucfirst($attribute->type) }}</h3>
                                            <p class="mb-0">Type d'affichage</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
