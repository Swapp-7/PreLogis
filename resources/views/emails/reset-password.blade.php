<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Réinitialisation de mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            background-color: #20364B;
            color: #FDC11F;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            border: 1px solid #ddd;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #FDC11F;
            color: #20364B;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PreLogis</h1>
    </div>
    <div class="content">
        <h2>Réinitialisation de votre mot de passe</h2>
        <p>Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.</p>
        
        <p>Cliquez sur le bouton ci-dessous pour réinitialiser votre mot de passe:</p>
        <a class="btn" href="{{ route('admin.password.reset', $token) }}?email={{ urlencode($email) }}">Réinitialiser le mot de passe</a>
        
        <p>Ce lien de réinitialisation expirera dans 60 minutes.</p>
        
        <p>Si vous n'avez pas demandé de réinitialisation de mot de passe, aucune action n'est requise.</p>
        
        <div class="footer">
            <p>Cordialement,<br>
            L'équipe PreLogis</p>
        </div>
    </div>
</body>
</html>