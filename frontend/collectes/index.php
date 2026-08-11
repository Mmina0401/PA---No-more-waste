<?php
session_start();

include "../includes/header.php";
include "../includes/navbar.php";
require_once "../includes/api.php";

$collectes = API::get("/api/collectes");
?>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2>Gestion des Collectes</h2>

<hr>

<a href="ajouter.php" class="btn btn-success mb-3">
+ Nouvelle collecte
</a>

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Utilisateur</th>
<th>Véhicule</th>
<th>Date</th>
<th>Ville</th>
<th>Statut</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php foreach($collectes as $c): ?>

<tr>

<td><?= $c["id_collecte"] ?></td>

<td><?= $c["id_utilisateur"] ?></td>

<td><?= $c["id_vehicule"] ?></td>

<td><?= substr($c["date_collecte"],0,10) ?></td>

<td><?= htmlspecialchars($c["ville"]) ?></td>

<td><?= htmlspecialchars($c["statut"]) ?></td>

<td>

<a href="modifier.php?id=<?= $c["id_collecte"] ?>" class="btn btn-warning">
    Modifier
</a>

<a
href="supprimer.php?id=<?= $c["id_collecte"] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Supprimer cette collecte ?');">
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