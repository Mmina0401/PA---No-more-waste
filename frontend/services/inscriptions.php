<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

include "../includes/header.php";
include "../includes/navbar.php";
require_once "../includes/api.php";

$idService = $_GET["id"] ?? null;
if (!$idService) { header("Location: index.php"); exit; }

$erreur = null;
$notice = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "inscrire") {
        $res = API::post("/api/inscriptions/create", [
            "id_service"     => (int) $idService,
            "id_utilisateur" => (int) $_POST["id_utilisateur"],
        ]);
        if (isset($res["error"])) {
            $erreur = "Impossible d'inscrire cette personne (déjà inscrite ou service complet).";
        } else {
            $notice = "Personne inscrite.";
        }
    } elseif ($action === "confirmer") {
        API::post("/api/inscriptions/update", [
            "id_service" => (int) $idService,
            "id_utilisateur" => (int) $_POST["id_utilisateur"],
            "statut" => "CONFIRME",
        ]);
        $notice = "Inscription confirmée.";
    } elseif ($action === "desinscrire") {
        API::post("/api/inscriptions/delete", [
            "id_service" => (int) $idService,
            "id_utilisateur" => (int) $_POST["id_utilisateur"],
        ]);
        $notice = "Personne désinscrite.";
    }
}

$service      = API::get("/api/services/get?id=" . urlencode($idService));
$inscriptions = API::get("/api/inscriptions?id_service=" . urlencode($idService));
$utilisateurs = API::get("/api/utilisateurs");
if (!is_array($inscriptions)) $inscriptions = [];
if (!is_array($utilisateurs)) $utilisateurs = [];

$idsInscrits = array_column($inscriptions, "id_utilisateur");
$disponibles = array_filter($utilisateurs, fn($u) => !in_array($u["id_utilisateur"], $idsInscrits));
?>

<div class="container-fluid">
<div class="row">
<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2>Inscriptions — <?= htmlspecialchars($service["nom"] ?? "") ?></h2>
<a href="index.php" class="btn btn-outline-secondary btn-sm mb-3">&laquo; Retour aux services</a>
<hr>

<?php if ($notice): ?><div class="alert alert-success"><?= htmlspecialchars($notice) ?></div><?php endif; ?>
<?php if ($erreur): ?><div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div><?php endif; ?>

<h5>Inscrire une personne</h5>
<form method="post" class="row g-2 align-items-end mb-4">
    <input type="hidden" name="action" value="inscrire">
    <div class="col-auto">
        <select name="id_utilisateur" class="form-select" required>
            <option value="">-- Choisir --</option>
            <?php foreach ($disponibles as $u): ?>
            <option value="<?= $u["id_utilisateur"] ?>">
                <?= htmlspecialchars($u["prenom"] . ' ' . $u["nom"]) ?> (<?= htmlspecialchars($u["role"]) ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-success">Inscrire</button>
    </div>
</form>

<h5>Liste des inscrits</h5>
<?php if (empty($inscriptions)): ?>
    <div class="alert alert-light border">Personne n'est encore inscrit à ce service.</div>
<?php else: ?>
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr><th>Nom</th><th>Inscrit le</th><th>Statut</th><th>Actions</th></tr>
</thead>
<tbody>
<?php foreach ($inscriptions as $i): ?>
<tr>
    <td><?= htmlspecialchars($i["prenom"] . ' ' . $i["nom"]) ?></td>
    <td><?= substr($i["date_inscription"], 0, 16) ?></td>
    <td>
        <?php if ($i["statut"] === "CONFIRME"): ?>
            <span class="badge bg-success">Confirmé</span>
        <?php elseif ($i["statut"] === "ANNULE"): ?>
            <span class="badge bg-secondary">Annulé</span>
        <?php else: ?>
            <span class="badge bg-warning text-dark">Inscrit</span>
        <?php endif; ?>
    </td>
    <td>
        <?php if ($i["statut"] !== "CONFIRME"): ?>
        <form method="post" style="display:inline;">
            <input type="hidden" name="action" value="confirmer">
            <input type="hidden" name="id_utilisateur" value="<?= $i["id_utilisateur"] ?>">
            <button type="submit" class="btn btn-outline-success btn-sm">Confirmer</button>
        </form>
        <?php endif; ?>
        <form method="post" style="display:inline;" onsubmit="return confirm('Désinscrire cette personne ?');">
            <input type="hidden" name="action" value="desinscrire">
            <input type="hidden" name="id_utilisateur" value="<?= $i["id_utilisateur"] ?>">
            <button type="submit" class="btn btn-danger btn-sm">Désinscrire</button>
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