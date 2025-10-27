@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
                            <p class="mb-0">Conversations</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-comments fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['unread'] }}</h4>
                            <p class="mb-0">Messages non lus</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-envelope fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['support'] }}</h4>
                            <p class="mb-0">Support</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-headset fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['general'] }}</h4>
                            <p class="mb-0">Général</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-comment fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['admin'] }}</h4>
                            <p class="mb-0">Admin</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-shield fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filtres et Recherche</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.messages.index') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Recherche</label>
                                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nom, email, sujet...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select name="type" class="form-control">
                                        <option value="">Tous</option>
                                        <option value="support" {{ request('type') == 'support' ? 'selected' : '' }}>Support</option>
                                        <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>Général</option>
                                        <option value="admin" {{ request('type') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Statut</label>
                                    <select name="status" class="form-control">
                                        <option value="">Tous</option>
                                        <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Non lus</option>
                                        <option value="important" {{ request('status') == 'important' ? 'selected' : '' }}>Importants</option>
                                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archivés</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des conversations -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Conversations</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#newConversationModal">
                            <i class="fas fa-plus"></i> Nouvelle Conversation
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Participants</th>
                                    <th>Sujet</th>
                                    <th>Type</th>
                                    <th>Dernier message</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($conversations as $conversation)
                                <tr class="{{ $conversation->hasUnreadMessages(auth()->id()) ? 'table-warning' : '' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                @if($conversation->user1->profile_pic_url)
                                                    <img src="{{ $conversation->user1->profile_pic_url }}" class="rounded-circle" width="32" height="32" alt="Avatar">
                                                @else
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                        {{ strtoupper(substr($conversation->user1->nom, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <strong>{{ $conversation->user1->nom }} {{ $conversation->user1->prenoms }}</strong>
                                                <br><small class="text-muted">{{ $conversation->user1->email }}</small>
                                            </div>
                                        </div>
                                        <div class="mt-1">
                                            <small class="text-muted">avec</small>
                                            <strong>{{ $conversation->user2->nom }} {{ $conversation->user2->prenoms }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $conversation->subject ?? 'Aucun sujet' }}
                                        @if($conversation->is_important)
                                            <br><span class="badge badge-warning">Important</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $conversation->conversation_type === 'support' ? 'warning' : ($conversation->conversation_type === 'admin' ? 'danger' : 'info') }}">
                                            {{ ucfirst($conversation->conversation_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($conversation->lastMessage)
                                            <div>
                                                <strong>{{ $conversation->lastMessage->sender->nom }}</strong>
                                                <br><small class="text-muted">{{ Str::limit($conversation->lastMessage->body, 50) }}</small>
                                                <br><small class="text-muted">{{ $conversation->lastMessage->created_at->diffForHumans() }}</small>
                                            </div>
                                        @else
                                            <small class="text-muted">Aucun message</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($conversation->is_archived)
                                            <span class="badge badge-secondary">Archivé</span>
                                        @elseif($conversation->hasUnreadMessages(auth()->id()))
                                            <span class="badge badge-danger">Non lu</span>
                                        @else
                                            <span class="badge badge-success">Lu</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.messages.show', $conversation) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-{{ $conversation->is_important ? 'warning' : 'secondary' }} btn-sm" onclick="toggleImportant({{ $conversation->id }})">
                                                <i class="fas fa-star"></i>
                                            </button>
                                            @if($conversation->is_archived)
                                                <button type="button" class="btn btn-success btn-sm" onclick="unarchiveConversation({{ $conversation->id }})">
                                                    <i class="fas fa-archive"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-warning btn-sm" onclick="archiveConversation({{ $conversation->id }})">
                                                    <i class="fas fa-archive"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteConversation({{ $conversation->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Aucune conversation trouvée</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Affichage de {{ $conversations->firstItem() }} à {{ $conversations->lastItem() }} sur {{ $conversations->total() }} résultats
                        </div>
                        <div>
                            {{ $conversations->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal nouvelle conversation -->
<div class="modal fade" id="newConversationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle Conversation</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.messages.create-conversation') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Utilisateur *</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">Sélectionner un utilisateur</option>
                            @foreach(\App\Models\User::where('id', '!=', auth()->id())->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->nom }} {{ $user->prenoms }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sujet</label>
                        <input type="text" name="subject" class="form-control" placeholder="Sujet de la conversation">
                    </div>
                    <div class="form-group">
                        <label>Type</label>
                        <select name="conversation_type" class="form-control">
                            <option value="general">Général</option>
                            <option value="support">Support</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleImportant(conversationId) {
    fetch(`/admin/messages/${conversationId}/toggle-important`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function archiveConversation(conversationId) {
    if (confirm('Êtes-vous sûr de vouloir archiver cette conversation ?')) {
        fetch(`/admin/messages/${conversationId}/archive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function unarchiveConversation(conversationId) {
    fetch(`/admin/messages/${conversationId}/unarchive`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function deleteConversation(conversationId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette conversation ? Cette action est irréversible.')) {
        fetch(`/admin/messages/${conversationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}
</script>
@endsection
