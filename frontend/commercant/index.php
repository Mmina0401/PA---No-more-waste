<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "statut") {
    API::post("/api/commercants/statut", [
        "id_utilisateur" => (int) ($_POST["id"] ?? 0),
        "actif" => ($_POST["actif"] ?? "0") === "1",
    ]);

    header("Location: index.php");
    exit;
}

$commercants = API::get("/api/utilisateurs?role=COMMERCANT");
$adhesions   = API::get("/api/adhesions");

if (!is_array($commercants)) $commercants = [];
if (!is_array($adhesions)) $adhesions = [];

$adhesionParCommercant = [];
$aujourdhui = date("Y-m-d");

foreach ($adhesions as $a) {
    $id = (int) ($a["id_utilisateur"] ?? 0);

    if (
        !isset($adhesionParCommercant[$id]) ||
        ($a["date_fin"] ?? "") > ($adhesionParCommercant[$id]["date_fin"] ?? "")
    ) {
        $adhesionParCommercant[$id] = $a;
    }
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

<h2>Gestion des commerçants</h2>
<p class="text-muted">
    Un commerçant devient adhérent lorsqu'il possède une adhésion annuelle active.
</p>

<hr>

<a href="formulaire.php" class="btn btn-success mb-3">
    + Nouveau commerçant
</a>

<table class="table table-bordered table-striped align-middle">

<thead class="table-dark">
<tr>
    <th>Raison sociale</th>
    <th>SIRET</th>
    <th>Contact</th>
    <th>Ville</th>
    <th>Compte</th>
    <th>Adhésion</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

<?php foreach ($commercants as $c): ?>
<?php
$id = (int) ($c["id_utilisateur"] ?? 0);
$derniere = $adhesionParCommercant[$id] ?? null;

$adhesionActive = $derniere
    && ($derniere["statut"] ?? "") === "ACTIVE"
    && ($derniere["date_debut"] ?? "") <= $aujourdhui
    && ($derniere["date_fin"] ?? "") >= $aujourdhui;
?>

<tr>
    <td><?= htmlspecialchars($c["raison_sociale"] ?? "—") ?></td>
    <td><?= htmlspecialchars($c["siret"] ?? "—") ?></td>
    <td><?= htmlspecialchars(trim(($c["prenom"] ?? "") . " " . ($c["nom"] ?? ""))) ?></td>
    <td><?= htmlspecialchars($c["ville"] ?? "—") ?></td>

    <td>
        <?php if (!empty($c["actif"])): ?>
            <span class="badge bg-success">Validé</span>
        <?php else: ?>
            <span class="badge bg-warning text-dark">En attente</span>
        <?php endif; ?>
    </td>

    <td>
        <?php if ($adhesionActive): ?>
            <span class="badge bg-success">Adhérent actif</span>
        <?php elseif ($derniere): ?>
            <span class="badge bg-secondary">
                <?= htmlspecialchars($derniere["statut"] ?? "Expirée") ?>
            </span>
        <?php else: ?>
            <span class="badge bg-light text-dark">Aucune adhésion</span>
        <?php endif; ?>
    </td>

    <td class="d-flex gap-1 flex-wrap">
        <a href="adhesions.php?id=<?= $id ?>" class="btn btn-outline-success btn-sm">
            Adhésions
        </a>

        <a href="formulaire.php?id=<?= $id ?>" class="btn btn-warning btn-sm">
            Modifier
        </a>

        <?php if (empty($c["actif"])): ?>
            <form method="post" class="d-inline">
                <input type="hidden" name="action" value="statut">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="actif" value="1">
                <button type="submit" class="btn btn-success btn-sm">
                    Valider le compte
                </button>
            </form>
        <?php else: ?>
            <form method="post" class="d-inline"
                  onsubmit="return confirm('Désactiver ce compte commerçant ? Son historique sera conservé.');">
                <input type="hidden" name="action" value="statut">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="actif" value="0">
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    Désactiver
                </button>
            </form>
        <?php endif; ?>
    </td>
</tr>

<?php endforeach; ?>

</tbody>
</table>

</div>
</div>
</div>

<?php include "../includes/footer.php"; ?>
