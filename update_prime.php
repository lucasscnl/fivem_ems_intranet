<?php
include 'config.php';

try {
    // Vérifier si les données nécessaires existent dans les tables
    $query = "
        UPDATE user u
        INNER JOIN (
            SELECT grade, salary_per_minute FROM grade_salary
        ) gs ON u.grade = gs.grade
        SET u.prime = u.prime + gs.salary_per_minute
        WHERE u.service = 'Yes';
    ";

    // Exécuter la requête
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Mise à jour des primes réussie']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
file_put_contents('log_update_prime.txt', date('Y-m-d H:i:s') . " - Mise à jour exécutée\n", FILE_APPEND);
?>
