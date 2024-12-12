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

    // Récupérer les grades disponibles
    
    $grades = [];
    try {
        $sql = "SELECT grade FROM grade_salary";
        $stmt = $pdo->query($sql);
        $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Erreur lors de la récupération des grades : ' . $e->getMessage());
    }

    // Récupérer les informations de l'utilisateur (exemple utilisateur ID = 1)
    $userId = $_GET['id'] ?? 0;
    $user = [];
    try {
        $sql = "SELECT grade, prime, name, surname, service, active FROM user WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Erreur lors de la récupération des données utilisateur : ' . $e->getMessage());
    }

    // Mettre à jour le grade en AJAX
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade'])) {
        $newGrade = $_POST['grade'];
        try {
            $sql = "UPDATE user SET grade = :grade WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['grade' => $newGrade, 'id' => $userId]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }


    // Vérifier que l'ID est valide
    if ($userId > 0) {
        // Requête pour récupérer les logs liés à cet utilisateur
        $query = "SELECT * FROM logs WHERE user = :userId ORDER BY time DESC";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $logs = []; // Pas de logs si l'ID est invalide
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

    <script>
        // Fonction pour mettre à jour le grade
        function updateGrade(newGrade) {
            const userId = <?= $userId; ?>;
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `grade=${encodeURIComponent(newGrade)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Grade mis à jour avec succès.');
                } else {
                    alert('Erreur lors de la mise à jour du grade : ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur réseau.');
            });
        }
    </script>

</head>
<body class="dashboard-container">
    <?php include 'nav.php';?>
    <main class="dashbaord-main">
        <section class="actions-container">
            <div class="action-button"><span>Prime cette semaine:</span><span><?php echo $user_prime; ?>$</span></div>
            <div class="action-button <?php echo ($user['active'] === 'Yes') ? 'success' : 'error'; ?>" 
                id="active-button" 
                data-user-id="<?php echo $userId; ?>" 
                data-active-status="<?php echo $user['active']; ?>">
                <a href="javascript:void(0)">
                    <?php echo ($user['active'] === 'Yes') ? 'Compte Actif' : 'Compte Inactif'; ?>
                </a>
            </div>
            <script src="js/active.js"></script>
			<script src="js/service.js"></script>
        </section>
		
		<section class="profile-container">
            <h2>Profil : <?= htmlspecialchars($user['name'] ?? 'Inconnu'); ?> <?= htmlspecialchars($user['surname'] ?? 'Inconnu'); ?></h2>
            <div class="profile-infos">
                <div class="profile-box">
                    <span>Grade:</span>
                    <select id="grade-selector" onchange="updateGrade(this.value)">
                        <?php foreach ($grades as $grade): ?>
                            <option value="<?= htmlspecialchars($grade['grade']); ?>"
                                <?= ($grade['grade'] === ($user['grade'] ?? '')) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($grade['grade']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="profile-box">
                    <span>Prime:</span>
                    <span class="infos-valeurs"><?= htmlspecialchars($user['prime'] ?? '0'); ?> $</span>
                </div>
                <div class="profile-box">
                    <span>Statut:</span>
                    <span class="infos-valeurs <?= $user['service'] === 'Yes' ? 'in-service' : 'out-of-service'; ?>"><?= $user['service'] === 'Yes' ? 'En service' : 'Pas en service'; ?></span>
                </div>
            </div>
            <div class="profile-logs">
                <h2>LOGS</h2>
                <table class="logs-table">
                    <thead class="head">
                        <tr>
                            <th>Time</th>
                            <th>Module</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)) { ?>
                            <?php foreach ($logs as $log) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['time']); ?></td>
                                    <td><?php echo htmlspecialchars($log['module']); ?></td>
                                    <td><?php echo htmlspecialchars($log['comment']); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="3">Aucun log disponible pour cet utilisateur.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
			</div>
		</section>

    </main>
</body>
</html>