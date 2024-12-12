<?php
session_start();
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include 'config.php';

// Vérification de l'authentification
if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = "Vous devez être connecté pour accéder à cette page.";
    $_SESSION['message_type'] = "error";
    header('Location: login.php');
    exit;
}

// Connexion utilisateur
try {
    // Récupérer les données de dispatch
    $stmt = $pdo->query("SELECT * FROM dispatch");
    $dispatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agents en service
    $stmt_agents_service = $pdo->prepare("SELECT COUNT(*) FROM user WHERE service = 'Yes'");
    $stmt_agents_service->execute();
    $agents_service_count = $stmt_agents_service->fetchColumn();

    // Total des patrouilles
    $stmt_total_patrol = $pdo->prepare("SELECT COUNT(*) FROM dispatch");
    $stmt_total_patrol->execute();
    $total_patrol_count = $stmt_total_patrol->fetchColumn();

    // Récupération des primes et grades utilisateur
    $stmt_prime_grade = $pdo->prepare("SELECT prime, grade FROM user WHERE id = :id");
    $stmt_prime_grade->bindParam(':id', $_SESSION['user']['id'], PDO::PARAM_INT);
    $stmt_prime_grade->execute();
    $user_data = $stmt_prime_grade->fetch(PDO::FETCH_ASSOC);

    $user_prime = $user_data['prime'] ?? 0;
    $user_grade = $user_data['grade'] ?? "Non spécifié";

    // Récupérer les données mises à jour de l'utilisateur
    $stmt_user = $pdo->prepare("SELECT * FROM user WHERE id = :id");
    $stmt_user->bindParam(':id', $_SESSION['user']['id'], PDO::PARAM_INT);
    $stmt_user->execute();
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    $_SESSION['user'] = $user;
} catch (PDOException $e) {
    die("Erreur : " . htmlspecialchars($e->getMessage()));
}

$query = "SELECT d.*, u1.name AS conducteur_name, u1.surname AS conducteur_surname, 
                 u2.name AS equipier1_name, u2.surname AS equipier1_surname, 
                 u3.name AS equipier2_name, u3.surname AS equipier2_surname, 
                 u4.name AS equipier3_name, u4.surname AS equipier3_surname
          FROM dispatch d
          LEFT JOIN user u1 ON d.conducteur = u1.id
          LEFT JOIN user u2 ON d.equipier1 = u2.id
          LEFT JOIN user u3 ON d.equipier2 = u3.id
          LEFT JOIN user u4 ON d.equipier3 = u4.id";
$statement = $pdo->query($query);
$dispatches = $statement->fetchAll(PDO::FETCH_ASSOC);

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
                <h1>Nombre de <br>patrouilles</h1>
                <span><?php echo $total_patrol_count; ?></span>
            </div>
            <div class="infos-box" style="background-color: var(--lightblue);">
                <h1><?php 
                    echo htmlspecialchars($user['name'] . ' ' . $user['surname']) ?? 'Utilisateur inconnu';
                ?></h1>
                <span><?php echo htmlspecialchars($user_grade); ?></span>
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
            <?php if (!empty($dispatches)): ?>
                <?php foreach ($dispatches as $dispatch): ?>
                    <div class="dispatch-box <?= htmlspecialchars($dispatch['statut']) ?>">
                        <div class="patrol-infos">
                            <span><strong>Conducteur :</strong> <?= htmlspecialchars($dispatch['conducteur_name']) . ' ' . htmlspecialchars($dispatch['conducteur_surname']) ?: 'X' ?></span>
                            <span><strong>Équipier 1 :</strong> <?= htmlspecialchars($dispatch['equipier1_name']) . ' ' . htmlspecialchars($dispatch['equipier1_surname']) ?: 'X' ?></span>
                            <span><strong>Équipier 2 :</strong> <?= htmlspecialchars($dispatch['equipier2_name']) . ' ' . htmlspecialchars($dispatch['equipier2_surname']) ?: 'X' ?></span>
                            <span><strong>Équipier 3 :</strong> <?= htmlspecialchars($dispatch['equipier3_name']) . ' ' . htmlspecialchars($dispatch['equipier3_surname']) ?: 'X' ?></span>
                            <span><strong>Véhicule :</strong> <?= htmlspecialchars($dispatch['vehicule']) ?: 'Non spécifié' ?></span>
                        </div>
                        <div class="patrol-actions">
                            <button onclick="window.location.href='modif_dispatch.php?id=<?= $dispatch['id'] ?>'">Modifier</button>
                            <button onclick="deleteDispatch(<?= $dispatch['id'] ?>)">Supprimer</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucunes patrouilles recensées</p>
            <?php endif; ?>
        </section>
        <script>
            function deleteDispatch(id) {
                    window.location.href = `delete_dispatch.php?id=${id}`;
            }
        </script>
    </main>
</body>
</html>
y