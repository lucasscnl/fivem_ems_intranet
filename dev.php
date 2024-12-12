<?php
session_start();

// Récupérer les informations de l'utilisateur connecté
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// Vérification de permissions
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$authorized_grades = ['D', 'DA', 'CS']; // Liste des grades autorisés
$user_grade = $user['grade'];
$_SESSION['error-perm'] = 'Bah? Tu ne peut pas faire cela!! Petit malin que tu es haha!';

// Vérification
if (!in_array($user_grade, $authorized_grades)) {
    header("Location: index.php");
    exit();
}

session_destroy();
session_start();
require_once 'config.php'; // Assurez-vous que la connexion à la base de données est incluse

// Récupérer toutes les sessions disponibles dans la table users
try {
    $sql = "SELECT id, username FROM user"; // Modifiez les champs si nécessaire
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta property="og:title" content="SAMS - Intranet - Flashland FA" />
	<meta property="og:description" content="Intranet en ligne du San Andras Medical Service du serveur Flashland." />
	<meta property="og:image" content="https://sams.verenium.be" />
	<meta property="og:url" content="https://sams.verenium.be/index.php" />
	<meta property="og:type" content="website" />
	<meta property="og:site_name" content="SAMS - Intranet" />
	<meta property="og:locale" content="fr_FR" />
	<meta property="og:author" content="Lucass" />
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/Sams.webp" type="image/x-icon">
    <title>SAMS - PANEL</title>
</head>
<body>
<main class="login-page">
        <section class="login-slider">
            <img src="img/Sams.webp" alt="logo">
        </section>  
        <section class="login-container">
            <div class="form-box">
                <h1>Dev Connect</h1>
                <form action="dev_connect.php" method="post">
                    <label for="user">Sélectionnez un utilisateur :</label>
                    <select name="user_id" id="user">
                        <?php foreach ($users as $user): ?>
                            <option value="<?= htmlspecialchars($user['id']) ?>"><?= htmlspecialchars($user['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
					<input class="btn-sub" type="submit" value="Connexion">
				</form>
            </div>
        </section>
    </main>
</body>
</html>
