<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

$id = $_GET["id"] ?? null;
$t = ["id_association" => "", "id_vehicule" => "", "date_livraison" => "", "heure_depart" => "", "heure_retour" => "", "statut" => "PLANIFIEE", "commentaire" => ""];

if ($id) {
    $t = API::get("/api/livraisons/get?id=" . $id);
    if (!$t || isset($t["error"])) {
        header("Location: index.php");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "enregistrer";

    if ($action === "ajouter-produit") {
        API::post("/api/lignes-livraison/create", [
            "id_livraison" => (int) $id,
            "id_produit"   => (int) $_POST["id_produit"],
            "quantite"     => (float) $_POST["quantite"],
        ]);
        header("Location: formulaire.php?id=" . $id);
        exit;
    }

    if ($action === "retirer-produit") {
        API::post("/api/lignes-livraison/delete", [
            "id_livraison" => (int) $id,
            "id_produit"   => (int) $_POST["id_produit"],
        ]);
        header("Location: formulaire.php?id=" . $id);
        exit;
    }

    $payload = [
        "id_association" => (int) $_POST["id_association"],
        "id_vehicule"    => (int) $_POST["id_vehicule"],
        "date_livraison" => $_POST["date_livraison"],
        "heure_depart"   => $_POST["heure_depart"] ?: null,
        "heure_retour"   => $_POST["heure_retour"] ?: null,
        "commentaire"    => trim($_POST["commentaire"]) ?: null,
    ];

    if ($id) {
        $payload["id_livraison"] = (int) $id;
        $payload["statut"] = $_POST["statut"];
        API::post("/api/livraisons/update", $payload);
        header("Location: formulaire.php?id=" . $id);
        exit;
    } else {
        $payload["statut"] = "PLANIFIEE";
        $res = API::post("/api/livraisons/create", $payload);
        header("Location: formulaire.php?id=" . $res["id_livraison"]);
        exit;
    }
}

$associations = API::get("/api/associations");
$vehicules    = API::get("/api/vehicules");
if (!is_array($associations)) $associations = [];
if (!is_array($vehicules))    $vehicules = [];

$lignes = [];
$produits = [];
if ($id) {
    $lignes = API::get("/api/lignes-livraison?id_livraison=" . $id);
    if (!is_array($lignes)) $lignes = [];
    $produits = API::get("/api/produits");
    if (!is_array($produits)) $produits = [];
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

<h2><?= $id ? "Gérer la Tournée" : "Nouvelle Tournée" ?></h2>
<hr>

<form method="post" class="col-md-6 mb-4">

<div class="mb-3">
<label class="form-label">Association bénéficiaire</label>
<select name="id_association" class="form-select" required>
<option value="">-- Choisir --</option>
<?php foreach ($associations as $a): ?>
<option value="<?= $a["id_association"] ?>" <?= ($t["id_association"] ?? "") == $a["id_association"] ? "selected" : "" ?>>
<?= htmlspecialchars($a["nom"]) ?> (<?= htmlspecialchars($a["ville"] ?? "") ?>)
</option>
<?php endforeach; ?>
</select>
</div>

<div class="mb-3">
<label class="form-label">Véhicule</label>
<select name="id_vehicule" class="form-select" required>
<option value="">-- Choisir --</option>
<?php foreach ($vehicules as $v): ?>
<option value="<?= $v["id_vehicule"] ?>" <?= ($t["id_vehicule"] ?? "") == $v["id_vehicule"] ? "selected" : "" ?>>
<?= htmlspecialchars($v["immatriculation"] . " — " . $v["marque"] . " " . $v["modele"]) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="mb-3">
<label class="form-label">Date</label>
<input type="date" name="date_livraison" class="form-control" required value="<?= htmlspecialchars($t["date_livraison"] ?? "") ?>">
</div>

<div class="row">
<div class="col mb-3">
<label class="form-label">Heure de départ</label>
<input type="time" name="heure_depart" class="form-control" value="<?= htmlspecialchars(substr($t["heure_depart"] ?? "", 0, 5)) ?>">
</div>
<div class="col mb-3">
<label class="form-label">Heure de retour</label>
<input type="time" name="heure_retour" class="form-control" value="<?= htmlspecialchars(substr($t["heure_retour"] ?? "", 0, 5)) ?>">
</div>
</div>

<?php if ($id): ?>
<div class="mb-3">
<label class="form-label">Statut</label>
<select name="statut" class="form-select">
<option value="PLANIFIEE" <?= $t["statut"] === "PLANIFIEE" ? "selected" : "" ?>>Planifiée</option>
<option value="EN_COURS" <?= $t["statut"] === "EN_COURS" ? "selected" : "" ?>>En cours</option>
<option value="TERMINEE" <?= $t["statut"] === "TERMINEE" ? "selected" : "" ?>>Terminée</option>
<option value="ANNULEE" <?= $t["statut"] === "ANNULEE" ? "selected" : "" ?>>Annulée</option>
</select>
</div>
<?php endif; ?>

<div class="mb-3">
<label class="form-label">Commentaire</label>
<textarea name="commentaire" class="form-control"><?= htmlspecialchars($t["commentaire"] ?? "") ?></textarea>
</div>

<button type="submit" class="btn btn-success"><?= $id ? "Enregistrer" : "Créer la tournée" ?></button>
<a href="index.php" class="btn btn-secondary">Annuler</a>

</form>

<?php if ($id): ?>

<h5>Produits chargés</h5>

<form method="post" class="row g-2 align-items-end mb-3">
<input type="hidden" name="action" value="ajouter-produit">
<div class="col-auto">
<select name="id_produit" class="form-select" required>
<option value="">-- Choisir un produit --</option>
<?php foreach ($produits as $p): ?>
<option value="<?= $p["id_produit"] ?>"><?= htmlspecialchars($p["nom"]) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-auto">
<input type="number" step="0.01" name="quantite" class="form-control" placeholder="Quantité" required>
</div>
<div class="col-auto">
<button type="submit" class="btn btn-success">Ajouter</button>
</div>
</form>

<?php if (empty($lignes)): ?>
<div class="alert alert-light border">Aucun produit chargé dans cette tournée.</div>
<?php else: ?>
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr><th>Produit</th><th>Quantité</th><th></th></tr>
</thead>
<tbody>
<?php foreach ($lignes as $l): ?>
<tr>
<td><?= htmlspecialchars($l["nom_produit"]) ?></td>
<td><?= $l["quantite"] ?></td>
<td>
<form method="post" style="display:inline;">
<input type="hidden" name="action" value="retirer-produit">
<input type="hidden" name="id_produit" value="<?= $l["id_produit"] ?>">
<button type="submit" class="btn btn-danger btn-sm">Retirer</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

<?php endif; ?>

</div>
</div>
</div>

<?php include "../includes/footer.php"; ?>