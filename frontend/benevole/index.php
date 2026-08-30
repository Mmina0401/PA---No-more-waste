<?php
// BENEVOLE — Point d'entrée — Redirection vers le tableau de bord

session_start();

header("Location: dashboard.php");
exit;