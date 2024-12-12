<?php
	session_start();
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

	try {
		$stmt = $pdo->query("SELECT * FROM dossiers");
		$users_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		die("Erreur : " . htmlspecialchars($e->getMessage()));
	}

	// Mettre à jour la session avec les nouvelles données utilisateur
	$_SESSION['user'] = $user;
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
		<?php
			if (isset($_SESSION['error-perm'])) {
				echo "<span class='alert error'> ". htmlspecialchars($_SESSION['error-perm']) ." </span>";
				unset($_SESSION['error-perm']); // Réinitialiser après affichage
			}
		?>
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
            <div class="action-button"><span>Prime cette semaine:</span><span><?php echo $user_prime; ?>$</span></div>
            <div class="action-button <?php echo ($user['service'] === 'Yes') ? 'error' : 'success'; ?>" 
				id="service-button" 
				data-user-id="<?php echo $_SESSION['user']['id']; ?>" 
				data-service-status="<?php echo $user['service']; ?>">
				<a href="javascript:void(0)">
					<?php echo ($user['service'] === 'Yes') ? 'Stopper son service' : 'Prendre son service'; ?>
				</a>
			</div>
			<script src="js/service.js"></script>
        </section>
		
		<section class="effectifs-container">
			<h2>Liste des Dossiers</h2>
			<div class="effectifs-list">
				<?php if (!empty($users_list)) : ?>
					<?php foreach ($users_list as $user) : ?>
							<div class="effectif-box">
								<div class="effectif-id">
									<a href="vue-dossier.php?id=<?= urlencode($user['id']); ?>" class="effectif-box-link"><h3>📁 <?= htmlspecialchars($user['name']) . ' ' . htmlspecialchars($user['surname']); ?></h3></a>
								</div>
							</div>
					<?php endforeach; ?>
				<?php else : ?>
					<p>Aucun Dossiers.</p>
				<?php endif; ?>
			</div>
		</section>

    </main>
</body>
</html>