<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidature envoyée - MEDICONNECT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #d7f8e8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            max-width: 600px;
            background: white;
            border-radius: 20px;
            padding: 60px 50px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(5,150,105,0.15);
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 40px;
        }

        .logo i {
            color: #059669;
            font-size: 2rem;
        }

        .logo h1 {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(90deg, #0a7a41 0%, #10b981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #d7f8e8, #e8f5e9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease;
        }

        .success-icon i {
            font-size: 4rem;
            color: #059669;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        h2 {
            font-size: 2rem;
            font-weight: 800;
            color: #0a7a41;
            margin-bottom: 16px;
        }

        .success-message {
            color: #6b7280;
            font-size: 1.1rem;
            line-height: 1.7;
            margin-bottom: 12px;
        }

        .candidature-id {
            background: #f0fff4;
            padding: 16px;
            border-radius: 10px;
            margin: 30px 0;
            border: 2px solid #d7f8e8;
        }

        .candidature-id strong {
            color: #0a7a41;
            font-size: 1.1rem;
        }

        .info-box {
            background: #f9fafb;
            padding: 20px;
            border-radius: 12px;
            text-align: left;
            margin: 30px 0;
            border-left: 4px solid #059669;
        }

        .info-box h4 {
            color: #0a7a41;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-box ul {
            list-style: none;
            padding: 0;
        }

        .info-box ul li {
            padding: 8px 0;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-box ul li i {
            color: #059669;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 40px;
            background: linear-gradient(90deg, #10b981, #059669);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.05rem;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 28px rgba(5,150,105,0.25);
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 42px rgba(5,150,105,0.35);
        }

        @media (max-width: 768px) {
            .success-container {
                padding: 40px 30px;
            }

            h2 {
                font-size: 1.6rem;
            }

            .success-icon {
                width: 100px;
                height: 100px;
            }

            .success-icon i {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <!-- Logo -->
        <div class="logo">
            <i class="fas fa-heartbeat"></i>
            <h1>MEDICONNECT</h1>
        </div>

        <!-- Icône de succès -->
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>

        <!-- Message de succès -->
        <h2>Candidature envoyée avec succès !</h2>
        
        <p class="success-message">
            Nous avons bien reçu votre candidature. Notre équipe RH l'examinera dans les plus brefs délais.
        </p>

        <?php if (isset($_GET['id'])): ?>
        <div class="candidature-id">
            <strong>Numéro de candidature : #<?php echo htmlspecialchars($_GET['id']); ?></strong>
        </div>
        <?php endif; ?>

        <!-- Informations importantes -->
        <div class="info-box">
            <h4>
                <i class="fas fa-info-circle"></i>
                Prochaines étapes
            </h4>
            <ul>
                <li>
                    <i class="fas fa-check"></i>
                    Vous recevrez un email de confirmation sous 24h
                </li>
                <li>
                    <i class="fas fa-check"></i>
                    Notre équipe RH examinera votre profil
                </li>
                <li>
                    <i class="fas fa-check"></i>
                    Si votre profil correspond, nous vous contacterons par téléphone
                </li>
                <li>
                    <i class="fas fa-check"></i>
                    Délai de traitement : 5 à 10 jours ouvrés
                </li>
            </ul>
        </div>

        <!-- Bouton retour -->
        <a href="index.html" class="btn-home">
            <i class="fas fa-home"></i>
            Retour à l'accueil
        </a>
    </div>
</body>
</html>
