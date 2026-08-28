<?php

$hoteMySQL        = "127.0.0.1";
$portMySQL        = 3306;
$utilisateurMySQL = "root";
$motDePasseMySQL  = "root";

$fichiersSQL = [
    "database/01_create_database.sql",
    "database/02_create_tables.sql",
    "database/03_insert_data.sql",
    "database/04_alter_utilisateur_commercant.sql",
    "database/05_ajout_role_visiteur.sql",
    "database/06_benevoles.sql",
    "database/07_offres_benevoles.sql",
    "database/08_refonte_offres_collectes.sql",
    "database/09_logique_adherents_services.sql",
];

function ouvrirConnexionMySQL($hote, $port, $utilisateur, $motDePasse)
{
    $connexion = mysqli_connect($hote, $utilisateur, $motDePasse, "", $port);

    if (!$connexion) {
        echo "Impossible de se connecter à MySQL : " . mysqli_connect_error() . "\n";
        exit(1);
    }

    return $connexion;
}

function executerFichierSQL($connexion, $cheminFichier)
{
    if (!file_exists($cheminFichier)) {
        echo "  Fichier introuvable, ignoré : $cheminFichier\n";
        return;
    }

    $contenuSQL = file_get_contents($cheminFichier);

    if (!mysqli_multi_query($connexion, $contenuSQL)) {
        echo "  Erreur pendant l'exécution de $cheminFichier :\n";
        echo "  " . mysqli_error($connexion) . "\n";
        exit(1);
    }

    // Vide les résultats avant de passer au fichier suivant
    do {
        mysqli_store_result($connexion);
    } while (mysqli_more_results($connexion) && mysqli_next_result($connexion));

    echo "  OK : $cheminFichier\n";
}

echo "=== Installation de la base No More Waste ===\n\n";

$connexion = ouvrirConnexionMySQL(
    $hoteMySQL,
    $portMySQL,
    $utilisateurMySQL,
    $motDePasseMySQL
);

foreach ($fichiersSQL as $fichier) {
    executerFichierSQL($connexion, $fichier);
}

mysqli_close($connexion);

echo "\nBase de données installée avec succès.\n";
echo "\nProchaines étapes :\n";
echo "  1. cd backend\n";
echo "  2. go mod tidy\n";
echo "  3. go run .\n";
echo "  4. Dans un autre terminal : cd frontend puis php -S localhost:8000\n";
