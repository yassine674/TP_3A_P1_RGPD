<?php
// backup-to-nextcloud.php
// Script de sauvegarde automatique vers Nextcloud

// Configuration Nextcloud
$nextcloud_url = "http://172.20.10.6/nextcloud/remote.php/dav/files/RGPD/";
$nextcloud_username = "RGPD";
$nextcloud_password = "MIDOUMIDOU22";

// Configuration de la base de données
$servername = "localhost";
$db_username = "pmauser";
$db_password = "pma123";
$dbname = "mediconnect_db";

// Créer un dossier de sauvegarde temporaire
$backup_dir = "/tmp/mediconnect_backup_" . date('Y-m-d_H-i-s');
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

echo "🔄 Démarrage de la sauvegarde...\n\n";

// 1. SAUVEGARDE DE LA BASE DE DONNÉES
echo "📊 Sauvegarde de la base de données...\n";
$sql_backup_file = $backup_dir . "/mediconnect_db_" . date('Y-m-d_H-i-s') . ".sql";

// Créer le backup SQL
$mysqldump_cmd = "mysqldump -u {$db_username}";
if (!empty($db_password)) {
    $mysqldump_cmd .= " -p{$db_password}";
}
$mysqldump_cmd .= " {$dbname} > {$sql_backup_file} 2>&1";

exec($mysqldump_cmd, $output, $return_var);

if ($return_var === 0 && file_exists($sql_backup_file)) {
    echo "✅ Base de données sauvegardée : " . basename($sql_backup_file) . "\n";
    echo "📏 Taille : " . round(filesize($sql_backup_file) / 1024, 2) . " KB\n";
} else {
    echo "❌ Erreur lors de la sauvegarde de la base\n";
    echo "Détails : " . implode("\n", $output) . "\n";
}

// 2. SAUVEGARDE DES CV
echo "\n📁 Sauvegarde des CV...\n";
$cv_backup_dir = $backup_dir . "/cv";
if (!file_exists($cv_backup_dir)) {
    mkdir($cv_backup_dir, 0755, true);
}

$cv_source = "/var/www/html/uploads/cv";
if (is_dir($cv_source)) {
    $files = scandir($cv_source);
    $count = 0;
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && is_file($cv_source . '/' . $file)) {
            copy($cv_source . '/' . $file, $cv_backup_dir . '/' . $file);
            $count++;
        }
    }
    echo "✅ {$count} fichiers CV sauvegardés\n";
} else {
    echo "⚠️  Dossier CV non trouvé : {$cv_source}\n";
}

// 3. CRÉER UNE ARCHIVE ZIP
echo "\n📦 Création de l'archive...\n";
$zip_file = "/tmp/mediconnect_backup_" . date('Y-m-d_H-i-s') . ".zip";

$zip = new ZipArchive();
if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
    
    // Ajouter le fichier SQL
    if (file_exists($sql_backup_file)) {
        $zip->addFile($sql_backup_file, 'database/' . basename($sql_backup_file));
    }
    
    // Ajouter les CV
    if (is_dir($cv_backup_dir)) {
        $cv_files = scandir($cv_backup_dir);
        foreach ($cv_files as $file) {
            if ($file != '.' && $file != '..') {
                $zip->addFile($cv_backup_dir . '/' . $file, 'cv/' . $file);
            }
        }
    }
    
    $zip->close();
    echo "✅ Archive créée : " . basename($zip_file) . "\n";
    echo "📏 Taille : " . round(filesize($zip_file) / 1024 / 1024, 2) . " MB\n";
} else {
    die("❌ Erreur lors de la création de l'archive\n");
}

