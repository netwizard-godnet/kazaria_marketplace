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
                            <h6>Images ({{ count($product->images) }})</h6>
                            <div class="row">
                                @php
                                    $imagesUrls = $product->images_urls ?? [];
                                @endphp
                                @foreach($product->images as $index => $image)
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <img src="{{ $imagesUrls[$index] ?? asset('storage/' . $image) }}" class="card-img-top" alt="Image {{ $index + 1 }}" style="height: 150px; object-fit: cover; cursor: pointer;" onclick="window.open(this.src, '_blank')">
                                        <div class="card-body p-2">
                                            <small class="text-muted">Image {{ $index + 1 }}</small>
                                            @if($index === 0)
                                                <span class="badge badge-primary ml-1">Principale</span>
                                            @endif
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

                    @if(isset($product->status))
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Statut d'approbation</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Statut:</strong></td>
                                    <td>
                                        @if($product->status == 'approved')
                                            <span class="badge badge-success">Approuvé</span>
                                        @elseif($product->status == 'rejected')
                                            <span class="badge badge-danger">Rejeté</span>
                                        @else
                                            <span class="badge badge-warning">En attente</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @endif

                    @if($product->is_featured || $product->is_trending || $product->is_new || $product->is_best_offer)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Badges et labels</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @if($product->is_featured)
                                    <span class="badge badge-primary"><i class="fas fa-star"></i> Produit vedette</span>
                                @endif
                                @if($product->is_trending)
                                    <span class="badge badge-info"><i class="fas fa-fire"></i> Tendance</span>
                                @endif
                                @if($product->is_new)
                                    <span class="badge badge-success"><i class="fas fa-tag"></i> Nouveau</span>
                                @endif
                                @if($product->is_best_offer)
                                    <span class="badge badge-warning"><i class="fas fa-percent"></i> Meilleure offre</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($product->tags && count($product->tags) > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Tags</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($product->tags as $tag)
                                    <span class="badge badge-secondary">{{ $tag }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($product->attributeValues && $product->attributeValues->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Attributs</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Attribut</th>
                                            <th>Valeur(s)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $attributesGrouped = $product->attributeValues->groupBy(function($item) {
                                                return $item->attribute->name ?? 'Autres';
                                            });
                                        @endphp
                                        @foreach($attributesGrouped as $attributeName => $values)
                                            <tr>
                                                <td><strong>{{ $attributeName }}</strong></td>
                                                <td>
                                                    @foreach($values as $value)
                                                        <span class="badge badge-info mr-1">{{ $value->value }}</span>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($product->variations && $product->variations->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Variations du produit</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>SKU</th>
                                            <th>Attributs</th>
                                            <th>Prix</th>
                                            <th>Ancien prix</th>
                                            <th>Stock</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($product->variations as $variation)
                                            <tr class="{{ $variation->is_default ? 'table-warning' : '' }}">
                                                <td>
                                                    {{ $variation->sku ?? 'N/A' }}
                                                    @if($variation->is_default)
                                                        <span class="badge badge-warning ml-1">Par défaut</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($variation->attributeValues && $variation->attributeValues->count() > 0)
                                                        @php
                                                            $variationAttributesGrouped = $variation->attributeValues->groupBy(function($item) {
                                                                return $item->attribute->name ?? 'Autres';
                                                            });
                                                        @endphp
                                                        @foreach($variationAttributesGrouped as $attrName => $values)
                                                            <strong>{{ $attrName }}:</strong>
                                                            @foreach($values as $value)
                                                                <span class="badge badge-secondary">{{ $value->value }}</span>
                                                            @endforeach
                                                            @if(!$loop->last) <br> @endif
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">Aucun attribut</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ number_format($variation->price, 0, ',', ' ') }} FCFA</strong>
                                                    @if($variation->old_price && $variation->old_price > $variation->price)
                                                        <br><small class="text-danger">Promo: {{ number_format($variation->price, 0, ',', ' ') }} FCFA</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($variation->old_price && $variation->old_price > $variation->price)
                                                        <span class="text-muted"><s>{{ number_format($variation->old_price, 0, ',', ' ') }} FCFA</s></span>
                                                        @if($variation->discount_percentage)
                                                            <br><small class="text-success">-{{ $variation->discount_percentage }}%</small>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $variation->stock > 0 ? 'success' : 'danger' }}">
                                                        {{ $variation->stock }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $variation->is_active ? 'success' : 'danger' }}">
                                                        {{ $variation->is_active ? 'Actif' : 'Inactif' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($product->meta_description || $product->meta_keywords)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Métadonnées SEO</h6>
                            <table class="table table-borderless">
                                @if($product->meta_description)
                                <tr>
                                    <td><strong>Meta description:</strong></td>
                                    <td>{{ $product->meta_description }}</td>
                                </tr>
                                @endif
                                @if($product->meta_keywords)
                                <tr>
                                    <td><strong>Meta keywords:</strong></td>
                                    <td>{{ $product->meta_keywords }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    @endif
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
                        
                        @if(isset($product->status))
                            @if($product->status == 'pending')
                                <form action="{{ route('admin.products.approve', $product) }}" method="POST" class="d-grid">
                                    @csrf
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Approuver ce produit ?')">
                                        <i class="fas fa-check-circle"></i> Approuver
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.products.reject', $product) }}" method="POST" class="d-grid">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Rejeter ce produit ?')">
                                        <i class="fas fa-times-circle"></i> Rejeter
                                    </button>
                                </form>
                            @endif
                        @endif
                        
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
                        @php
                            $storeStatus = $product->store->effective_kyc_status ?? $product->store->status ?? 'pending';
                            $statusLabels = [
                                'active' => ['label' => 'Actif', 'class' => 'success'],
                                'pending' => ['label' => 'En attente', 'class' => 'warning'],
                                'suspended' => ['label' => 'Suspendu', 'class' => 'danger'],
                                'rejected' => ['label' => 'Rejeté', 'class' => 'danger'],
                                'validated' => ['label' => 'Validé', 'class' => 'success'],
                                'approved' => ['label' => 'Approuvé', 'class' => 'success'],
                                'approve' => ['label' => 'Approuvé', 'class' => 'success'],
                            ];
                            $statusInfo = $statusLabels[strtolower($storeStatus)] ?? ['label' => ucfirst($storeStatus), 'class' => 'secondary'];
                        @endphp
                        <span class="badge badge-{{ $statusInfo['class'] }}">
                            {{ $statusInfo['label'] }}
                        </span>
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
