<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact - KAZARIA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f04e27;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .info-row {
            margin-bottom: 15px;
            padding: 10px;
            background-color: white;
            border-radius: 4px;
            border-left: 4px solid #f04e27;
        }
        .label {
            font-weight: bold;
            color: #204fa1;
        }
        .message-box {
            background-color: white;
            padding: 20px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Nouveau message de contact</h1>
        <p>KAZARIA - Plateforme e-commerce</p>
    </div>

    <div class="content">
        <h2 style="color: #204fa1; margin-bottom: 25px;">Informations du contact</h2>

        <div class="info-row">
            <span class="label">Nom complet :</span> {{ $prenom }} {{ $nom }}
        </div>

        <div class="info-row">
            <span class="label">Email :</span> {{ $email }}
        </div>

        @if($telephone)
        <div class="info-row">
            <span class="label">Téléphone :</span> {{ $telephone }}
        </div>
        @endif

        <div class="info-row">
            <span class="label">Sujet :</span> {{ $sujet }}
        </div>

        <div class="info-row">
            <span class="label">Date :</span> {{ $date }}
        </div>

        @if($newsletter)
        <div class="info-row">
            <span class="label">Newsletter :</span> L'utilisateur souhaite recevoir nos actualités
        </div>
        @endif

        <div class="message-box">
            <h3 style="color: #f04e27; margin-bottom: 15px;">Message :</h3>
            <p style="white-space: pre-line;">{{ $message }}</p>
        </div>

        <div style="margin-top: 25px; padding: 15px; background-color: #e9ecef; border-radius: 4px;">
            <p style="margin: 0; font-size: 14px; color: #666;">
                <strong>Note :</strong> Vous pouvez répondre directement à cet email pour contacter l'utilisateur.
            </p>
        </div>
    </div>

    <div class="footer">
        <p>Ce message a été envoyé depuis le formulaire de contact de KAZARIA</p>
        <p>IP: {{ $ip }} | Date: {{ $date }}</p>
    </div>
</body>
</html>
