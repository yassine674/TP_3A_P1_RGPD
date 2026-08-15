<?php
// verify-login.php
// Script de vérification de l'authentification admin/RH

// Démarrer la session
session_start();

// Configuration de la base de données
$servername = "localhost";
$db_username = "pmauser";  // À modifier selon votre configuration
$db_password = "pma123";      // À modifier selon votre configuration
$dbname = "mediconnect_db";

// Créer la connexion
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données: " . $conn->connect_error);
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupérer et nettoyer les données du formulaire
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validation basique
    if (empty($username) || empty($password)) {
        header("Location: admin-login.php?error=empty");
        exit();
    }
    
    // Préparer la requête SQL pour éviter les injections SQL
    $stmt = $conn->prepare("SELECT id, username, password, role, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Vérifier si l'utilisateur existe
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Vérifier le mot de passe avec password_verify
        if (password_verify($password, $user['password'])) {
            
            // Authentification réussie
            // Créer les variables de session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // Régénérer l'ID de session pour la sécurité
            session_regenerate_id(true);
            
            // Logger l'activité de connexion
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, 'LOGIN', 'Connexion réussie', ?)");
            $ip = $_SERVER['REMOTE_ADDR'];
            $log_stmt->bind_param("is", $user['id'], $ip);
            $log_stmt->execute();
            $log_stmt->close();
            
            // Mettre à jour last_login
            $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Rediriger selon le rôle
            if ($user['role'] === 'admin') {
                header("Location: admin-dashboard.php");
            } elseif ($user['role'] === 'rh') {
                header("Location: rh-dashboard.php");
            } else {
                header("Location: index.html");
            }
            exit();
            
        } else {
            // Mot de passe incorrect
            // Logger la tentative échouée
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (NULL, 'LOGIN_FAILED', ?, ?)");
            $details = "Tentative de connexion échouée pour: " . $username;
            $ip = $_SERVER['REMOTE_ADDR'];
            $log_stmt->bind_param("ss", $details, $ip);
            $log_stmt->execute();
            $log_stmt->close();
            
            header("Location: admin-login.php?error=invalid");
            exit();
        }
        
    } else {
        // Utilisateur non trouvé
        // Logger la tentative
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (NULL, 'LOGIN_FAILED', ?, ?)");
        $details = "Tentative de connexion avec utilisateur inexistant: " . $username;
        $ip = $_SERVER['REMOTE_ADDR'];
        $log_stmt->bind_param("ss", $details, $ip);
        $log_stmt->execute();
        $log_stmt->close();
        
        header("Location: admin-login.php?error=invalid");
        exit();
    }
    
    $stmt->close();
    
} else {
    // Accès direct au script sans formulaire
    header("Location: admin-login.php");
    exit();
}

$conn->close();
?>
