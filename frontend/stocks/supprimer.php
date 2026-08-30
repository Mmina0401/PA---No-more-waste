<?php
session_start();

require_once "../includes/api.php";

$id = (int)($_GET["id"] ?? 0);

if ($id > 0) {

    API::delete("/api/stocks/delete", [

        "id_stock" => $id

    ]);
}

echo "<script>window.location='index.php';</script>";
exit;