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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupérer les données du formulaire
        $name = trim($_POST['name']);
        $surname = trim($_POST['surname']);
        $age = trim($_POST['age']);
        $phone = trim($_POST['phone']);
        $adress = trim($_POST['adress']);
        $sex = $_POST['sex'];
        $sang = $_POST['sang'];
    
        // Vérifier si les champs obligatoires sont remplis
        if (empty($name) || empty($surname)) {
            $_SESSION['error'] = 'Le Nom et le Prénom est obligatoire';
        } else {
            // Vérifier si le nom et prénom existent déjà
            $sql = "SELECT COUNT(*) FROM dossiers WHERE name = :name AND surname = :surname";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['name' => $name, 'surname' => $surname]);
            $exists = $stmt->fetchColumn();
    
            if ($exists) {
                $_SESSION['error'] = 'Le dossier existe déjà';
                header('Location: dossier.php');
            } else {
                // Insérer les données si elles n'existent pas
                $sql = "INSERT INTO dossiers (name, surname, age, tel, adress, sex, sang) 
                        VALUES (:name, :surname, :age, :tel, :adress, :sex, :sang)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'name' => $name,
                    'surname' => $surname,
                    'age' => $age,
                    'tel' => $phone,
                    'adress' => $adress,
                    'sex' => $sex,
                    'sang' => $sang
                ]);
                $_SESSION['success'] = 'Dossier enregistré';
                header('Location: dossier.php');
            }
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
    <?php
        if (isset($_SESSION['error'])) {
            echo "<span class='alert error'> ". htmlspecialchars($_SESSION['error']) ." </span>";
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo "<span class='alert success'> ". htmlspecialchars($_SESSION['success']) ." </span>";
            unset($_SESSION['success']);
        }
    ?>
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
            <h2>Nouveau dossier</h2>
            <form action="" method="POST">
                <section class="form-content">
                    <div class="form-column">
                        <label for="name">Nom</label>
                        <input type="text" id="name" name="name">
                        <label for="surname">Prénom</label>
                        <input type="text" id="surname" name="surname">
                        <label for="age">Age</label>
                        <input type="text" id="age" name="age">
                        <label for="phone">Tel</label>
                        <input type="text" id="phone" name="phone">
                        <label for="adress">Adresse</label>
                        <input type="text" id="adress" name="adress">
                    </div>
                    <div class="form-content">
                        <label for="sex">Sexe</label>
                        <select name="sex" id="sex">
                            <option value="H">H</option>
                            <option value="F">F</option>
                            <option value="NB">N-B</option>
                        </select>
                        <label for="sang">Groupe sanguin</label>
                        <select name="sang" id="sang">
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                </section>
                <button type="submit" class="btn-sub">Soumettre</button>
            </form>
        </section>
    </main>
</body>
</html>