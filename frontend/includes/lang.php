<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$languesAutorisees = ["fr", "en"];

if (isset($_GET["lang"]) && in_array($_GET["lang"], $languesAutorisees, true)) {
    $_SESSION["lang"] = $_GET["lang"];
}

$langue = $_SESSION["lang"] ?? "fr";

$fichier = __DIR__ . "/../lang/" . $langue . ".php";

$traductions = file_exists($fichier)
    ? require $fichier
    : require __DIR__ . "/../lang/fr.php";

function t(string $cle): string
{
    global $traductions;

    return $traductions[$cle] ?? $cle;
}