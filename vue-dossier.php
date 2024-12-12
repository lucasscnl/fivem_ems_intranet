<?php
	session_start();
	include 'config.php';

	// Vérifier si une session est ouverte et que l'utilisateur est authentifié
	if (!isset($_SESSION['user'])) {
		$_SESSION['message'] = "Vous devez être connecté pour accéder à cette page.";
		$_SESSION['message_type'] = "error";
		header('Location: login.php'); // Rediriger vers la page de connexion si non connecté
		exit;
	}

	// Récupérer les informations de l'utilisateur connecté
	$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

    // Vérification de permissions
    $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
    $authorized_grades = ['DRS', 'CC', 'RS', 'MC', 'C', 'CS', 'D', 'DA']; // Liste des grades autorisés
    $user_grade = $user['grade'];
    // Vérification
    if (!in_array($user_grade, $authorized_grades)) {
        $_SESSION['error-perm'] = 'Permission manquantes ;-)';
        header("Location: effectifs.php");
        exit();
    }

	// Récupérer prime
	$sql_prime = "SELECT prime FROM user WHERE id = :id";
	$stmt_prime = $pdo->prepare($sql_prime);
	$stmt_prime->bindParam(':id', $_SESSION['user']['id'], PDO::PARAM_INT);
	$stmt_prime->execute();
	$user_prime = $stmt_prime->fetchColumn();

	try {
		$stmt = $pdo->query("SELECT name, surname, grade, service, active FROM user");
		$users_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		die("Erreur : " . htmlspecialchars($e->getMessage()));
	}

	// Mettre à jour la session avec les nouvelles données utilisateur
	$_SESSION['user'] = $user;

    // Récupérer les informations de l'utilisateur (exemple utilisateur ID = 1)
    $userId = $_GET['id'] ?? 0;
    try {
        $sql = "SELECT * FROM dossiers WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $dataUser = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Erreur lors de la récupération des données dossier utilisateur : ' . $e->getMessage());
    }
    try {
        $sql = "SELECT * FROM rapports WHERE identite = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $rapportUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Erreur lors de la récupération des données rapports utilisateur : ' . $e->getMessage());
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
    <title>SAMS - PANEL</title>

</head>
<body class="dashboard-container">
    <?php include 'nav.php';?>
    <main class="dashbaord-main">
        <section class="actions-container">
            <div class="action-button"><span>Prime cette semaine:</span><span><?php echo $user_prime; ?>$</span></div>
            <div class="action-button error"><span><a href="dossier-list.php">Retour</a></span></div>
        </section>
		
		<section class="profile-container">
            <h2>📁 <?= htmlspecialchars($dataUser['name'] . " " . $dataUser['surname']); ?></h2>
            <div class="profile-infos">
                <div class="profile-box">
                    <span>Sexe:</span>
                    <span class="infos-valeurs"><?= htmlspecialchars($dataUser['sex']); ?></span>
                </div>
                <div class="profile-box">
                    <span>Age:</span>
                    <span class="infos-valeurs"><?= htmlspecialchars($dataUser['age']); ?></span>
                </div>
                <div class="profile-box">
                    <span>Tel:</span>
                    <span class="infos-valeurs"><?= htmlspecialchars($dataUser['tel']); ?></span>
                </div>
                <div class="profile-box">
                    <span>Sang:</span>
                    <span class="infos-valeurs"><?= htmlspecialchars($dataUser['sang']); ?></span>
                </div>
            </div>
            <h2 style="color: var(--lightblue);">Interventions</h2>
            <div class="inters-container">
            <?php if (!empty($rapportUser)) : ?>
                <?php foreach ($rapportUser as $rapport) : ?>
                <div class="inter-line">
                    <div class="inter-item">
                        <span class="infos-valeurs"><?= htmlspecialchars($rapport['date']); ?></span>
                    </div>
                    <div class="inter-item">
                        <span class="infos-valeurs"><?= htmlspecialchars($rapport['raison']); ?></span>
                    </div>
                    <div class="inter-item">
                        <span class="infos-valeurs"><?= htmlspecialchars($rapport['comment']); ?></span>
                    </div>
                    <div class="inter-item">
                        <span class="infos-valeurs"><?= htmlspecialchars($rapport['medoc']); ?></span>
                    </div>
                    <div class="inter-item">
                        <span class="infos-valeurs"><?= htmlspecialchars($rapport['author']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php else : ?>
                <p>Aucun antécédents.</p>
            <?php endif; ?>
            </div>
		</section>

    </main>
</body>
</html>