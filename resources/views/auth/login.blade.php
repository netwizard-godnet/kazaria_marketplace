<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Connexion & Inscription - KAZARIA</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- CUSTOM CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
</head>

<body>
    <main class="container-fluid py-3">
        <div class="text-center mb-3">
            <img src="{{ asset('images/logo-orange.png') }}" class="logo-size-header" alt="KAZARIA">
            <p class="mb-0 fw-bold fs-7">Bienvenue chez KAZARIA</p>
            <p class="mb-0 fs-8">Veuillez vous connecter ou inscrivez-vous si vous n'avez pas de compte</p>
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
                                <form method="POST" action="{{ route('login') }}" id="loginForm">
                                    @csrf
                                    
                                    <div id="loginAlert">
                                        @if(session('success'))
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        @endif

                                        @if(session('info'))
                                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                                <i class="bi bi-info-circle me-1"></i>{{ session('info') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        @endif

                                        @if($errors->any() && !session('resend_verification') && old('_form') !== 'register')
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <ul class="mb-0 small">
                                                    @foreach($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        @endif

                                        @if(session('resend_verification'))
                                            <div class="alert alert-warning">
                                                <p class="mb-2 small"><i class="bi bi-exclamation-circle me-1"></i>Votre email n'est pas encore vérifié.</p>
                                                <form method="POST" action="{{ route('verification.resend') }}">
                                                    @csrf
                                                    <input type="hidden" name="email" value="{{ old('email') }}">
                                                    <button type="submit" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-envelope me-1"></i>Renvoyer l'email de vérification
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <input type="hidden" name="_form" value="login">
                                    
                                    <div class="form-floating mb-4">
                                        <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror" 
                                               id="loginEmail" placeholder="name@example.com" name="email" 
                                               value="{{ old('email') }}" required autofocus>
                                        <label for="loginEmail" class="small"><i class="bi bi-envelope me-1"></i>Adresse email</label>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-floating mb-4">
                                        <input type="password" class="form-control form-control-sm @error('password') is-invalid @enderror" 
                                               id="loginPassword" placeholder="Mot de passe" name="password" required>
                                        <label for="loginPassword" class="small"><i class="bi bi-lock me-1"></i>Mot de passe</label>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                                            <label class="form-check-label small" for="rememberMe">
                                                Se souvenir de moi
                                            </label>
                                        </div>
                                        <a href="{{ route('password.request') }}" class="text-decoration-none small">Mot de passe oublié ?</a>
                                    </div>
                                    
                                    <button type="submit" class="btn blue-bg text-white w-100 mb-3">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>Se connecter
                                    </button>
                                    
                                    <div class="text-center">
                                        <a href="{{ route('accueil') }}" class="text-decoration-none small">
                                            <i class="bi bi-arrow-left me-1"></i>Retour à l'accueil
                                        </a>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Register Form -->
                            <div class="tab-pane fade {{ old('_form') === 'register' ? 'show active' : '' }}" id="register" role="tabpanel">
                                <form method="POST" action="{{ route('register') }}" id="registerForm">
                                    @csrf
                                    
                                    <div id="registerAlert">
                                        @if($errors->any() && old('_form') === 'register')
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <ul class="mb-0 small">
                                                    @foreach($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <input type="hidden" name="_form" value="register">
                                    
                                    <div class="row g-2">
                                        <div class="col-md-12">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control form-control-sm @error('nom') is-invalid @enderror" 
                                                       id="registerLastName" placeholder="Nom" name="nom" 
                                                       value="{{ old('nom') }}" required>
                                                <label for="registerLastName" class="small"><i class="bi bi-person me-1"></i>Nom</label>
                                                @error('nom')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control form-control-sm @error('prenoms') is-invalid @enderror" 
                                                       id="registerFirstName" placeholder="Prénom(s)" name="prenoms" 
                                                       value="{{ old('prenoms') }}" required>
                                                <label for="registerFirstName" class="small"><i class="bi bi-person me-1"></i>Prénom(s)</label>
                                                @error('prenoms')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror" 
                                               id="registerEmail" placeholder="name@example.com" name="email" 
                                               value="{{ old('email') }}" required>
                                        <label for="registerEmail" class="small"><i class="bi bi-envelope me-1"></i>Adresse email</label>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="tel" class="form-control form-control-sm @error('telephone') is-invalid @enderror" 
                                               id="registerPhone" placeholder="+225 XX XX XX XX" name="telephone" 
                                               value="{{ old('telephone') }}">
                                        <label for="registerPhone" class="small"><i class="bi bi-telephone me-1"></i>Téléphone (optionnel)</label>
                                        @error('telephone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control form-control-sm @error('password') is-invalid @enderror" 
                                               id="registerPassword" placeholder="Mot de passe" name="password" required>
                                        <label for="registerPassword" class="small"><i class="bi bi-lock me-1"></i>Mot de passe</label>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Minimum 8 caractères</small>
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control form-control-sm" 
                                               id="registerConfirmPassword" placeholder="Confirmer le mot de passe" 
                                               name="password_confirmation" required>
                                        <label for="registerConfirmPassword" class="small"><i class="bi bi-lock-fill me-1"></i>Confirmer le mot de passe</label>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input @error('termes_condition') is-invalid @enderror" 
                                               type="checkbox" id="acceptTerms" value="1" name="termes_condition" 
                                               {{ old('termes_condition') ? 'checked' : '' }} required>
                                        <label class="form-check-label small" for="acceptTerms">
                                            J'accepte les <a href="#" class="text-primary text-decoration-none">conditions d'utilisation</a> et la <a href="#" class="text-primary text-decoration-none">politique de confidentialité</a>
                                        </label>
                                        @error('termes_condition')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-check mb-4">
                                        <input class="form-check-input" type="checkbox" id="acceptNewsletter" 
                                               value="1" name="newsletter" {{ old('newsletter') ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="acceptNewsletter">
                                            Je souhaite recevoir les offres et actualités par email
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn blue-bg text-white w-100 mb-3">
                                        <i class="bi bi-person-plus me-1"></i>Créer mon compte
                                    </button>
                                    
                                    <div class="text-center">
                                        <a href="{{ route('accueil') }}" class="text-decoration-none small">
                                            <i class="bi bi-arrow-left me-1"></i>Retour à l'accueil
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
            <img src="{{ asset('images/favicon.png') }}" class="logo-size-header mt-2" alt="KAZARIA">
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Si des erreurs d'inscription, afficher l'onglet inscription
        @if(old('_form') === 'register')
            document.addEventListener('DOMContentLoaded', function() {
                var registerTab = new bootstrap.Tab(document.getElementById('register-tab'));
                registerTab.show();
            });
        @endif
    </script>
</body>
</html>
