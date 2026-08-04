<?php
session_start();

include "../includes/header.php";
include "../includes/navbar.php";
require_once "../includes/api.php";

$id = $_GET["id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    API::post("/api/adhesions/create", [
        "id_utilisateur" => (int) $id,
        "date_debut"     => $_POST["date_debut"],
        "date_fin"       => $_POST["date_fin"],
        "montant"        => (float) $_POST["montant"],
        "statut"         => "ACTIVE",
    ]);

    header("Location: adhesions.php?id=" . $id);
    exit;
}

$commercant = API::get("/api/utilisateurs/get?id=" . $id);
$adhesions  = API::get("/api/adhesions?id_utilisateur=" . $id);
?>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2>Adhésions — <?= htmlspecialchars($commercant["raison_sociale"] ?? "") ?></h2>

<a href="index.php" class="btn btn-outline-secondary btn-sm mb-3">Retour</a>

<hr>

<h5>Renouveler l'adhésion</h5>

<form method="post" class="row g-2 align-items-end mb-4">

<div class="col-auto">
<label class="form-label">Début</label>
<input type="date" name="date_debut" class="form-control" required>
</div>

<div class="col-auto">
<label class="form-label">Fin</label>
<input type="date" name="date_fin" class="form-control" required>
</div>

<div class="col-auto">
<label class="form-label">Montant (€)</label>
<input type="number" step="0.01" name="montant" class="form-control" value="50" required>
</div>

<div class="col-auto">
<button type="submit" class="btn btn-success">Renouveler</button>
</div>

</form>

<h5>Historique</h5>

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th>Période</th>
<th>Montant</th>
<th>Statut</th>

</tr>

</thead>

<tbody>

<?php foreach ($adhesions as $a): ?>

<tr>

<td><?= $a["date_debut"] ?> → <?= $a["date_fin"] ?></td>

<td><?= $a["montant"] ?> €</td>

<td><?= htmlspecialchars($a["statut"]) ?></td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

<?php include "../includes/footer.php"; ?>