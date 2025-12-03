@extends('admin.layouts.app')
@section('title', 'Ajouter un produit')
@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Ajouter un produit</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="{{ route('admin.products.index') }}">Produits</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Ajouter</span></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header"><h3>Ajouter un produit</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-3">
                            <label>Nom</label>
                            <input type="text" name="name" class="form-control" required autofocus>
                        </div>
                        <div class="form-group mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label>Prix</label>
                            <input type="number" name="price" class="form-control" required min="0">
                        </div>
                        <div class="form-group mb-3">
                            <label>Stock</label>
                            <input type="number" name="stock" class="form-control" required min="0">
                        </div>
                        <div class="form-group mb-3">
                            <label>Catégorie</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">-- Choisir une catégorie --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label>Images</label>
                            <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-success">Enregistrer</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-link">Annuler</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
