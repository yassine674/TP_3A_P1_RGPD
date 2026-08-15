<?php
// submit-candidature.php
// Script de traitement des candidatures avec upload Nextcloud

// Configuration Nextcloud
$nextcloud_url = "http://172.20.10.6/nextcloud/remote.php/dav/files/RGPD/";
$nextcloud_username = "RGPD";
$nextcloud_password = "MIDOUMIDOU22";
$nextcloud_base_url = "http://172.20.10.6/nextcloud";

// Configuration de la base de données
$servername = "localhost";
$db_username = "pmauser";
$db_password = "pma123";
$dbname = "mediconnect_db";

// Dossier de sauvegarde LOCAL des CV (backup)
$upload_dir = "uploads/cv/";

// Créer le dossier s'il n'existe pas
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Créer la connexion
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupérer et nettoyer les données
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $date_naissance = trim($_POST['naissance']);
    $diplome = trim($_POST['diplome']);
    $adresse_postale = trim($_POST['adresse_postale']);
    $securite_sociale = trim($_POST['securite']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    
    // Validation des champs obligatoires
    if (empty($prenom) || empty($nom) || empty($email) || empty($telephone)) {
        die("Erreur : Tous les champs obligatoires doivent être remplis.");
    }
    
    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Erreur : Adresse email invalide.");
    }
    
    // Variables pour le CV
    $cv_filename = "";
    $cv_path = "";
    $cv_nextcloud_url = "";
    
    // Traitement du fichier CV
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] == 0) {
        
        $allowed_extensions = ['pdf', 'doc', 'docx'];
        $max_size = 10 * 1024 * 1024; // 10 MB
        
        $file_name = $_FILES['cv']['name'];
        $file_size = $_FILES['cv']['size'];
        $file_tmp = $_FILES['cv']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Vérifier l'extension
        if (!in_array($file_ext, $allowed_extensions)) {
            die("Erreur : Format de fichier non autorisé. Formats acceptés : PDF, DOC, DOCX");
        }
        
        // Vérifier la taille
        if ($file_size > $max_size) {
            die("Erreur : Le fichier est trop volumineux. Taille maximale : 10 MB");
        }
        
        // Créer un nom de fichier unique
        $cv_filename = date('Y-m-d_His') . '_' . $prenom . '_' . $nom . '.' . $file_ext;
        $cv_filename = preg_replace("/[^a-zA-Z0-9._-]/", "", $cv_filename);
        $cv_path = $upload_dir . $cv_filename;
        
        // Sauvegarder localement (backup)
        if (move_uploaded_file($file_tmp, $cv_path)) {
            
            // UPLOAD VERS NEXTCLOUD
            $nextcloud_cv_folder = $nextcloud_url . "MEDICONNECT_CV/";
            $nextcloud_cv_path = $nextcloud_cv_folder . $cv_filename;
            
            // Créer le dossier MEDICONNECT_CV sur Nextcloud
            $mkdir_ch = curl_init();
            curl_setopt($mkdir_ch, CURLOPT_URL, $nextcloud_cv_folder);
            curl_setopt($mkdir_ch, CURLOPT_USERPWD, $nextcloud_username . ":" . $nextcloud_password);
            curl_setopt($mkdir_ch, CURLOPT_CUSTOMREQUEST, "MKCOL");
            curl_setopt($mkdir_ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($mkdir_ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($mkdir_ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_exec($mkdir_ch);
            curl_close($mkdir_ch);
            
            // Upload le fichier vers Nextcloud
            $ch = curl_init();
            $file_handle = fopen($cv_path, 'r');
            
            curl_setopt($ch, CURLOPT_URL, $nextcloud_cv_path);
            curl_setopt($ch, CURLOPT_USERPWD, $nextcloud_username . ":" . $nextcloud_password);
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_INFILE, $file_handle);
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($cv_path));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            fclose($file_handle);
            curl_close($ch);
            
            if ($http_code >= 200 && $http_code < 300) {
                // Upload réussi sur Nextcloud
                // Créer le lien de téléchargement Nextcloud
                $cv_nextcloud_url = $nextcloud_base_url . "/index.php/apps/files/?dir=/MEDICONNECT_CV&openfile=" . $cv_filename;
                
                // Alternativement, créer un partage public (plus complexe)
                // Pour l'instant, on utilise le lien direct
                
            } else {
                // Échec upload Nextcloud, on garde quand même le fichier local
                error_log("Échec upload Nextcloud : HTTP {$http_code}");
            }
            
        } else {
            die("Erreur : Impossible de sauvegarder le fichier CV.");
        }
    }
    
    // Préparer la requête SQL
    $stmt = $conn->prepare("INSERT INTO candidatures (prenom, nom, date_naissance, diplome, adresse_postale, securite_sociale, email, telephone, cv_filename, cv_path, cv_nextcloud_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("sssssssssss", 
        $prenom, 
        $nom, 
        $date_naissance, 
        $diplome, 
        $adresse_postale, 
        $securite_sociale, 
        $email, 
        $telephone, 
        $cv_filename, 
        $cv_path,
        $cv_nextcloud_url
    );
    
    // Exécuter la requête
    if ($stmt->execute()) {
        $candidature_id = $stmt->insert_id;
        
        // Redirection vers une page de succès
        header("Location: candidature-success.php?id=" . $candidature_id);
        exit();
        
    } else {
        echo "Erreur lors de l'enregistrement : " . $stmt->error;
    }
    
    $stmt->close();
    
} else {
    // Accès direct au script
    header("Location: formulaire-candidature.html");
    exit();
}

$conn->close();
?>
