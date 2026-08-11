<?php

session_start();

require_once "../includes/auth.php";

$id = (int)($_GET["id"] ?? 0);

header("Location: http://localhost:8080/api/livraisons/pdf?id=" . $id);
exit;