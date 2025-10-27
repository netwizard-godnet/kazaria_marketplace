@extends('admin.layouts.app')

@section('title', 'Rapport utilisateurs')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Rapport utilisateurs</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="{{ route('admin.reports.index') }}">Rapports</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Utilisateurs</span></li>
        </ul>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.users') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Du</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Au</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i> Filtrer</button>
                    <a href="{{ route('admin.reports.users') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Réinitialiser</a>
                    <a href="{{ route('admin.reports.export', 'users') }}" class="btn btn-outline-success"><i class="fas fa-download"></i> Export CSV</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Total</h5>
                <h3 class="mb-0">{{ number_format($totals['users'] ?? 0, 0, ',', ' ') }}</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Date</th><th>Nb utilisateurs</th></tr></thead>
                    <tbody>
                        @forelse($usersData as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                            <td>{{ $row->users_count }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center">Aucune donnée</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


