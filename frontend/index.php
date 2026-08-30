<?php
session_start();

if (!isset($_SESSION["utilisateur"])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION["utilisateur"]["role"] ?? "";

if ($role === "BENEVOLE") {
    header("Location: /benevole/dashboard.php");
    exit;
}

if ($role === "COMMERCANT") {
    header("Location: /commercant/dashboard.php");
    exit;
}

if (in_array($role, ["ADMIN", "RESPONSABLE"], true)) {
    header("Location: /dashboard.php");
    exit;
}

header("Location: /public/accueil.php");
exit;
