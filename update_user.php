<?php
require_once 'config.php';
session_start();

// Vérifier si les données nécessaires sont présentes
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['id'], $input['field'], $input['value'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

// Récupérer les données
$userId = (int)$input['id'];
$field = $input['field'];
$value = $input['value'];

// Valider le champ à mettre à jour
$validFields = ['grade', 'prime', 'service', 'active'];
if (!in_array($field, $validFields)) {
    echo json_encode(['success' => false, 'message' => 'Champ non valide.']);
    exit;
}

try {
    // Mettre à jour la base de données
    $sql = "UPDATE user SET $field = :value WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':value', $value, PDO::PARAM_STR);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
