<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de votre email</title>
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
            background-color: #28a745;
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
        .button {
            display: inline-block;
            padding: 15px 40px;
            background-color: #28a745;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #218838;
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
            <p>Bienvenue sur la marketplace !</p>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $userName }}</strong>,</p>
            
            <p>Merci de vous être inscrit sur KAZARIA ! Nous sommes ravis de vous compter parmi nous.</p>
            
            <p>Pour finaliser la création de votre compte et accéder à toutes les fonctionnalités de notre marketplace, veuillez vérifier votre adresse email en cliquant sur le bouton ci-dessous :</p>
            
            <div class="text-center">
                <a href="{{ $verificationUrl }}" class="button">Vérifier mon email</a>
            </div>
            
            <div class="info-box">
                <strong>ℹ️ Remarque :</strong>
                <p style="margin: 5px 0 0 0;">Si le bouton ne fonctionne pas, vous pouvez copier et coller le lien suivant dans votre navigateur :</p>
                <p style="margin: 5px 0 0 0; word-break: break-all;">
                    <a href="{{ $verificationUrl }}" style="color: #007bff;">{{ $verificationUrl }}</a>
                </p>
            </div>
            
            <p>Une fois votre email vérifié, vous pourrez :</p>
            <ul>
                <li>Parcourir notre catalogue de produits</li>
                <li>Passer des commandes en toute sécurité</li>
                <li>Suivre vos commandes en temps réel</li>
                <li>Gérer votre profil et vos préférences</li>
            </ul>
            
            <p>Si vous n'avez pas créé de compte sur KAZARIA, vous pouvez ignorer cet email en toute sécurité.</p>
            
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

