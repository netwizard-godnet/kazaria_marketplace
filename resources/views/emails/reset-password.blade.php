<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe</title>
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
            background-color: #ffc107;
            color: #000000;
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
        .button {
            display: inline-block;
            padding: 15px 40px;
            background-color: #ffc107;
            color: #000000 !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #e0a800;
        }
        .warning {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 20px 0;
        }
        .info-box {
            background-color: #d1ecf1;
            border-left: 4px solid #17a2b8;
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
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>KAZARIA</h1>
            <p>Réinitialisation de mot de passe</p>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $userName }}</strong>,</p>
            
            <p>Vous avez demandé la réinitialisation de votre mot de passe KAZARIA. Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
            
            <div class="text-center">
                <a href="{{ $resetUrl }}" class="button">Réinitialiser mon mot de passe</a>
            </div>
            
            <div class="warning">
                <strong>⚠️ Important :</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Ce lien expire dans <strong>1 heure</strong></li>
                    <li>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email</li>
                    <li>Votre mot de passe actuel restera inchangé jusqu'à ce que vous en créiez un nouveau</li>
                </ul>
            </div>
            
            <div class="info-box">
                <strong>ℹ️ Remarque :</strong>
                <p style="margin: 5px 0 0 0;">Si le bouton ne fonctionne pas, vous pouvez copier et coller le lien suivant dans votre navigateur :</p>
                <p style="margin: 5px 0 0 0; word-break: break-all;">
                    <a href="{{ $resetUrl }}" style="color: #007bff;">{{ $resetUrl }}</a>
                </p>
            </div>
            
            <p><strong>Conseils pour un mot de passe sécurisé :</strong></p>
            <ul>
                <li>Utilisez au moins 8 caractères</li>
                <li>Mélangez majuscules et minuscules</li>
                <li>Incluez des chiffres et des caractères spéciaux</li>
                <li>Évitez les mots courants ou les informations personnelles</li>
            </ul>
            
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

