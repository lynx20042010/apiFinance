<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur API Finance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .credentials {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }
        .credentials h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .credential-item {
            margin: 10px 0;
            padding: 8px;
            background-color: #ffffff;
            border-radius: 3px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .account-info {
            background-color: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #27ae60;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏦 Bienvenue sur API Finance</h1>
            <p>Bonjour {{ $user->name }},</p>
            <p>Votre compte a été créé avec succès ! Voici vos informations de connexion :</p>
        </div>

        <div class="credentials">
            <h3>🔐 Vos identifiants de connexion</h3>
            <div class="credential-item">
                <strong>Email :</strong> {{ $user->email }}
            </div>
            <div class="credential-item">
                <strong>Mot de passe :</strong> {{ $password }}
            </div>
        </div>

        @if($client && $compte)
        <div class="account-info">
            <h3>📋 Informations de votre compte</h3>
            <p><strong>Numéro client :</strong> {{ $client->numeroCompte }}</p>
            <p><strong>Numéro de compte :</strong> {{ $compte->numeroCompte }}</p>
            <p><strong>Type de compte :</strong> {{ ucfirst($compte->type) }}</p>
            <p><strong>Devise :</strong> {{ $compte->devise }}</p>
            <p><strong>Solde actuel :</strong> {{ number_format($compte->solde, 2) }} {{ $compte->devise }}</p>
        </div>
        @endif

        <div class="warning">
            <strong>⚠️ Sécurité importante :</strong><br>
            • Conservez ces informations en lieu sûr<br>
            • Changez votre mot de passe lors de votre première connexion<br>
            • Ne partagez jamais vos identifiants avec qui que ce soit
        </div>

        <p>Vous pouvez maintenant vous connecter à votre compte et commencer à utiliser nos services bancaires.</p>

        <p>Cordialement,<br>
        <strong>L'équipe API Finance</strong></p>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
            <p>© {{ date('Y') }} API Finance - Tous droits réservés</p>
        </div>
    </div>
</body>
</html>