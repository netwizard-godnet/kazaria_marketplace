<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code d'authentification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #007bff;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 40px 30px;
        }
        .code-box {
            background-color: #f8f9fa;
            border: 2px dashed #007bff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #007bff;
            font-family: 'Courier New', monospace;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>KAZARIA</h1>
            <p>Code d'authentification</p>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $userName }}</strong>,</p>
            
            <p>Vous tentez de vous connecter à votre compte KAZARIA. Pour des raisons de sécurité, veuillez utiliser le code d'authentification ci-dessous :</p>
            
            <div class="code-box">
                <div class="code">{{ $authCode }}</div>
                <p style="margin: 10px 0 0 0; font-size: 14px; color: #6c757d;">Code d'authentification</p>
            </div>
            
            <div class="warning">
                <strong>⚠️ Important :</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Ce code expire dans <strong>15 minutes</strong></li>
                    <li>Ne partagez jamais ce code avec personne</li>
                    <li>Si vous n'avez pas demandé ce code, ignorez cet email</li>
                </ul>
            </div>
            
            <p>Si vous rencontrez des difficultés, n'hésitez pas à contacter notre support.</p>
            
            <p>Cordialement,<br>
            <strong>L'équipe KAZARIA</strong></p>
        </div>
        
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; {{ date('Y') }} KAZARIA. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>

