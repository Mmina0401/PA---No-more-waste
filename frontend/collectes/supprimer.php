<?php
session_start();

require_once "../includes/api.php";

if (isset($_GET["id"])) {

    API::delete("/api/collectes/delete", [
        "id_collecte" => (int)$_GET["id"]
    ]);
}

echo "<script>window.location='index.php';</script>";
exit;