<?php

session_start();

require_once __DIR__ . "/../includes/auth.php";

exigerRole("ADMIN", "RESPONSABLE");

$id = (int) ($_GET["id"] ?? 0);
$token = $_SESSION["jeton"] ?? "";

if ($id <= 0) {
    die("Identifiant de livraison invalide.");
}

if ($token === "") {
    die("Token de connexion introuvable.");
}

$adresseAPI = getenv("ADRESSE_API");

if (!$adresseAPI) {
    $adresseAPI = "localhost:8080";
}

$url = "http://" . $adresseAPI . "/api/livraisons/pdf?id=" . $id;

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $token
]);

$data = curl_exec($ch);

if ($data === false) {
    die("Impossible de contacter l'API : " . curl_error($ch));
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($httpCode !== 200) {
    die("Erreur API : " . htmlspecialchars($data));
}

header("Content-Type: application/pdf");
header(
    "Content-Disposition: attachment; filename=livraison_" .
    $id .
    ".pdf"
);
header("Content-Length: " . strlen($data));

echo $data;
exit;