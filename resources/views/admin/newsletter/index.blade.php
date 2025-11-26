@extends('admin.layouts.app')

@section('title', 'Newsletter')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0">Newsletter</h1>
            <p class="text-muted mb-0">Gérez les inscriptions et envoyez un message à tous vos abonnés.</p>
        </div>
        <div class="badge bg-primary text-white">
            {{ $total }} abonné{{ $total > 1 ? 's' : '' }}
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Merci de corriger les erreurs suivantes :</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Envoyer un email aux abonnés</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.newsletter.send') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Objet</label>
                            <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" placeholder="Nouveautés, promotions..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message (HTML autorisé)</label>
                            <textarea name="message" rows="8" class="form-control font-monospace" placeholder="Vous pouvez utiliser du HTML : &lt;p&gt;, &lt;strong&gt;, &lt;a&gt;, &lt;img&gt;, etc." required>{{ old('message') }}</textarea>
                            <small class="text-muted">
                                Vous pouvez coller du HTML (par exemple depuis un éditeur) pour ajouter des images, liens stylés, titres, etc.
                            </small>
                        </div>
                        <button class="btn btn-primary" type="submit" {{ $total === 0 ? 'disabled' : '' }}>
                            <i class="fas fa-paper-plane me-2"></i>Envoyer à tous les abonnés
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Inscriptions récentes</h5>
                    <span class="text-muted small">Total : {{ $total }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Email</th>
                                <th>Source</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscribers as $subscriber)
                                <tr>
                                    <td class="fw-semibold">{{ $subscriber->email }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $subscriber->source ?? 'N/A' }}</span></td>
                                    <td>{{ $subscriber->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        Aucun abonné pour le moment.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    {{ $subscribers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

