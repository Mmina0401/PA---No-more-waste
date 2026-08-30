<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

$collectes = API::get("/api/collectes");

if (!is_array($collectes)) {
    $collectes = [];
}

$collectesArchivees = array_values(array_filter($collectes, function ($collecte) {
    return in_array(
        $collecte["statut"] ?? "",
        ["TERMINEE", "ANNULEE"],
        true
    );
}));

usort($collectesArchivees, function ($a, $b) {
    return strcmp(
        (string) ($b["date_collecte"] ?? ""),
        (string) ($a["date_collecte"] ?? "")
    );
});

include "../includes/header.php";
include "../includes/navbar.php";
?>

<div class="container-fluid">
<div class="row">

<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h2 class="mb-1">Historique des collectes</h2>
        <p class="text-muted mb-0">
            Les collectes terminées et annulées sont conservées ici pour assurer la traçabilité.
        </p>
    </div>

    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i>
        Collectes en cours
    </a>
</div>

<hr>

<?php if (empty($collectesArchivees)): ?>

    <div class="alert alert-light border">
        Aucune collecte archivée pour le moment.
    </div>

<?php else: ?>

<div class="table-responsive">
<table class="table table-bordered table-striped align-middle">

<thead class="table-dark">
<tr>
    <th>ID</th>
    <th>Utilisateur</th>
    <th>Véhicule</th>
    <th>Date</th>
    <th>Horaires</th>
    <th>Ville</th>
    <th>Statut</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php foreach ($collectesArchivees as $c): ?>
<?php
$idCollecte = (int) ($c["id_collecte"] ?? 0);
$statut = $c["statut"] ?? "";

$dateCollecte = substr($c["date_collecte"] ?? "", 0, 10);
$timestamp = $dateCollecte !== "" ? strtotime($dateCollecte) : false;

$heureDebut = substr($c["heure_debut"] ?? "", 0, 5);
$heureFin = substr($c["heure_fin"] ?? "", 0, 5);
?>

<tr>
    <td><?= $idCollecte ?></td>

    <td><?= (int) ($c["id_utilisateur"] ?? 0) ?></td>

    <td>
        <?= !empty($c["id_vehicule"])
            ? (int) $c["id_vehicule"]
            : "—"
        ?>
    </td>

    <td>
        <?= htmlspecialchars(
            $timestamp
                ? date("d/m/Y", $timestamp)
                : ($dateCollecte ?: "—")
        ) ?>
    </td>

    <td>
        <?= htmlspecialchars($heureDebut ?: "—") ?>
        -
        <?= htmlspecialchars($heureFin ?: "—") ?>
    </td>

    <td><?= htmlspecialchars($c["ville"] ?? "—") ?></td>

    <td>
        <?php if ($statut === "TERMINEE"): ?>
            <span class="badge bg-secondary">Terminée</span>
        <?php else: ?>
            <span class="badge bg-danger">Annulée</span>
        <?php endif; ?>
    </td>

    <td>
        <a href="modifier.php?id=<?= $idCollecte ?>" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-eye me-1"></i>
            Consulter
        </a>
    </td>
</tr>

<?php endforeach; ?>

</tbody>
</table>
</div>

<?php endif; ?>

</div>
</div>
</div>

<?php include "../includes/footer.php"; ?>
