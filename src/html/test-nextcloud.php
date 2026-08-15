<?php
echo "<h1>Test connexion Nextcloud LOCAL</h1>";

// TES paramètres Nextcloud
$nextcloud_url = "http://172.20.10.6/nextcloud/remote.php/dav/files/RGPD/";
$nextcloud_username = "RGPD";
$nextcloud_password = "MIDOUMIDOU22";

echo "<p><strong>URL testée :</strong> $nextcloud_url</p>";

// Test avec curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $nextcloud_url);
curl_setopt($ch, CURLOPT_USERPWD, $nextcloud_username . ":" . $nextcloud_password);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPFIND');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Depth: 1']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "<p><strong>Code HTTP :</strong> $http_code</p>";

if ($http_code >= 200 && $http_code < 300) {
    echo "<p style='color:green; font-size:20px;'>✅ CONNEXION RÉUSSIE à Nextcloud !</p>";
    echo "<p>Tu peux maintenant utiliser le backup automatique.</p>";
} else {
    echo "<p style='color:red; font-size:20px;'>❌ ERREUR de connexion</p>";
    echo "<pre>Réponse : " . htmlspecialchars($response) . "</pre>";
    
    echo "<h3>Solutions possibles :</h3>";
    echo "<ul>";
    echo "<li>Vérifie que Nextcloud est bien accessible sur <a href='http://10.30.176.50'>http://10.30.176.50</a></li>";
    echo "<li>Vérifie le username et password</li>";
    echo "<li>Assure-toi que le dossier Backups/MEDICONNECT existe dans Nextcloud</li>";
    echo "</ul>";
}

curl_close($ch);
?>
