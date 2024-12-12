<?php
session_start();
require_once 'config.php';

// Vérifier si l'ID utilisateur est transmis via le formulaire
if (isset($_POST['user_id'])) {
    $userId = (int)$_POST['user_id'];

    try {
        // Vérifier si l'utilisateur existe
        $sql = "SELECT * FROM user WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Initialiser la session comme si l'utilisateur était connecté
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'], // Assurez-vous que les données stockées correspondent aux champs de votre table
            ];

            // Rediriger vers la page de développement ou une page protégée
            header('Location: index.php');
            exit;
        } else {
            echo "Utilisateur non trouvé.";
        }
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
} else {
    echo "ID utilisateur non spécifié.";
}
?>
