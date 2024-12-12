<?php
// Inclure la configuration pour se connecter à la base de données
require_once 'config.php';

// Démarrer une session
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupérer les données du formulaire
    $username = $_POST['username'];
    $password = $_POST['pass'];

    // Préparer la requête pour récupérer l'utilisateur
    $sql = "SELECT * FROM user WHERE username = :username";
    
    try {
        // Préparer la requête
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        // Récupérer l'utilisateur
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user['active'] === 'Yes') {
            if ($user && password_verify($password, $user['pass'])) {
                // Les informations d'identification sont correctes, on ouvre une session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'name' => $user['name'],
                    'surname' => $user['surname'],
                ];

                echo "<span class='alert success'>Connexion réussie</span>";
                header("Location: index.php");
                exit();
            } else {
                // Informations incorrectes
                echo "<span class='alert error'>Mots de passe ou utilisateur incorrect</span>";
            };
        } else {
            echo "<span class='alert error'>Ce compte n'as pas encore été activé</span>";
        }
    } catch (PDOException $e) {
        echo "Erreur:" . $e->getMessage();
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
    <title>SAMS - Login</title>
</head>
<body>
    <main class="login-page">
        <section class="login-slider">
            <img src="img/Sams.webp" alt="logo">
        </section>  
        <section class="login-container">
            <div class="form-box">
                <h1>Login</h1>
                <form action="login.php" method="post">
					<label for="username">username</label>
					<input type="text" id="username" name="username" required>

					<label for="pass">password</label>
					<input type="password" id="pass" name="pass" required>

					<input class="btn-sub" type="submit" value="Connexion">
				</form>
            </div>
        </section>
    </main>
</body>
</html>