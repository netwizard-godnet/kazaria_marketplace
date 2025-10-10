<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Vérification du code - KAZARIA</title>

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
            <p class="mb-0 fw-bold fs-7">Vérification de connexion</p>
            <p class="mb-0 fs-8">Entrez le code reçu par email pour finaliser votre connexion</p>
        </div>
        
        <!-- SECTION FORM -->
        <div class="d-flex align-items-center justify-content-center">
            <div class="w-100" style="max-width: 450px;">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="blue-bg bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <i class="bi bi-shield-lock blue-color" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Code de sécurité</h5>
                        </div>
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

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

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-1"></i>
                            <small>Un code à 8 chiffres a été envoyé à votre adresse email. Le code expire dans <strong>15 minutes</strong>.</small>
                        </div>
                        
                        <form method="POST" action="{{ route('verify-code.verify') }}">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="code" class="form-label small fw-bold">
                                    <i class="bi bi-key me-1"></i>Code d'authentification
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg text-center @error('code') is-invalid @enderror" 
                                       id="code" name="code" maxlength="8" pattern="[0-9]{8}" 
                                       placeholder="00000000" required autofocus 
                                       style="letter-spacing: 0.5em; font-size: 1.5rem; font-family: monospace;">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Entrez le code à 8 chiffres reçu par email</small>
                            </div>
                            
                            <button type="submit" class="btn blue-bg text-white w-100 mb-3">
                                <i class="bi bi-shield-check me-1"></i>Vérifier le code
                            </button>
                        </form>

                        <hr>

                        <div class="text-center">
                            <p class="mb-2 small text-muted">Vous n'avez pas reçu le code ?</p>
                            <form method="POST" action="{{ route('verify-code.resend') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Renvoyer le code
                                </button>
                            </form>
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ route('login') }}" class="text-decoration-none small">
                                <i class="bi bi-arrow-left me-1"></i>Retour à la connexion
                            </a>
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
        // Permettre uniquement les chiffres dans le champ code
        document.getElementById('code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>
