<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - MEDICONNECT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-green: #059669;
            --dark-green: #047857;
            --light-green: #10b981;
            --bg-green: #e8f5e9;
            --text-dark: #0f1720;
            --text-muted: #6b7280;
        }

        body {
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* Container principal split */
        .login-container {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        /* Partie gauche - Formulaire */
        .login-form-section {
            flex: 1;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 80px;
            position: relative;
        }

        /* Logo en haut */
        .login-logo {
            position: absolute;
            top: 40px;
            left: 80px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .login-logo i {
            color: var(--primary-green);
            font-size: 2rem;
        }

        .login-logo h1 {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(90deg, #0a7a41 0%, #10b981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Formulaire de connexion */
        .login-form-wrapper {
            width: 100%;
            max-width: 400px;
        }

        .login-title {
            margin-bottom: 10px;
        }

        .login-title h2 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 40px;
        }

        /* Champs de formulaire */
        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .form-group label i {
            color: var(--text-muted);
            font-size: 1rem;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-green);
            background: white;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        /* Bouton de connexion */
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(90deg, #10b981, #059669);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 28px rgba(5,150,105,0.25);
            font-family: 'Inter', sans-serif;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 42px rgba(5,150,105,0.35);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Lien de retour */
        .back-link {
            text-align: center;
            margin-top: 24px;
        }

        .back-link a {
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: var(--dark-green);
        }

        /* Message d'erreur */
        .error-message {
            background: #fee;
            color: #c81e1e;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: none;
            border: 1px solid #fcc;
        }

        .error-message.show {
            display: block;
        }

        /* Partie droite - Image */
        .login-image-section {
            flex: 1;
            background: #1a1a1a;
            position: relative;
            overflow: hidden;
        }

        .login-image-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(5,150,105,0.85) 0%, rgba(4,120,87,0.9) 100%);
            z-index: 1;
        }

        .login-image-section img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(100%);
        }

        /* Overlay content */
        .image-overlay-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            text-align: center;
            color: white;
            width: 80%;
        }

        .image-overlay-content i {
            font-size: 5rem;
            margin-bottom: 24px;
            opacity: 0.95;
        }

        .image-overlay-content h3 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .image-overlay-content p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .login-form-section {
                padding: 60px 40px;
            }

            .login-logo {
                left: 40px;
            }
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .login-image-section {
                display: none;
            }

            .login-form-section {
                padding: 40px 30px;
            }

            .login-logo {
                position: static;
                margin-bottom: 40px;
                justify-content: center;
            }

            .image-overlay-content h3 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Partie gauche - Formulaire -->
        <div class="login-form-section">
            <!-- Logo -->
            <div class="login-logo">
                <i class="fas fa-heartbeat"></i>
                <h1>MEDICONNECT</h1>
            </div>

            <!-- Formulaire -->
            <div class="login-form-wrapper">
                <div class="login-title">
                    <h2>Connexion</h2>
                    <p class="login-subtitle">Accédez à votre espace administrateur</p>
                </div>

                <!-- Message d'erreur -->
                <div class="error-message" id="errorMessage">
                    <i class="fas fa-exclamation-circle"></i> Identifiants incorrects
                </div>

                <!-- Formulaire -->
                <form id="loginForm" action="verify-login.php" method="POST">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i>
                            Nom d'utilisateur
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input" 
                            placeholder="Entrez votre nom d'utilisateur"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Mot de passe
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Entrez votre mot de passe"
                            required
                        >
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </form>

                <div class="back-link">
                    <a href="index.html">
                        <i class="fas fa-arrow-left"></i> Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>

        <!-- Partie droite - Image -->
        <div class="login-image-section">
            <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=1200&q=80" alt="Équipe médicale MEDICONNECT">
            
            <div class="image-overlay-content">
                <i class="fas fa-shield-alt"></i>
                <h3>Espace Sécurisé</h3>
                <p>Accédez à votre tableau de bord administrateur pour gérer les candidatures et les données en toute sécurité.</p>
            </div>
        </div>
    </div>

    <script>
        // Gestion du formulaire
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            // En développement local sans serveur PHP, vous pouvez commenter cette ligne pour tester
            // e.preventDefault();
            
            // Exemple de validation côté client (optionnel)
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                document.getElementById('errorMessage').classList.add('show');
                return false;
            }
            
            // Le formulaire sera envoyé à verify-login.php
            // qui devra vérifier les identifiants dans la base de données
        });

        // Masquer le message d'erreur lors de la saisie
        document.getElementById('username').addEventListener('input', function() {
            document.getElementById('errorMessage').classList.remove('show');
        });

        document.getElementById('password').addEventListener('input', function() {
            document.getElementById('errorMessage').classList.remove('show');
        });
    </script>
</body>
</html>
