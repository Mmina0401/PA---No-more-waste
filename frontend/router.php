<?php

register_shutdown_function(function () {
    $erreur = error_get_last();
    $typesGraves = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];

    if ($erreur && in_array($erreur["type"], $typesGraves)) {
        http_response_code(500);
        afficherPageErreur(500, "Erreur interne", "Une erreur inattendue est survenue. Réessayez plus tard.");
    }
});

$cheminDemande = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
$cheminSurDisque = __DIR__ . $cheminDemande;

// Sert directement le fichier s'il existe
if ($cheminDemande !== "/" && file_exists($cheminSurDisque) && !is_dir($cheminSurDisque)) {
    return false;
}

// Cherche un index.php dans le dossier
$dossierSansSlashFinal = rtrim($cheminSurDisque, "/");
if (is_dir($dossierSansSlashFinal) && file_exists($dossierSansSlashFinal . "/index.php")) {
    include $dossierSansSlashFinal . "/index.php";
    return true;
}

// Ajoute .php si besoin
if (file_exists($cheminSurDisque . ".php")) {
    include $cheminSurDisque . ".php";
    return true;
}

if ($cheminDemande === "/" && file_exists(__DIR__ . "/index.php")) {
    return false;
}

// Affiche une erreur 404 si rien n'existe
http_response_code(404);
afficherPageErreur(404, "Page introuvable", "Cette page n'existe pas ou plus.");
return true;

function afficherPageErreur($code, $titre, $message)
{
    echo "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'>";
    echo "<title>Erreur $code — No More Waste</title>";
    echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
    echo "</head><body class='bg-light'>";
    echo "<div class='d-flex align-items-center justify-content-center' style='min-height:100vh;'>";
    echo "<div class='text-center'>";
    echo "<h1 class='display-1 text-success'>$code</h1>";
    echo "<h3>" . htmlspecialchars($titre) . "</h3>";
    echo "<p class='text-muted'>" . htmlspecialchars($message) . "</p>";
    echo "<a href='/login.php' class='btn btn-success'>Retour à l'accueil</a>";
    echo "</div></div></body></html>";
}
