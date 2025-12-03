<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de vérification - KAZARIA</title>
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
        .code-container {
            background-color: #f8f9fa;
            border: 2px dashed #ff8c00;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            color: #ff8c00;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .message {
            font-size: 16px;
            margin-bottom: 20px;
            color: #555;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
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
            <h1>
                @if($type === 'login')
                    Code de Connexion
                @elseif($type === 'password_reset')
                    Code de Réinitialisation
                @else
                    Code de Vérification
                @endif
            </h1>
        </div>
        
        <div class="content">
            @if($userName)
                <p class="message">Bonjour <strong>{{ $userName }}</strong>,</p>
            @else
                <p class="message">Bonjour,</p>
            @endif
            
            <p class="message">
                @if($type === 'login')
                    Vous avez demandé à vous connecter à votre compte KAZARIA. Voici votre code de connexion :
                @elseif($type === 'password_reset')
                    Vous avez demandé à réinitialiser votre mot de passe. Voici votre code de réinitialisation :
                @else
                    Voici votre code de vérification pour finaliser votre inscription :
                @endif
            </p>
            
            <div class="code-container">
                <div class="code">{{ $code }}</div>
            </div>
            
            <div class="warning">
                <strong>⚠️ Important :</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Ce code est valide pendant <strong>15 minutes</strong> seulement</li>
                    <li>Ne partagez jamais ce code avec qui que ce soit</li>
                    <li>Si vous n'avez pas demandé cette action, ignorez cet email</li>
                </ul>
            </div>
            
            <p class="message">
                Entrez ce code dans le champ prévu à cet effet pour 
                @if($type === 'login')
                    vous connecter à votre compte.
                @elseif($type === 'password_reset')
                    réinitialiser votre mot de passe.
                @else
                    vérifier votre adresse email.
                @endif
            </p>
        </div>
        
        <div class="footer">
            <p>
                Cet email a été envoyé automatiquement par KAZARIA.<br>
                Si vous avez des questions, contactez notre service client.
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                © {{ date('Y') }} KAZARIA. Tous droits réservés.
            </p>
        </div>
    </div>
</body>
</html>
