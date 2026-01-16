<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo e($stats['total']); ?></h4>
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
                            <h4 class="mb-0"><?php echo e($stats['unread']); ?></h4>
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
                            <h4 class="mb-0"><?php echo e($stats['support']); ?></h4>
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
                            <h4 class="mb-0"><?php echo e($stats['general']); ?></h4>
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
                            <h4 class="mb-0"><?php echo e($stats['admin']); ?></h4>
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
                    <form method="GET" action="<?php echo e(route('admin.messages.index')); ?>">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Recherche</label>
                                    <input type="text" name="search" class="form-control" value="<?php echo e(request('search')); ?>" placeholder="Nom, email, sujet...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select name="type" class="form-control">
                                        <option value="">Tous</option>
                                        <option value="support" <?php echo e(request('type') == 'support' ? 'selected' : ''); ?>>Support</option>
                                        <option value="general" <?php echo e(request('type') == 'general' ? 'selected' : ''); ?>>Général</option>
                                        <option value="admin" <?php echo e(request('type') == 'admin' ? 'selected' : ''); ?>>Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Statut</label>
                                    <select name="status" class="form-control">
                                        <option value="">Tous</option>
                                        <option value="unread" <?php echo e(request('status') == 'unread' ? 'selected' : ''); ?>>Non lus</option>
                                        <option value="important" <?php echo e(request('status') == 'important' ? 'selected' : ''); ?>>Importants</option>
                                        <option value="archived" <?php echo e(request('status') == 'archived' ? 'selected' : ''); ?>>Archivés</option>
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
                                <?php $__empty_1 = true; $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conversation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="<?php echo e($conversation->hasUnreadMessages(auth()->id()) ? 'table-warning' : ''); ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <?php if($conversation->user1->profile_pic_url): ?>
                                                    <img src="<?php echo e($conversation->user1->profile_pic_url); ?>" class="rounded-circle" width="32" height="32" alt="Avatar">
                                                <?php else: ?>
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                        <?php echo e(strtoupper(substr($conversation->user1->nom, 0, 1))); ?>

                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <strong><?php echo e($conversation->user1->nom); ?> <?php echo e($conversation->user1->prenoms); ?></strong>
                                                <br><small class="text-muted"><?php echo e($conversation->user1->email); ?></small>
                                            </div>
                                        </div>
                                        <div class="mt-1">
                                            <small class="text-muted">avec</small>
                                            <strong><?php echo e($conversation->user2->nom); ?> <?php echo e($conversation->user2->prenoms); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo e($conversation->subject ?? 'Aucun sujet'); ?>

                                        <?php if($conversation->is_important): ?>
                                            <br><span class="badge badge-warning">Important</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo e($conversation->conversation_type === 'support' ? 'warning' : ($conversation->conversation_type === 'admin' ? 'danger' : 'info')); ?>">
                                            <?php echo e(ucfirst($conversation->conversation_type)); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php if($conversation->lastMessage): ?>
                                            <div>
                                                <strong><?php echo e($conversation->lastMessage->sender->nom); ?></strong>
                                                <br><small class="text-muted"><?php echo e(Str::limit($conversation->lastMessage->body, 50)); ?></small>
                                                <br><small class="text-muted"><?php echo e($conversation->lastMessage->created_at->diffForHumans()); ?></small>
                                            </div>
                                        <?php else: ?>
                                            <small class="text-muted">Aucun message</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($conversation->is_archived): ?>
                                            <span class="badge badge-secondary">Archivé</span>
                                        <?php elseif($conversation->hasUnreadMessages(auth()->id())): ?>
                                            <span class="badge badge-danger">Non lu</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Lu</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo e(route('admin.messages.show', $conversation)); ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-<?php echo e($conversation->is_important ? 'warning' : 'secondary'); ?> btn-sm" onclick="toggleImportant(<?php echo e($conversation->id); ?>)">
                                                <i class="fas fa-star"></i>
                                            </button>
                                            <?php if($conversation->is_archived): ?>
                                                <button type="button" class="btn btn-success btn-sm" onclick="unarchiveConversation(<?php echo e($conversation->id); ?>)">
                                                    <i class="fas fa-archive"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-warning btn-sm" onclick="archiveConversation(<?php echo e($conversation->id); ?>)">
                                                    <i class="fas fa-archive"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteConversation(<?php echo e($conversation->id); ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucune conversation trouvée</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Affichage de <?php echo e($conversations->firstItem()); ?> à <?php echo e($conversations->lastItem()); ?> sur <?php echo e($conversations->total()); ?> résultats
                        </div>
                        <div>
                            <?php echo e($conversations->links()); ?>

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
            <form method="POST" action="<?php echo e(route('admin.messages.create-conversation')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Utilisateur *</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">Sélectionner un utilisateur</option>
                            <?php $__currentLoopData = \App\Models\User::where('id', '!=', auth()->id())->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>"><?php echo e($user->nom); ?> <?php echo e($user->prenoms); ?> (<?php echo e($user->email); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/admin/messages/index.blade.php ENDPATH**/ ?>