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
    
$message = ""; // Variable pour stocker les messages

$dossier = [];
    try {
        $sql = "SELECT id, name, surname FROM dossiers";
        $stmt = $pdo->query($sql);
        $dossier = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Erreur lors de la récupération des dossiers : ' . $e->getMessage());
    }

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire en les sécurisant
    $identite = htmlspecialchars($_POST['identite']);
    $age = htmlspecialchars($_POST['age']);
    $tel = htmlspecialchars($_POST['phone']);
    $sex = htmlspecialchars($_POST['sex']);
    $price = htmlspecialchars($_POST['price']);
    $raison = htmlspecialchars($_POST['raison']);
    $comment = htmlspecialchars($_POST['com']);
    $medoc = htmlspecialchars($_POST['medoc']);
    $date = date('Y-m-d H:i:s'); // Date actuelle
    $author = $_SESSION['user']['id'] ?? null; // ID de l'auteur (à partir de la session)

    try {
        // Préparer la requête d'insertion
        $sql = "INSERT INTO rapports (identite, age, tel, sex, price, raison, comment, medoc, date, author)
                VALUES (:identite, :age, :tel, :sex, :price, :raison, :comment, :medoc, :date, :author)";
        $stmt = $pdo->prepare($sql);

        // Lier les paramètres
        $stmt->bindParam(':identite', $identite);
        $stmt->bindParam(':age', $age, PDO::PARAM_INT);
        $stmt->bindParam(':tel', $tel);
        $stmt->bindParam(':sex', $sex);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':raison', $raison);
        $stmt->bindParam(':comment', $comment);
        $stmt->bindParam(':medoc', $medoc);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':author', $author, PDO::PARAM_INT);

        // Exécuter la requête
        if ($stmt->execute()) {
            $message = "<div class='success-message'>Le rapport a été ajouté avec succès !</div>";
        } else {
            $message = "<div class='error-message'>Une erreur s'est produite lors de l'ajout du rapport.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='error-message'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
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
    <main class="dashbaord-main">
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
            <h2>Rapport d'intervention</h2>
            <form action="" method="POST">
                <section class="form-content">
                    <div class="form-column">
                        <label for="identite">Identité</label>
                        <select name="identite" id="identite" for="identite">
                            <?php foreach ($dossier as $dossiers): ?>
                                <option value="<?= htmlspecialchars($dossiers['id']); ?>">
                                    <?= htmlspecialchars($dossiers['name'] . " " . $dossiers['surname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="age">Age</label>
                        <input type="number" id="age" name="age" required>
                        <label for="phone">Tel</label>
                        <input type="text" id="phone" name="phone" required>
                        <label for="sex">Sexe</label>
                        <select name="sex" id="sex" required>
                            <option value="H">H</option>
                            <option value="F">F</option>
                        </select>
                        <label for="price">à régler</label>
                        <input type="text" id="price" name="price" required>
                    </div>
                    <div>
                        <label for="raison">Raison(s)</label>
                        <textarea name="raison" id="raison" rows="5" required></textarea>
                        <label for="com">Commentaire(s)</label>
                        <textarea name="com" id="com" rows="5"></textarea>
                        <label for="medoc">Prescription(s)</label>
                        <textarea name="medoc" id="medoc" rows="5"></textarea>
                    </div>
                </section>
                <button type="submit" class="btn-sub">Soumettre</button>
            </form>
        </section>
    </main>
</body>
</html>