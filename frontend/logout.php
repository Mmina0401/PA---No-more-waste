<?php
// AUTHENTIFICATION — Déconnexion + destruction de la session

session_start();
session_destroy();
header("Location: /login.php");
exit;