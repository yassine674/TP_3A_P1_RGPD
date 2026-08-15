<?php
// rh-dashboard.php
session_start();

// Vérifier si l'utilisateur est connecté et est RH
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'rh') {
    header("Location: admin-login.php");
    exit();
}

// Configuration de la base de données
$servername = "localhost";
$db_username = "pmauser";
$db_password = "pma123";  // Modifier avec ton mot de passe MySQL
$dbname = "mediconnect_db";

// Connexion à la base
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

// Récupérer toutes les candidatures
$sql = "SELECT * FROM candidatures ORDER BY date_soumission DESC";
$result = $conn->query($sql);

// Compter les candidatures par statut
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
    SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as en_cours,
    SUM(CASE WHEN statut = 'accepte' THEN 1 ELSE 0 END) as accepte,
    SUM(CASE WHEN statut = 'refuse' THEN 1 ELSE 0 END) as refuse
    FROM candidatures";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard RH - MEDICONNECT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0fff4;
            color: #0f1720;
        }

        /* Header */
        .header {
            background: linear-gradient(90deg, #10b981, #059669);
            color: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 800;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .btn-logout {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 40px;
        }

        .page-title {
            font-size: 2rem;
            color: #0a7a41;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: #6b7280;
            margin-bottom: 30px;
        }

        .info-box {
            background: #e0f2fe;
            border-left: 4px solid #3b82f6;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .info-box i {
            color: #3b82f6;
            margin-right: 10px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid;
        }

        .stat-card.total { border-left-color: #059669; }
        .stat-card.pending { border-left-color: #f59e0b; }
        .stat-card.progress { border-left-color: #3b82f6; }
        .stat-card.accepted { border-left-color: #10b981; }
        .stat-card.refused { border-left-color: #ef4444; }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #0a7a41;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .table-header h3 {
            color: #0a7a41;
            font-size: 1.3rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f0fff4;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 700;
            color: #0a7a41;
            font-size: 0.9rem;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-en_attente {
            background: #fef3c7;
            color: #92400e;
        }

        .status-en_cours {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-accepte {
            background: #d1fae5;
            color: #065f46;
        }

        .status-refuse {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Buttons */
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #059669;
            color: white;
        }

        .btn-primary:hover {
            background: #047857;
        }

        .btn-download {
            background: #3b82f6;
            color: white;
        }

        .btn-download:hover {
            background: #2563eb;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .close-modal {
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }

        .detail-row {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-label {
            font-weight: 600;
            color: #0a7a41;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #4b5563;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-heartbeat"></i>
                MEDICONNECT - RH
            </div>
            <div class="user-info">
                <span><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <h1 class="page-title">Tableau de bord RH</h1>
        <p class="page-subtitle">Consultation des candidatures</p>

        <!-- Info Box -->
        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <strong>Accès en lecture seule :</strong> Vous pouvez consulter les candidatures et télécharger les CV, mais vous ne pouvez pas les modifier ou les supprimer.
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label"><i class="fas fa-file-alt"></i> Total Candidatures</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number"><?php echo $stats['en_attente']; ?></div>
                <div class="stat-label"><i class="fas fa-clock"></i> En attente</div>
            </div>
            <div class="stat-card progress">
                <div class="stat-number"><?php echo $stats['en_cours']; ?></div>
                <div class="stat-label"><i class="fas fa-spinner"></i> En cours</div>
            </div>
            <div class="stat-card accepted">
                <div class="stat-number"><?php echo $stats['accepte']; ?></div>
                <div class="stat-label"><i class="fas fa-check-circle"></i> Acceptées</div>
            </div>
            <div class="stat-card refused">
                <div class="stat-number"><?php echo $stats['refuse']; ?></div>
                <div class="stat-label"><i class="fas fa-times-circle"></i> Refusées</div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-header">
                <h3><i class="fas fa-list"></i> Liste des candidatures</h3>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['prenom'] . ' ' . $row['nom']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['telephone']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['date_soumission'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $row['statut']; ?>">
                                        <?php 
                                        $status_labels = [
                                            'en_attente' => 'En attente',
                                            'en_cours' => 'En cours',
                                            'accepte' => 'Acceptée',
                                            'refuse' => 'Refusée'
                                        ];
                                        echo $status_labels[$row['statut']];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-primary" onclick="viewDetails(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-eye"></i> Voir
                                        </button>
                                        <?php if (!empty($row['cv_path'])): ?>
                                            <a href="download-cv.php?id=<?php echo $row['id']; ?>" class="btn btn-download">
                                                <i class="fas fa-download"></i> CV
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>
                                Aucune candidature pour le moment
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Details -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Détails de la candidature</h3>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        function viewDetails(id) {
            fetch('get-candidature-details.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    let html = `
                        <div class="detail-row">
                            <div class="detail-label">Nom complet</div>
                            <div class="detail-value">${data.prenom} ${data.nom}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Date de naissance</div>
                            <div class="detail-value">${data.date_naissance || 'Non renseignée'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Email</div>
                            <div class="detail-value">${data.email}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Téléphone</div>
                            <div class="detail-value">${data.telephone}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Diplôme</div>
                            <div class="detail-value">${data.diplome || 'Non renseigné'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Adresse</div>
                            <div class="detail-value">${data.adresse_postale || 'Non renseignée'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Sécurité sociale</div>
                            <div class="detail-value">${data.securite_sociale || 'Non renseigné'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Date de soumission</div>
                            <div class="detail-value">${data.date_soumission}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Statut</div>
                            <div class="detail-value"><span class="status-badge status-${data.statut}">${data.statut.replace('_', ' ')}</span></div>
                        </div>
                    `;
                    
                    document.getElementById('modalBody').innerHTML = html;
                    document.getElementById('detailsModal').style.display = 'flex';
                });
        }

        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
