<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Mot de passe oublié - KAZARIA</title>

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
            <p class="mb-0 fw-bold fs-7">Mot de passe oublié</p>
            <p class="mb-0 fs-8">Nous allons vous aider à réinitialiser votre mot de passe</p>
        </div>
        
        <!-- SECTION FORM -->
        <div class="d-flex align-items-center justify-content-center">
            <div class="w-100" style="max-width: 450px;">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <i class="bi bi-key text-warning" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Réinitialiser votre mot de passe</h5>
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

                        <p class="text-muted small mb-4">
                            <i class="bi bi-info-circle me-1"></i>
                            Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
                        </p>
                        
                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            
                            <div class="form-floating mb-4">
                                <input type="email" 
                                       class="form-control form-control-sm @error('email') is-invalid @enderror" 
                                       id="email" placeholder="name@example.com" name="email" 
                                       value="{{ old('email') }}" required autofocus>
                                <label for="email" class="small"><i class="bi bi-envelope me-1"></i>Adresse email</label>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <button type="submit" class="btn btn-warning w-100 mb-3">
                                <i class="bi bi-send me-1"></i>Envoyer le lien de réinitialisation
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
