<?php
session_start();
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
include 'config.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = "Vous devez être connecté pour accéder à cette page.";
    $_SESSION['message_type'] = "error";
    header('Location: login.php'); // Rediriger vers la page de connexion si non connecté
    exit;
}

// Vérification de permissions
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$authorized_grades = ['DRS', 'CC', 'RS', 'MC', 'C', 'CS', 'D', 'DA']; // Liste des grades autorisés
$user_grade = $user['grade'];

// Vérification
if (!in_array($user_grade, $authorized_grades)) {
    header("Location: index.php");
    exit();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupérer les données du formulaire
    $name = trim($_POST['name']); // Prénom
    $surname = trim($_POST['surname']); // Nom

    // Validation des données d'entrée
    if (empty($name) || empty($surname)) {
        echo "Le prénom et le nom sont obligatoires.";
        exit();
    }

    // Générer le username au format prénom.nom
    $username = strtolower($name) . '.' . ucfirst($surname);

    // Générer le mot de passe au format PrenoMnoM!$
    $password = ucfirst($name) . strtolower($surname) . ucfirst($name[0]) . strtolower($surname[0]) . '!$';

    // Stocker le mot de passe généré dans la session
    $_SESSION['generated_password'] = $password;

    // Hashage du mot de passe pour stockage
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $grade = 'IPA'; // Grade par défaut
    $prime = '0'; // Prime par défaut
    $service = 'No'; // Statut de service par défaut

    // Préparer la requête SQL d'insertion
    $sql = "INSERT INTO user (username, name, surname, pass, grade, prime, service) 
            VALUES (:username, :name, :surname, :pass, :grade, :prime, :service)";
    
    try {
        // Préparer la requête
        $stmt = $pdo->prepare($sql);

        // Lier les valeurs
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':surname', $surname);
        $stmt->bindParam(':pass', $hashed_password);
        $stmt->bindParam(':grade', $grade);
        $stmt->bindParam(':prime', $prime);
        $stmt->bindParam(':service', $service);

        // Exécuter la requête
        $stmt->execute();

        // Ajouter une entrée dans la table 'logs'
        $logSql = "INSERT INTO logs (user, module, comment, time) VALUES (:user, :module, :comment, NOW())";
        $logStmt = $pdo->prepare($logSql);
        $logStmt->bindParam(':user', $_SESSION['user']['id'], PDO::PARAM_INT);
        $logStmt->bindParam(':module', $module, PDO::PARAM_STR);
        $logStmt->bindParam(':comment', $comment, PDO::PARAM_STR);

        // Définir les valeurs pour le module et le commentaire
        $module = 'Recrutement';
        $comment = $_SESSION['user']['id'] . ' à créé le profil de: ' . $name . ' ' . $surname . ' avec le mdp: ' . $password ;

        $logStmt->execute();

        // Rediriger vers index.php après l'enregistrement
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        // Gérer les erreurs
        echo "Erreur : " . $e->getMessage();
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/Sams.webp" type="image/x-icon">
    <title>SAMS - Recrutement</title>
</head>
<body>
    <main class="login-page">
        <section class="login-slider">
            <img src="img/Sams.webp" alt="logo">
        </section>  
        <section class="login-container">
            <div class="form-box">
                <h1>Recruter</h1>
                <form action="register.php" method="post">
					<!-- <label for="username">username</label>
					<input type="text" id="username" name="username" required> -->

					<label for="name">name</label>
					<input type="text" id="name" name="name" required>

					<label for="surname">surname</label>
					<input type="text" id="surname" name="surname" required>

					<!-- <label for="pass">password</label>
					<input type="password" id="pass" name="pass" required> -->

					<input class="btn-sub" type="submit" value="Créé le profil">
				</form>
            </div>
        </section>
    </main>
</body>
</html>