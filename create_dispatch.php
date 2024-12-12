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

// Vérification de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les valeurs du formulaire
    $conducteur = $_POST['conducteur'];
    $equipier1 = $_POST['equipier1'];
    $equipier2 = $_POST['equipier2'];
    $equipier3 = $_POST['equipier3'];
    $vl = $_POST['vl'];
    $statut = $_POST['statut'];

    try {

        $query = "INSERT INTO dispatch (conducteur, equipier1, equipier2, equipier3, vehicule, statut, date, author) 
                  VALUES (:conducteur, :equipier1, :equipier2, :equipier3, :vehicule, :statut, NOW(), :author)";
        
        // Préparer la requête avec les paramètres
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':conducteur', $conducteur);
        $stmt->bindParam(':equipier1', $equipier1);
        $stmt->bindParam(':equipier2', $equipier2);
        $stmt->bindParam(':equipier3', $equipier3);
        $stmt->bindParam(':vehicule', $vl);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':author', $_SESSION['user']['id']);
        
        $stmt->execute();

        // Ajouter une entrée dans la table 'logs'
        $logSql = "INSERT INTO logs (user, module, comment, time) VALUES (:user, :module, :comment, NOW())";
        $logStmt = $pdo->prepare($logSql);
        $logStmt->bindParam(':user', $_SESSION['user']['id'], PDO::PARAM_INT);
        $logStmt->bindParam(':module', $module, PDO::PARAM_STR);
        $logStmt->bindParam(':comment', $comment, PDO::PARAM_STR);

        // Définir les valeurs pour le module et le commentaire
        $module = 'Dispatch';
        $comment = 'Patrouille Crée';

        $logStmt->execute();

        echo "<span class='alert success'>Patrouille crée</span>";
        sleep(2);
        header('Location: dispatch.php');
    } catch (PDOException $e) {
        echo "<span class='alert error'>" . $e->getMessage() . "</span>";
    }
}

    // Récupérer les utilisateurs
    $query = "SELECT id, name, surname FROM user";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        <section class="form-container">
            <h2>Créé la patrouille</h2>
            <form action="" method="POST">
                <section class="form-content">
                    <div class="form-column">
                        <label for="conducteur">Conducteur</label>
                        <select name="conducteur" id="conducteur" required>
                            <option value="N/A">N/A</option>
                            <?php foreach ($users as $user) { ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo $user['name'] . ' ' . $user['surname']; ?></option>
                            <?php } ?>
                        </select>
                        
                        <label for="equipier1">Equipier 1</label>
                        <select name="equipier1" id="equipier1" required>
                            <option value="N/A">N/A</option>
                            <?php foreach ($users as $user) { ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo $user['name'] . ' ' . $user['surname']; ?></option>
                            <?php } ?>
                        </select>
                        
                        <label for="equipier2">Equipier 2</label>
                        <select name="equipier2" id="equipier2" required>
                            <option value="N/A">N/A</option>
                            <?php foreach ($users as $user) { ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo $user['name'] . ' ' . $user['surname']; ?></option>
                            <?php } ?>
                        </select>
                        
                        <label for="equipier3">Equipier 3</label>
                        <select name="equipier3" id="equipier3" required>
                            <option value="N/A">N/A</option>
                            <?php foreach ($users as $user) { ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo $user['name'] . ' ' . $user['surname']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <label for="vl">Véhicule</label>
                        <input type="text" id="vl" name="vl" required>
                        
                        <label for="statut">Statut</label>
                        <select name="statut" id="statut" required>
                            <option value="dispo">Disponible</option>
                            <option value="indispo">Indisponible</option>
                            <option value="inter">En Intervention</option>
                        </select>
                    </div>
                </section>
                <button type="submit" class="btn-sub">Créé</button>
            </form>
        </section>
    </main>
</body>
</html>
