<?php
require_once 'config.php';
session_start();

// Vérifier si les données nécessaires sont présentes
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['id']) || !isset($input['service'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

// Sécuriser les données
$userId = (int)$input['id'];
$newServiceStatus = $input['service'] === 'Yes' ? 'Yes' : 'No';

// Vérifier si l'utilisateur connecté correspond à l'utilisateur modifié
if ($userId !== $_SESSION['user']['id']) {
    echo json_encode(['success' => false, 'message' => 'Action non autorisée.']);
    exit;
}

try {
    // Mettre à jour la colonne 'service'
    $sql = "UPDATE user SET service = :service WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':service', $newServiceStatus, PDO::PARAM_STR);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    // Ajouter une entrée dans la table 'logs'
    $logSql = "INSERT INTO logs (user, module, comment, time) VALUES (:user, :module, :comment, NOW())";
    $logStmt = $pdo->prepare($logSql);
    $logStmt->bindParam(':user', $userId, PDO::PARAM_INT);
    $logStmt->bindParam(':module', $module, PDO::PARAM_STR);
    $logStmt->bindParam(':comment', $comment, PDO::PARAM_STR);

    // Définir les valeurs pour le module et le commentaire
    $module = 'Service';
    $comment = $newServiceStatus === 'Yes' ? 'Prise de service' : 'Fin de service';

    $logStmt->execute();

    // Réponse JSON
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Afficher l'erreur avec des détails
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la mise à jour ou de l\'enregistrement du log : ' . $e->getMessage()
    ]);
}
