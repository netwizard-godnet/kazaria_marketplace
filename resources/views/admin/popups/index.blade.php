@extends('admin.layouts.app')

@section('title', 'Gestion des popups')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Popups marketing</h4>
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
                <span>Popups</span>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Popups configurées</h4>
                    <a href="{{ route('admin.popups.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus me-1"></i> Nouvelle popup
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($popups->isEmpty())
                        <div class="text-center py-5">
                            <i class="fa fa-window-restore fa-3x text-muted"></i>
                            <h5 class="mt-3 text-muted">Aucune popup configurée</h5>
                            <p class="text-muted">Créez votre première popup marketing pour communiquer avec vos visiteurs.</p>
                            <a href="{{ route('admin.popups.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus me-1"></i> Créer une popup
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Popup</th>
                                        <th>Planning</th>
                                        <th>Affichage</th>
                                        <th>Fréquence</th>
                                        <th>Priorité</th>
                                        <th class="text-center">Statut</th>
                                        <th>Modifiée le</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($popups as $popup)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $popup->title }}</div>
                                                <small class="text-muted">Slug : {{ $popup->slug }}</small>
                                                @if($popup->image)
                                                    <div>
                                                        <small class="text-muted">Image : {{ $popup->image }}</small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted d-block">
                                                    @if($popup->display_start)
                                                        <i class="fa fa-calendar me-1"></i>
                                                        Du {{ $popup->display_start->format('d/m/Y H:i') }}
                                                    @else
                                                        <span class="badge bg-secondary">Dès activation</span>
                                                    @endif
                                                </small>
                                                <small class="text-muted d-block">
                                                    @if($popup->display_end)
                                                        <i class="fa fa-calendar-check me-1"></i>
                                                        Au {{ $popup->display_end->format('d/m/Y H:i') }}
                                                    @else
                                                        <span class="badge bg-secondary">Sans fin</span>
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                <div class="mb-1">
                                                    <small class="text-muted">Pages :</small>
                                                    @if(!empty($popup->display_pages))
                                                        <span class="badge bg-light text-dark border">
                                                            {{ implode(', ', $popup->display_pages) }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">Toutes</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <small class="text-muted">Appareils :</small>
                                                    @if(!empty($popup->display_devices))
                                                        @foreach($popup->display_devices as $device)
                                                            <span class="badge bg-light text-dark border text-capitalize">{{ $device }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="badge bg-secondary">Tous</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary">
                                                    {{ $frequencies[$popup->frequency] ?? ucfirst(str_replace('_', ' ', $popup->frequency)) }}
                                                </span>
                                                <div>
                                                    <small class="text-muted">
                                                        Délai : {{ $popup->delay_seconds }}s
                                                    </small>
                                                </div>
                                                @if($popup->max_impressions)
                                                    <small class="text-muted d-block">
                                                        Max. affichages : {{ $popup->max_impressions }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-dark">#{{ $popup->priority }}</span>
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('admin.popups.toggle', $popup) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $popup->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                                        <i class="fa {{ $popup->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }} me-1"></i>
                                                        {{ $popup->is_active ? 'Actif' : 'Inactif' }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $popup->updated_at?->format('d/m/Y H:i') ?? '—' }}
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.popups.edit', $popup) }}" class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.popups.destroy', $popup) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette popup ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $popups->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

