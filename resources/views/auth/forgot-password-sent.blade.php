<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <title>Email envoyé - KAZARIA</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Fontawesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
        <!-- CUSTOM CSS -->
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    </head>
    <body>
        <main class="container-fluid py-3">
            <div class="text-center mb-3">
                <img src="{{ asset('images/logo-orange.png') }}" class="logo-size-header" alt="">
                <p class="mb-0 fw-bold fs-7">Email envoyé</p>
            </div>
            
            <div class="d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 500px;">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-4 p-md-5 text-center">
                            <i class="fa-solid fa-envelope-open fa-4x text-success mb-4"></i>
                            
                            <h3 class="text-success mb-3">Email de réinitialisation envoyé !</h3>
                            
                            <p class="text-muted mb-4">
                                Nous avons envoyé un email de réinitialisation à votre adresse email. 
                                Veuillez vérifier votre boîte de réception et suivre les instructions.
                            </p>
                            
                            <div class="alert alert-info mb-4">
                                <i class="fa-solid fa-info-circle me-2"></i>
                                <strong>Important :</strong> Vérifiez aussi votre dossier spam ou indésirables si vous ne trouvez pas l'email.
                            </div>
                            
                            <div class="alert alert-warning mb-4">
                                <i class="fa-solid fa-clock me-2"></i>
                                <strong>Délai :</strong> Le lien de réinitialisation est valide pendant 1 heure seulement.
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="{{ route('login') }}" class="btn blue-bg text-white">
                                    <i class="fa-solid fa-sign-in-alt me-1"></i>Retour à la connexion
                                </a>
                                
                                <a href="{{ route('forgot-password') }}" class="btn btn-outline-primary">
                                    <i class="fa-solid fa-redo me-1"></i>Renvoyer l'email
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Aide supplémentaire -->
                    <div class="card mt-3 border-0 bg-light">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-3">
                                <i class="fa-solid fa-question-circle me-2"></i>Besoin d'aide ?
                            </h6>
                            <div class="row text-center">
                                <div class="col-6">
                                    <i class="fa-solid fa-envelope fa-2x text-primary mb-2"></i>
                                    <p class="small mb-0 fw-bold">Vérifiez votre email</p>
                                    <p class="small text-muted mb-0">Recherchez "KAZARIA"</p>
                                </div>
                                <div class="col-6">
                                    <i class="fa-solid fa-headset fa-2x text-warning mb-2"></i>
                                    <p class="small mb-0 fw-bold">Support client</p>
                                    <p class="small text-muted mb-0">Contactez-nous</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <p class="mb-0 fs-8">Si vous ne recevez pas l'email dans les 5 minutes, contactez notre service client.</p>
                <img src="{{ asset('images/Favicon.png') }}" class="logo-size-header" alt="">
            </div>
        </main>

        <script>
            // Animation d'entrée
            document.addEventListener('DOMContentLoaded', function() {
                const card = document.querySelector('.card');
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            });
        </script>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
