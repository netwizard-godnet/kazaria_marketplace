<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <title>Vérification d'email - KAZARIA</title>
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
                <p class="mb-0 fw-bold fs-7">Vérification d'Email</p>
            </div>
            
            <div class="d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 450px;">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-4 p-md-5 text-center">
                            <div id="verificationResult">
                                <!-- Le résultat sera affiché ici -->
                            </div>
                            
                            <div class="mt-4">
                                <a href="{{ route('login') }}" class="btn blue-bg text-white">
                                    <i class="fa fa-arrow-left me-2"></i>Retour à la connexion
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <script>
            // Vérifier l'email automatiquement au chargement
            document.addEventListener('DOMContentLoaded', async function() {
                const resultDiv = document.getElementById('verificationResult');
                const token = '{{ $token }}';
                
                try {
                    const response = await fetch(`/api/verify-email/${token}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                                <h4>Email vérifié avec succès !</h4>
                                <p class="mb-0">Votre compte est maintenant actif. Vous pouvez vous connecter.</p>
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fa fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                                <h4>Erreur de vérification</h4>
                                <p class="mb-0">${data.message}</p>
                            </div>
                        `;
                    }
                } catch (error) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                            <h4>Erreur de connexion</h4>
                            <p class="mb-0">Une erreur est survenue. Veuillez réessayer.</p>
                        </div>
                    `;
                }
            });
        </script>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
