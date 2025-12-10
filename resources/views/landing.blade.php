<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $settings['site_name'] ?? 'KAZARIA' }} - Bientôt disponible</title>
    <meta name="description" content="KAZARIA arrive bientôt ! Une nouvelle marketplace en ligne pour tous vos besoins en Côte d'Ivoire.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Fontawesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #FF8C00;
            --secondary-color: #FFA500;
            --dark-color: #1a1a1a;
            --light-color: #f8f9fa;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            overflow-x: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
            z-index: 0;
        }
        
        .landing-container {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .landing-content {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 30px;
            padding: 4rem 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            text-align: center;
            animation: fadeInUp 1s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-container {
            margin-bottom: 2rem;
        }
        
        .logo-container img {
            max-width: 200px;
            height: auto;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        h1 {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--dark-color);
            margin-bottom: 0.8rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .subtitle {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 1.5rem;
            font-weight: 400;
        }
        
        .description {
            font-size: 0.95rem;
            color: #555;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin: 3rem 0;
        }
        
        .feature-item {
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(255, 140, 0, 0.1), rgba(255, 165, 0, 0.1));
            border-radius: 15px;
            transition: transform 0.3s ease;
        }
        
        .feature-item:hover {
            transform: translateY(-5px);
        }
        
        .feature-item i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.8rem;
        }
        
        .feature-item h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0;
        }
        
        .newsletter-form {
            margin-top: 3rem;
            padding: 2rem;
            background: linear-gradient(135deg, rgba(255, 140, 0, 0.05), rgba(255, 165, 0, 0.05));
            border-radius: 20px;
        }
        
        .newsletter-form h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.8rem;
        }
        
        .countdown-container {
            margin: 2rem 0;
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(255, 140, 0, 0.1), rgba(255, 165, 0, 0.1));
            border-radius: 15px;
        }
        
        .countdown-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 1rem;
        }
        
        .countdown {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .countdown-item {
            background: white;
            padding: 1rem 1.2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            min-width: 70px;
        }
        
        .countdown-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1;
        }
        
        .countdown-label {
            font-size: 0.75rem;
            color: #666;
            text-transform: uppercase;
            margin-top: 0.3rem;
            letter-spacing: 0.5px;
        }
        
        .form-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .form-control {
            flex: 1;
            padding: 1rem 1.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 140, 0, 0.1);
        }
        
        .btn-primary {
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 0, 0.4);
        }
        
        
        .alert {
            margin-top: 1rem;
            border-radius: 10px;
            border: none;
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 1.8rem;
            }
            
            .subtitle {
                font-size: 1rem;
            }
            
            .landing-content {
                padding: 2rem 1.5rem;
            }
            
            .features {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .form-group {
                flex-direction: column;
            }
            
            .countdown-item {
                min-width: 60px;
                padding: 0.8rem 1rem;
            }
            
            .countdown-number {
                font-size: 1.5rem;
            }
            
            .countdown-label {
                font-size: 0.7rem;
            }
        }
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        
        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 20s infinite ease-in-out;
        }
        
        .shape:nth-child(1) {
            top: 10%;
            left: 10%;
            width: 100px;
            height: 100px;
            background: var(--primary-color);
            border-radius: 50%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            top: 60%;
            right: 10%;
            width: 150px;
            height: 150px;
            background: var(--secondary-color);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            animation-delay: 5s;
        }
        
        .shape:nth-child(3) {
            bottom: 20%;
            left: 20%;
            width: 80px;
            height: 80px;
            background: var(--primary-color);
            border-radius: 20% 80% 80% 20% / 20% 20% 80% 80%;
            animation-delay: 10s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            33% {
                transform: translate(30px, -30px) rotate(120deg);
            }
            66% {
                transform: translate(-20px, 20px) rotate(240deg);
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="landing-container">
        <div class="landing-content">
            <div class="logo-container">
                <h1>{{ $settings['site_name'] ?? 'KAZARIA' }}</h1>
            </div>
            
            <h2 class="subtitle">🚀 Bientôt disponible !</h2>
            
            <p class="description">
                Nous préparons quelque chose d'extraordinaire pour vous. Une nouvelle expérience de shopping en ligne 
                arrive bientôt en Côte d'Ivoire. Restez connecté pour être parmi les premiers à découvrir KAZARIA !
            </p>
            
            <div class="countdown-container">
                <div class="countdown-title">⏰ Lancement dans :</div>
                <div class="countdown" id="countdown">
                    <div class="countdown-item">
                        <div class="countdown-number" id="hours">00</div>
                        <div class="countdown-label">Heures</div>
                    </div>
                    <div class="countdown-item">
                        <div class="countdown-number" id="minutes">00</div>
                        <div class="countdown-label">Minutes</div>
                    </div>
                    <div class="countdown-item">
                        <div class="countdown-number" id="seconds">00</div>
                        <div class="countdown-label">Secondes</div>
                    </div>
                </div>
            </div>
            
            <div class="features">
                <div class="feature-item">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Large Choix</h3>
                </div>
                <div class="feature-item">
                    <i class="fas fa-shipping-fast"></i>
                    <h3>Livraison Rapide</h3>
                </div>
                <div class="feature-item">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Paiement Sécurisé</h3>
                </div>
                <div class="feature-item">
                    <i class="fas fa-headset"></i>
                    <h3>Support 24/7</h3>
                </div>
            </div>
            
            <div class="newsletter-form">
                <h3>📧 Soyez informé en premier !</h3>
                <p style="color: #666; margin-bottom: 0;">Recevez une notification dès notre lancement</p>
                
                <form id="newsletterForm" class="form-group" method="POST" action="{{ route('newsletter.subscribe') }}">
                    @csrf
                    <input 
                        type="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="Votre adresse email" 
                        required
                        id="emailInput"
                    >
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>S'inscrire
                    </button>
                </form>
                
                <div id="newsletterAlert" style="display: none;"></div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Compte à rebours de 24 heures
        function startCountdown() {
            // Date cible : maintenant + 24 heures
            const targetDate = new Date().getTime() + (24 * 60 * 60 * 1000);
            
            function updateCountdown() {
                const now = new Date().getTime();
                const distance = targetDate - now;
                
                if (distance < 0) {
                    // Le compte à rebours est terminé
                    document.getElementById('hours').textContent = '00';
                    document.getElementById('minutes').textContent = '00';
                    document.getElementById('seconds').textContent = '00';
                    return;
                }
                
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                document.getElementById('hours').textContent = String(hours).padStart(2, '0');
                document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
                document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
            }
            
            // Mettre à jour immédiatement
            updateCountdown();
            
            // Mettre à jour toutes les secondes
            setInterval(updateCountdown, 1000);
        }
        
        // Démarrer le compte à rebours au chargement de la page
        document.addEventListener('DOMContentLoaded', startCountdown);
        
        // Gestion du formulaire de newsletter
        document.getElementById('newsletterForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = this;
            const emailInput = document.getElementById('emailInput');
            const alertDiv = document.getElementById('newsletterAlert');
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Désactiver le bouton pendant l'envoi
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Envoi...';
            
            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                alertDiv.style.display = 'block';
                
                if (response.ok && data.success) {
                    alertDiv.className = 'alert alert-success';
                    alertDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + (data.message || 'Merci ! Vous serez informé dès notre lancement.');
                    form.reset();
                } else {
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + (data.message || 'Une erreur est survenue. Veuillez réessayer.');
                }
            } catch (error) {
                alertDiv.style.display = 'block';
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Une erreur est survenue. Veuillez réessayer.';
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                
                // Masquer l'alerte après 5 secondes
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</body>
</html>

