<?php
// ADMIN COLLECTES — Liste principale — Collectes actives + actions

session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

$collectes = API::get("/api/collectes");


if (!is_array($collectes)) {
    $collectes = [];
}

$collectes = array_values(array_filter($collectes, function ($collecte) {
    return !in_array(
        $collecte["statut"] ?? "",
        ["TERMINEE", "ANNULEE"],
        true
    );
}));

$offres = API::get("/api/offres-benevoles");

if (!is_array($offres)) {
    $offres = [];
}

$offresParCollecte = [];

foreach ($offres as $offre) {
    if (($offre["type_evenement"] ?? "") !== "COLLECTE") {
        continue;
    }

    $idCollecte = (int) ($offre["id_evenement"] ?? 0);

    if ($idCollecte <= 0) {
        continue;
    }

    $offresParCollecte[$idCollecte] = $offre;
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

<h2>Gestion des Collectes</h2>
<hr>


<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <p class="text-muted mb-0">
        Les demandes de collecte sont créées depuis l'espace public par un particulier ou un commerçant.
    </p>

    <a href="historique.php" class="btn btn-outline-secondary">
        <i class="fa-solid fa-clock-rotate-left me-1"></i>
        Historique
    </a>
</div>

<?php if (empty($collectes)): ?>

<div class="alert alert-light border">
    Aucune collecte disponible.
</div>

<?php else: ?>

<div class="table-responsive">

<table class="table table-bordered table-striped align-middle">

<thead class="table-dark">
<tr>
    <th>ID</th>
    <th>Demandeur</th>
    <th>Véhicule attribué</th>
    <th>Date</th>
    <th>Horaires</th>
    <th>Ville</th>
    <th>Statut</th>
    <th>Offre bénévoles</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

<?php foreach ($collectes as $c): ?>

<?php
$idCollecte = (int) ($c["id_collecte"] ?? 0);
$statut = $c["statut"] ?? "";
$offre = $offresParCollecte[$idCollecte] ?? null;

$peutCreerOffre = in_array(
    $statut,
    ["VALIDEE", "PLANIFIEE", "EN_COURS"],
    true
);
?>

<tr>

<td>
    <?= $idCollecte ?>
</td>

<td>
    <?php
    $nomDemandeur = trim(
        ($c["demandeur_prenom"] ?? "") . " " .
        ($c["demandeur_nom"] ?? "")
    );
    $raisonSociale = trim((string) ($c["demandeur_raison_sociale"] ?? ""));
    ?>
    <strong><?= htmlspecialchars($raisonSociale !== "" ? $raisonSociale : ($nomDemandeur ?: "—")) ?></strong>

    <?php if ($raisonSociale !== "" && $nomDemandeur !== ""): ?>
        <div class="small text-muted"><?= htmlspecialchars($nomDemandeur) ?></div>
    <?php endif; ?>

    <div class="small text-muted">
        <?= htmlspecialchars($c["demandeur_email"] ?? "") ?>
    </div>
</td>

<td>
    <?php if (!empty($c["id_vehicule"])): ?>
        <strong><?= htmlspecialchars($c["vehicule_immatriculation"] ?? ("Véhicule #" . $c["id_vehicule"])) ?></strong>
        <div class="small text-muted">
            <?= htmlspecialchars(trim(
                ($c["vehicule_marque"] ?? "") . " " .
                ($c["vehicule_modele"] ?? "")
            )) ?>
        </div>
    <?php else: ?>
        <span class="text-muted">Non attribué</span>
    <?php endif; ?>
</td>

<td>
<?php
$dateCollecte = substr(
    $c["date_collecte"] ?? "",
    0,
    10
);

if ($dateCollecte !== "") {
    $timestamp = strtotime($dateCollecte);

    echo htmlspecialchars(
        $timestamp
            ? date("d/m/Y", $timestamp)
            : $dateCollecte
    );
} else {
    echo "—";
}
?>
</td>

<td>
<?php
$heureDebut = substr(
    $c["heure_debut"] ?? "",
    0,
    5
);

$heureFin = substr(
    $c["heure_fin"] ?? "",
    0,
    5
);
?>

<?= htmlspecialchars($heureDebut ?: "—") ?>
-
<?= htmlspecialchars($heureFin ?: "—") ?>
</td>

<td>
    <?= htmlspecialchars($c["ville"] ?? "—") ?>
</td>

<td>

<?php if ($statut === "EN_ATTENTE"): ?>

<span class="badge bg-warning text-dark">
    En attente
</span>

<?php elseif ($statut === "VALIDEE"): ?>

<span class="badge bg-info text-dark">
    Validée
</span>

<?php elseif ($statut === "PLANIFIEE"): ?>

<span class="badge bg-success">
    Planifiée
</span>

<?php elseif ($statut === "EN_COURS"): ?>

<span class="badge bg-primary">
    En cours
</span>

<?php elseif ($statut === "TERMINEE"): ?>

<span class="badge bg-secondary">
    Terminée
</span>

<?php elseif ($statut === "ANNULEE"): ?>

<span class="badge bg-danger">
    Annulée
</span>

<?php else: ?>

<span class="badge bg-light text-dark">
    <?= htmlspecialchars($statut) ?>
</span>

<?php endif; ?>

</td>

<td>

<?php if ($offre): ?>

<?php if (($offre["statut"] ?? "") === "OUVERTE"): ?>

<span class="badge bg-success mb-2">
    Offre ouverte
</span>

<?php else: ?>

<span class="badge bg-secondary mb-2">
    <?= htmlspecialchars(
        $offre["statut"] ?? ""
    ) ?>
</span>

<?php endif; ?>

<div>

<a
    href="/offres-benevoles/reponses.php?id=<?= (int) ($offre["id_offre"] ?? 0) ?>"
    class="btn btn-outline-success btn-sm"
>
    <i class="fa-solid fa-users me-1"></i>
    Voir les réponses
</a>

</div>

<?php elseif ($peutCreerOffre): ?>

<a
    href="/offres-benevoles/formulaire.php?type=COLLECTE&id=<?= $idCollecte ?>"
    class="btn btn-primary btn-sm"
>
    <i class="fa-solid fa-bullhorn me-1"></i>
    Créer une offre
</a>

<?php else: ?>

<span class="text-muted">
    Non disponible
</span>

<?php endif; ?>

</td>

<td>

<div class="d-flex gap-2 flex-wrap">

<a
    href="modifier.php?id=<?= $idCollecte ?>"
    class="btn btn-warning btn-sm"
>
    Gérer
</a>

<a
    href="supprimer.php?id=<?= $idCollecte ?>"
    class="btn btn-danger btn-sm"
    onclick="return confirm('Supprimer cette collecte ?');"
>
    Supprimer
</a>

</div>

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