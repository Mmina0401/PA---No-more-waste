<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete") {
    API::post("/api/services/delete", ["id_service" => (int) $_POST["id"]]);
    header("Location: index.php");
    exit;
}

$services = API::get("/api/services");
if (!is_array($services)) $services = [];

function badgeStatutService($statut) {
    return match ($statut) {
        "OUVERT"  => '<span class="badge bg-success">Ouvert</span>',
        "COMPLET" => '<span class="badge bg-warning text-dark">Complet</span>',
        "ANNULE"  => '<span class="badge bg-secondary">Annulé</span>',
        default   => '<span class="badge bg-light text-dark">'.htmlspecialchars($statut).'</span>',
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

<h2>Services & Créneaux</h2>
<hr>

<a href="formulaire.php" class="btn btn-success mb-3">+ Nouveau Service</a>

<?php if (empty($services)): ?>
    <div class="alert alert-light border">Aucun service programmé pour le moment.</div>
<?php else: ?>
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
    <th>Service</th>
    <th>Date</th>
    <th>Horaires</th>
    <th>Lieu</th>
    <th>Places</th>
    <th>Statut</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($services as $s): ?>
<tr>
    <td><?= htmlspecialchars($s["nom"]) ?></td>
    <td><?= htmlspecialchars($s["date_service"] ?? '—') ?></td>
    <td><?= htmlspecialchars(substr($s["heure_debut"] ?? '', 0, 5)) ?> - <?= htmlspecialchars(substr($s["heure_fin"] ?? '', 0, 5)) ?></td>
    <td><?= htmlspecialchars($s["lieu"] ?? '—') ?></td>
    <td>
        <?= (int) ($s["nb_inscrits"] ?? 0) ?><?= isset($s["capacite_max"]) && $s["capacite_max"] !== null ? '/' . (int) $s["capacite_max"] : '' ?>
    </td>
    <td><?= badgeStatutService($s["statut"]) ?></td>
    <td>
        <a href="inscriptions.php?id=<?= $s["id_service"] ?>" class="btn btn-outline-success btn-sm">Inscriptions</a>
        <a href="formulaire.php?id=<?= $s["id_service"] ?>" class="btn btn-warning btn-sm">Modifier</a>
        <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce service ?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $s["id_service"] ?>">
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