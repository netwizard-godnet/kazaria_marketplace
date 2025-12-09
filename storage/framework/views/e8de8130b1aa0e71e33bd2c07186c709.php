<?php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
?>

<div class="main-header">
    <div class="main-header-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="logo">
                <img src="<?php echo e(asset('storage/' . ($adminSettings['site_logo']->value ?? 'logo.png'))); ?>" alt="<?php echo e($adminSettings['site_name']->value ?? 'KAZARIA'); ?> Admin" class="navbar-brand" height="30" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>
    
    <!-- Navbar Header -->
    <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom w-100">
        <div class="container-fluid">
            <nav class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <button type="submit" class="btn btn-search pe-1" onclick="performSearch()">
                            <i class="fa fa-search search-icon"></i>
                        </button>
                    </div>
                    <input type="text" id="globalSearch" placeholder="Rechercher..." class="form-control" onkeypress="handleSearchKeypress(event)" />
                </div>
                <!-- Résultats de recherche -->
                <div id="searchResults" class="search-results dropdown-menu" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; max-height: 400px; overflow-y: auto;">
                    <div id="searchContent"></div>
                </div>
            </nav>

            <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <!-- Search Mobile -->
                <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" aria-haspopup="true">
                        <i class="fa fa-search"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-search animated fadeIn">
                        <form class="navbar-left navbar-form nav-search">
                            <div class="input-group">
                                <input type="text" placeholder="Rechercher..." class="form-control" />
                            </div>
                        </form>
                    </ul>
                </li>

                <!-- Messages -->
                <li class="nav-item topbar-icon dropdown hidden-caret">
                    <a class="nav-link dropdown-toggle" href="#" id="messageDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="loadMessages()">
                        <i class="fa fa-envelope"></i>
                        <span class="notification" id="messageCount">0</span>
                    </a>
                    <ul class="dropdown-menu messages-notif-box animated fadeIn" aria-labelledby="messageDropdown">
                        <li>
                            <div class="dropdown-title d-flex justify-content-between align-items-center">
                                Messages
                                <a href="#" class="small" onclick="markAllMessagesAsRead()">Marquer tout comme lu</a>
                            </div>
                        </li>
                        <li>
                            <div class="message-notif-scroll scrollbar-outer">
                                <div class="notif-center" id="messagesList">
                                    <div class="text-center p-3">
                                        <i class="fa fa-spinner fa-spin"></i> Chargement...
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <a class="see-all" href="javascript:void(0);">Voir tous les messages<i class="fa fa-angle-right"></i></a>
                        </li>
                    </ul>
                </li>

                <!-- Notifications -->
                <li class="nav-item topbar-icon dropdown hidden-caret">
                    <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="loadNotifications()">
                        <i class="fa fa-bell"></i>
                        <span class="notification" id="notificationCount">0</span>
                    </a>
                    <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
                        <li>
                            <div class="dropdown-title d-flex justify-content-between align-items-center">
                                <span id="notificationTitle">Notifications</span>
                                <a href="#" class="small" onclick="markAllNotificationsAsRead()">Marquer tout comme lu</a>
                            </div>
                        </li>
                        <li>
                            <div class="notif-scroll scrollbar-outer">
                                <div class="notif-center" id="notificationsList">
                                    <div class="text-center p-3">
                                        <i class="fa fa-spinner fa-spin"></i> Chargement...
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <a class="see-all" href="javascript:void(0);">Voir toutes les notifications<i class="fa fa-angle-right"></i></a>
                        </li>
                    </ul>
                </li>

                <!-- User Profile -->
                <li class="nav-item dropdown hidden-caret">
                    <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">
                        <?php
                            $user = Auth::user();
                            $profilePicUrl = null;

                            if ($user) {
                                $rawPath = trim($user->profile_pic_url ?? '');

                                if (!empty($rawPath)) {
                                    if (Str::startsWith($rawPath, ['http://', 'https://'])) {
                                        $profilePicUrl = $rawPath;
                                    } else {
                                        $trimmed = ltrim($rawPath, '/');

                                        $publicCandidates = [
                                            $trimmed,
                                            'storage/' . $trimmed,
                                        ];

                                        if (Str::startsWith($trimmed, 'public/')) {
                                            $publicCandidates[] = Str::after($trimmed, 'public/');
                                            $publicCandidates[] = 'storage/' . Str::after($trimmed, 'public/');
                                        }

                                        foreach ($publicCandidates as $candidate) {
                                            if (file_exists(public_path($candidate))) {
                                                $profilePicUrl = asset($candidate);
                                                break;
                                            }
                                        }

                                        if (!$profilePicUrl) {
                                            $storagePath = Str::startsWith($trimmed, 'public/')
                                                ? Str::after($trimmed, 'public/')
                                                : $trimmed;

                                            if (Storage::disk('public')->exists($storagePath)) {
                                                $profilePicUrl = Storage::disk('public')->url($storagePath);
                                            }
                                        }
                                    }
                                }
                            }

                            $profilePicUrl = $profilePicUrl ?: null;
                        ?>
                        <div class="avatar-sm">
                            <?php if($profilePicUrl): ?>
                                <img src="<?php echo e($profilePicUrl); ?>" alt="<?php echo e($user->prenoms ?? 'Admin'); ?>" class="avatar-img rounded-circle">
                            <?php else: ?>
                                <div class="avatar-img rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px; font-size: 16px; font-weight: bold;">
                                    <?php echo e(strtoupper(substr($user->prenoms ?? 'A', 0, 1))); ?><?php echo e(strtoupper(substr($user->nom ?? 'U', 0, 1))); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-user animated fadeIn p-2">
                        <div class="dropdown-user-details">
                            <div class="user-name"><?php echo e(Auth::user()->prenoms ?? 'Admin'); ?> <?php echo e(Auth::user()->nom ?? 'User'); ?></div>
                            <div class="dropdown-user-email"><?php echo e(Auth::user()->email ?? 'admin@kazaria.ci'); ?></div>
                        </div>
                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('admin.profile.index')); ?>">
                                <i class="fa fa-user"></i> Mon profil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('admin.settings.index')); ?>">
                                <i class="fa fa-cog"></i> Paramètres
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('accueil')); ?>" target="_blank">
                                <i class="fa fa-external-link-alt"></i> Voir le site
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('admin.logout')); ?>" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fa fa-sign-out-alt"></i> Déconnexion
                            </a>
                            <form id="logout-form" action="<?php echo e(route('admin.logout')); ?>" method="POST" class="d-none">
                                <?php echo csrf_field(); ?>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    <!-- End Navbar -->
