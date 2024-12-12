<?php
	session_start();
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
	header("Expires: 0");
	include 'config.php';

	// error_reporting(E_ALL);
	// ini_set('display_errors', 1);

	// // Debug : Affichage du contenu de la session
	// var_dump($_SESSION);

	// Vérifier si une session est ouverte et que l'utilisateur est authentifié
	if (!isset($_SESSION['user'])) {
		$_SESSION['message'] = "Vous devez être connecté pour accéder à cette page.";
		$_SESSION['message_type'] = "error";
		header('Location: login.php'); // Rediriger vers la page de connexion si non connecté
		exit;
	}

	// Récupérer les informations de l'utilisateur connecté
	$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

	// Récupérer le nombre d'agents en service (service = Yes)
	$sql_agents_service = "SELECT COUNT(*) FROM user WHERE service = 'Yes'";
	$stmt_agents_service = $pdo->prepare($sql_agents_service);
	$stmt_agents_service->execute();
	$agents_service_count = $stmt_agents_service->fetchColumn();

	// Récupérer le nombre total d'employés
	$sql_total_employees = "SELECT COUNT(*) FROM user";
	$stmt_total_employees = $pdo->prepare($sql_total_employees);
	$stmt_total_employees->execute();
	$total_employees_count = $stmt_total_employees->fetchColumn();

	// Récupérer prime
	$sql_prime = "SELECT prime FROM user WHERE id = :id";
	$stmt_prime = $pdo->prepare($sql_prime);
	$stmt_prime->bindParam(':id', $_SESSION['user']['id'], PDO::PARAM_INT);
	$stmt_prime->execute();
	$user_prime = $stmt_prime->fetchColumn();

	// Récupérer prime
	$sql_grade = "SELECT grade FROM user WHERE id = :id";
	$stmt_grade = $pdo->prepare($sql_grade);
	$stmt_grade->bindParam(':id', $_SESSION['user']['id'], PDO::PARAM_INT);
	$stmt_grade->execute();
	$user_grade = $stmt_grade->fetchColumn();

	// Récupérer les informations mises à jour de l'utilisateur connecté
	$sql_user = "SELECT * FROM user WHERE id = :id";
	$stmt_user = $pdo->prepare($sql_user);
	$stmt_user->bindParam(':id', $_SESSION['user']['id'], PDO::PARAM_INT);
	$stmt_user->execute();
	$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

	// Mettre à jour la session avec les nouvelles données utilisateur
	$_SESSION['user'] = $user;

// Vérification de l'authentification
if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = "Vous devez être connecté pour accéder à cette page.";
    $_SESSION['message_type'] = "error";
    header('Location: login.php');
    exit;
}

// Vérification de l'ID de la patrouille à supprimer
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "ID de patrouille invalide.";
    $_SESSION['message_type'] = "error";
    header('Location: dispatch.php');
    exit;
}

$dispatch_id = intval($_GET['id']);

// Récupérer les informations de la patrouille pour confirmation
try {
    $stmt = $pdo->prepare("SELECT * FROM dispatch WHERE id = :id");
    $stmt->bindParam(':id', $dispatch_id, PDO::PARAM_INT);
    $stmt->execute();
    $dispatch = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dispatch) {
        $_SESSION['message'] = "Patrouille introuvable.";
        $_SESSION['message_type'] = "error";
        header('Location: dispatch.php');
        exit;
    }
} catch (PDOException $e) {
    die("Erreur : " . htmlspecialchars($e->getMessage()));
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt_delete = $pdo->prepare("DELETE FROM dispatch WHERE id = :id");
        $stmt_delete->bindParam(':id', $dispatch_id, PDO::PARAM_INT);
        $stmt_delete->execute();

        // Ajouter une entrée dans la table 'logs'
        $logSql = "INSERT INTO logs (user, module, comment, time) VALUES (:user, :module, :comment, NOW())";
        $logStmt = $pdo->prepare($logSql);
        $logStmt->bindParam(':user', $_SESSION['user']['id'], PDO::PARAM_INT);
        $logStmt->bindParam(':module', $module, PDO::PARAM_STR);
        $logStmt->bindParam(':comment', $comment, PDO::PARAM_STR);

        // Définir les valeurs pour le module et le commentaire
        $module = 'Dispatch';
        $comment = 'Patrouille ' . $dispatch_id . ' suprimée';

        $logStmt->execute();

        $_SESSION['message'] = "Patrouille supprimée avec succès.";
        $_SESSION['message_type'] = "success";
        header('Location: dispatch.php');
        exit;
    } catch (PDOException $e) {
        die("Erreur : " . htmlspecialchars($e->getMessage()));
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
    <title>SAMS - PANEL</title>
</head>
<body class="dashboard-container">
    <?php include 'nav.php';?>
    <main class="dashboard-main">
    <section class="infos-container">
            <div class="infos-box">
                <h1>Agents en <br>services</h1>
                <span><?php echo $agents_service_count; ?></span>
            </div>
            <div class="infos-box">
                <h1>Employés <br>total</h1>
                <span><?php echo $total_employees_count; ?></span>
            </div>
            <div class="infos-box" style="background-color: var(--lightblue);">
                <h1><?php 
						if ($user) {
							echo htmlspecialchars($user['name']) . ' ' . htmlspecialchars($user['surname']);
						} else {
							echo 'Utilisateur inconnu';
						}
					?>
				</h1>    
                <span>
					<?php echo $user_grade; ?>
				</span>
            </div>
        </section>
        <section class="actions-container">
            <div class="action-button">
                <span>Prime cette semaine :</span>
                <span><?php echo htmlspecialchars($user_prime); ?>$</span>
            </div>
            <div class="action-button <?php echo ($user['service'] === 'Yes') ? 'error' : 'success'; ?>" 
                 id="service-button" 
                 data-user-id="<?php echo $_SESSION['user']['id']; ?>" 
                 data-service-status="<?php echo $user['service']; ?>">
                <a href="javascript:void(0)">
                    <?php echo ($user['service'] === 'Yes') ? 'Stopper son service' : 'Prendre son service'; ?>
                </a>
            </div>
            <script src="js/service.js"></script>
            <div class="action-button success">
                <a href="create_dispatch.php">Créer une patrouille</a>
            </div>
        </section>
            <section class="dispatch-container">
                <h2>Êtes-vous sûr de vouloir supprimer cette patrouille ?</h2>
                <div class="dispatch-box">
                    <div class="patrol-infos">
                        <p><strong>Conducteur :</strong> <?= htmlspecialchars($dispatch['conducteur']); ?></p>
                        <p><strong>Véhicule :</strong> <?= htmlspecialchars($dispatch['vehicule']); ?></p>
                        <p><strong>Statut :</strong> <?= htmlspecialchars($dispatch['statut']); ?></p>
                    </div>
                    <div class="patrol-actions">
                        <form action="" method="POST">
                            <button type="submit" class="btn-sub" >Supprimer</button><br>
                            <a href="dispatch.php" class="btn-sub">Annuler</a>
                        </form>
                    </div>
                </div>
            </section>
    </main>
</body>
</html>


