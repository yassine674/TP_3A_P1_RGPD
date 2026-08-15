<?php
// download-cv.php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: admin-login.php");
    exit();
}

$servername = "localhost";
$db_username = "pmauser";
$db_password = "pma123";
$dbname = "mediconnect_db";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("ID invalide");
}

$stmt = $conn->prepare("SELECT cv_filename, cv_path, prenom, nom FROM candidatures WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $file_path = $row['cv_path'];
    $file_name = $row['cv_filename'];
    
    if (file_exists($file_path)) {
        // Logger l'activité
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, 'DOWNLOAD_CV', ?, ?)");
        $details = "Téléchargement CV de " . $row['prenom'] . " " . $row['nom'];
        $ip = $_SERVER['REMOTE_ADDR'];
        $log_stmt->bind_param("iss", $_SESSION['user_id'], $details, $ip);
        $log_stmt->execute();
        $log_stmt->close();
        
        // Headers pour téléchargement
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        
        ob_clean();
        flush();
        readfile($file_path);
        exit;
    } else {
        die("Fichier non trouvé");
    }
} else {
    die("Candidature non trouvée");
}

$stmt->close();
$conn->close();
?>
