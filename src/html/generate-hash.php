<?php
// Génération des hash pour les mots de passe

$admin_password = "admin123";
$rh_password = "rh123";

echo "Hash pour admin123 :\n";
echo password_hash($admin_password, PASSWORD_DEFAULT);
echo "\n\n";

echo "Hash pour rh123 :\n";
echo password_hash($rh_password, PASSWORD_DEFAULT);
echo "\n";
?>
