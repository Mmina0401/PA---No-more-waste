<?php
session_start();

require_once __DIR__ . "/../includes/auth.php";

exigerRole("ADMIN", "RESPONSABLE");

$token = $_SESSION["token"] ?? $_SESSION["jeton"] ?? "";

if ($token === "") {
    die("Token de connexion introuvable.");
}

$ch = curl_init("http://localhost:8080/api/planning/export");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $token
]);

$data = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($data === false) {
    die("Impossible de contacter l'API.");
}

if ($httpCode !== 200) {
    die("Erreur API : " . htmlspecialchars($data));
}

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=planning.xlsx");
header("Content-Length: " . strlen($data));

echo $data;
exit;