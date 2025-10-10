<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Réinitialiser le mot de passe - KAZARIA</title>

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
            <p class="mb-0 fw-bold fs-7">Nouveau mot de passe</p>
            <p class="mb-0 fs-8">Créez un nouveau mot de passe sécurisé</p>
        </div>
        
        <!-- SECTION FORM -->
        <div class="d-flex align-items-center justify-content-center">
            <div class="w-100" style="max-width: 450px;">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <i class="bi bi-shield-lock text-warning" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Réinitialiser le mot de passe</h5>
                        </div>
                        
                        @if($errors->any())
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

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="email" value="{{ $email }}">

                            <div class="form-floating mb-3">
                                <input type="email" class="form-control form-control-sm" 
                                       id="email_display" value="{{ $email }}" disabled>
                                <label for="email_display" class="small"><i class="bi bi-envelope me-1"></i>Adresse email</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="password" 
                                       class="form-control form-control-sm @error('password') is-invalid @enderror" 
                                       id="password" placeholder="Nouveau mot de passe" name="password" 
                                       required autofocus>
                                <label for="password" class="small"><i class="bi bi-lock me-1"></i>Nouveau mot de passe</label>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimum 8 caractères</small>
                            </div>

                            <div class="form-floating mb-4">
                                <input type="password" class="form-control form-control-sm" 
                                       id="password_confirmation" placeholder="Confirmer le mot de passe" 
                                       name="password_confirmation" required>
                                <label for="password_confirmation" class="small"><i class="bi bi-lock-fill me-1"></i>Confirmer le mot de passe</label>
                            </div>

                            <div class="alert alert-light">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <strong>Conseils pour un mot de passe sécurisé :</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Au moins 8 caractères</li>
                                        <li>Majuscules et minuscules</li>
                                        <li>Chiffres et caractères spéciaux</li>
                                    </ul>
                                </small>
                            </div>

                            <button type="submit" class="btn btn-warning w-100 mb-3">
                                <i class="bi bi-check-circle me-1"></i>Réinitialiser le mot de passe
                            </button>

                            <hr>

                            <div class="text-center">
                                <a href="{{ route('login') }}" class="text-decoration-none small">
                                    <i class="bi bi-arrow-left me-1"></i>Retour à la connexion
                                </a>
                            </div>
                        </form>
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
</body>
</html>
