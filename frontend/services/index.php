<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "delete"
) {
    API::post("/api/services/delete", [
        "id_service" => (int) ($_POST["id"] ?? 0)
    ]);

    header("Location: index.php");
    exit;
}

$services = API::get("/api/services");

if (!is_array($services)) {
    $services = [];
}

$offres = API::get("/api/offres-benevoles");

if (!is_array($offres)) {
    $offres = [];
}

$offresParService = [];

foreach ($offres as $offre) {
    if (($offre["type_evenement"] ?? "") !== "SERVICE") {
        continue;
    }

    $idService = (int) ($offre["id_evenement"] ?? 0);

    if ($idService <= 0) {
        continue;
    }

    $offresParService[$idService] = $offre;
}

function badgeStatutService($statut)
{
    return match ($statut) {
        "OUVERT" => '<span class="badge bg-success">Ouvert</span>',
        "COMPLET" => '<span class="badge bg-warning text-dark">Complet</span>',
        "ANNULE" => '<span class="badge bg-secondary">Annulé</span>',
        default => '<span class="badge bg-light text-dark">' .
            htmlspecialchars($statut) .
            '</span>',
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

<a href="formulaire.php" class="btn btn-success mb-3">
    + Nouveau Service
</a>

<?php if (empty($services)): ?>

<div class="alert alert-light border">
    Aucun service programmé pour le moment.
</div>

<?php else: ?>

<div class="table-responsive">

<table class="table table-bordered table-striped align-middle">

<thead class="table-dark">
<tr>
    <th>Service</th>
    <th>Date</th>
    <th>Horaires</th>
    <th>Lieu</th>
    <th>Places</th>
    <th>Statut</th>
    <th>Offre bénévoles</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

<?php foreach ($services as $s): ?>

<?php
$idService = (int) ($s["id_service"] ?? 0);
$statut = $s["statut"] ?? "";
$offre = $offresParService[$idService] ?? null;

$peutCreerOffre = in_array(
    $statut,
    ["OUVERT", "COMPLET"],
    true
);
?>

<tr>

<td>
    <?= htmlspecialchars($s["nom"] ?? "") ?>
</td>

<td>
    <?= htmlspecialchars($s["date_service"] ?? "—") ?>
</td>

<td>
    <?= htmlspecialchars(
        substr($s["heure_debut"] ?? "", 0, 5)
    ) ?>
    -
    <?= htmlspecialchars(
        substr($s["heure_fin"] ?? "", 0, 5)
    ) ?>
</td>

<td>
    <?= htmlspecialchars($s["lieu"] ?? "—") ?>
</td>

<td>
    <?= (int) ($s["nb_inscrits"] ?? 0) ?>

    <?php if (
        isset($s["capacite_max"]) &&
        $s["capacite_max"] !== null
    ): ?>

        /<?= (int) $s["capacite_max"] ?>

    <?php endif; ?>
</td>

<td>
    <?= badgeStatutService($statut) ?>
</td>

<td>

<?php if ($offre): ?>

    <?php if (($offre["statut"] ?? "") === "OUVERTE"): ?>

        <span class="badge bg-success mb-2">
            Offre ouverte
        </span>

    <?php else: ?>

        <span class="badge bg-secondary mb-2">
            <?= htmlspecialchars($offre["statut"] ?? "") ?>
        </span>

    <?php endif; ?>

    <div>
        <a
            href="/offres-benevoles/reponses.php?id=<?= (int) $offre["id_offre"] ?>"
            class="btn btn-outline-success btn-sm"
        >
            <i class="fa-solid fa-users"></i>
            Voir les réponses
        </a>
    </div>

<?php elseif ($peutCreerOffre): ?>

    <a
        href="/offres-benevoles/formulaire.php?type=SERVICE&id=<?= $idService ?>"
        class="btn btn-primary btn-sm"
    >
        <i class="fa-solid fa-users"></i>
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
    href="inscriptions.php?id=<?= $idService ?>"
    class="btn btn-outline-success btn-sm"
>
    Inscriptions
</a>

<a
    href="formulaire.php?id=<?= $idService ?>"
    class="btn btn-warning btn-sm"
>
    Modifier
</a>

<form
    method="post"
    class="d-inline"
    onsubmit="return confirm('Supprimer ce service ?');"
>
    <input
        type="hidden"
        name="action"
        value="delete"
    >

    <input
        type="hidden"
        name="id"
        value="<?= $idService ?>"
    >

    <button
        type="submit"
        class="btn btn-danger btn-sm"
    >
        Supprimer
    </button>
</form>

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