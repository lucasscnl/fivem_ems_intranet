<?php
require_once 'config.php';
session_start();

// Vérifier si les données nécessaires sont présentes
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['id']) || !isset($input['active'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

// Sécuriser les données
$userId = (int)$input['id'];
$newActiveStatus = $input['active'] === 'Yes' ? 'Yes' : 'No';

// Mettre à jour la base de données
try {
    $sql = "UPDATE user SET active = :active WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['active' => $newActiveStatus, 'id' => $userId]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
