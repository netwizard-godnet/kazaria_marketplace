<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Vérification email - KAZARIA</title>

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
            <p class="mb-0 fw-bold fs-7">Vérification de votre email</p>
            <p class="mb-0 fs-8">Consultez votre boîte email pour continuer</p>
        </div>
        
        <!-- SECTION FORM -->
        <div class="d-flex align-items-center justify-content-center">
            <div class="w-100" style="max-width: 450px;">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4 p-md-5 text-center">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show text-start" role="alert">
                                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('info'))
                            <div class="alert alert-info alert-dismissible fade show text-start" role="alert">
                                <i class="bi bi-info-circle me-1"></i>{{ session('info') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="my-4">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                                <i class="bi bi-envelope-check text-success" style="font-size: 3.5rem;"></i>
                            </div>
                        </div>

                        <h5 class="mb-3">Vérifiez votre email</h5>
                        
                        <p class="text-muted small">
                            Un lien de vérification a été envoyé à votre adresse email. 
                            Veuillez cliquer sur le lien dans l'email pour vérifier votre compte.
                        </p>

                        <div class="alert alert-light text-start">
                            <i class="bi bi-lightbulb text-warning me-1"></i>
                            <small class="text-muted">
                                <strong>Astuce :</strong> Vérifiez également votre dossier spam/courrier indésirable si vous ne trouvez pas l'email.
                            </small>
                        </div>

                        <hr>

                        <p class="text-muted small mb-3">
                            Vous n'avez pas reçu l'email ?
                        </p>

                        <form method="POST" action="{{ route('verification.resend') }}">
                            @csrf
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control form-control-sm" 
                                       id="email" name="email" placeholder="name@example.com" required>
                                <label for="email" class="small"><i class="bi bi-envelope me-1"></i>Votre adresse email</label>
                            </div>
                            <button type="submit" class="btn blue-bg text-white w-100 mb-3">
                                <i class="bi bi-arrow-clockwise me-1"></i>Renvoyer l'email de vérification
                            </button>
                        </form>

                        <div class="text-center">
                            <a href="{{ route('accueil') }}" class="text-decoration-none small">
                                <i class="bi bi-arrow-left me-1"></i>Retour à l'accueil
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
</body>
</html>