</div>

<script>
// Variables globales
let searchTimeout;
let notificationsLoaded = false;
let messagesLoaded = false;

// Fonction de recherche globale
function performSearch() {
    console.log('performSearch called');
    const query = document.getElementById('globalSearch').value.trim();
    console.log('Query:', query);
    if (query.length < 2) {
        hideSearchResults();
        return;
    }

    fetch(`/admin/header/search?q=${encodeURIComponent(query)}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySearchResults(data.results, query);
        }
    })
    .catch(error => {
        console.error('Erreur lors de la recherche:', error);
    });
}

// Gestion de la touche Entrée dans la recherche
function handleSearchKeypress(event) {
    console.log('handleSearchKeypress called, key:', event.key);
    if (event.key === 'Enter') {
        event.preventDefault();
        performSearch();
    } else {
        // Recherche en temps réel avec délai
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch();
        }, 300);
    }
}

// Afficher les résultats de recherche
function displaySearchResults(results, query) {
    const searchResults = document.getElementById('searchResults');
    const searchContent = document.getElementById('searchContent');
    
    let html = `<div class="p-2"><h6 class="mb-2">Résultats pour "${query}"</h6>`;
    
    if (results.products && results.products.length > 0) {
        html += `<div class="mb-3"><h6 class="text-primary">Produits (${results.products.length})</h6>`;
        results.products.forEach(product => {
            html += `
                <a href="${product.url}" class="d-block p-2 text-decoration-none border-bottom">
                    <div class="d-flex align-items-center">
                        <img src="${product.image}" alt="${product.name}" class="me-2" style="width: 30px; height: 30px; object-fit: cover;">
                        <div>
                            <div class="fw-bold">${product.name}</div>
                            <small class="text-muted">${product.sku} - ${product.price}</small>
                        </div>
                    </div>
                </a>
            `;
        });
        html += `</div>`;
    }
    
    if (results.orders && results.orders.length > 0) {
        html += `<div class="mb-3"><h6 class="text-success">Commandes (${results.orders.length})</h6>`;
        results.orders.forEach(order => {
            html += `
                <a href="${order.url}" class="d-block p-2 text-decoration-none border-bottom">
                    <div>
                        <div class="fw-bold">${order.order_number}</div>
                        <small class="text-muted">${order.customer_name} - ${order.total}</small>
                    </div>
                </a>
            `;
        });
        html += `</div>`;
    }
    
    if (results.users && results.users.length > 0) {
        html += `<div class="mb-3"><h6 class="text-info">Utilisateurs (${results.users.length})</h6>`;
        results.users.forEach(user => {
            html += `
                <a href="${user.url}" class="d-block p-2 text-decoration-none border-bottom">
                    <div class="d-flex align-items-center">
                        ${user.avatar ? `<img src="${user.avatar}" alt="${user.name}" class="me-2 rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">` : '<div class="me-2 bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;"><i class="fa fa-user text-white"></i></div>'}
                        <div>
                            <div class="fw-bold">${user.name}</div>
                            <small class="text-muted">${user.email}</small>
                        </div>
                    </div>
                </a>
            `;
        });
        html += `</div>`;
    }
    
    if (Object.keys(results).length === 0) {
        html += `<div class="text-center p-3 text-muted">Aucun résultat trouvé</div>`;
    }
    
    html += `</div>`;
    
    searchContent.innerHTML = html;
    searchResults.style.display = 'block';
}

// Masquer les résultats de recherche
function hideSearchResults() {
    document.getElementById('searchResults').style.display = 'none';
}

// Charger les notifications
function loadNotifications() {
    if (notificationsLoaded) return;
    
    fetch('/admin/header/notifications', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayNotifications(data.notifications);
            document.getElementById('notificationCount').textContent = data.count;
            document.getElementById('notificationTitle').textContent = `Vous avez ${data.count} nouvelles notifications`;
            notificationsLoaded = true;
        }
    })
    .catch(error => {
        console.error('Erreur lors du chargement des notifications:', error);
    });
}

// Afficher les notifications
function displayNotifications(notifications) {
    const notificationsList = document.getElementById('notificationsList');
    
    if (notifications.length === 0) {
        notificationsList.innerHTML = '<div class="text-center p-3 text-muted">Aucune notification</div>';
        return;
    }
    
    let html = '';
    notifications.forEach(notification => {
        html += `
            <a href="#" class="d-block p-2 text-decoration-none border-bottom" onclick="markNotificationAsRead(${notification.id})">
                <div class="d-flex align-items-center">
                    <div class="notif-icon notif-${notification.color} me-2">
                        <i class="fa ${notification.icon}"></i>
                    </div>
                    <div class="notif-content">
                        <div class="fw-bold">${notification.title}</div>
                        <div class="text-muted small">${notification.message}</div>
                        <div class="time">${notification.created_at}</div>
                    </div>
                </div>
            </a>
        `;
    });
    
    notificationsList.innerHTML = html;
}

// Charger les messages
function loadMessages() {
    if (messagesLoaded) return;
    
    fetch('/admin/header/messages', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayMessages(data.messages);
            document.getElementById('messageCount').textContent = data.count;
            messagesLoaded = true;
        }
    })
    .catch(error => {
        console.error('Erreur lors du chargement des messages:', error);
    });
}

// Afficher les messages
function displayMessages(messages) {
    const messagesList = document.getElementById('messagesList');
    
    if (messages.length === 0) {
        messagesList.innerHTML = '<div class="text-center p-3 text-muted">Aucun message</div>';
        return;
    }
    
    let html = '';
    messages.forEach(message => {
        html += `
            <a href="#" class="d-block p-2 text-decoration-none border-bottom" onclick="markMessageAsRead(${message.id})">
                <div class="d-flex align-items-center">
                    ${message.sender_avatar ? `<img src="${message.sender_avatar}" alt="${message.sender_name}" class="me-2 rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">` : '<div class="me-2 bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;"><i class="fa fa-user text-white"></i></div>'}
                    <div class="notif-content">
                        <div class="fw-bold">${message.subject}</div>
                        <div class="text-muted small">${message.body}</div>
                        <div class="time">${message.created_at}</div>
                    </div>
                </div>
            </a>
        `;
    });
    
    messagesList.innerHTML = html;
}

// Marquer une notification comme lue
function markNotificationAsRead(notificationId) {
    fetch('/admin/header/notifications/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recharger les notifications
            notificationsLoaded = false;
            loadNotifications();
        }
    });
}

// Marquer un message comme lu
function markMessageAsRead(messageId) {
    fetch('/admin/header/messages/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ message_id: messageId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recharger les messages
            messagesLoaded = false;
            loadMessages();
        }
    });
}

// Marquer toutes les notifications comme lues
function markAllNotificationsAsRead() {
    fetch('/admin/header/notifications/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notificationsLoaded = false;
            loadNotifications();
        }
    });
}

// Marquer tous les messages comme lus
function markAllMessagesAsRead() {
    fetch('/admin/header/messages/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messagesLoaded = false;
            loadMessages();
        }
    });
}

// Masquer les résultats de recherche quand on clique ailleurs
document.addEventListener('click', function(event) {
    const searchResults = document.getElementById('searchResults');
    const searchInput = document.getElementById('globalSearch');
    
    if (!searchResults.contains(event.target) && !searchInput.contains(event.target)) {
        hideSearchResults();
    }
});

// Charger les données au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Charger les compteurs
    loadNotifications();
    loadMessages();
});
</script>

<?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/admin/layouts/header.blade.php ENDPATH**/ ?>