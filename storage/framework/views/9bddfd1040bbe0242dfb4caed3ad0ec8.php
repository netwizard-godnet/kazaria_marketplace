<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($settings['site_name'] ?? 'KAZARIA'); ?> - Bientôt disponible</title>
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
        
        /* Animations de Noël */
        .snowflake {
            position: absolute;
            top: -10px;
            color: white;
            font-size: 1.5rem;
            opacity: 0.8;
            animation: snowfall linear infinite;
            pointer-events: none;
            z-index: 2;
        }
        
        @keyframes snowfall {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.8;
            }
            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0.2;
            }
        }
        
        .star {
            position: absolute;
            color: #FFD700;
            font-size: 1.2rem;
            animation: twinkle 2s ease-in-out infinite;
            pointer-events: none;
            z-index: 2;
        }
        
        @keyframes twinkle {
            0%, 100% {
                opacity: 0.3;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }
        
        .christmas-light {
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            animation: blink 1.5s ease-in-out infinite;
            pointer-events: none;
            z-index: 2;
        }
        
        @keyframes blink {
            0%, 100% {
                opacity: 0.3;
                box-shadow: 0 0 5px currentColor;
            }
            50% {
                opacity: 1;
                box-shadow: 0 0 15px currentColor, 0 0 25px currentColor;
            }
        }
        
        .light-red { background-color: #ff0000; color: #ff0000; }
        .light-green { background-color: #00ff00; color: #00ff00; }
        .light-blue { background-color: #0066ff; color: #0066ff; }
        .light-yellow { background-color: #ffff00; color: #ffff00; }
        .light-purple { background-color: #ff00ff; color: #ff00ff; }
        
        .garland {
            position: absolute;
            width: 100%;
            height: 40px;
            top: 0;
            left: 0;
            background: repeating-linear-gradient(
                90deg,
                #ff0000 0px,
                #ff0000 20px,
                #00ff00 20px,
                #00ff00 40px,
                #0066ff 40px,
                #0066ff 60px,
                #ffff00 60px,
                #ffff00 80px
            );
            opacity: 0.6;
            z-index: 1;
            animation: garlandMove 3s linear infinite;
        }
        
        @keyframes garlandMove {
            0% {
                background-position: 0 0;
            }
            100% {
                background-position: 80px 0;
            }
        }
        
        .santa-hat {
            position: absolute;
            font-size: 3rem;
            animation: bounce 3s ease-in-out infinite;
            pointer-events: none;
            z-index: 2;
        }
        
        @keyframes bounce {
            0%, 100% {
                transform: translateY(0) rotate(-5deg);
            }
            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }
        
        /* Animations CAN Football - Mélangées avec Noël */
        .football {
            position: absolute;
            font-size: 2rem;
            animation: footballMove 4s linear infinite;
            opacity: 0.7;
            pointer-events: none;
            z-index: 2;
        }
        
        @keyframes footballMove {
            0% {
                transform: translateY(-50px) rotate(0deg);
            }
            50% {
                transform: translateY(50vh) rotate(180deg);
            }
            100% {
                transform: translateY(100vh) rotate(360deg);
            }
        }
        
        .trophy {
            position: absolute;
            font-size: 2.5rem;
            animation: trophyFloat 5s ease-in-out infinite;
            pointer-events: none;
            z-index: 2;
        }
        
        @keyframes trophyFloat {
            0%, 100% {
                transform: translateY(0) rotate(-10deg);
            }
            50% {
                transform: translateY(-30px) rotate(10deg);
            }
        }
        
        .confetti-can {
            position: absolute;
            width: 8px;
            height: 8px;
            background-color: #FFD700;
            animation: confettiFall 3s linear infinite;
            pointer-events: none;
            z-index: 2;
        }
        
        .confetti-can:nth-child(odd) {
            background-color: #FF6B35;
        }
        
        .confetti-can:nth-child(3n) {
            background-color: #004E89;
        }
        
        @keyframes confettiFall {
            0% {
                transform: translateY(-10px) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }
        
        .flag-wave {
            position: absolute;
            font-size: 1.5rem;
            animation: flagWave 2s ease-in-out infinite;
            pointer-events: none;
            z-index: 2;
        }
        
        @keyframes flagWave {
            0%, 100% {
                transform: rotate(-15deg);
            }
            50% {
                transform: rotate(15deg);
            }
        }
        
        .santa-hat {
            position: absolute;
            font-size: 3rem;
            animation: bounce 3s ease-in-out infinite;
            pointer-events: none;
            z-index: 2;
        }
        
        #mixed-animations-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            pointer-events: none;
            z-index: 2;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <!-- Guirlande de Noël en haut -->
    <div class="garland"></div>
    
    <!-- Animations mélangées (Noël + CAN) sur toute la page -->
    <div id="mixed-animations-container"></div>
    
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="landing-container">
        <div class="landing-content">
            <div class="logo-container">
                <h1><?php echo e($settings['site_name'] ?? 'KAZARIA'); ?></h1>
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
                
                <form id="newsletterForm" class="form-group" method="POST" action="<?php echo e(route('newsletter.subscribe')); ?>">
                    <?php echo csrf_field(); ?>
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
        // Compte à rebours avec date fixe
        function startCountdown() {
            // Date cible depuis le serveur (timestamp en millisecondes)
            <?php if(isset($targetTimestamp)): ?>
            const targetDate = <?php echo e($targetTimestamp); ?>;
            <?php else: ?>
            // Par défaut : maintenant + 24 heures si pas de date configurée
            const targetDate = new Date().getTime() + (24 * 60 * 60 * 1000);
            <?php endif; ?>
            
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
        
        // Créer toutes les animations mélangées sur toute la page
        function createMixedAnimations() {
            const container = document.getElementById('mixed-animations-container');
            
            // Flocons de neige (15 au lieu de 30 pour équilibrer)
            const snowflakes = ['❄', '❅', '❆', '✻', '✼'];
            for (let i = 0; i < 15; i++) {
                const snowflake = document.createElement('div');
                snowflake.className = 'snowflake';
                snowflake.textContent = snowflakes[Math.floor(Math.random() * snowflakes.length)];
                snowflake.style.left = Math.random() * 100 + '%';
                snowflake.style.animationDuration = (Math.random() * 3 + 2) + 's';
                snowflake.style.animationDelay = Math.random() * 2 + 's';
                container.appendChild(snowflake);
            }
            
            // Étoiles scintillantes (8 au lieu de 15)
            for (let i = 0; i < 8; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                star.textContent = '⭐';
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.animationDelay = Math.random() * 2 + 's';
                star.style.animationDuration = (Math.random() * 1 + 1.5) + 's';
                container.appendChild(star);
            }
            
            // Lumières de Noël (10 au lieu de 20)
            const colors = ['light-red', 'light-green', 'light-blue', 'light-yellow', 'light-purple'];
            for (let i = 0; i < 10; i++) {
                const light = document.createElement('div');
                light.className = 'christmas-light ' + colors[Math.floor(Math.random() * colors.length)];
                light.style.left = Math.random() * 100 + '%';
                light.style.top = Math.random() * 100 + '%';
                light.style.animationDelay = Math.random() * 1.5 + 's';
                container.appendChild(light);
            }
            
            // Ballons de football (5)
            for (let i = 0; i < 5; i++) {
                const football = document.createElement('div');
                football.className = 'football';
                football.textContent = '⚽';
                football.style.left = Math.random() * 100 + '%';
                football.style.animationDelay = (i * 0.8) + 's';
                football.style.animationDuration = (Math.random() * 2 + 3) + 's';
                container.appendChild(football);
            }
            
            // Trophées flottants (3)
            for (let i = 0; i < 3; i++) {
                const trophy = document.createElement('div');
                trophy.className = 'trophy';
                trophy.textContent = '🏆';
                trophy.style.left = Math.random() * 100 + '%';
                trophy.style.top = Math.random() * 100 + '%';
                trophy.style.animationDelay = (i * 1.5) + 's';
                container.appendChild(trophy);
            }
            
            // Drapeaux africains (4)
            const flags = ['🇨🇮', '🇸🇳', '🇪🇬', '🇲🇦'];
            for (let i = 0; i < 4; i++) {
                const flag = document.createElement('div');
                flag.className = 'flag-wave';
                flag.textContent = flags[i];
                flag.style.left = Math.random() * 100 + '%';
                flag.style.top = Math.random() * 100 + '%';
                flag.style.animationDelay = (i * 0.5) + 's';
                container.appendChild(flag);
            }
            
            // Confettis CAN (15 au lieu de 25)
            for (let i = 0; i < 15; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti-can';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.animationDelay = Math.random() * 3 + 's';
                confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
                container.appendChild(confetti);
            }
            
            // Emojis de Noël (3)
            const christmasEmojis = ['🎅', '🎄', '🎁'];
            for (let i = 0; i < 3; i++) {
                const emoji = document.createElement('div');
                emoji.className = 'santa-hat';
                emoji.textContent = christmasEmojis[i];
                emoji.style.left = Math.random() * 100 + '%';
                emoji.style.top = Math.random() * 100 + '%';
                emoji.style.animationDelay = (i * 1) + 's';
                container.appendChild(emoji);
            }
        }
        
        // Initialiser toutes les animations mélangées
        document.addEventListener('DOMContentLoaded', function() {
            createMixedAnimations();
        });
        
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

<?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\landing.blade.php ENDPATH**/ ?>