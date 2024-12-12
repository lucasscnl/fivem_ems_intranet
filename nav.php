<aside>
		<div>
            <h2></h2>
            <a href="index.php">Retour Accueil</a>
        </div>
        <div>
            <h2>Employés</h2>
            <a href="dossier.php">Dossiers patiens</a>
			<a href="rapport.php">Rapport d'intervention</a>
            <a href="dossier-list.php">Base de données patiens</a>
            <a href="dispatch.php">Dispatch</a>
            <a href="effectifs.php">Effectifs</a>
            <a href="">Guide</a>
        </div>
        <div>
            <h2>Administratif</h2>
            <a href="register.php">Recruter</a>
            <!-- <a href="">Convoquer</a>
            <a href="">Liencier</a> -->
        </div>
        <?php
			if ($_SESSION['user']['grade'] == 'D' || $_SESSION['user']['grade'] == 'DA' || $_SESSION['user']['grade'] == 'CS') {
				echo "<div>
						<h2>Dev Tools</h2>
						<a href='dev.php'>Dev Connect</a>
					</div>";
			}
			?>
		<div>
            <h2>Autres</h2>
            <a href="logout.php">Se déconnecter</a>
        </div>
    </aside>