<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <title>Réinitialisation de mot de passe - KAZARIA</title>
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
                <p class="mb-0 fw-bold fs-7">Réinitialisation de Mot de Passe</p>
                <p class="mb-0 fs-8">Créez un nouveau mot de passe sécurisé</p>
            </div>
            
            <div class="d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 450px;">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-4 p-md-5">
                            <form id="resetPasswordForm">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">
                                
                                <div id="resetAlert"></div>
                                
                                <div class="form-floating mb-4">
                                    <input type="password" class="form-control form-control-sm" id="newPassword" placeholder="Nouveau mot de passe" name="password" required minlength="8">
                                    <label for="newPassword" class="small"><i class="fa fa-lock me-1"></i>Nouveau mot de passe</label>
                                </div>
                                
                                <div class="form-floating mb-4">
                                    <input type="password" class="form-control form-control-sm" id="confirmPassword" placeholder="Confirmer le mot de passe" name="password_confirmation" required minlength="8">
                                    <label for="confirmPassword" class="small"><i class="fa fa-lock me-1"></i>Confirmer le mot de passe</label>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <small class="text-muted" id="passwordStrengthText">Force du mot de passe</small>
                                </div>
                                
                                <button type="submit" class="btn blue-bg text-white w-100 mb-4" id="submitBtn">
                                    <i class="fa fa-key me-1"></i>Réinitialiser le mot de passe
                                </button>
                                
                                <div class="text-center">
                                    <a href="{{ route('login') }}" class="text-decoration-none">
                                        <i class="fa fa-arrow-left me-1"></i>Retour à la connexion
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <script>
            // Validation du mot de passe en temps réel
            document.getElementById('newPassword').addEventListener('input', function() {
                const password = this.value;
                const strengthBar = document.getElementById('passwordStrength');
                const strengthText = document.getElementById('passwordStrengthText');
                
                let strength = 0;
                let strengthLabel = '';
                
                if (password.length >= 8) strength += 20;
                if (password.match(/[a-z]/)) strength += 20;
                if (password.match(/[A-Z]/)) strength += 20;
                if (password.match(/[0-9]/)) strength += 20;
                if (password.match(/[^a-zA-Z0-9]/)) strength += 20;
                
                if (strength < 40) {
                    strengthLabel = 'Faible';
                    strengthBar.className = 'progress-bar bg-danger';
                } else if (strength < 80) {
                    strengthLabel = 'Moyen';
                    strengthBar.className = 'progress-bar bg-warning';
                } else {
                    strengthLabel = 'Fort';
                    strengthBar.className = 'progress-bar bg-success';
                }
                
                strengthBar.style.width = strength + '%';
                strengthText.textContent = `Force du mot de passe: ${strengthLabel}`;
            });
            
            // Validation de la confirmation du mot de passe
            document.getElementById('confirmPassword').addEventListener('input', function() {
                const password = document.getElementById('newPassword').value;
                const confirmPassword = this.value;
                
                if (confirmPassword && password !== confirmPassword) {
                    this.setCustomValidity('Les mots de passe ne correspondent pas');
                } else {
                    this.setCustomValidity('');
                }
            });
            
            // Soumission du formulaire
            document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = document.getElementById('submitBtn');
                const alertDiv = document.getElementById('resetAlert');
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Traitement...';
                
                let formData = new FormData(this);
                let object = {};
                formData.forEach((value, key) => {object[key] = value});
                
                try {
                    const response = await fetch('/api/reset-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(object)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alertDiv.innerHTML = `
                            <div class="alert alert-success">
                                <i class="fa fa-check-circle me-2"></i>${data.message}
                            </div>
                        `;
                        
                        // Rediriger vers la page de connexion après 3 secondes
                        setTimeout(() => {
                            window.location.href = '{{ route("login") }}';
                        }, 3000);
                    } else {
                        alertDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fa fa-exclamation-triangle me-2"></i>${data.message}
                            </div>
                        `;
                    }
                } catch (error) {
                    alertDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-triangle me-2"></i>Une erreur est survenue. Veuillez réessayer.
                        </div>
                    `;
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa fa-key me-1"></i>Réinitialiser le mot de passe';
                }
            });
        </script>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
