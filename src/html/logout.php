<?php
// logout.php
session_start();

// Configuration de la base de données
$servername = "localhost";
$db_username = "pmauser";
$db_password = "pma123";
$dbname = "mediconnect_db";

// Logger la déconnexion
if (isset($_SESSION['logged_in']) && isset($_SESSION['user_id'])) {
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    
    if (!$conn->connect_error) {
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, 'LOGOUT', 'Déconnexion', ?)");
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param("is", $_SESSION['user_id'], $ip);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
}

// Détruire la session
$_SESSION = array();
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}
session_destroy();

// Redirection
header("Location: admin-login.php");
exit();
?>
