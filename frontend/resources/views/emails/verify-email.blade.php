<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérifiez votre email - KAZARIA</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #ff8c00, #ff6b35);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .content {
            padding: 40px 30px;
        }
        .welcome-message {
            font-size: 18px;
            margin-bottom: 30px;
            color: #555;
        }
        .verify-button {
            display: inline-block;
            background: linear-gradient(135deg, #ff8c00, #ff6b35);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0;
            transition: transform 0.2s ease;
        }
        .verify-button:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            margin: 30px 0;
            border-radius: 5px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #e9ecef;
        }
        .logo {
            width: 120px;
            height: auto;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="KAZARIA" class="logo">
            <h1>Bienvenue sur KAZARIA !</h1>
        </div>
        
        <div class="content">
            <div class="welcome-message">
                <p>Bonjour <strong>{{ $user->prenoms }} {{ $user->nom }}</strong>,</p>
                <p>Félicitations ! Votre compte KAZARIA a été créé avec succès.</p>
            </div>
            
            <p>Pour activer votre compte et commencer à profiter de tous nos services, veuillez vérifier votre adresse email en cliquant sur le bouton ci-dessous :</p>
            
            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="verify-button">
                    <i class="fa fa-check-circle"></i> Vérifier mon email
                </a>
            </div>
            
            <div class="info-box">
                <h3 style="margin-top: 0; color: #1976d2;">🎉 Que vous attend après la vérification :</h3>
                <ul style="margin-bottom: 0;">
                    <li>Accès complet à votre espace personnel</li>
                    <li>Suivi de vos commandes en temps réel</li>
                    <li>Historique de vos achats</li>
                    <li>Notifications personnalisées</li>
                    <li>Accès aux offres exclusives</li>
                </ul>
            </div>
            
            <p><strong>Si le bouton ne fonctionne pas</strong>, copiez et collez ce lien dans votre navigateur :</p>
            <p style="word-break: break-all; color: #ff8c00; font-family: monospace; background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                {{ $verificationUrl }}
            </p>
            
            <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0;">
                <strong>⚠️ Important :</strong> Ce lien de vérification est valide pendant 24 heures. Si vous ne vérifiez pas votre email dans ce délai, vous devrez demander un nouveau lien de vérification.
            </div>
        </div>
        
        <div class="footer">
            <p>
                Bienvenue dans la famille KAZARIA !<br>
                Si vous avez des questions, notre service client est là pour vous aider.
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                © {{ date('Y') }} KAZARIA. Tous droits réservés.
            </p>
        </div>
    </div>
</body>
</html>
