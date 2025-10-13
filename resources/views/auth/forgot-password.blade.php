<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <title>Mot de passe oublié - KAZARIA</title>
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
                <p class="mb-0 fw-bold fs-7">Mot de passe oublié</p>
                <p class="mb-0 fs-8">Entrez votre adresse email pour recevoir un lien de réinitialisation</p>
            </div>
            
            <div class="d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 450px;">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-4 p-md-5">
                            <!-- Étapes du processus -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 14px;">
                                            <i class="fa-solid fa-envelope"></i>
                                        </div>
                                        <span class="ms-2 small fw-bold text-primary">1. Email</span>
                                    </div>
                                    <div class="flex-grow-1 mx-2">
                                        <div class="progress" style="height: 2px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 33%"></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 14px;">
                                            <i class="fa-solid fa-link"></i>
                                        </div>
                                        <span class="ms-2 small text-muted">2. Lien</span>
                                    </div>
                                    <div class="flex-grow-1 mx-2">
                                        <div class="progress" style="height: 2px;">
                                            <div class="progress-bar bg-secondary" role="progressbar" style="width: 33%"></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 14px;">
                                            <i class="fa-solid fa-key"></i>
                                        </div>
                                        <span class="ms-2 small text-muted">3. Nouveau mot de passe</span>
                                    </div>
                                </div>
                            </div>

                            <form id="forgotPasswordForm">
                                @csrf
                                
                                <div id="forgotPasswordAlert"></div>
                                
                                <div class="form-floating mb-4">
                                    <input type="email" class="form-control form-control-sm" id="forgotEmail" 
                                           placeholder="name@example.com" name="email" required autofocus>
                                    <label for="forgotEmail" class="small">
                                        <i class="fa-solid fa-envelope me-1"></i>Adresse email
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn blue-bg text-white w-100 mb-4" id="submitBtn">
                                    <i class="fa-solid fa-paper-plane me-1"></i>Envoyer le lien de réinitialisation
                                </button>
                                
                                <div class="text-center">
                                    <a href="{{ route('login') }}" class="text-decoration-none">
                                        <i class="fa-solid fa-arrow-left me-1"></i>Retour à la connexion
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Informations supplémentaires -->
                    <div class="card mt-3 border-0 bg-light">
                        <div class="card-body p-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <i class="fa-solid fa-shield-alt fa-2x text-primary mb-2"></i>
                                    <p class="small mb-0 fw-bold">Sécurisé</p>
                                    <p class="small text-muted mb-0">100% sécurisé</p>
                                </div>
                                <div class="col-4">
                                    <i class="fa-solid fa-clock fa-2x text-warning mb-2"></i>
                                    <p class="small mb-0 fw-bold">Rapide</p>
                                    <p class="small text-muted mb-0">Quelques minutes</p>
                                </div>
                                <div class="col-4">
                                    <i class="fa-solid fa-envelope-open fa-2x text-success mb-2"></i>
                                    <p class="small mb-0 fw-bold">Email</p>
                                    <p class="small text-muted mb-0">Vérifiez votre boîte</p>
                                </div>
                            </div>
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
            // Fonction pour afficher un message
            function showMessage(message, type = 'success') {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
                
                document.getElementById('forgotPasswordAlert').innerHTML = `
                    <div class="alert ${alertClass}" role="alert">
                        <i class="fa-solid ${icon} me-2"></i>${message}
                    </div>
                `;
                
                // Scroll vers le message
                document.getElementById('forgotPasswordAlert').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // Soumission du formulaire
            document.getElementById('forgotPasswordForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = document.getElementById('submitBtn');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Envoi en cours...';

                const formData = {
                    email: document.getElementById('forgotEmail').value
                };

                try {
                    const response = await fetch('/api/forgot-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        // Rediriger vers la page de confirmation
                        window.location.href = '{{ route("forgot-password-sent") }}';
                    } else {
                        showMessage(data.message, 'danger');
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    showMessage('Erreur de connexion. Veuillez réessayer.', 'danger');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });

            // Validation en temps réel de l'email
            document.getElementById('forgotEmail').addEventListener('input', function() {
                const email = this.value;
                const submitBtn = document.getElementById('submitBtn');
                
                // Validation basique de l'email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (emailRegex.test(email)) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('btn-secondary');
                    submitBtn.classList.add('blue-bg');
                } else {
                    submitBtn.disabled = true;
                    submitBtn.classList.remove('blue-bg');
                    submitBtn.classList.add('btn-secondary');
                }
            });

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
