<?php
// get-candidature-details.php
session_start();

if (!isset($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

$servername = "localhost";
$db_username = "pmauser";
$db_password = "pma123";
$dbname = "mediconnect_db";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion']);
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID invalide']);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM candidatures WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $data['date_soumission'] = date('d/m/Y à H:i', strtotime($data['date_soumission']));
    
    header('Content-Type: application/json');
    echo json_encode($data);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Candidature non trouvée']);
}

$stmt->close();
$conn->close();
?>
