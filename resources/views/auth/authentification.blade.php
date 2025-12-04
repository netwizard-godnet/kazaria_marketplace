<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $siteFavicon = \App\Models\Setting::get('site_favicon');
            $faviconUrl = $siteFavicon ? asset('storage/' . ltrim($siteFavicon, '/')) : asset('favicon.png');
        @endphp
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">

        <title>{{ config('app.name', 'KAZARIA Connexion') }}</title>

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
        <link rel="stylesheet" href="{{ asset('slick/slick.css') }}">
        <link rel="stylesheet" href="{{ asset('slick/slick-theme.css') }}">
        <!-- CUSTOM CSS -->
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
        <link rel="stylesheet" href="{{ asset('css/profil.css') }}">
        <link rel="stylesheet" href="{{ asset('css/carousel.css') }}">
        <!-- FONTS -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    </head>

    <body>
        <main class="container-fluid py-3">
            <div class="text-center mb-3">
                <img src="{{ asset('images/logo-orange.png') }}" class="logo-size-header" alt="">
                <p class="mb-0 fw-bold fs-7">Bienvenue chez KAZARIA</p>
                <p class="mb-0 fs-8">Veuillez connecter ou inscrivez-vous si vous n'avez pas de compte</p>
            </div>
            <!-- SECTION FORM -->
            <div class="d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 450px;">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-4 p-md-5">
                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                </div>
                            @endif
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                </div>
                            @endif
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
                                        @csrf
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
                                            <a href="{{ route('forgot-password') }}" class="text-decoration-none small">Mot de passe oublié ?</a>
                                        </div>
                                        
                                        <button type="submit" class="btn orange-bg text-white w-100 mb-4">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>Se connecter
                                        </button>
                                        
                                        <div class="text-center mb-4">
                                            <span class="text-muted small">Ou continuer avec</span>
                                        </div>
                                        
                                        <div class="d-grid gap-3">
                                            <a href="{{ route('social.redirect', ['provider' => 'google']) }}" class="btn btn-outline-danger fs-6">
                                                <i class="bi bi-google me-1 h4"></i>Google
                                            </a>
                                            <a href="{{ route('social.redirect', ['provider' => 'facebook']) }}" class="btn btn-outline-primary fs-6">
                                                <i class="bi bi-facebook me-1 h4"></i>Facebook
                                            </a>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Register Form -->
                                <div class="tab-pane fade" id="register" role="tabpanel">
                                    <form id="registerForm">
                                        @csrf
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
                                            <a href="{{ route('social.redirect', ['provider' => 'google']) }}" class="btn btn-outline-danger">
                                                <i class="bi bi-google me-1 h4"></i>Google
                                            </a>
                                            <a href="{{ route('social.redirect', ['provider' => 'facebook']) }}" class="btn btn-outline-primary">
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
                <img src="{{ asset('images/Favicon.png') }}" class="logo-size-header" alt="">
            </div>
        </main>

        <script>
            let currentStep = 'login'; // 'login', 'code', 'register'
            let userEmail = '';

            // Fonction pour afficher un message
            function showMessage(elementId, message, type = 'success') {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
                
                document.getElementById(elementId).innerHTML = `
                    <div class="alert ${alertClass}" role="alert">
                        <i class="fa-solid ${icon} me-2"></i>${message}
                    </div>
                `;
                
                // Scroll vers le message d'erreur
                document.getElementById(elementId).scrollIntoView({ behavior: 'smooth', block: 'center' });
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
                        @csrf
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
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ email: email, type: 'login' })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        showMessage('codeAlert', 'Code renvoyé avec succès !', 'success');
                    } else {
                        showMessage('codeAlert', data.message, 'danger');
                    }
                } catch (error) {
                    showMessage('codeAlert', 'Erreur lors du renvoi du code', 'danger');
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

                try {
                    const response = await fetch('/api/register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();
                    console.log('Réponse serveur:', data); // Debug
                    
                    if (data.success) {
                        showMessage('registerAlert', data.message, 'success');
                        
                        // Afficher un message de succès plus détaillé
                        setTimeout(() => {
                            document.getElementById('register').innerHTML = `
                                <div class="text-center">
                                    <i class="fa-solid fa-check-circle fa-4x text-success mb-3"></i>
                                    <h4 class="text-success">Inscription réussie !</h4>
                                    <p class="text-muted">${data.message}</p>
                                    <p class="text-muted">Vérifiez votre boîte email et cliquez sur le lien de vérification.</p>
                                    <a href="{{ route('login') }}" class="btn blue-bg text-white">
                                        <i class="fa-solid fa-sign-in-alt me-1"></i>Se connecter
                                    </a>
                                </div>
                            `;
                        }, 2000);
                    } else {
                        // Afficher les erreurs détaillées si disponibles
                        let errorMessage = data.message;
                        if (data.errors) {
                            const errorList = Object.values(data.errors).flat();
                            errorMessage = errorList.join('<br>');
                        }
                        showMessage('registerAlert', errorMessage, 'danger');
                    }
                } catch (error) {
                    console.error('Erreur:', error); // Debug
                    showMessage('registerAlert', 'Erreur de connexion. Veuillez réessayer.', 'danger');
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

                try {
                    const response = await fetch('/api/login', {
                        method: 'POST',
                    headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(object)
                });

                const data = await response.json();
                    
                    if (data.success && data.requires_code) {
                        showCodeForm(data.email);
                    } else if (data.success) {
                        showMessage('loginAlert', data.message, 'success');
                        // Rediriger vers la page d'accueil
                        setTimeout(() => {
                            window.location.replace('{{ route("accueil") }}');
                        }, 2000);
                    } else {
                        showMessage('loginAlert', data.message || 'Erreur de connexion', 'danger');
                    }
                } catch (error) {
                    console.error('Erreur connexion:', error);
                    showMessage('loginAlert', 'Erreur de connexion. Veuillez réessayer.', 'danger');
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

                    try {
                        // Utiliser la route web au lieu de /api/ pour avoir accès aux sessions
                        const response = await fetch('{{ route("web.verify-login-code") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'include', // CRITIQUE : inclure les cookies de session
                            body: JSON.stringify(object)
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            showMessage('codeAlert', 'Connexion réussie ! Redirection...', 'success');
                            
                            console.log('✅ Authentification réussie', data);
                            console.log('Session ID:', data.session_id);
                            
                            // Vérifier immédiatement si on peut accéder à une route protégée
                            // pour confirmer que la session fonctionne
                            fetch('{{ route("accueil") }}', {
                                method: 'GET',
                                credentials: 'include',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            }).then(res => {
                                console.log('✅ Test accès accueil - Status:', res.status);
                                // Rediriger après le test
                                setTimeout(() => {
                                    const redirectUrl = data.redirect || '{{ route("accueil") }}';
                                    const separator = redirectUrl.includes('?') ? '&' : '?';
                                    const finalUrl = redirectUrl + separator + 'login=' + Date.now() + '&t=' + Math.random();
                                    console.log('🔄 Redirection vers:', finalUrl);
                                    window.location.href = finalUrl;
                                }, 500);
                            }).catch(err => {
                                console.error('❌ Erreur test accueil:', err);
                                // Rediriger quand même
                                setTimeout(() => {
                                    const redirectUrl = data.redirect || '{{ route("accueil") }}';
                                    window.location.href = redirectUrl;
                                }, 500);
                            });
                        } else {
                            showMessage('codeAlert', data.message || 'Code invalide ou expiré', 'danger');
                        }
                    } catch (error) {
                        console.error('Erreur validation code:', error);
                        showMessage('codeAlert', 'Erreur lors de la vérification du code. Veuillez réessayer.', 'danger');
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
            });

        </script>
<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
</body>
</html>