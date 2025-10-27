@extends('admin.layouts.app')

@section('title', 'Gestion des Attributs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-tags me-2"></i>Gestion des Attributs
                    </h4>
                    <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Ajouter un Attribut
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($attributes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Filtrable</th>
                                        <th>Valeurs</th>
                                        <th>Ordre</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attributes as $attribute)
                                    <tr>
                                        <td>
                                            <strong>{{ $attribute->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $attribute->slug }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $attribute->type === 'select' ? 'primary' : ($attribute->type === 'checkbox' ? 'success' : 'info') }}">
                                                {{ ucfirst($attribute->type) }}
                                            </span>
                                        </td>
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
                                        <td>
                                            <span class="badge bg-info">{{ $attribute->attributeValues->count() }} valeurs</span>
                                            @if($attribute->attributeValues->count() > 0)
                                                <br>
                                                <small class="text-muted">
                                                    {{ $attribute->attributeValues->take(3)->pluck('value')->join(', ') }}
                                                    @if($attribute->attributeValues->count() > 3)
                                                        ...
                                                    @endif
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $attribute->order }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.attributes.show', $attribute) }}" 
                                                   class="btn btn-sm btn-outline-info" 
                                                   title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.attributes.edit', $attribute) }}" 
                                                   class="btn btn-sm btn-outline-warning" 
                                                   title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.attributes.destroy', $attribute) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet attribut ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun attribut trouvé</h5>
                            <p class="text-muted">Commencez par créer votre premier attribut.</p>
                            <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Créer un Attribut
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
