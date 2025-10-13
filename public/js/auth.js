/**
 * Gestion de l'authentification côté client
 */
class AuthManager {
    constructor() {
        this.token = localStorage.getItem('auth_token');
        this.user = JSON.parse(localStorage.getItem('user_data') || 'null');
        this.init();
    }

    init() {
        this.updateHeaderAuthState();
        this.setupLogoutHandler();
    }

    // Vérifier si l'utilisateur est connecté
    isAuthenticated() {
        return this.token && this.user;
    }

    // Obtenir les informations de l'utilisateur
    getUser() {
        return this.user;
    }

    // Obtenir le token
    getToken() {
        return this.token;
    }

    // Connecter l'utilisateur
    login(token, user) {
        this.token = token;
        this.user = user;
        localStorage.setItem('auth_token', token);
        localStorage.setItem('user_data', JSON.stringify(user));
        this.updateHeaderAuthState();
    }

    // Déconnecter l'utilisateur
    async logout() {
        try {
            // Appeler l'API de déconnexion si un token existe
            if (this.token) {
                await fetch('/api/logout-client', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
            }
        } catch (error) {
            console.error('Erreur lors de la déconnexion:', error);
        } finally {
            // Nettoyer les données locales dans tous les cas
            this.token = null;
            this.user = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_data');
            this.updateHeaderAuthState();
            
            // Rediriger vers l'accueil
            window.location.href = '/';
        }
    }

    // Mettre à jour l'affichage du header selon l'état de connexion
    updateHeaderAuthState() {
        const authElement = document.querySelector('#auth-section');
        if (!authElement) return;

        if (this.isAuthenticated()) {
            this.showUserProfile(authElement);
        } else {
            this.showLoginButton(authElement);
        }
    }

    // Afficher le profil utilisateur
    showUserProfile(authElement) {
        const userName = this.user.prenoms ? 
            `${this.user.prenoms} ${this.user.nom}` : 
            this.user.email;

        authElement.innerHTML = `
            <li class="nav-item px-1 d-flex align-items-center justify-content-center">
                <a class="nav-link d-flex align-items-center" href="/profil?token=${this.token}" onclick="return handleProfileClick(event)">
                    <i class="fa-solid fa-user-circle text-white fa-2x"></i>
                    <div class="vstack text-white ms-2">
                        <span class="fs-8 fw-lighter">Bonjour</span>
                        <span class="fs-8 fw-bold">${userName}</span>
                    </div>
                </a>
            </li>
        `;
    }

    // Afficher le bouton de connexion
    showLoginButton(authElement) {
        authElement.innerHTML = `
            <li class="nav-item px-1 d-flex align-items-center justify-content-center">
                <a class="nav-link d-flex align-items-center" href="/authentification">
                    <i class="fa-solid fa-user text-white fa-2x"></i>
                    <div class="vstack text-white ms-2">
                        <span class="fs-8 fw-lighter">Connexion</span>
                        <span class="fs-8 fw-lighter">Inscription</span>
                    </div>
                </a>
            </li>
        `;
    }

    // Configurer le gestionnaire de déconnexion
    setupLogoutHandler() {
        // Écouter les clics sur les liens de déconnexion
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-action="logout"]')) {
                e.preventDefault();
                this.logout();
            }
        });
    }


    // Vérifier la validité du token avec le serveur
    async validateToken() {
        if (!this.token) return false;

        try {
            const response = await fetch('/api/me', {
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.user = data.user;
                    localStorage.setItem('user_data', JSON.stringify(data.user));
                    return true;
                }
            }
        } catch (error) {
            console.error('Erreur de validation du token:', error);
        }

        // Si le token n'est pas valide, déconnecter
        this.logout();
        return false;
    }
}

// Initialiser le gestionnaire d'authentification
let authManager;
document.addEventListener('DOMContentLoaded', () => {
    authManager = new AuthManager();
    
        // Valider le token au chargement de la page
        authManager.validateToken();
    });

    // Fonction globale pour gérer le clic sur le profil
    window.handleProfileClick = function(event) {
        const token = localStorage.getItem('auth_token');
        
        if (!token) {
            event.preventDefault();
            alert('Vous devez être connecté pour accéder à votre profil.');
            return false;
        }
        
        // Ajouter le token à l'URL si pas déjà présent
        const url = new URL(event.target.closest('a').href);
        if (!url.searchParams.has('token')) {
            url.searchParams.set('token', token);
            event.target.closest('a').href = url.toString();
        }
        
        return true;
    };

    // Fonction pour vérifier l'authentification avant d'accéder au profil
    window.checkAuthBeforeProfile = function() {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.href = '/authentification';
            return false;
        }
        return true;
    };

    // Fonction pour aller vers les favoris
    window.goToFavorites = function(event) {
        event.preventDefault();
        const token = localStorage.getItem('auth_token');
        
        if (!token) {
            // Si non connecté, demander de se connecter
            if (confirm('Vous devez être connecté pour voir vos favoris. Se connecter maintenant ?')) {
                localStorage.setItem('redirect_after_login', 'favorites');
                window.location.href = '/authentification';
            }
            return false;
        }
        
        // Si connecté, aller vers le profil avec l'ancre favorites
        window.location.href = `/profil?token=${token}#favorites`;
        return true;
    };
