<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe - KAZARIA</title>
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
        .reset-button {
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
        .reset-button:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 20px;
            margin: 30px 0;
            color: #856404;
        }
        .security-tips {
            background-color: #f8f9fa;
            border-left: 4px solid #6c757d;
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
            <img src="{{ asset('images/logo-orange.png') }}" alt="KAZARIA" class="logo">
            <h1>Réinitialisation de Mot de Passe</h1>
        </div>
        
        <div class="content">
            @if($userName)
                <p>Bonjour <strong>{{ $userName }}</strong>,</p>
            @else
                <p>Bonjour,</p>
            @endif
            
            <p>Vous avez demandé à réinitialiser votre mot de passe pour votre compte KAZARIA.</p>
            
            <p>Pour créer un nouveau mot de passe sécurisé, cliquez sur le bouton ci-dessous :</p>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="reset-button">
                    <i class="fa fa-key"></i> Réinitialiser mon mot de passe
                </a>
            </div>
            
            <div class="warning-box">
                <h3 style="margin-top: 0;">⚠️ Important :</h3>
                <ul style="margin-bottom: 0;">
                    <li>Ce lien est valide pendant <strong>1 heure</strong> seulement</li>
                    <li>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email</li>
                    <li>Votre mot de passe actuel reste valide jusqu'à ce que vous le changiez</li>
                </ul>
            </div>
            
            <p><strong>Si le bouton ne fonctionne pas</strong>, copiez et collez ce lien dans votre navigateur :</p>
            <p style="word-break: break-all; color: #ff8c00; font-family: monospace; background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                {{ $resetUrl }}
            </p>
            
            <div class="security-tips">
                <h3 style="margin-top: 0; color: #495057;">🔒 Conseils de sécurité :</h3>
                <ul style="margin-bottom: 0;">
                    <li>Utilisez un mot de passe fort avec au moins 8 caractères</li>
                    <li>Incluez des majuscules, minuscules, chiffres et symboles</li>
                    <li>N'utilisez pas le même mot de passe sur plusieurs sites</li>
                    <li>Changez régulièrement vos mots de passe</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p>
                Cet email a été envoyé automatiquement par KAZARIA.<br>
                Si vous n'avez pas demandé cette réinitialisation, contactez notre service client.
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                © {{ date('Y') }} KAZARIA. Tous droits réservés.
            </p>
        </div>
    </div>
</body>
</html>
