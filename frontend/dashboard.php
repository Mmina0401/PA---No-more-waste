<?php
session_start();
require_once __DIR__ . "/includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

include "includes/header.php";
include "includes/navbar.php";
require_once "includes/api.php";

$produits  = API::get("/api/produits");
$stocks    = API::get("/api/stocks");
$collectes = API::get("/api/collectes");

$nbProduits  = is_array($produits)  ? count($produits)  : 0;
$nbStocks    = is_array($stocks)    ? count($stocks)    : 0;
$nbCollectes = is_array($collectes) ? count($collectes) : 0;
?>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 p-0">
<?php include "includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2>Bienvenue sur No More Waste</h2>

<p>Tableau de bord <?= htmlspecialchars($_SESSION["utilisateur"]["role"]) ?></p>

<div class="row mt-4">

<div class="col-md-4">
<div class="card">
<div class="card-body">
<h5>Collectes</h5>
<h1><?= $nbCollectes ?></h1>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card">
<div class="card-body">
<h5>Stocks</h5>
<h1><?= $nbStocks ?></h1>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card">
<div class="card-body">
<h5>Produits</h5>
<h1><?= $nbProduits ?></h1>
</div>
</div>
</div>

</div>

</div>

</div>

</div>

<?php include "includes/footer.php"; ?>