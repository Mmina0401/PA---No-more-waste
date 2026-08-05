<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

include "../includes/header.php";
include "../includes/navbar.php";
require_once "../includes/api.php";

$commercants = API::get("/api/utilisateurs?role=COMMERCANT");
$adhesions   = API::get("/api/adhesions");

$statutParCommercant = [];
foreach ($adhesions as $a) {
    $id = $a["id_utilisateur"];
    if (!isset($statutParCommercant[$id]) || $a["date_fin"] > $statutParCommercant[$id]["date_fin"]) {
        $statutParCommercant[$id] = $a;
    }
}
?>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2>Gestion des Commerçants</h2>

<hr>

<a href="ajouter.php" class="btn btn-success mb-3">
+ Nouveau Commerçant
</a>

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th>Raison sociale</th>
<th>SIRET</th>
<th>Contact</th>
<th>Ville</th>
<th>Adhésion</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php foreach ($commercants as $c): $statut = $statutParCommercant[$c["id_utilisateur"]]["statut"] ?? "Aucune"; ?>

<tr>

<td><?= htmlspecialchars($c["raison_sociale"] ?? "—") ?></td>

<td><?= htmlspecialchars($c["siret"] ?? "—") ?></td>

<td><?= htmlspecialchars($c["prenom"] . " " . $c["nom"]) ?></td>

<td><?= htmlspecialchars($c["ville"] ?? "—") ?></td>

<td><?= htmlspecialchars($statut) ?></td>

<td>

<a href="adhesions.php?id=<?= $c["id_utilisateur"] ?>"
class="btn btn-outline-success btn-sm">

Adhésions

</a>

<a href="modifier.php?id=<?= $c["id_utilisateur"] ?>"
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