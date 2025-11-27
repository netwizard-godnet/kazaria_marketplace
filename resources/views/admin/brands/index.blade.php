@extends('admin.layouts.app')

@section('title', 'Gestion des Marques')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Gestion des Marques</h4>
        <p class="text-muted">Gérez les marques affichées sur la page d'accueil (maximum 12 marques, 2 lignes de 6)</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="mb-2"><i class="fas fa-exclamation-triangle mr-1"></i>Une ou plusieurs erreurs sont survenues :</h6>
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Ajouter une marque</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>Nom de la marque</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Ex: Samsung" value="{{ old('name') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Image de la marque <span class="text-danger">*</span></label>
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" required accept="image/*">
                            <small class="text-muted">Tous formats acceptés, aucune limite de taille (optimisez vos fichiers).</small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Lien (optionnel)</label>
                            <input type="url" name="link_url" class="form-control @error('link_url') is-invalid @enderror" placeholder="https://..." value="{{ old('link_url') }}">
                            <small class="text-muted">Lien vers lequel la marque redirigera.</small>
                            @error('link_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-row">
                            <div class="col">
                                <label>Ordre d'affichage</label>
                                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                            </div>
                            <div class="col">
                                <label>Statut</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Actif</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactif</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-plus"></i> Ajouter la marque
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Liste des marques ({{ $brands->count() }}/12)</h4>
                </div>
                <div class="card-body">
                    @if($brands->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Nom</th>
                                        <th>Lien</th>
                                        <th>Ordre</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($brands as $brand)
                                        <tr>
                                            <td>
                                                @if($brand->image_url)
                                                    <img src="{{ $brand->image_url }}" alt="{{ $brand->name }}" style="max-width: 80px; max-height: 50px; object-fit: contain;">
                                                @else
                                                    <span class="text-muted">Aucune image</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(filled($brand->name))
                                                    <strong>{{ $brand->name }}</strong>
                                                @endif
                                            </td>
                                            <td>
                                                @if($brand->link_url)
                                                    <a href="{{ $brand->link_url }}" target="_blank" class="text-primary">
                                                        <i class="fas fa-external-link-alt"></i> Voir
                                                    </a>
                                                @else
                                                    <span class="text-muted">Aucun lien</span>
                                                @endif
                                            </td>
                                            <td>{{ $brand->sort_order }}</td>
                                            <td>
                                                @if($brand->is_active)
                                                    <span class="badge badge-success">Actif</span>
                                                @else
                                                    <span class="badge badge-secondary">Inactif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info btn-edit-brand" data-toggle="modal" data-target="#editBrandModal{{ $brand->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette marque ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune marque ajoutée pour le moment</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

</div>

@foreach($brands as $brand)
    <div class="modal fade" id="editBrandModal{{ $brand->id }}" tabindex="-1" aria-labelledby="editBrandLabel{{ $brand->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBrandLabel{{ $brand->id }}">Modifier la marque</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.brands.update', $brand) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nom de la marque</label>
                            <input type="text" name="name" class="form-control" value="{{ $brand->name }}">
                        </div>
                        <div class="form-group">
                            <label>Image actuelle</label>
                            @if($brand->image_url)
                                <div class="mb-2">
                                    <img src="{{ $brand->image_url }}" alt="{{ $brand->name }}" style="max-width: 200px; max-height: 100px; object-fit: contain;">
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Laisser vide pour conserver l'image actuelle.</small>
                        </div>
                        <div class="form-group">
                            <label>Lien (optionnel)</label>
                            <input type="url" name="link_url" class="form-control" value="{{ $brand->link_url }}" placeholder="https://...">
                        </div>
                        <div class="form-row">
                            <div class="col">
                                <label>Ordre d'affichage</label>
                                <input type="number" name="sort_order" class="form-control" value="{{ $brand->sort_order }}" min="0">
                            </div>
                            <div class="col">
                                <label>Statut</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" {{ $brand->is_active ? 'selected' : '' }}>Actif</option>
                                    <option value="0" {{ !$brand->is_active ? 'selected' : '' }}>Inactif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-edit-brand').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                const targetSelector = this.getAttribute('data-target');

                if (!targetSelector) {
                    return;
                }

                const modal = document.querySelector(targetSelector);

                if (!modal) {
                    return;
                }

                if (typeof window.$ === 'function' && typeof window.$.fn.modal === 'function') {
                    window.$(modal).modal('show');
                } else if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function') {
                    const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
                    bsModal.show();
                }
            });
        });
    });
</script>
@endpush
@endsection
