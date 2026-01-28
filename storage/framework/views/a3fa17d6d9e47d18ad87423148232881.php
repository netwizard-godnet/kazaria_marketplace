<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php
            $siteFavicon = \App\Models\Setting::get('site_favicon');
            $faviconUrl = $siteFavicon ? asset('storage/' . ltrim($siteFavicon, '/')) : asset('favicon.png');
        ?>
        <link rel="icon" type="image/png" href="<?php echo e($faviconUrl); ?>">

        <title><?php echo e(config('app.name', 'KAZARIA Connexion')); ?></title>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <!-- Fontawesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-(your integrity hash)" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <!-- SLICK -->
        <link rel="stylesheet" href="<?php echo e(asset('slick/slick.css')); ?>">
        <link rel="stylesheet" href="<?php echo e(asset('slick/slick-theme.css')); ?>">
        <!-- CUSTOM CSS -->
        <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>">
        <link rel="stylesheet" href="<?php echo e(asset('css/profil.css')); ?>">
        <link rel="stylesheet" href="<?php echo e(asset('css/carousel.css')); ?>">
        <!-- FONTS -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    </head>

    <body>
        <main class="container-fluid py-3">
            <div class="text-center mb-3">
                <img src="<?php echo e(asset('images/logo-orange.png')); ?>" class="logo-size-header" alt="">
                <p class="mb-0 fw-bold fs-7">Bienvenue chez KAZARIA</p>
                <p class="mb-0 fs-8">Veuillez connecter ou inscrivez-vous si vous n'avez pas de compte</p>
            </div>
            <!-- SECTION FORM -->
            <div class="d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 450px;">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-4 p-md-5">
                            <?php if(session('error')): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo e(session('error')); ?>

                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                </div>
                            <?php endif; ?>
                            <?php if(session('success')): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo e(session('success')); ?>

                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                </div>
                            <?php endif; ?>
                            <!-- Navigation Tabs -->
                            <ul class="nav nav-pills nav-fill mb-3" id="authTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active small" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>Connexion
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link small" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">
                                        <i class="bi bi-person-plus me-1"></i>Inscription
                                    </button>
                                </li>
                            </ul>
                            
                            <!-- Tab Content -->
                            <div class="tab-content" id="authTabsContent">
                                <!-- Login Form -->
                                <div class="tab-pane fade show active" id="login" role="tabpanel">
                                    <form id="loginForm">
                                        <?php echo csrf_field(); ?>
                                        <div id="loginAlert"></div>
                                        <div class="form-floating mb-4">
                                            <input type="email" class="form-control form-control-sm" id="loginEmail" placeholder="name@example.com" name="email" required>
                                            <label for="loginEmail" class="small"><i class="bi bi-envelope me-1"></i>Adresse email</label>
                                        </div>
                                        
                                        <div class="form-floating mb-4">
                                            <input type="password" class="form-control form-control-sm" id="loginPassword" placeholder="Mot de passe" name="password" required>
                                            <label for="loginPassword" class="small"><i class="bi bi-lock me-1"></i>Mot de passe</label>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="rememberMe">
                                                <label class="form-check-label small" for="rememberMe">
                                                    Se souvenir de moi
                                                </label>
                                            </div>
                                            <a href="<?php echo e(route('forgot-password')); ?>" class="text-decoration-none small">Mot de passe oublié ?</a>
                                        </div>
                                        
                                        <button type="submit" class="btn orange-bg text-white w-100 mb-4">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>Se connecter
                                        </button>
                                        
                                        <div class="text-center mb-4">
                                            <span class="text-muted small">Ou continuer avec</span>
                                        </div>
                                        
                                        <div class="d-grid gap-3">
                                            <a href="<?php echo e(route('social.redirect', ['provider' => 'google'])); ?>" class="btn btn-outline-danger fs-6">
                                                <i class="bi bi-google me-1 h4"></i>Google
                                            </a>
                                            <a href="<?php echo e(route('social.redirect', ['provider' => 'facebook'])); ?>" class="btn btn-outline-primary fs-6">
                                                <i class="bi bi-facebook me-1 h4"></i>Facebook
                                            </a>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Register Form -->
                                <div class="tab-pane fade" id="register" role="tabpanel">
                                    <form id="registerForm">
                                        <?php echo csrf_field(); ?>
                                        <div id="registerAlert">
                                            
                                        </div>
                                        
                                        <div class="row g-2">
                                            <div class="col-md-12">
                                                <div class="form-floating mb-4">
                                                    <input type="text" class="form-control form-control-sm" id="registerLastName" placeholder="Nom" name="nom" required>
                                                    <label for="registerLastName" class="small"><i class="bi bi-person me-1"></i>Nom</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-floating mb-4">
                                                    <input type="text" class="form-control form-control-sm" id="registerFirstName" placeholder="Prénom(s)" name="prenoms" required>
                                                    <label for="registerFirstName" class="small"><i class="bi bi-person me-1"></i>Prénom</label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-floating mb-4">
                                            <input type="email" class="form-control form-control-sm" id="registerEmail" placeholder="name@example.com" name="email" required>
                                            <label for="registerEmail" class="small"><i class="bi bi-envelope me-1"></i>Adresse email</label>
                                        </div>
                                        
                                        <div class="form-floating mb-4">
                                            <input type="tel" class="form-control form-control-sm" id="registerPhone" placeholder="+225 XX XX XX XX" name="telephone" required>
                                            <label for="registerPhone" class="small"><i class="bi bi-telephone me-1"></i>Téléphone</label>
                                        </div>
                                        
                                        <div class="form-floating mb-4">
                                            <input type="password" class="form-control form-control-sm" id="registerPassword" placeholder="Mot de passe" name="password" required>
                                            <label for="registerPassword" class="small"><i class="bi bi-lock me-1"></i>Mot de passe</label>
                                        </div>
                                        
                                        <div class="form-floating mb-4">
                                            <input type="password" class="form-control form-control-sm" id="registerConfirmPassword" placeholder="Confirmer le mot de passe" name="password_confirmation" required>
                                            <label for="registerConfirmPassword" class="small"><i class="bi bi-lock-fill me-1"></i>Confirmer le mot de passe</label>
                                        </div>
                                        
                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="acceptTerms" value="true" name="termes_condition" required>
                                            <label class="form-check-label small" for="acceptTerms">
                                                J'accepte les <a href="#" class="text-primary text-decoration-none">conditions d'utilisation</a> et la <a href="#" class="text-primary text-decoration-none">politique de confidentialité</a>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="acceptNewsletter" value="true" name="newsletter">
                                            <label class="form-check-label small" for="acceptNewsletter">
                                                Je souhaite recevoir les offres et actualités par email
                                            </label>
                                        </div>
                                        
                                        <button type="submit" class="btn orange-bg text-white w-100 mb-4">
                                            <i class="bi bi-person-plus me-1"></i>Créer mon compte
                                        </button>
                                        
                                        <div class="text-center mb-4">
                                            <span class="text-muted small">Ou s'inscrire avec</span>
                                        </div>
                                        
                                        <div class="d-grid gap-3">
                                            <a href="<?php echo e(route('social.redirect', ['provider' => 'google'])); ?>" class="btn btn-outline-danger">
                                                <i class="bi bi-google me-1 h4"></i>Google
                                            </a>
                                            <a href="<?php echo e(route('social.redirect', ['provider' => 'facebook'])); ?>" class="btn btn-outline-primary">
                                                <i class="bi bi-facebook me-1 h4"></i>Facebook
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- SECTION FORM END -->
            <div class="text-center mt-3">
                <p class="mb-0 fs-8">Si besoin d'aide, merci de vous référer au Centre d'Assistance ou de contacter notre service client.</p>
                <img src="<?php echo e(asset('images/Favicon.png')); ?>" class="logo-size-header" alt="">
            </div>
        </main>

        <script>
            let currentStep = 'login'; // 'login', 'code', 'register'
            let userEmail = '';

            // Fonction pour obtenir l'ID de session invité depuis localStorage
            function getSessionId() {
                let sessionId = localStorage.getItem('guest_session_id');
                if (!sessionId) {
                    sessionId = 'guest_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                    localStorage.setItem('guest_session_id', sessionId);
                }
                return sessionId;
            }

            // Fonction pour afficher un message
            function showMessage(elementId, message, type = 'success', errors = null) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
                
                let errorList = '';
                if (errors && Object.keys(errors).length > 0) {
                    errorList = '<ul class="mb-0 mt-2">';
                    Object.keys(errors).forEach(field => {
                        errors[field].forEach(errorMsg => {
                            errorList += `<li>${errorMsg}</li>`;
                        });
                    });
                    errorList += '</ul>';
                }
                
                document.getElementById(elementId).innerHTML = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        <i class="fa-solid ${icon} me-2"></i><strong>${message}</strong>${errorList}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                `;
                
                // Scroll vers le message d'erreur
                document.getElementById(elementId).scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // Fonction pour mettre en évidence les champs en erreur
            function highlightFieldErrors(errors) {
                // Réinitialiser tous les champs
                document.querySelectorAll('.form-control.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
                document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                if (!errors) return;

                // Mapper les noms de champs du serveur vers les IDs des champs HTML
                const fieldMapping = {
                    'nom': 'registerLastName',
                    'prenoms': 'registerFirstName',
                    'email': 'registerEmail', // Par défaut pour l'inscription
                    'telephone': 'registerPhone',
                    'password': 'registerPassword',
                    'password_confirmation': 'registerConfirmPassword',
                    'termes_condition': 'acceptTerms',
                    'code': 'verificationCode'
                };

                Object.keys(errors).forEach(field => {
                    // Pour la connexion, utiliser loginEmail si le champ email est en erreur
                    let fieldId;
                    if (field === 'email' && document.getElementById('loginEmail')) {
                        fieldId = 'loginEmail';
                    } else {
                        fieldId = fieldMapping[field] || field;
                    }
                    
                    const input = document.getElementById(fieldId);
                    
                    if (input) {
                        input.classList.add('is-invalid');
                        
                        // Créer le message d'erreur sous le champ
                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = errors[field][0];
                        input.parentElement.appendChild(feedback);
                    }
                });
            }

            // Fonction pour réinitialiser les erreurs visuelles
            function resetFieldErrors() {
                document.querySelectorAll('.form-control.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
                document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
            }

            // Fonction pour afficher le formulaire de code
            function showCodeForm(email) {
                userEmail = email;
                currentStep = 'code';
                
                document.getElementById('login').innerHTML = `
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-envelope-open-text fa-3x orange-color mb-3"></i>
                        <h5>Code de Connexion</h5>
                        <p class="text-muted">Un code de 8 chiffres a été envoyé à<br><strong>${email}</strong></p>
                    </div>
                    
                    <form id="codeForm">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="email" value="${email}">
                        
                        <div id="codeAlert"></div>
                        
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control form-control-sm text-center" id="verificationCode" 
                                   placeholder="Code de vérification" name="code" required maxlength="8" 
                                   pattern="[0-9]{8}" style="font-size: 1.5rem; letter-spacing: 0.5rem;">
                            <label for="verificationCode" class="small">
                                <i class="fa-solid fa-key me-1"></i>Code de vérification (8 chiffres)
                            </label>
                        </div>
                        
                        <button type="submit" class="btn blue-bg text-white w-100 mb-4">
                            <i class="fa-solid fa-check me-1"></i>Vérifier le code
                        </button>
                        
                        <div class="text-center">
                            <button type="button" class="btn btn-link text-decoration-none" onclick="resendCode('${email}')">
                                <i class="fa-solid fa-redo me-1"></i>Renvoyer le code
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="backToLogin()">
                                <i class="fa-solid fa-arrow-left me-1"></i>Retour
                            </button>
                        </div>
                    </form>
                `;
            }

            // Fonction pour revenir au formulaire de connexion
            function backToLogin() {
                currentStep = 'login';
                location.reload();
            }

            // Fonction pour renvoyer le code
            async function resendCode(email) {
                try {
                    const response = await fetch('/api/resend-verification-code', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({ email: email, type: 'login' })
                    });

                    let data;
                    try {
                        data = await response.json();
                    } catch (parseError) {
                        throw new Error('Réponse invalide du serveur');
                    }
                    
                    if (data.success) {
                        showMessage('codeAlert', 'Code renvoyé avec succès ! Vérifiez votre boîte email.', 'success');
                    } else {
                        const errorMessage = data.message || 'Erreur lors du renvoi du code';
                        showMessage('codeAlert', errorMessage, 'danger');
                    }
                } catch (error) {
                    console.error('Erreur renvoi code:', error);
                    
                    let errorMessage = 'Erreur lors du renvoi du code. ';
                    if (error.message === 'Failed to fetch' || error.message.includes('NetworkError')) {
                        errorMessage += 'Vérifiez votre connexion internet et réessayez.';
                    } else if (error.message.includes('Réponse invalide')) {
                        errorMessage += 'Le serveur a renvoyé une réponse invalide. Veuillez réessayer.';
                    } else {
                        errorMessage += 'Veuillez réessayer dans quelques instants.';
                    }
                    
                    showMessage('codeAlert', errorMessage, 'danger');
                }
            }

            // INSCRIPTION AJAX
            document.getElementById('registerForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Création du compte...';

                // Collecter les données du formulaire manuellement
                const formData = {
                    nom: document.getElementById('registerLastName').value,
                    prenoms: document.getElementById('registerFirstName').value,
                    email: document.getElementById('registerEmail').value,
                    telephone: document.getElementById('registerPhone').value,
                    password: document.getElementById('registerPassword').value,
                    password_confirmation: document.getElementById('registerConfirmPassword').value,
                    termes_condition: document.getElementById('acceptTerms').checked,
                    newsletter: document.getElementById('acceptNewsletter').checked
                };

                console.log('Données envoyées:', formData); // Debug

                // Réinitialiser les erreurs visuelles avant la soumission
                resetFieldErrors();

                try {
                    const response = await fetch('/api/register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify(formData)
                    });

                    let data;
                    try {
                        data = await response.json();
                    } catch (parseError) {
                        throw new Error('Réponse invalide du serveur');
                    }

                    console.log('Réponse serveur:', data);
                    
                    if (data.success) {
                        resetFieldErrors();
                        showMessage('registerAlert', data.message, 'success');
                        
                        // Vérifier si l'utilisateur invité avait des articles dans son panier
                        if (data.has_cart_items && data.cart_count > 0) {
                            // Afficher un message avec option de choix panier
                            setTimeout(() => {
                                document.getElementById('register').innerHTML = `
                                    <div class="text-center">
                                        <i class="fa-solid fa-check-circle fa-4x text-success mb-3"></i>
                                        <h4 class="text-success">Inscription réussie !</h4>
                                        <p class="text-muted">${data.message}</p>
                                        <p class="text-muted mb-3">Vous avez <strong>${data.cart_count}</strong> article(s) dans votre panier.</p>
                                        <div class="d-grid gap-2">
                                            <a href="<?php echo e(route('login')); ?>" class="btn blue-bg text-white">
                                                <i class="fa-solid fa-sign-in-alt me-1"></i>Se connecter pour valider mon panier
                                            </a>
                                            <a href="<?php echo e(route('accueil')); ?>" class="btn btn-outline-primary">
                                                <i class="fa-solid fa-store me-1"></i>Continuer mes achats
                                            </a>
                                        </div>
                                    </div>
                                `;
                            }, 2000);
                        } else {
                            // Afficher un message de succès plus détaillé
                            setTimeout(() => {
                                document.getElementById('register').innerHTML = `
                                    <div class="text-center">
                                        <i class="fa-solid fa-check-circle fa-4x text-success mb-3"></i>
                                        <h4 class="text-success">Inscription réussie !</h4>
                                        <p class="text-muted">${data.message}</p>
                                        <p class="text-muted">Vérifiez votre boîte email et cliquez sur le lien de vérification.</p>
                                        <a href="<?php echo e(route('login')); ?>" class="btn blue-bg text-white">
                                            <i class="fa-solid fa-sign-in-alt me-1"></i>Se connecter
                                        </a>
                                    </div>
                                `;
                            }, 2000);
                        }
                    } else {
                        // Afficher les erreurs détaillées avec mise en évidence des champs
                        const errorMessage = data.message || 'Veuillez corriger les erreurs dans le formulaire';
                        showMessage('registerAlert', errorMessage, 'danger', data.errors);
                        
                        // Mettre en évidence les champs en erreur
                        if (data.errors) {
                            highlightFieldErrors(data.errors);
                        }
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    
                    let errorMessage = 'Erreur de connexion au serveur. ';
                    if (error.message === 'Failed to fetch' || error.message.includes('NetworkError')) {
                        errorMessage += 'Vérifiez votre connexion internet et réessayez.';
                    } else if (error.message.includes('Réponse invalide')) {
                        errorMessage += 'Le serveur a renvoyé une réponse invalide. Veuillez réessayer.';
                    } else {
                        errorMessage += 'Veuillez réessayer dans quelques instants.';
                    }
                    
                    showMessage('registerAlert', errorMessage, 'danger');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
            
            // CONNEXION AJAX
            document.getElementById('loginForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Connexion...';

                let formData = new FormData(this);
                let object = {};
                formData.forEach((value, key) => {object[key] = value});

                // Réinitialiser les erreurs visuelles avant la soumission
                resetFieldErrors();

                try {
                    const response = await fetch('/api/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-Session-ID': getSessionId() // ⚠️ IMPORTANT : Envoyer l'ID de session invité pour fusionner le panier
                        },
                        credentials: 'same-origin', // Important : inclure les cookies dans la requête
                        body: JSON.stringify(object)
                    });

                    let data;
                    try {
                        data = await response.json();
                    } catch (parseError) {
                        throw new Error('Réponse invalide du serveur');
                    }
                    
                    if (data.success && data.requires_code) {
                        resetFieldErrors();
                        showCodeForm(data.email);
                    } else if (data.success) {
                        resetFieldErrors();
                        showMessage('loginAlert', data.message, 'success');
                        
                        // Vérifier si l'utilisateur a des articles dans son panier
                        if (data.has_cart_items && data.cart_count > 0) {
                            // Afficher le modal de choix au lieu de rediriger immédiatement
                            setTimeout(() => {
                                showCartChoiceModal(data.cart_count);
                            }, 500);
                        } else {
                            // Rediriger vers la page d'accueil avec cache-busting pour forcer le rechargement
                            setTimeout(() => {
                                const redirectUrl = (data.redirect || '<?php echo e(route("accueil")); ?>') + '?login=' + Date.now();
                                window.location.replace(redirectUrl);
                            }, 1000);
                        }
                    } else {
                        const errorMessage = data.message || 'Erreur lors de la connexion';
                        showMessage('loginAlert', errorMessage, 'danger', data.errors);
                        
                        // Mettre en évidence les champs en erreur
                        if (data.errors) {
                            highlightFieldErrors(data.errors);
                        } else if (data.error_type === 'email_not_found') {
                            // Mettre en évidence le champ email
                            const emailField = document.getElementById('loginEmail');
                            if (emailField) {
                                emailField.classList.add('is-invalid');
                                const feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                feedback.textContent = 'Aucun compte trouvé avec cette adresse email';
                                emailField.parentElement.appendChild(feedback);
                            }
                        } else if (data.error_type === 'invalid_password') {
                            // Mettre en évidence le champ mot de passe
                            const passwordField = document.getElementById('loginPassword');
                            if (passwordField) {
                                passwordField.classList.add('is-invalid');
                                const feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                feedback.textContent = 'Mot de passe incorrect';
                                passwordField.parentElement.appendChild(feedback);
                            }
                        }
                    }
                } catch (error) {
                    console.error('Erreur connexion:', error);
                    
                    let errorMessage = 'Erreur de connexion au serveur. ';
                    if (error.message === 'Failed to fetch' || error.message.includes('NetworkError')) {
                        errorMessage += 'Vérifiez votre connexion internet et réessayez.';
                    } else if (error.message.includes('Réponse invalide')) {
                        errorMessage += 'Le serveur a renvoyé une réponse invalide. Veuillez réessayer.';
                    } else {
                        errorMessage += 'Veuillez réessayer dans quelques instants.';
                    }
                    
                    showMessage('loginAlert', errorMessage, 'danger');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });

            // VÉRIFICATION DU CODE AJAX
            document.addEventListener('submit', async function(e) {
                if (e.target.id === 'codeForm') {
                e.preventDefault();

                    const submitBtn = e.target.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Vérification...';

                    let formData = new FormData(e.target);
                let object = {};
                formData.forEach((value, key) => {object[key] = value});

                    // Réinitialiser les erreurs visuelles avant la soumission
                    resetFieldErrors();

                    try {
                        const response = await fetch('/verify-login-code', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-Session-ID': getSessionId() // ⚠️ IMPORTANT : Envoyer l'ID de session invité pour fusionner le panier
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(object)
                        });

                        let data;
                        try {
                            data = await response.json();
                        } catch (parseError) {
                            throw new Error('Réponse invalide du serveur');
                        }
                        
                        if (data.success) {
                            resetFieldErrors();
                            showMessage('codeAlert', 'Connexion réussie ! Redirection...', 'success');
                            
                            // Vérifier si l'utilisateur a des articles dans son panier
                            if (data.has_cart_items && data.cart_count > 0) {
                                // Afficher le modal de choix au lieu de rediriger immédiatement
                                setTimeout(() => {
                                    showCartChoiceModal(data.cart_count);
                                }, 500);
                            } else {
                                // Rediriger vers la page d'accueil avec session
                                setTimeout(() => {
                                    // Ajouter un paramètre de cache-busting pour forcer le rechargement
                                    const redirectUrl = (data.redirect || '<?php echo e(route("accueil")); ?>') + '?login=' + Date.now();
                                    // Utiliser replace pour éviter de garder la page d'auth dans l'historique
                                    window.location.replace(redirectUrl);
                                }, 1000);
                            }
                        } else {
                            const errorMessage = data.message || 'Code invalide ou expiré';
                            showMessage('codeAlert', errorMessage, 'danger', data.errors);
                            
                            // Mettre en évidence le champ code si erreur
                            if (data.errors && data.errors.code) {
                                const codeField = document.getElementById('verificationCode');
                                if (codeField) {
                                    codeField.classList.add('is-invalid');
                                    const feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    feedback.textContent = data.errors.code[0];
                                    codeField.parentElement.appendChild(feedback);
                                }
                            } else if (data.error_type === 'code_expired') {
                                const codeField = document.getElementById('verificationCode');
                                if (codeField) {
                                    codeField.classList.add('is-invalid');
                                    const feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    feedback.textContent = 'Ce code a expiré. Veuillez demander un nouveau code.';
                                    codeField.parentElement.appendChild(feedback);
                                }
                            }
                        }
                    } catch (error) {
                        console.error('Erreur validation code:', error);
                        
                        let errorMessage = 'Erreur lors de la vérification du code. ';
                        if (error.message === 'Failed to fetch' || error.message.includes('NetworkError')) {
                            errorMessage += 'Vérifiez votre connexion internet et réessayez.';
                        } else if (error.message.includes('Réponse invalide')) {
                            errorMessage += 'Le serveur a renvoyé une réponse invalide. Veuillez réessayer.';
                        } else {
                            errorMessage += 'Veuillez réessayer dans quelques instants.';
                        }
                        
                        showMessage('codeAlert', errorMessage, 'danger');
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                }
            });

            // Validation du code en temps réel
            document.addEventListener('input', function(e) {
                if (e.target.id === 'verificationCode') {
                    e.target.value = e.target.value.replace(/\D/g, ''); // Seulement des chiffres
                    if (e.target.value.length === 8) {
                        e.target.form.querySelector('button[type="submit"]').focus();
                    }
                }
                
                // Réinitialiser l'erreur visuelle quand l'utilisateur commence à taper
                if (e.target.classList.contains('is-invalid')) {
                    e.target.classList.remove('is-invalid');
                    const feedback = e.target.parentElement.querySelector('.invalid-feedback');
                    if (feedback) {
                        feedback.remove();
                    }
                }
            });

        </script>

        <!-- Modal de choix panier/achats -->
        <div class="modal fade" id="cartChoiceModal" tabindex="-1" aria-labelledby="cartChoiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title" id="cartChoiceModalLabel">
                            <i class="fa-solid fa-cart-shopping text-primary me-2"></i>
                            Articles dans votre panier
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <p class="mb-3">
                            Vous avez <strong id="cartItemCount">0</strong> article(s) dans votre panier.
                        </p>
                        <p class="text-muted mb-4">
                            Que souhaitez-vous faire ?
                        </p>
                        <div class="d-grid gap-2">
                            <a href="<?php echo e(route('checkout')); ?>" class="btn blue-bg text-white">
                                <i class="fa-solid fa-shopping-bag me-2"></i>
                                Valider mon panier
                            </a>
                            <a href="<?php echo e(route('accueil')); ?>" class="btn btn-outline-primary">
                                <i class="fa-solid fa-store me-2"></i>
                                Continuer mes achats
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Fonction pour afficher le modal de choix panier
            function showCartChoiceModal(cartCount) {
                const modal = new bootstrap.Modal(document.getElementById('cartChoiceModal'));
                document.getElementById('cartItemCount').textContent = cartCount || 0;
                modal.show();
            }

            // Vérifier si on doit afficher le modal après connexion/inscription
            // (pour les redirections depuis SocialAuthController)
            <?php if(session('show_cart_choice')): ?>
                document.addEventListener('DOMContentLoaded', function() {
                    const cartCount = <?php echo e(session('cart_count', 0)); ?>;
                    if (cartCount > 0) {
                        showCartChoiceModal(cartCount);
                    }
                });
            <?php endif; ?>
        </script>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
</body>
</html><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\auth\authentification.blade.php ENDPATH**/ ?>