@extends('admin.layouts.app')

@section('title', 'Codes promo')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Codes promo</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Marketing</span></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Codes promo</span></li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Générer des codes</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.coupons.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nombre de codes</label>
                            <input type="number" name="quantity" class="form-control" min="1" max="500" value="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pourcentage de remise</label>
                            <input type="number" name="discount_percent" class="form-control" min="1" max="100" value="10" required>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Préfixe</label>
                                <input type="text" name="prefix" class="form-control" placeholder="KAZ">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Max utilisations / code</label>
                                <input type="number" name="max_uses" class="form-control" min="1" placeholder="illimité">
                            </div>
                        </div>
                        <div class="row g-2 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Début</label>
                                <input type="datetime-local" name="starts_at" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fin</label>
                                <input type="datetime-local" name="ends_at" class="form-control">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary w-100" type="submit"><i class="fas fa-magic me-1"></i>Générer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Liste des codes</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>%</th>
                                    <th>Utilisations</th>
                                    <th>Validité</th>
                                    <th>Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($coupons as $coupon)
                                <tr>
                                    <td class="fw-bold">{{ $coupon->code }}</td>
                                    <td>{{ $coupon->discount_percent }}%</td>
                                    <td>{{ $coupon->uses }}{{ $coupon->max_uses ? ' / '.$coupon->max_uses : '' }}</td>
                                    <td>
                                        @if($coupon->starts_at) {{ $coupon->starts_at->format('d/m/Y H:i') }} @else — @endif
                                        <br>
                                        @if($coupon->ends_at) {{ $coupon->ends_at->format('d/m/Y H:i') }} @else — @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $coupon->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $coupon->is_active ? 'Actif' : 'Inactif' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.coupons.toggle', $coupon) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary">{{ $coupon->is_active ? 'Désactiver' : 'Activer' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="d-inline" onsubmit="return confirm('Supprimer ce code ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted">Aucun code</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $coupons->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


