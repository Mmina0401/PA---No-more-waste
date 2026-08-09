<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

$id = $_GET["id"] ?? null;
$s = ["nom" => "", "description" => "", "lieu" => "", "date_service" => "", "heure_debut" => "", "heure_fin" => "", "capacite_max" => "", "statut" => "OUVERT"];

if ($id) {
    $s = API::get("/api/services/get?id=" . urlencode($id));
    if (!$s || isset($s["error"])) {
        header("Location: index.php");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $payload = [
        "nom"          => trim($_POST["nom"]),
        "description"  => trim($_POST["description"]) ?: null,
        "lieu"         => trim($_POST["lieu"]) ?: null,
        "date_service" => $_POST["date_service"] ?: null,
        "heure_debut"  => $_POST["heure_debut"] ?: null,
        "heure_fin"    => $_POST["heure_fin"] ?: null,
        "capacite_max" => $_POST["capacite_max"] !== "" ? (int) $_POST["capacite_max"] : null,
    ];

    if ($id) {
        $payload["id_service"] = (int) $id;
        $payload["statut"] = $_POST["statut"];
        API::post("/api/services/update", $payload);
    } else {
        $payload["statut"] = "OUVERT";
        API::post("/api/services/create", $payload);
    }

    header("Location: index.php");
    exit;
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

<h2><?= $id ? "Modifier le Service" : "Nouveau Service" ?></h2>
<hr>

<form method="post" class="col-md-6">
    <div class="mb-3">
        <label class="form-label">Nom du service</label>
        <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($s["nom"] ?? "") ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"><?= htmlspecialchars($s["description"] ?? "") ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Lieu</label>
        <input type="text" name="lieu" class="form-control" value="<?= htmlspecialchars($s["lieu"] ?? "") ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Date</label>
        <input type="date" name="date_service" class="form-control" value="<?= htmlspecialchars($s["date_service"] ?? "") ?>">
    </div>

    <div class="row">
        <div class="col mb-3">
            <label class="form-label">Heure de début</label>
            <input type="time" name="heure_debut" class="form-control" value="<?= htmlspecialchars(substr($s["heure_debut"] ?? "", 0, 5)) ?>">
        </div>

        <div class="col mb-3">
            <label class="form-label">Heure de fin</label>
            <input type="time" name="heure_fin" class="form-control" value="<?= htmlspecialchars(substr($s["heure_fin"] ?? "", 0, 5)) ?>">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Capacité maximale <?= $id ? "" : "(laisser vide = illimité)" ?></label>
        <input type="number" name="capacite_max" class="form-control" min="1" value="<?= htmlspecialchars($s["capacite_max"] ?? "") ?>">
    </div>

    <?php if ($id): ?>
    <div class="mb-3">
        <label class="form-label">Statut</label>
        <select name="statut" class="form-select">
            <option value="OUVERT" <?= $s["statut"] === "OUVERT" ? "selected" : "" ?>>Ouvert</option>
            <option value="COMPLET" <?= $s["statut"] === "COMPLET" ? "selected" : "" ?>>Complet</option>
            <option value="ANNULE" <?= $s["statut"] === "ANNULE" ? "selected" : "" ?>>Annulé</option>
        </select>
    </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-success"><?= $id ? "Enregistrer" : "Créer le service" ?></button>
    <a href="index.php" class="btn btn-secondary">Annuler</a>
</form>

</div>
</div>
</div>

<?php include "../includes/footer.php"; ?>