<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

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
                @auth
                Connecté
                @else
                Pas Connecté
                @endauth
            </div>
            <!-- SECTION FORM -->
            <div class="d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 450px;">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-4 p-md-5">
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
                                            <a href="#" class="text-decoration-none small" onclick="showForgotPassword()">Mot de passe oublié ?</a>
                                        </div>
                                        
                                        <button type="submit" class="btn blue-bg text-white w-100 mb-4">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>Se connecter
                                        </button>
                                        
                                        <div class="text-center mb-4">
                                            <span class="text-muted small">Ou continuer avec</span>
                                        </div>
                                        
                                        <div class="d-grid gap-3">
                                            <button type="button" class="btn btn-outline-danger fs-6">
                                                <i class="bi bi-google me-1 h4"></i>Google
                                            </button>
                                            <button type="button" class="btn btn-outline-primary fs-6">
                                                <i class="bi bi-facebook me-1 h4"></i>Facebook
                                            </button>
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
                                        
                                        <button type="submit" class="btn blue-bg text-white w-100 mb-4">
                                            <i class="bi bi-person-plus me-1"></i>Créer mon compte
                                        </button>
                                        
                                        <div class="text-center mb-4">
                                            <span class="text-muted small">Ou s'inscrire avec</span>
                                        </div>
                                        
                                        <div class="d-grid gap-3">
                                            <button type="button" class="btn btn-outline-danger">
                                                <i class="bi bi-google me-1 h4"></i>Google
                                            </button>
                                            <button type="button" class="btn btn-outline-primary">
                                                <i class="bi bi-facebook me-1 h4"></i>Facebook
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- SECTION FORM END -->

            <!-- Forgot Password Modal -->
            <div class="modal fade" id="forgotPasswordModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-key me-2"></i>Récupération de mot de passe</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted">Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>
                            <form id="forgotPasswordForm">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="forgotEmail" placeholder="name@example.com" required>
                                    <label for="forgotEmail"><i class="bi bi-envelope me-2"></i>Adresse email</label>
                                </div>
                                <button type="submit" class="btn blue-bg text-white w-100">
                                    <i class="bi bi-send me-2"></i>Envoyer le lien de réinitialisation
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3">
                <p class="mb-0 fs-8">Si besoin d'aide, merci de vous référer au Centre d'Assistance ou de contacter notre service client.</p>
                <img src="{{ asset('images/Favicon.png') }}" class="logo-size-header" alt="">
            </div>
        </main>

        <script>
            //INSCRIPTION AJAX
            document.getElementById('registerForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                let object = {};
                formData.forEach((value, key) => {object[key] = value});

                const response = await fetch("{{ url('/api/register') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(object)
                });

                const data = await response.json();
                console.log(data);
                // Afficher le message dans le HTML
                if (data.success) {
                    document.getElementById('registerAlert').textContent = data.message;
                    document.getElementById('register').innerHTML = `<div class="alert alert-success" role="alert">`+
                                                                data.message + `
                                                                </div>`;
                }else{
                    document.getElementById('registerAlert').textContent = data.message;
                    document.getElementById('register').innerHTML = `<div class="alert alert-danger" role="alert">`+
                                                                data.message + `<i class="fa-solid fa-circle-notch fa-spin"></i>
                                                                </div><br><div class="text-center"><a href="" class="btn btn-sm btn-light">Actualiser <i class="bi bi-arrow-clockwise"></i></a></div>`;
                }
                
            });
            
            //CONNEXION AJAX
            document.getElementById('loginForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                let object = {};
                formData.forEach((value, key) => {object[key] = value});

                const response = await fetch("{{ url('/api/login') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(object)
                });

                const data = await response.json();
                console.log(data);
                // Afficher le message dans le HTML
                if (data.success) {
                    document.getElementById('login').innerHTML = `<div class="alert alert-success" role="alert">`+
                                                                data.message + `<i class="fa-solid fa-circle-notch fa-spin"></i>
                                                                </div>`;
                }else{
                    document.getElementById('login').innerHTML = `<div class="alert alert-danger" role="alert">`+
                                                                data.message + `
                                                                </div><br><div class="text-center"><a href="" class="btn btn-sm btn-light">Actualiser <i class="bi bi-arrow-clockwise"></i></a></div>`;
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