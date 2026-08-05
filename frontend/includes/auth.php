<?php
// À inclure (toujours après session_start()) en haut de toute page qui doit
// être protégée. Ensuite, appelle exigerConnexion() ou exigerRole(...) juste
// après l'avoir inclus, sinon il ne se passe rien.

function exigerConnexion()
{
    if (!isset($_SESSION["utilisateur"]) || !isset($_SESSION["jeton"])) {
        header("Location: /login.php");
        exit;
    }
}

function exigerRole(...$rolesAutorises)
{
    exigerConnexion();

    if (!in_array($_SESSION["utilisateur"]["role"], $rolesAutorises)) {
        http_response_code(403);
        echo "<div style='padding:40px;font-family:sans-serif;'>";
        echo "Accès refusé : cette page nécessite le rôle " . implode(" ou ", $rolesAutorises) . ".";
        echo "</div>";
        exit;
    }
}

function utilisateurConnecte()
{
    return $_SESSION["utilisateur"] ?? null;
}