// 4. TESTER LA CONNEXION NEXTCLOUD
echo "\n🔌 Test de connexion à Nextcloud...\n";
$test_ch = curl_init();
curl_setopt($test_ch, CURLOPT_URL, $nextcloud_url);
curl_setopt($test_ch, CURLOPT_USERPWD, $nextcloud_username . ":" . $nextcloud_password);
curl_setopt($test_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($test_ch, CURLOPT_CUSTOMREQUEST, "PROPFIND");
curl_setopt($test_ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($test_ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($test_ch, CURLOPT_TIMEOUT, 10);

$test_response = curl_exec($test_ch);
$test_http_code = curl_getinfo($test_ch, CURLINFO_HTTP_CODE);
curl_close($test_ch);

if ($test_http_code == 207 || $test_http_code == 200) {
    echo "✅ Connexion Nextcloud OK (HTTP {$test_http_code})\n";
} else {
    echo "⚠️  Problème de connexion Nextcloud (HTTP {$test_http_code})\n";
    echo "URL testée : {$nextcloud_url}\n";
}

// 5. CRÉER LE DOSSIER Backups/MEDICONNECT sur Nextcloud
echo "\n📂 Création du dossier sur Nextcloud...\n";
$folder_paths = [
    $nextcloud_url . "Backups/",
    $nextcloud_url . "Backups/MEDICONNECT/"
];

foreach ($folder_paths as $folder_path) {
    $mkdir_ch = curl_init();
    curl_setopt($mkdir_ch, CURLOPT_URL, $folder_path);
    curl_setopt($mkdir_ch, CURLOPT_USERPWD, $nextcloud_username . ":" . $nextcloud_password);
    curl_setopt($mkdir_ch, CURLOPT_CUSTOMREQUEST, "MKCOL");
    curl_setopt($mkdir_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($mkdir_ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($mkdir_ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $mkdir_response = curl_exec($mkdir_ch);
    $mkdir_code = curl_getinfo($mkdir_ch, CURLINFO_HTTP_CODE);
    curl_close($mkdir_ch);
    
    if ($mkdir_code == 201 || $mkdir_code == 405) {
        // 201 = créé, 405 = existe déjà
        echo "✅ Dossier OK : " . basename($folder_path) . "\n";
    }
}

// 6. UPLOAD VERS NEXTCLOUD avec PUT
echo "\n☁️  Upload vers Nextcloud...\n";

$ch = curl_init();
$nextcloud_upload_path = $nextcloud_url . "Backups/MEDICONNECT/" . basename($zip_file);

$file_handle = fopen($zip_file, 'r');

curl_setopt($ch, CURLOPT_URL, $nextcloud_upload_path);
curl_setopt($ch, CURLOPT_USERPWD, $nextcloud_username . ":" . $nextcloud_password);
curl_setopt($ch, CURLOPT_PUT, true);
curl_setopt($ch, CURLOPT_INFILE, $file_handle);
curl_setopt($ch, CURLOPT_INFILESIZE, filesize($zip_file));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout

echo "📤 Upload en cours vers : {$nextcloud_upload_path}\n";

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);

fclose($file_handle);
curl_close($ch);

if ($http_code >= 200 && $http_code < 300) {
    echo "✅ Sauvegarde uploadée sur Nextcloud avec succès ! (HTTP {$http_code})\n";
    echo "📍 Emplacement : Backups/MEDICONNECT/" . basename($zip_file) . "\n";
    
    // Logger dans la base de données
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    if (!$conn->connect_error) {
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (NULL, 'BACKUP', ?, ?)");
        $details = "Sauvegarde Nextcloud réussie: " . basename($zip_file);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $stmt->bind_param("ss", $details, $ip);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
    
} else {
    echo "❌ Erreur lors de l'upload vers Nextcloud\n";
    echo "Code HTTP : {$http_code}\n";
    echo "Erreur cURL : {$curl_error}\n";
    echo "Réponse serveur : {$response}\n";
}

// 7. NETTOYAGE
echo "\n🧹 Nettoyage des fichiers temporaires...\n";

function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return rmdir($dir);
}

deleteDirectory($backup_dir);
if (file_exists($zip_file)) {
    unlink($zip_file);
}

echo "✅ Nettoyage terminé\n";

echo "\n✨ Sauvegarde terminée !\n";
echo "📅 Date : " . date('d/m/Y H:i:s') . "\n";
?>
