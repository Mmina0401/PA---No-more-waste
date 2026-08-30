<?php
// ADMIN COMMERCANTS — Liste et gestion des comptes commerçants

session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "statut") {
    API::post("/api/commercants/statut", [
        "id_utilisateur" => (int) ($_POST["id"] ?? 0),
        "statut_compte" => $_POST["statut_compte"] ?? "",
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

$statutCompte = $c["statut_compte"] ?? (!empty($c["actif"]) ? "ACTIF" : "EN_ATTENTE");

$dateDebut = !empty($derniere["date_debut"]) ? strtotime($derniere["date_debut"]) : false;
$dateFin   = !empty($derniere["date_fin"]) ? strtotime($derniere["date_fin"]) : false;
$dateAujourdhui = strtotime($aujourdhui);

$adhesionValide = $derniere
    && ($derniere["statut"] ?? "") === "ACTIVE"
    && $dateDebut !== false
    && $dateFin !== false
    && $dateDebut <= $dateAujourdhui
    && $dateFin >= $dateAujourdhui;

$adhesionActive = $statutCompte === "ACTIF"
    && !empty($c["actif"])
    && $adhesionValide;
?>

<tr>
    <td><?= htmlspecialchars($c["raison_sociale"] ?? "—") ?></td>
    <td><?= htmlspecialchars($c["siret"] ?? "—") ?></td>
    <td><?= htmlspecialchars(trim(($c["prenom"] ?? "") . " " . ($c["nom"] ?? ""))) ?></td>
    <td><?= htmlspecialchars($c["ville"] ?? "—") ?></td>

    <td>
        <?php if ($statutCompte === "ACTIF"): ?>
            <span class="badge bg-success">Validé</span>
        <?php elseif ($statutCompte === "DESACTIVE"): ?>
            <span class="badge bg-danger">Désactivé</span>
        <?php else: ?>
            <span class="badge bg-warning text-dark">En attente</span>
        <?php endif; ?>
    </td>

    <td>
        <?php if ($adhesionActive): ?>
            <span class="badge bg-success">Adhérent actif</span>
        <?php elseif ($statutCompte === "DESACTIVE" && $adhesionValide): ?>
            <span class="badge bg-secondary">Adhésion suspendue</span>
        <?php elseif ($statutCompte === "EN_ATTENTE" && $adhesionValide): ?>
            <span class="badge bg-warning text-dark">Adhésion en attente du compte</span>
        <?php elseif ($derniere): ?>
            <?php
            $statutAdhesion = $derniere["statut"] ?? "";
            $libelleAdhesion = match ($statutAdhesion) {
                "EXPIREE" => "Adhésion expirée",
                "EN_ATTENTE" => "Adhésion en attente",
                "ACTIVE" => "Adhésion hors période",
                default => "Adhésion inactive",
            };
            ?>
            <span class="badge bg-secondary">
                <?= htmlspecialchars($libelleAdhesion) ?>
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

        <?php if ($statutCompte === "EN_ATTENTE"): ?>
            <form method="post" class="d-inline">
                <input type="hidden" name="action" value="statut">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="statut_compte" value="ACTIF">
                <button type="submit" class="btn btn-success btn-sm">
                    Valider le compte
                </button>
            </form>

        <?php elseif ($statutCompte === "ACTIF"): ?>
            <form method="post" class="d-inline"
                  onsubmit="return confirm('Désactiver ce compte commerçant ? Son historique et son adhésion seront conservés, mais il ne pourra plus se connecter.');">
                <input type="hidden" name="action" value="statut">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="statut_compte" value="DESACTIVE">
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    Désactiver
                </button>
            </form>

        <?php else: ?>
            <form method="post" class="d-inline">
                <input type="hidden" name="action" value="statut">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="statut_compte" value="ACTIF">
                <button type="submit" class="btn btn-outline-success btn-sm">
                    Réactiver
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
