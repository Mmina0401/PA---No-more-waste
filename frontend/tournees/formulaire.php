<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

function messageErreurAPI($resultat, $messageParDefaut)
{
    $data = $resultat["data"] ?? [];

    if (is_array($data)) {
        if (!empty($data["message"])) {
            return $data["message"];
        }

        if (!empty($data["error"])) {
            return $data["error"];
        }
    }

    return $messageParDefaut;
}

$id = $_GET["id"] ?? null;
$messageErreur = null;
$messageSucces = isset($_GET["ok"])
    ? "Tournée enregistrée avec succès."
    : null;

$t = [
    "id_association" => "",
    "id_vehicule" => "",
    "date_livraison" => "",
    "heure_depart" => "",
    "heure_retour" => "",
    "statut" => "PLANIFIEE",
    "commentaire" => ""
];

if ($id) {
    $t = API::get("/api/livraisons/get?id=" . (int) $id);

    if (!$t || isset($t["error"])) {
        header("Location: index.php");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "enregistrer";

    if ($action === "ajouter-produit") {
        $resultat = API::postAvecStatut("/api/lignes-livraison/create", [
            "id_livraison" => (int) $id,
            "id_produit"   => (int) $_POST["id_produit"],
            "quantite"     => (float) $_POST["quantite"],
        ]);

        if ($resultat["status"] >= 200 && $resultat["status"] < 300) {
            header("Location: formulaire.php?id=" . (int) $id);
            exit;
        }

        $messageErreur = messageErreurAPI(
            $resultat,
            "Impossible d'ajouter ce produit à la tournée."
        );
    } elseif ($action === "retirer-produit") {
        $resultat = API::postAvecStatut("/api/lignes-livraison/delete", [
            "id_livraison" => (int) $id,
            "id_produit"   => (int) $_POST["id_produit"],
        ]);

        if ($resultat["status"] >= 200 && $resultat["status"] < 300) {
            header("Location: formulaire.php?id=" . (int) $id);
            exit;
        }

        $messageErreur = messageErreurAPI(
            $resultat,
            "Impossible de retirer ce produit de la tournée."
        );
    } else {
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

            $resultat = API::postAvecStatut("/api/livraisons/update", $payload);

            if ($resultat["status"] >= 200 && $resultat["status"] < 300) {
                header("Location: formulaire.php?id=" . (int) $id . "&ok=1");
                exit;
            }

            $messageErreur = messageErreurAPI(
                $resultat,
                "Impossible d'enregistrer la tournée."
            );

            // On garde les valeurs saisies dans le formulaire si l'API refuse
            // l'enregistrement (par exemple si le stock est insuffisant).
            $t = array_merge($t, $payload);
        } else {
            $payload["statut"] = "PLANIFIEE";
            $resultat = API::postAvecStatut("/api/livraisons/create", $payload);

            if (
                $resultat["status"] >= 200 &&
                $resultat["status"] < 300 &&
                !empty($resultat["data"]["id_livraison"])
            ) {
                header("Location: formulaire.php?id=" . (int) $resultat["data"]["id_livraison"]);
                exit;
            }

            $messageErreur = messageErreurAPI(
                $resultat,
                "Impossible de créer la tournée."
            );

            $t = array_merge($t, $payload);
        }
    }
}

$associations = API::get("/api/associations");
$vehicules    = API::get("/api/vehicules");

if (!is_array($associations)) {
    $associations = [];
}

if (!is_array($vehicules)) {
    $vehicules = [];
}

$lignes = [];
$produits = [];

if ($id) {
    $lignes = API::get("/api/lignes-livraison?id_livraison=" . (int) $id);
    if (!is_array($lignes)) {
        $lignes = [];
    }

    $produits = API::get("/api/produits");
    if (!is_array($produits)) {
        $produits = [];
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

<h2><?= $id ? "Gérer la Tournée" : "Nouvelle Tournée" ?></h2>
<hr>

<?php if ($messageErreur): ?>
<div class="alert alert-danger">
    <?= htmlspecialchars($messageErreur) ?>
</div>
<?php endif; ?>

<?php if ($messageSucces): ?>
<div class="alert alert-success">
    <?= htmlspecialchars($messageSucces) ?>
</div>
<?php endif; ?>

<?php if ($id && ($t["statut"] ?? "") === "TERMINEE"): ?>
<div class="alert alert-success">
    Cette tournée est terminée : les quantités livrées ont déjà été retirées du stock.
    Les produits chargés ne peuvent plus être modifiés.
</div>
<?php endif; ?>

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

<?php if (($t["statut"] ?? "") === "TERMINEE"): ?>
<input type="hidden" name="statut" value="TERMINEE">
<input type="text" class="form-control" value="Terminée" disabled>
<?php else: ?>
<select name="statut" class="form-select">
<option value="PLANIFIEE" <?= ($t["statut"] ?? "") === "PLANIFIEE" ? "selected" : "" ?>>Planifiée</option>
<option value="EN_COURS" <?= ($t["statut"] ?? "") === "EN_COURS" ? "selected" : "" ?>>En cours</option>
<option value="TERMINEE" <?= ($t["statut"] ?? "") === "TERMINEE" ? "selected" : "" ?>>Terminée</option>
<option value="ANNULEE" <?= ($t["statut"] ?? "") === "ANNULEE" ? "selected" : "" ?>>Annulée</option>
</select>
<?php endif; ?>
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

<?php if (($t["statut"] ?? "") !== "TERMINEE"): ?>
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
<input type="number" step="0.01" min="0.01" name="quantite" class="form-control" placeholder="Quantité" required>
</div>
<div class="col-auto">
<button type="submit" class="btn btn-success">Ajouter</button>
</div>
</form>
<?php endif; ?>

<?php if (empty($lignes)): ?>
<div class="alert alert-light border">Aucun produit chargé dans cette tournée.</div>
<?php else: ?>
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr><th>Produit</th><th>Quantité</th><th>Action</th></tr>
</thead>
<tbody>
<?php foreach ($lignes as $l): ?>
<tr>
<td><?= htmlspecialchars($l["nom_produit"]) ?></td>
<td><?= htmlspecialchars((string) $l["quantite"]) ?></td>
<td>
<?php if (($t["statut"] ?? "") !== "TERMINEE"): ?>
<form method="post" style="display:inline;">
<input type="hidden" name="action" value="retirer-produit">
<input type="hidden" name="id_produit" value="<?= $l["id_produit"] ?>">
<button type="submit" class="btn btn-danger btn-sm">Retirer</button>
</form>
<?php else: ?>
<span class="text-muted">Verrouillé</span>
<?php endif; ?>
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
