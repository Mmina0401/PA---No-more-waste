<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete") {
    API::post("/api/livraisons/delete", ["id_livraison" => (int) $_POST["id"]]);
    header("Location: index.php");
    exit;
}

$tournees = API::get("/api/livraisons");
if (!is_array($tournees)) $tournees = [];

function badgeStatutTournee($statut) {
    return match ($statut) {
        "PLANIFIEE" => '<span class="badge bg-secondary">Planifiée</span>',
        "EN_COURS"  => '<span class="badge bg-warning text-dark">En cours</span>',
        "TERMINEE"  => '<span class="badge bg-success">Terminée</span>',
        "ANNULEE"   => '<span class="badge bg-danger">Annulée</span>',
        default     => '<span class="badge bg-light text-dark">' . htmlspecialchars($statut) . '</span>',
    };
}

include "../includes/header.php";
include "../includes/navbar.php";
?>

<div class="container-fluid">
<div class="row">
<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2>Tournées de distribution</h2>
<hr>

<a href="formulaire.php" class="btn btn-success mb-3">+ Nouvelle Tournée</a>

<?php if (empty($tournees)): ?>
    <div class="alert alert-light border">Aucune tournée programmée pour le moment.</div>
<?php else: ?>
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>Association bénéficiaire</th>
<th>Véhicule</th>
<th>Date</th>
<th>Horaires</th>
<th>Statut</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($tournees as $t): ?>
<tr>
<td><?= htmlspecialchars($t["nom_association"]) ?></td>
<td><?= htmlspecialchars($t["immatriculation"]) ?></td>
<td><?= htmlspecialchars($t["date_livraison"]) ?></td>
<td><?= htmlspecialchars(substr($t["heure_depart"] ?? "", 0, 5)) ?> - <?= htmlspecialchars(substr($t["heure_retour"] ?? "", 0, 5)) ?></td>
<td><?= badgeStatutTournee($t["statut"]) ?></td>
<td>
<a href="formulaire.php?id=<?= $t["id_livraison"] ?>" class="btn btn-warning btn-sm">Gérer</a>

<a href="pdf.php?id=<?= $t["id_livraison"] ?>"class="btn btn-primary btn-sm">PDF</a>

<form method="post" style="display:inline;" onsubmit="return confirm('Supprimer cette tournée ?');">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?= $t["id_livraison"] ?>">
<button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

</div>
</div>
</div>

<?php include "../includes/footer.php"; ?>