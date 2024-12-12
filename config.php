<?php
// Configuration de la base de données
$host = 'localhost'; // Nom d'hôte (souvent 'localhost' en local)
$dbname = 'sams_intra'; // Nom de la base de données
$username = 'rootsams'; // Nom d'utilisateur de la base de données
$password = 'ScANluc11!'; // Mot de passe de la base de données

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>