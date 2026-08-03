<?php
session_start();

include "../includes/header.php";
include "../includes/navbar.php";
require_once "../includes/api.php";

$stocks = API::get("/api/stocks");
?>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2>Gestion des Stocks</h2>

<hr>

<a href="ajouter.php" class="btn btn-success mb-3">
+ Nouveau Stock
</a>

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>ID Produit</th>
<th>Quantité</th>
<th>Emplacement</th>
<th>Date entrée</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php foreach($stocks as $stock): ?>

<tr>

<td><?= $stock["id_stock"] ?></td>

<td><?= $stock["id_produit"] ?></td>

<td><?= $stock["quantite"] ?></td>

<td><?= $stock["emplacement"] ?></td>

<td><?= substr($stock["date_entree"],0,10) ?></td>

<td>

<a href="modifier.php?id=<?= $stock["id_stock"] ?>"
class="btn btn-warning btn-sm">

Modifier

</a>

<a href="#"
class="btn btn-danger btn-sm">

Supprimer

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

<?php include "../includes/footer.php"; ?>