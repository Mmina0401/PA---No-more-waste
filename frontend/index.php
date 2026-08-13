<?php
session_start();

if (!isset($_SESSION["utilisateur"])) {
    header("Location: login.php");
    exit;
}

if (($_SESSION["utilisateur"]["role"] ?? "") === "BENEVOLE") {
    header("Location: /benevole/index.php");
    exit;
}

header("Location: dashboard.php");
exit;
